<template>
  <div class="comments-section-container">
    <div class="header-container">
      <div class="header-left">
        <i class="fa-solid fa-comments"></i>
        <span>Bình luận</span>
      </div>
      <div class="header-right">
        <div class="sort-buttons">
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
    </div>

    <div class="ratings-sidebar">
      <div class="ratings-summary">
        <h4>Đánh giá tổng quan</h4>
        <div class="rating-display">
          <span class="overall-rating">{{ averageRating }}</span>
          <div class="star-rating">
            <span v-for="star in 5" :key="star" class="star-icon" :class="{ 'filled': star <= averageRating }">★</span>
          </div>
          <span class="review-count">({{ totalReviews }} lượt đánh giá)</span>
        </div>
      </div>
      <div class="rating-breakdown">
        <div v-for="rating in 5" :key="rating" class="rating-bar-row">
          <span class="rating-label">{{ rating }} sao</span>
          <div class="rating-bar-container">
            <div class="rating-bar" :class="{ 'filled': ratingStats[rating] > 0 }" :style="{ width: getRatingPercentage(rating) }"></div>
          </div>
          <span class="rating-number">{{ ratingStats[rating] || 0 }}</span>
          <a v-if="ratingStats[rating] > 0" href="#" class="view-more" @click.prevent="openRatingModal(rating)">Xem thêm</a>
        </div>
      </div>
    </div>
    
    <div class="main-content">
      <div class="comments-content">
        <div class="comment-form">
          <div class="user-avatar">
            <img v-if="isLoggedIn" class="avatar" :src="user.avatar || 'https://i.pravatar.cc/50?u=' + user.nguoi_dung_id" alt="User Avatar">
            <i v-else class="fa-solid fa-user-circle"></i>
          </div>
          <div class="comment-input-area">
            <div 
              class="comment-input" 
              :contenteditable="isLoggedIn" 
              @input="onInput"
              @paste="onPaste"
              :placeholder="placeholderText"
              :class="{ 'disabled-input': !isLoggedIn }"
            ></div>
            
            <div class="toolbar" v-if="isLoggedIn">
              <label for="image-upload">
                <i class="fa-regular fa-image toolbar-icon"></i>
              </label>
              <input type="file" id="image-upload" style="display: none;" @change="handleImageFile" accept="image/*">
              <i class="fa-solid fa-link toolbar-icon" @click="addLink"></i>
              <i class="fa-regular fa-face-smile toolbar-icon" @click="toggleEmojiPicker"></i>
            </div>
            
            <div class="user-rating" v-if="isLoggedIn">
              <span>Đánh giá của bạn:</span>
              <div class="star-rating">
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
            
            <button 
                class="submit-button" 
                :class="{ 'active': isLoggedIn }"
                @click="submitComment"
            >
              <i v-if="isSubmitting" class="fa-solid fa-spinner fa-spin"></i>
              <span v-else>{{ isLoggedIn ? 'Đăng' : 'Đăng nhập để bình luận' }}</span>
            </button>
          </div>
        </div>
        
        <div v-if="showRatingModal" class="rating-modal-overlay" @click.self="closeRatingModal">
          <div class="rating-modal">
            <div class="modal-header">
              <h4>Lọc bình luận</h4>
              <i class="fa-solid fa-xmark close-button" @click="closeRatingModal"></i>
            </div>
            <div class="modal-options">
              <div class="sort-options-section">
                <p class="section-title">Sắp xếp theo:</p>
                <div class="sort-options-group">
                  <div class="sort-option" @click="fetchCommentsByRatingAndSort('newest')">Mới nhất</div>
                  <div class="sort-option" @click="fetchCommentsByRatingAndSort('oldest')">Cũ nhất</div>
                  <div class="sort-option" @click="fetchCommentsByRatingAndSort('popular')">Phổ biến</div>
                  <div class="sort-option" @click="fetchCommentsByRatingAndSort('disliked')">Không thích</div>
                </div>
              </div>
              
              <div class="rating-options-section">
                <p class="section-title">Điểm xếp hạng:</p>
                <div class="rating-options-group">
                  <div 
                    v-for="star in 5" 
                    :key="star" 
                    class="rating-option"
                    :class="{ 'active-rating': currentRating === star }"
                    @click="setRatingForModal(star)"
                  >
                    <span class="rating-label">{{ star }} sao</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="loading" class="info-message loading">
          <i class="fa-solid fa-spinner fa-spin"></i> Đang tải bình luận...
        </div>
        <div v-else-if="comments.length === 0" class="info-message no-comments">
          <i class="fa-regular fa-comment-dots"></i> Chưa có bình luận nào.
        </div>
        <div v-else class="comment-list">
          <div v-for="comment in comments" :key="comment.id" class="comment-item">
            <div class="user-info">
              <img class="avatar" :src="comment.nguoi_dung.avatar || 'https://i.pravatar.cc/50?u=' + comment.nguoi_dung.nguoi_dung_id" alt="User Avatar">
              <div class="user-meta">
                <span class="username">{{ comment.nguoi_dung.ho_ten || 'Người dùng' }}</span>
                <span class="timestamp">{{ timeAgo(comment.ngay_binh_luan) }}</span>
                <div v-if="comment.danh_gia" class="user-rating-display">
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
            </div>
            <div class="comment-body" v-html="comment.noidung"></div>
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
              <button class="action-button" @click="reportComment(comment, 1)">
                <i class="fa-solid fa-triangle-exclamation" style="color: #f39c12;"></i> Spam
              </button>
              <button class="action-button" @click="reportComment(comment, 2)">
                <i class="fa-solid fa-user-slash" style="color: #e74c3c;"></i> Lăng mạ
              </button>
              <button class="action-button"><i class="fa-solid fa-reply"></i> Trả lời</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showAlertModal" class="alert-modal-overlay">
      <div class="alert-modal">
        <i class="fa-solid fa-exclamation-triangle alert-icon"></i>
        <p class="alert-message">{{ alertMessage }}</p>
        <button class="alert-button" @click="closeAlertModal">Đã hiểu</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const user = JSON.parse(localStorage.getItem('user')) || null;
