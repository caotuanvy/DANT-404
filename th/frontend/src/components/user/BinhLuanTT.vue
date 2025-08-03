<template>
  <div class="comments-section-container">
    <div class="comments-header">
      <div class="header-left">
        <i class="fa-solid fa-comments"></i>
        <span>Bình luận</span>
      </div>
      <div class="header-right">
        <button 
          class="sort-button" 
          :class="{ 'active': currentSort === 'newest' }"
          @click="changeSort('newest')"
        >
          Mới nhất
        </button>
        <button 
          class="sort-button"
          :class="{ 'active': currentSort === 'popular' }"
          @click="changeSort('popular')"
        >
          Phổ biến
        </button>
      </div>
    </div>

    <div class="comment-form-wrapper">
      <div class="user-avatar">
        <i class="fa-solid fa-user-circle"></i>
      </div>
      <div class="comment-form-content">
        <div 
          class="comment-input" 
          contenteditable="true" 
          @input="onInput"
          @paste="onPaste"
          @focus="onFocus"
          @blur="onBlur"
          :placeholder="placeholderText"
        ></div>

        <div class="custom-toolbar">
          <label for="file-upload" class="toolbar-icon" title="Chèn ảnh">
            <i class="fa-regular fa-image"></i>
          </label>
          <input type="file" id="file-upload" class="hidden-input" @change="handleImageFile" accept="image/*">
          
          <button class="toolbar-icon" @click="addLink" title="Chèn liên kết">
            <i class="fa-solid fa-link"></i>
          </button>
          
          <button class="toolbar-icon" @click="toggleEmojiPicker" title="Chèn Emoji">
            <i class="fa-regular fa-face-smile"></i>
          </button>
        </div>

        <div class="star-rating-container">
          <span class="star-label">Đánh giá của bạn:</span>
          <span 
            v-for="star in 5" 
            :key="star" 
            class="star-icon"
            :class="{ 'filled': star <= newComment.danh_gia }"
            @click="setRating(star)"
          >
            ★
          </span>
        </div>

        <div v-if="showEmojiPicker" class="emoji-picker-container">
          <span
            v-for="emoji in emojis"
            :key="emoji"
            @click="insertEmoji(emoji)"
            class="emoji-item"
          >
            {{ emoji }}
          </span>
        </div>

        <div v-if="showLinkInput" class="link-modal-overlay">
          <div class="link-modal">
            <p>Nhập URL liên kết:</p>
            <input 
              type="text" 
              v-model="linkUrl" 
              placeholder="https://example.com" 
              @keyup.enter="insertLinkFromInput"
            />
            <div class="modal-actions">
              <button @click="insertLinkFromInput" class="modal-button primary">Chèn</button>
              <button @click="cancelLink" class="modal-button">Hủy</button>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button
            type="submit"
            class="submit-button"
            :class="{ active: !isSubmitDisabled }"
            :disabled="isSubmitDisabled"
            @click="submitComment"
          >
            <i v-if="isSubmitting" class="fa-solid fa-spinner fa-spin"></i>
            <span v-else>Đăng</span>
          </button>
        </div>
      </div>
    </div>
    <div v-if="loading" class="info-message loading">
      <i class="fa-solid fa-spinner fa-spin"></i> Đang tải bình luận...
    </div>
    <div v-else-if="comments.length === 0" class="info-message no-comments">
      <i class="fa-regular fa-comment-dots"></i> Chưa có bình luận nào. Hãy là người đầu tiên!
    </div>
    <ul v-else class="comments-list">
      <li v-for="comment in comments" :key="comment.id" class="comment-item">
        <div class="comment-item-header">
          <div class="user-avatar">
            <img :src="comment.nguoi_dung.avatar || 'https://i.pravatar.cc/50?u=' + comment.nguoi_dung.nguoi_dung_id" alt="Avatar">
          </div>
          <div class="comment-meta">
            <div class="comment-author-info">
              <span class="comment-author">{{ comment.nguoi_dung.ho_ten || 'Người dùng' }}</span>
              <div v-if="comment.danh_gia" class="comment-rating">
                <span 
                  v-for="star in 5" 
                  :key="star"
                  class="star-icon"
                  :class="{ 'filled': star <= comment.danh_gia }"
                >
                  ★
                </span>
              </div>
            </div>
            <span class="comment-date">{{ timeAgo(comment.ngay_binh_luan) }}</span>
          </div>
        </div>
        
        <div class="comment-content" v-html="comment.noidung"></div>

        <div class="comment-actions">
          <button
            class="action-button"
            :class="{ liked: comment.liked }"
            @click="toggleLike(comment)"
          >
            <i :class="['fa-regular', 'fa-heart', { 'fa-solid': comment.liked, 'text-red': comment.liked }]"></i>
            <span>{{ comment.luot_thich }}</span>
          </button>
        <button
            class="action-button"
            :class="{ disliked: comment.disliked }"
            @click="toggleDislike(comment)"
          >
            <i :class="['fa-regular', 'fa-thumbs-down', { 'fa-solid': comment.disliked, 'text-blue': comment.disliked }]"></i>
            <span>{{ comment.luot_khong_thich || 0 }}</span>
          </button>
          <button class="action-button"><i class="fa-solid fa-reply"></i><span>Trả lời</span></button>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const user = JSON.parse(localStorage.getItem('user')) || {};
