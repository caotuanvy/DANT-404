<template>
  <div class="contact-container">
    <div class="contact-info-section">
      <h2 class="title">Thông Tin Liên Hệ</h2>
      <p class="description">Chúng tôi luôn sẵn lòng lắng nghe bạn. Hãy liên hệ với chúng tôi qua các kênh dưới đây hoặc để lại tin nhắn!</p>
      <div class="info-item">
          <i class="fas fa-map-marker-alt"></i>
          <span>Công viên phần mềm Quang Trung ,
Quận 12, Hồ Chí Minh, Việt Nam</span>
      </div>
      <div class="info-item">
          <i class="fas fa-phone-alt"></i>
          <span>Điện thoại: +84 987 654 321</span>
      </div>
      <div class="info-item">
          <i class="fas fa-envelope"></i>
          <span>Email: contact@example.com</span>
      </div>
      <div class="info-item">
          <i class="fas fa-clock"></i>
          <span>Giờ làm việc: 8:00 - 18:00 (Thứ Hai - Thứ Sáu)</span>
      </div>
      <div class="social-icons">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-linkedin-in"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
      </div>
    </div>

    <div class="contact-form-section">
      <h2 class="title">Gửi Tin Nhắn</h2>
      <form @submit.prevent="submitForm">
        <div class="form-group">
          <label for="name">Họ và Tên</label>
          <input type="text" id="name" v-model="form.name" required placeholder="Nhập họ và tên của bạn">
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" v-model="form.email" required placeholder="Nhập địa chỉ email của bạn">
        </div>
        <div class="form-group">
          <label for="subject">Tiêu đề</label>
          <input type="text" id="subject" v-model="form.subject" required placeholder="Tiêu đề của tin nhắn">
        </div>
        <div class="form-group">
          <label for="message">Nội dung</label>
          <textarea id="message" v-model="form.message" rows="5" required placeholder="Nhập nội dung tin nhắn của bạn"></textarea>
        </div>
        <button type="submit" class="submit-btn" :disabled="loading">
          <span v-if="loading">Đang gửi...</span>
          <span v-else>Gửi Tin Nhắn</span>
        </button>
      </form>
      <div v-if="statusMessage" :class="['status-message', statusType]">
        {{ statusMessage }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const form = ref({
  name: '',
  email: '',
  subject: '',
  message: '',
});

const loading = ref(false);
const statusMessage = ref('');
const statusType = ref('');

const submitForm = async () => {
  loading.value = true;
  statusMessage.value = '';
  statusType.value = '';

  try {
    const response = await axios.post('https://api.sieuthi404.io.vn/send-email', form.value);
    
    statusMessage.value = response.data.message;
    statusType.value = 'success';
    form.value = { name: '', email: '', subject: '', message: '' }; // Reset form
  } catch (error) {
    statusMessage.value = 'Đã có lỗi xảy ra. Vui lòng thử lại.';
    statusType.value = 'error';
    console.error('Lỗi khi gửi form:', error.response?.data || error.message);
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.contact-container {
  display: flex;
  gap: 40px;
  padding: 40px 20px;
  max-width: 1200px;
  margin: 0 auto;
  flex-wrap: wrap;
}

.contact-info-section,
.contact-form-section {
  flex: 1 1 45%;
  background: #fff;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.title {
  font-size: 22px;
  font-weight: 700;
  margin-bottom: 15px;
  color: #1e293b;
}

.description {
  color: #475569;
  margin-bottom: 20px;
  line-height: 1.6;
}

.info-item {
  display: flex;
  align-items: center;
  margin-bottom: 12px;
  color: #334155;
  font-size: 15px;
}

.info-item i {
  margin-right: 10px;
  font-size: 16px;
  color: #33ccff;
}

.social-icons {
  margin-top: 15px;
}

.social-icons a {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: #f1f5f9;
  color: #33ccff;
  font-size: 16px;
  transition: all 0.3s ease;
}

.social-icons a:hover {
  background: #33ccff;
  color: #fff;
  transform: translateY(-2px);
}

form {
  display: flex;
  flex-direction: column;
}

.form-group {
  margin-bottom: 16px;
}

label {
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 6px;
  display: block;
  color: #334155;
}

input,
textarea {
  width: 100%;
  padding: 12px 14px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 14px;
  outline: none;
  transition: border-color 0.3s;
}

input:focus,
textarea:focus {
  border-color: #33ccff;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
}

.submit-btn {
  padding: 12px 18px;
  background: #33ccff;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.submit-btn:hover:not(:disabled) {
  background: #2497d5;
  transform: translateY(-2px);
}

.submit-btn:disabled {
  background: #94a3b8;
  cursor: not-allowed;
}

.status-message {
  margin-top: 15px;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
}

.status-message.success {
  background: #dcfce7;
  color: #166534;
  border: 1px solid #bbf7d0;
}

.status-message.error {
  background: #fee2e2;
  color: #b91c1c;
  border: 1px solid #fecaca;
}

/* Responsive */
@media (max-width: 768px) {
  .contact-container {
    flex-direction: column;
    padding: 20px;
  }

  .contact-info-section,
  .contact-form-section {
    flex: 1 1 100%;
  }
}

</style>