const isLoggedIn = computed(() => !!user);
const comments = ref([]);
const loading = ref(false);
const isSubmitting = ref(false);

const placeholderText = computed(() => {
    return isLoggedIn.value ? 'Viết bình luận...' : 'Bạn cần đăng nhập để bình luận.';
});

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
    nguoi_dung_id: user ? user.nguoi_dung_id : null,
    noidung: '',
    danh_gia: null,
});

const isSubmitDisabled = computed(() => {
    return !isLoggedIn.value || isSubmitting.value || (!newComment.value.noidung.trim() && newComment.value.danh_gia === null);
});

const ratingStats = ref({});
const totalReviews = ref(0);
const averageRating = ref(0);

const showRatingModal = ref(false);
const currentRating = ref(null);

const showAlertModal = ref(false);
const alertMessage = ref('');

function openAlertModal(message) {
  alertMessage.value = message;
  showAlertModal.value = true;
}

function closeAlertModal() {
  showAlertModal.value = false;
  alertMessage.value = '';
}


watch(() => route.params.slug, (newSlug) => {
    if (newSlug) {
      newComment.value.tin_tuc_id = null;
      fetchComments();
      fetchRatingStatistics();
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

async function fetchRatingStatistics() {
    try {
        const newsId = await getNewsIdFromSlug(route.params.slug);
        const response = await axios.get(`http://localhost:8000/api/binh-luan/thong-ke/${newsId}`);
        ratingStats.value = response.data.stats;
        totalReviews.value = response.data.total;
        averageRating.value = response.data.average;
    } catch (error) {
        console.error("Lỗi khi tải thống kê đánh giá:", error);
    }
}

function openRatingModal(rating) {
    currentRating.value = rating;
    showRatingModal.value = true;
}

function closeRatingModal() {
    showRatingModal.value = false;
    currentRating.value = null; 
}

function setRatingForModal(rating) {
    currentRating.value = rating;
    fetchCommentsByRatingAndSort();
}

async function fetchCommentsByRatingAndSort(sortBy = 'newest') {
    loading.value = true;
    try {
        const newsId = await getNewsIdFromSlug(route.params.slug);
        let url = `http://localhost:8000/api/binh-luan/danh-gia/${newsId}`;

        const params = {};
        if (currentRating.value) {
          params.rating = currentRating.value;
        }
        
        switch (sortBy) {
          case 'newest':
            params.sort_by = 'ngay_binh_luan';
            params.sort_order = 'desc';
            break;
          case 'oldest':
            params.sort_by = 'ngay_binh_luan';
            params.sort_order = 'asc';
            break;
          case 'popular':
            params.sort_by = 'luot_thich';
            params.sort_order = 'desc';
            break;
          case 'disliked':
            params.sort_by = 'luot_khong_thich';
            params.sort_order = 'desc';
            break;
        }

        const response = await axios.get(url, { params });
        comments.value = response.data.data;
        closeRatingModal();
    } catch (error) {
        console.error("Lỗi khi xem thêm bình luận theo sao:", error);
    } finally {
        loading.value = false;
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
        url += '?sort_by=luot_thich&sort_order=desc';
      } else { // default to newest
        url += '?sort_by=ngay_binh_luan&sort_order=desc';
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

function setRating(star) {
  if (!isLoggedIn.value) {
      openAlertModal('Bạn cần đăng nhập để đánh giá.');
      return;
  }
  if (newComment.value.danh_gia === star) {
    newComment.value.danh_gia = null;
  } else {
    newComment.value.danh_gia = star;
  }
}
function onInput(event) {
    if (!isLoggedIn.value) {
        // Clear input nếu người dùng chưa đăng nhập
        event.target.innerHTML = '';
        return;
    }
    newComment.value.noidung = event.target.innerHTML;
}
function onPaste(event) {
    if (!isLoggedIn.value) {
        event.preventDefault();
        return;
    }
    event.preventDefault();
    const text = event.clipboardData.getData('text/plain');
    document.execCommand('insertText', false, text);
}
function toggleEmojiPicker() {
    if (!isLoggedIn.value) {
        openAlertModal('Bạn cần đăng nhập để sử dụng tính năng này.');
        return;
    }
    showEmojiPicker.value = !showEmojiPicker.value;
}
function insertEmoji(emoji) {
    if (!isLoggedIn.value) return;
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
    if (!isLoggedIn.value) {
        openAlertModal('Bạn cần đăng nhập để tải ảnh lên.');
        return;
    }
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
        
        const lastNode = inputElement.lastChild;
        if (lastNode && lastNode.nodeName === '#text' && lastNode.textContent.trim() === '') {
            inputElement.removeChild(lastNode);
        }
        inputElement.appendChild(imgNode);
        
        const selection = window.getSelection();
        const range = document.createRange();
        range.setStartAfter(imgNode);
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
    if (!isLoggedIn.value) {
        openAlertModal('Bạn cần đăng nhập để thêm liên kết.');
        return;
    }
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
    // 1. Kiểm tra đăng nhập trước tiên
    if (!isLoggedIn.value) {
      openAlertModal('Bạn cần đăng nhập để bình luận.');
      // Có thể thêm logic chuyển hướng tới trang đăng nhập ở đây
      return;
    }

    if (isSubmitDisabled.value) {
      return;
    }

    if (newComment.value.danh_gia === null) {
        openAlertModal('Bạn quên đánh giá sao rồi!');
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
          danh_gia: newComment.value.danh_gia,
      });
      
      fetchComments();
      fetchRatingStatistics();

      newComment.value.noidung = '';
      newComment.value.danh_gia = null;
      inputElement.innerHTML = '';
      inputElement.blur();
    } catch (error) {
      console.error("Lỗi khi gửi bình luận:", error);
      openAlertModal("Không thể gửi bình luận. Vui lòng thử lại.");
    } finally {
      isSubmitting.value = false;
    }
}

async function toggleLike(comment) {
    if (!isLoggedIn.value) {
        openAlertModal("Bạn cần đăng nhập để thích bình luận.");
        return;
    }
    // ... (logic cũ)
}
async function toggleDislike(comment) {
    if (!isLoggedIn.value) {
        openAlertModal("Bạn cần đăng nhập để không thích bình luận.");
        return;
    }
    // ... (logic cũ)
}

async function setBaoCao(commentId, baoCaoValue) {
  try {
    const token = localStorage.getItem('token');
    await axios.post(`http://localhost:8000/api/binh-luan/${commentId}/bao-cao`, { 
      bao_cao: baoCaoValue 
    }, {
      headers: {
        'Authorization': token ? `Bearer ${token}` : '',
      },
    });
    openAlertModal(`Bạn đã tố cáo bình luận thành công!`);
  } catch (error) {
    console.error("Lỗi khi báo cáo bình luận:", error);
    openAlertModal("Đã xảy ra lỗi khi báo cáo bình luận. Vui lòng thử lại.");
  }
}

function reportComment(comment, type) {
  if (user && user.nguoi_dung_id) {
    if (type === 1) {
      setBaoCao(comment.binh_luan_id, 1);
    } else if (type === 2) {
      setBaoCao(comment.binh_luan_id, 2);
    }
  } else {
    openAlertModal("Bạn cần đăng nhập để báo cáo bình luận.");
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

const getRatingPercentage = (rating) => {
    if (totalReviews.value === 0) return '0%';
    const count = ratingStats.value[rating] || 0;
    return `${(count / totalReviews.value) * 100}%`;
};
</script>

<style scoped>
/* Thêm style cho trạng thái vô hiệu hóa */
.comment-input.disabled-input {
    background-color: #f5f5f5;
    cursor: not-allowed;
    color: #a0aec0;
}
.comment-input.disabled-input:empty::before {
    color: #a0aec0;
}
.submit-button.active {
    background-color: #55a8e0;
    color: white;
    cursor: pointer;
}
.submit-button:not(.active) {
    background-color: #e0e0e0;
    color: #999;
    cursor: not-allowed;
}

/* ... (giữ nguyên các style còn lại) */
body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    background-color: #f0f2f5;
    margin: 0;
    padding: 20px;
}

.comments-section-container {
    max-width: 1600px;
    margin: auto;
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.header-container {
    background-color: #55a8e0;
    color: white;
    padding: 16px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-left {
    display: flex;
    align-items: center;
    font-size: 1.2rem;
    font-weight: 600;
}

.header-left i {
    margin-right: 12px;
}

.sort-buttons {
    display: flex;
    gap: 8px;
}

.sort-button {
    background-color: transparent;
    color: rgba(255, 255, 255, 0.7);
    border: none;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.sort-button.active {
    background-color: rgba(255, 255, 255, 0.3);
    color: white;
    font-weight: 600;
}

.main-content {
    display: flex;
    flex-direction: column; 
    padding: 24px;
}

.ratings-sidebar {
    width: 100%;
    background-color: #f7f9fb;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 24px; 
    border: 1px solid #e0e6ed;
}

.ratings-summary h4 {
    margin: 0 0 16px 0;
    font-size: 1.1rem;
    color: #333;
}

.rating-display {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.overall-rating {
    font-size: 3rem;
    font-weight: 700;
    color: #ffc107;
    line-height: 1;
}

.star-rating {
    margin: 0 8px;
    display: flex;
    gap: 2px;
}

.star-icon {
    color: #e0e0e0;
    font-size: 1.2rem;
}

.star-icon.filled {
    color: #ffc107;
}

.review-count {
    font-size: 0.9rem;
    color: #777;
}

.rating-breakdown {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.rating-bar-row {
    display: flex;
    align-items: center;
    font-size: 14px;
}

.rating-label {
    width: 50px;
    flex-shrink: 0;
    color: #555;
}

.rating-bar-container {
    flex-grow: 1;
    height: 8px;
    background-color: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
}

.rating-bar {
    height: 100%;
    background-color: #ffc107;
    transition: width 0.5s ease;
}
.rating-bar:not(.filled) {
    background-color: #e0e0e0;
}

.rating-number {
    width: 30px;
    text-align: right;
    flex-shrink: 0;
}

.view-more {
    margin-left: 10px;
    font-size: 14px;
    color: #55a8e0;
    text-decoration: none;
}

.comments-content {
    width: 100%;
}

.comment-form {
    display: flex;
    gap: 15px;
    align-items: flex-start;
    margin-bottom: 24px;
    padding: 16px;
    border: 1px solid #e0e6ed;
    border-radius: 8px;
}

.comment-input-area {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    position: relative;
}

.comment-input {
    width: 100%;
    min-height: 80px;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
    font-size: 14px;
    margin-bottom: 10px;
    outline: none;
    word-wrap: break-word;
    cursor: text;
}
.comment-input:empty::before {
    content: attr(placeholder);
    color: #a0aec0;
    pointer-events: none;
}

.toolbar {
    display: flex;
    gap: 12px;
    margin-bottom: 12px;
}

.toolbar-icon {
    font-size: 1.2rem;
    color: #777;
    cursor: pointer;
}

.user-rating {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    font-size: 14px;
}

.user-rating .star-icon {
    cursor: pointer;
    color: #e0e0e0;
}
.user-rating .star-icon.filled {
    color: #ffc107;
}

.submit-button {
    align-self: flex-end;
    padding: 8px 20px;
    border: none;
    border-radius: 20px;
    font-weight: 600;
    transition: all 0.2s;
}

.comment-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.comment-item {
    display: flex;
    flex-direction: column;
    padding: 16px;
    border: 1px solid #e0e6ed;
    border-radius: 8px;
}

.user-info {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: -12px;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.user-meta {
    display: flex;
    flex-direction: column;
}

.username {
    font-weight: 600;
    font-size: 15px;
}

.timestamp {
    font-size: 12px;
    color: #777;
}

.user-rating-display {
    margin-top: 4px;
    display: flex;
    gap: 2px;
}

.comment-body {
    margin-left: 52px; 
    margin-top: 8px; 
    font-size: 16px;
}

.comment-body :deep(p) {
    margin: 0;
    line-height: 1.5;
}
.comment-body :deep(img) {
    max-width: 100%;
    border-radius: 4px;
    margin-top: 4px; 
    display: block;
}
.comment-body :deep(a) {
    color: #4a90e2;
    text-decoration: underline;
    word-break: break-all;
}

.comment-actions {
    display: flex;
    gap: 16px;
    margin-top: 12px;
    margin-left: 52px; 
}

.action-button {
    display: flex;
    align-items: center;
    gap: 6px;
    background-color: transparent;
    border: none;
    color: #777;
    font-size: 14px;
    cursor: pointer;
}

.action-button:hover {
    color: #55a8e0;
}
.text-red {
    color: #ff4d4f;
}
.text-blue {
    color: #1890ff;
}

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

.link-modal-overlay {
    position: fixed;
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

.rating-modal-overlay {
    position: fixed;
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

.rating-modal {
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    width: 90%;
    max-width: 400px;
    padding: 24px;
    animation: fadeInScale 0.2s ease-out;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f0f2f5;
  padding-bottom: 12px;
  margin-bottom: 12px;
}

.modal-header h4 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
}

.close-button {
  font-size: 1.5rem;
  color: #777;
  cursor: pointer;
  transition: color 0.2s;
}

.close-button:hover {
  color: #333;
}

.modal-options {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.section-title {
  font-weight: 600;
  color: #555;
  margin-bottom: 10px;
  font-size: 1rem;
}

.sort-options-group, .rating-options-group {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.sort-option, .rating-option {
  padding: 10px 16px;
  border: 1px solid #e0e6ed;
  border-radius: 20px;
  cursor: pointer;
  font-size: 14px;
  color: #555;
  transition: all 0.2s;
}

.sort-option:hover, .rating-option:hover {
  background-color: #f0f4f8;
  border-color: #4a90e2;
  color: #4a90e2;
}

.rating-option.active-rating {
  background-color: #4a90e2;
  color: white;
  border-color: #4a90e2;
}

@keyframes fadeInScale {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.alert-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
}

.alert-modal {
    background-color: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    width: 90%;
    max-width: 350px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    animation: fadeIn 0.3s ease-out;
}

.alert-icon {
    font-size: 3rem;
    color: #f39c12; 
    margin-bottom: 15px;
}

.alert-message {
    font-size: 1.1rem;
    color: #333;
    font-weight: 500;
    margin: 0 0 20px 0;
}

.alert-button {
    background-color: #55a8e0;
    color: white;
    border: none;
    border-radius: 25px;
    padding: 10px 25px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.alert-button:hover {
    background-color: #4a90e2;
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
</style>