const comments = ref([]);
const loading = ref(false);
const isSubmitting = ref(false);
const placeholderText = ref('Viết bình luận...');
const showEmojiPicker = ref(false);
const showLinkInput = ref(false);
const linkUrl = ref('');

const currentSort = ref('newest'); 
const emojis = ref([
  '😀', '😂', '😅', '😇', '🥰', '😊', '😋', '😎', '🤩', '🥳',
  '😭', '😱', '😡', '👍', '👎', '❤️', '💔', '💯', '🔥', '🎉'
]);

const newComment = ref({
    tin_tuc_id: null,
    nguoi_dung_id: user.nguoi_dung_id || null,
    noidung: '',
    danh_gia: null, // Thêm biến này để lưu số sao
});

const isSubmitDisabled = computed(() => {
    // Chỉ vô hiệu hóa nút gửi nếu cả nội dung và đánh giá đều rỗng
    return isSubmitting.value || (!newComment.value.noidung.trim() && !newComment.value.danh_gia);
});

watch(() => route.params.slug, (newSlug) => {
    if (newSlug) {
      newComment.value.tin_tuc_id = null;
      fetchComments();
    }
}, { immediate: true });

async function getNewsIdFromSlug(slug) {
    try {
        const response = await axios.get(`http://localhost:8000/api/tintuc-cong-khai/slug/${slug}`);
        return response.data.id;
    } catch (error) {
        console.error("Lỗi khi lấy ID tin tức từ slug:", error);
        return null;
    }
}

async function fetchComments() {
    const newsId = await getNewsIdFromSlug(route.params.slug);
    if (!newsId) {
        comments.value = [];
        return;
    }
    newComment.value.tin_tuc_id = newsId;

    loading.value = true;
    try {
      let url = `http://localhost:8000/api/binh-luan/tin-tuc/${newsId}`;
      if (currentSort.value === 'popular') {
        url += '?sort_by=luot_thich';
      }

      const response = await axios.get(url);
      comments.value = response.data;
      comments.value.forEach(comment => {
          comment.liked = false;
          comment.disliked = false;
      });
    } catch (error) {
        console.error("Lỗi khi tải bình luận:", error);
    } finally {
        loading.value = false;
    }
}

function changeSort(sortBy) {
  currentSort.value = sortBy;
  fetchComments();
}

// Logic cho đánh giá sao
function setRating(star) {
  if (newComment.value.danh_gia === star) {
    newComment.value.danh_gia = null;
  } else {
    newComment.value.danh_gia = star;
  }
}

function onInput(event) {
    newComment.value.noidung = event.target.innerHTML;
}

