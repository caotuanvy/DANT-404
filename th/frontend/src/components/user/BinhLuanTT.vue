<template>
  <div class="comments-section-container">
    <div class="comments-header">
      <div class="header-left">
        <i class="fa-solid fa-comments"></i>
        <span>Bình luận</span>
      </div>
      <div class="header-right">
        <button class="sort-button active">Mới nhất</button>
        <button class="sort-button">Phổ biến</button>
      </div>
    </div>

    <div class="comment-form-wrapper">
      <div class="user-avatar">
        <i class="fa-solid fa-user-circle"></i>
      </div>
      <div class="comment-form-content">
        <textarea
          v-model="newComment.noidung"
          class="comment-textarea"
          placeholder="Viết bình luận..."
          rows="1"
          required
          @focus="expandForm"
          @blur="collapseForm"
        ></textarea>
        <div class="form-actions" :class="{ 'expanded': isFormExpanded }">
          <div class="action-icons">
            <i class="fa-regular fa-face-smile"></i>
            <i class="fa-regular fa-image"></i>
          </div>
          <button
            type="submit"
            class="submit-button"
            :disabled="isSubmitting || !newComment.noidung.trim()"
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
            </div>
            <span class="comment-date">{{ timeAgo(comment.ngay_binh_luan) }}</span>
            <span class="comment-noidung">{{ comment.noidung }}</span> <!-- Hiển thị nội dung -->
          </div>
        </div>
        <p class="comment-content">{{ comment.noi_dung }}</p>
        <div class="comment-actions">
         <button
            class="action-button"
            :class="{ liked: comment.liked }"
            @click="toggleLike(comment)"
          >
            <i :class="['fa-regular', 'fa-heart', { 'fa-solid': comment.liked, 'text-red': comment.liked }]"></i>
            <span>{{ comment.luot_thich }}</span>
          </button>
          <button class="action-button"><i class="fa-solid fa-reply"></i><span>Trả lời</span></button>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

// Lấy `tin_tuc_id` từ props hoặc route params
const route = useRoute();

const user = JSON.parse(localStorage.getItem('user')) || {};

// State
const comments = ref([]);
const loading = ref(false);
const isSubmitting = ref(false);
const isFormExpanded = ref(false);

const newComment = ref({
  tin_tuc_id: null,
  nguoi_dung_id: user.nguoi_dung_id || null, // Lấy từ user đã đăng nhập
  noidung: '',
});
const userName = ref('Nguyễn Văn A'); // CHÚ Ý: Thay thế bằng tên người dùng thực tế

// Watch cho thay đổi route để tải lại bình luận khi chuyển trang tin tức
watch(() => route.params.slug, (newSlug) => {
  if (newSlug) {
    newComment.value.tin_tuc_id = null; // Reset id để fetch lại
    fetchComments();
  }
}, { immediate: true });

// Lấy ID tin tức từ API endpoint
async function getNewsIdFromSlug(slug) {
  try {
    const response = await axios.get(`http://localhost:8000/api/tintuc-cong-khai/slug/${slug}`);
    return response.data.id;
  } catch (error) {
    console.error("Lỗi khi lấy ID tin tức từ slug:", error);
    return null;
  }
}

// Lấy danh sách bình luận
async function fetchComments() {
  const newsId = await getNewsIdFromSlug(route.params.slug);
  if (!newsId) {
    comments.value = [];
    return;
  }
  newComment.value.tin_tuc_id = newsId;

  loading.value = true;
  try {
    const response = await axios.get(`http://localhost:8000/api/binh-luan/tin-tuc/${newsId}`);
    comments.value = response.data;
  } catch (error) {
    console.error("Lỗi khi tải bình luận:", error);
  } finally {
    loading.value = false;
  }
}

// Gửi bình luận mới
async function submitComment() {
  if (!newComment.value.noidung.trim()) {
    return;
  }

  isSubmitting.value = true;
  try {
    const response = await axios.post(`http://localhost:8000/api/binh-luan/tin-tuc`, newComment.value);
    fetchComments(); // Hoặc cập nhật comments.value nếu muốn
    newComment.value.noidung = '';
    isFormExpanded.value = false;
  } catch (error) {
    console.error("Lỗi khi gửi bình luận:", error);
    alert("Không thể gửi bình luận. Vui lòng thử lại.");
  } finally {
    isSubmitting.value = false;
  }
}

// Hàm để giãn form khi focus
function expandForm() {
  isFormExpanded.value = true;
}

function collapseForm() {
  if (!newComment.value.noidung.trim()) {
    isFormExpanded.value = false;
  }
}

// Hàm để toggle like
async function toggleLike(comment) {
  try {
    const response = await axios.post(
      `http://localhost:8000/api/binh-luan/${comment.binh_luan_id}/like`,
      { like: !comment.liked } // gửi trạng thái muốn chuyển đổi
    );
    comment.luot_thich = response.data.luot_thich;
    comment.liked = !comment.liked;
  } catch (error) {
    console.error("Lỗi khi like:", error);
  }
}

// Hàm định dạng thời gian tùy chỉnh (thay thế cho date-fns)
function timeAgo(dateString) {
  if (!dateString) return '';

  const now = new Date();
  const date = new Date(dateString);
  const seconds = Math.floor((now - date) / 1000);

  let interval = seconds / 31536000;
  if (interval > 1) {
    return `${Math.floor(interval)} năm trước`;
  }
  interval = seconds / 2592000;
  if (interval > 1) {
    return `${Math.floor(interval)} tháng trước`;
  }
  interval = seconds / 86400;
  if (interval > 1) {
    return `${Math.floor(interval)} ngày trước`;
  }
  interval = seconds / 3600;
  if (interval > 1) {
    return `${Math.floor(interval)} giờ trước`;
  }
  interval = seconds / 60;
  if (interval > 1) {
    return `${Math.floor(interval)} phút trước`;
  }
  return 'vài giây trước';
}
</script>

<style scoped>
/* GENERAL STYLES */
.comments-section-container {
  /* Tăng max-width để nó rộng hơn trên màn hình lớn */
  max-width: 1600px; 
  /* Căn giữa container trên màn hình lớn */
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
}

.comment-textarea {
  width: 100%;
  border: none;
  outline: none;
  font-size: 1rem;
  padding: 12px 0;
  border-bottom: 1px solid #e0e0e0;
  transition: border-color 0.2s;
  resize: none;
  overflow: hidden;
  line-height: 1.5;
}

.comment-textarea:focus {
  border-bottom-color: #4a90e2;
}

.form-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 0;
  opacity: 0;
  visibility: hidden;
  transition: all 0.2s ease-in-out;
  overflow: hidden;
  margin-top: 8px;
}

.form-actions.expanded {
  height: 36px;
  opacity: 1;
  visibility: visible;
  margin-top: 16px;
}

.action-icons i {
  color: #9e9e9e;
  font-size: 1.25rem;
  cursor: pointer;
  margin-right: 12px;
  transition: color 0.2s;
}

.action-icons i:hover {
  color: #4a90e2;
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
  .comment-textarea {
    padding: 8px 0;
  }
  .form-actions.expanded {
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