function onFocus(event) {}
function onBlur(event) {}
function onPaste(event) {
    event.preventDefault();
    const text = event.clipboardData.getData('text/plain');
    document.execCommand('insertText', false, text);
}
function toggleEmojiPicker() {
    showEmojiPicker.value = !showEmojiPicker.value;
}
function insertEmoji(emoji) {
    const inputElement = document.querySelector('.comment-input');
    inputElement.focus();
    let currentContent = inputElement.innerHTML;
    currentContent += emoji;
    inputElement.innerHTML = currentContent;
    const selection = window.getSelection();
    const range = document.createRange();
    const childNodes = inputElement.childNodes;
    if (childNodes.length > 0) {
        range.setStartAfter(childNodes[childNodes.length - 1]);
        range.collapse(true);
        selection.removeAllRanges();
        selection.addRange(range);
    }
    newComment.value.noidung = currentContent;
    showEmojiPicker.value = false;
}
async function handleImageFile(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = async (e) => {
      const img = new Image();
      img.onload = () => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const maxWidth = 400;
        const scaleFactor = maxWidth / img.width;
        const newWidth = img.width > maxWidth ? maxWidth : img.width;
        const newHeight = img.width > maxWidth ? img.height * scaleFactor : img.height;
        canvas.width = newWidth;
        canvas.height = newHeight;
        ctx.drawImage(img, 0, 0, newWidth, newHeight);
        const resizedDataUrl = canvas.toDataURL(file.type);
        const inputElement = document.querySelector('.comment-input');
        const imgNode = document.createElement('img');
        imgNode.src = resizedDataUrl;
        imgNode.alt = 'Ảnh bình luận';
        inputElement.appendChild(imgNode);
        const brNode = document.createElement('br');
        inputElement.appendChild(brNode);
        const selection = window.getSelection();
        const range = document.createRange();
        range.setStartAfter(brNode);
        range.collapse(true);
        selection.removeAllRanges();
        selection.addRange(range);
        inputElement.focus();
        newComment.value.noidung = inputElement.innerHTML;
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}
async function uploadImageToServer(base64Data) {
    const blob = await fetch(base64Data).then(res => res.blob());
    const formData = new FormData();
    formData.append('file', blob, 'image.png');
    const token = localStorage.getItem('token'); 
    const response = await axios.post('http://localhost:8000/api/tinymce/upload-image', formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
            'Authorization': token ? `Bearer ${token}` : '',
        },
    });
    return response.data.location;
}
function addLink() {
    showLinkInput.value = true;
    linkUrl.value = '';
}
function insertLinkFromInput() {
    const url = linkUrl.value.trim();
    if (url) {
      const inputElement = document.querySelector('.comment-input');
      const linkNode = document.createElement('a');
      linkNode.href = url;
      linkNode.textContent = url;
      linkNode.target = '_blank';
      inputElement.appendChild(linkNode);
      inputElement.focus();
      newComment.value.noidung = inputElement.innerHTML;
    }
    showLinkInput.value = false;
    linkUrl.value = '';
}
function cancelLink() {
    showLinkInput.value = false;
    linkUrl.value = '';
}

async function submitComment() {
    if (isSubmitDisabled.value) {
      return;
    }
    isSubmitting.value = true;
    try {
      const inputElement = document.querySelector('.comment-input');
      const images = inputElement.querySelectorAll('img');
      if (images.length > 0) {
          for (const img of images) {
              const imageUrl = await uploadImageToServer(img.src);
              img.src = imageUrl;
          }
      }
      
      await axios.post(`http://localhost:8000/api/binh-luan/tin-tuc`, {
          tin_tuc_id: newComment.value.tin_tuc_id,
          nguoi_dung_id: newComment.value.nguoi_dung_id,
          noidung: inputElement.innerHTML,
          danh_gia: newComment.value.danh_gia, // Gửi giá trị đánh giá lên
      });
      
      fetchComments();
      newComment.value.noidung = '';
      newComment.value.danh_gia = null; // Reset giá trị đánh giá
      inputElement.innerHTML = '';
      inputElement.blur();
    } catch (error) {
      console.error("Lỗi khi gửi bình luận:", error);
      alert("Không thể gửi bình luận. Vui lòng thử lại.");
    } finally {
      isSubmitting.value = false;
    }
}
async function toggleLike(comment) {
    if (comment.liked) {
      return;
    }
    try {
      const response = await axios.post(`http://localhost:8000/api/binh-luan/${comment.binh_luan_id}/like`);
      comment.luot_thich = response.data.luot_thich;
      comment.liked = true;
    } catch (error) {
      console.error("Lỗi khi like:", error);
      alert("Đã xảy ra lỗi khi thích bình luận. Vui lòng thử lại.");
    }
}
async function toggleDislike(comment) {
    if (comment.disliked) {
      return;
    }
    try {
      const response = await axios.post(`http://localhost:8000/api/binh-luan/${comment.binh_luan_id}/dislike`);
      comment.luot_khong_thich = response.data.luot_khong_thich;
      comment.disliked = true;
    } catch (error) {
      console.error("Lỗi khi dislike:", error);
      alert("Đã xảy ra lỗi khi không thích bình luận. Vui lòng thử lại.");
    }
}
function timeAgo(dateString) {
    if (!dateString) return '';
    const now = new Date();
    const date = new Date(dateString);
    const seconds = Math.floor((now - date) / 1000);
    let interval = seconds / 31536000;
    if (interval > 1) { return `${Math.floor(interval)} năm trước`; }
    interval = seconds / 2592000;
    if (interval > 1) { return `${Math.floor(interval)} tháng trước`; }
    interval = seconds / 86400;
    if (interval > 1) { return `${Math.floor(interval)} ngày trước`; }
    interval = seconds / 3600;
    if (interval > 1) { return `${Math.floor(interval)} giờ trước`; }
    interval = seconds / 60;
    if (interval > 1) { return `${Math.floor(interval)} phút trước`; }
    return 'vài giây trước';
}
</script>

<style scoped>
/* GENERAL STYLES */
.comments-section-container {
    max-width: 1600px;
    margin: 36px auto;
    background-color: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
    padding: 0;
    overflow: hidden;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #333;
}
/* HEADER */
.comments-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    background: linear-gradient(135deg, #4a90e2, #50b0f0);
    color: white;
}
.comments-header .header-left {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.25rem;
    font-weight: 600;
}
.sort-button {
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.7);
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    padding: 8px 12px;
    border-radius: 20px;
    transition: all 0.2s ease;
}
.sort-button:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: #fff;
}
.sort-button.active {
    background-color: rgba(255, 255, 255, 0.2);
    color: #fff;
    font-weight: 600;
}
/* COMMENT FORM */
.comment-form-wrapper {
    display: flex;
    gap: 16px;
    padding: 24px;
    border-bottom: 1px solid #f0f0f0;
}
.user-avatar {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    background-color: #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.user-avatar i {
    color: #9e9e9e;
    font-size: 2rem;
}
.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.comment-form-content {
    flex-grow: 1;
    position: relative;
}

/* CUSTOM COMMENT INPUT */
.comment-input {
    min-height: 100px;
    padding: 10px 14px;
    font-size: 1rem;
    line-height: 1.5;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    outline: none;
    background-color: #fff;
    transition: border-color 0.2s, box-shadow 0.2s;
    cursor: text;
    word-wrap: break-word;
    position: relative;
}
.comment-input:focus {
    border-color: #4a90e2;
    box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
}
.comment-input:empty:not(:focus)::before {
    content: attr(placeholder);
    color: #a0aec0;
    pointer-events: none;
    position: absolute;
    top: 10px;
    left: 14px;
}

/* CUSTOM TOOLBAR */
.custom-toolbar {
    display: flex;
    gap: 12px;
    margin-top: 8px;
}
.toolbar-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: #f7fafc;
    color: #4a5568;
    cursor: pointer;
    border: none;
    transition: background-color 0.2s, color 0.2s;
}
.toolbar-icon:hover {
    background-color: #e2e8f0;
}
.toolbar-icon i {
    font-size: 1.2rem;
}
.hidden-input {
    display: none;
}

/* Star Rating */
.star-rating-container {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 12px;
}
.star-label {
    font-size: 0.9rem;
    color: #718096;
}
.star-icon {
    font-size: 1.5rem;
    color: #e0e0e0;
    cursor: pointer;
    transition: color 0.2s;
}
.star-icon.filled {
    color: #f7b003;
}
.star-rating-container .star-icon:hover {
    color: #f7b003;
}
.star-rating-container .star-icon:hover ~ .star-icon:not(.filled) {
    color: #e0e0e0;
}

/* Emoji Picker */
.emoji-picker-container {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    padding: 8px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background-color: #f7fafc;
    margin-top: 8px;
    max-width: 300px;
}
.emoji-item {
    cursor: pointer;
    padding: 4px;
    font-size: 1.2rem;
    transition: transform 0.1s ease;
}
.emoji-item:hover {
    transform: scale(1.2);
}

/* Link Modal */
.link-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.link-modal {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    width: 90%;
    max-width: 400px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.link-modal p {
    margin: 0;
    font-weight: 600;
    color: #333;
}
.link-modal input {
    padding: 10px 12px;
    font-size: 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    outline: none;
    transition: border-color 0.2s;
}
.link-modal input:focus {
    border-color: #4a90e2;
}
.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}
.modal-button {
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    background-color: #f0f4f8;
    color: #4a5568;
    transition: background-color 0.2s;
}
.modal-button.primary {
    background-color: #4a90e2;
    color: #fff;
}
.modal-button:hover {
    opacity: 0.8;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    padding-top: 16px;
    border-top: 1px solid #f0f0f0;
    margin-top: 12px;
}
.submit-button {
    background-color: #4a90e2;
    color: #fff;
    border: none;
    padding: 8px 20px;
    border-radius: 20px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background-color 0.2s, box-shadow 0.2s;
    font-weight: 600;
}
.submit-button:hover {
    background-color: #357bd8;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
.submit-button:disabled {
    background-color: #d1d5db;
    cursor: not-allowed;
}
/* COMMENT LIST */
.comments-list {
    list-style-type: none;
    padding: 0;
    margin: 0;
}
.comment-item {
    display: flex;
    flex-direction: column;
    padding: 24px;
    border-bottom: 1px solid #f0f0f0;
}
.comment-item:last-child {
    border-bottom: none;
}
.comment-item-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 12px;
}
.comment-meta {
    display: flex;
    flex-direction: column;
}
.comment-author-info {
    display: flex;
    align-items: center;
    gap: 8px;
}
.comment-author {
    font-weight: 600;
    color: #1a202c;
    font-size: 1rem;
}
.verified-icon {
    color: #4a90e2;
    font-size: 0.9rem;
}
.comment-date {
    font-size: 0.85rem;
    color: #718096;
}
.comment-content {
    margin: 0;
    color: #4a5568;
    line-height: 1.6;
    margin-left: 64px;
    word-wrap: break-word;
}
/* CSS chỉnh kích thước ảnh trong comment và input */
.comment-content :deep(img), .comment-input img {
    max-width: 10%;
    height: auto;
    border-radius: 8px;
    margin-top: 8px;
    display: block;
}
.comment-content :deep(a) {
    color: #4a90e2;
    text-decoration: underline;
    word-break: break-all;
}
.comment-content :deep(p):last-child {
    margin-bottom: 0;
}
.comment-actions {
    display: flex;
    gap: 24px;
    margin-top: 12px;
    margin-left: 64px;
}
.action-button {
    display: flex;
    align-items: center;
    gap: 8px;
    background: none;
    border: none;
    color: #718096;
    font-size: 0.9rem;
    cursor: pointer;
    transition: color 0.2s;
    padding: 4px;
}
.action-button:hover {
    color: #4a90e2;
}
.action-button span {
    font-weight: 500;
}
/* INFO MESSAGES */
.info-message {
    text-align: center;
    color: #718096;
    padding: 24px;
    font-style: italic;
    font-size: 1rem;
}
.info-message i {
    margin-right: 8px;
    color: #a0aec0;
}
.disliked.fa-thumbs-down {
    color: blue;
}
.liked .fa-heart {
    color: red;
}
/* RESPONSIVE DESIGN */
@media (max-width: 768px) {
    .comments-section-container {
        max-width: 100%;
        margin: 24px 16px;
    }
    .comments-header {
        padding: 12px 16px;
        flex-direction: column;
        align-items: flex-start;
    }
    .comments-header .header-left {
        margin-bottom: 8px;
    }
    .comment-form-wrapper {
        padding: 16px;
        gap: 12px;
    }
    .user-avatar {
        width: 40px;
        height: 40px;
    }
    .user-avatar i {
        font-size: 1.5rem;
    }
    .form-actions {
        height: auto;
        flex-direction: column;
        align-items: flex-end;
        gap: 12px;
    }
    .action-icons {
        display: none;
    }
    .submit-button {
        width: 100%;
    }
    .comment-item {
        padding: 16px;
    }
    .comment-content {
        margin-left: 52px;
    }
    .comment-actions {
        margin-left: 52px;
    }
}
</style>