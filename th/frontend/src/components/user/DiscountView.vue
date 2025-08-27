<template>
  <div class="discount-app-container">
    <div class="discount-app-header-main">
      <div class="discount-app-grid-container">
        <div class="discount-app-grid-item">
          <svg class="discount-app-icon" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L12.343 14H12a4 4 0 01-5.657 0l-1.172-1.172a4 4 0 010-5.656zM8 9a1 1 0 11-2 0 1 1 0 012 0zm0-3a1 1 0 100 2 1 1 0 000-2zm4 3a1 1 0 11-2 0 1 1 0 012 0zm4-3a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"></path></svg>
          <div>
            <h4 class="discount-app-item-title">Giao hàng hỏa tốc</h4>
            <p class="discount-app-item-subtitle">Nội thành TP. HCM trong 4h</p>
          </div>
        </div>
        <div class="discount-app-grid-item">
          <svg class="discount-app-icon" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM6.53 9.47a.75.75 0 011.06 0L10 11.88l2.41-2.41a.75.75 0 111.06 1.06l-3 3a.75.75 0 01-1.06 0l-3-3a.75.75 0 010-1.06z" clip-rule="evenodd"></path></svg>
          <div>
            <h4 class="discount-app-item-title">Đổi trả miễn phí</h4>
            <p class="discount-app-item-subtitle">Trong vòng 30 ngày miễn phí</p>
          </div>
        </div>
        <div class="discount-app-grid-item">
          <svg class="discount-app-icon" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a1 1 0 00-.895.553l-3.5 6a1 1 0 00.895 1.447h7a1 1 0 00.895-1.447l-3.5-6A1 1 0 0010 5z" clip-rule="evenodd"></path></svg>
          <div>
            <h4 class="discount-app-item-title">Hỗ trợ 24/7</h4>
            <p class="discount-app-item-subtitle">Hỗ trợ khách hàng 24/7</p>
          </div>
        </div>
        <div class="discount-app-grid-item">
          <svg class="discount-app-icon" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
          <div>
            <h4 class="discount-app-item-title">Deal hot bùng nổ</h4>
            <p class="discount-app-item-subtitle">Flash sale giảm giá cực sốc</p>
          </div>
        </div>
      </div>
    </div>

    <div v-if="discounts.length > 0" class="discount-app-cards-grid">
      <div v-for="discount in discounts" :key="discount.giam_gia_id" 
           class="discount-app-card-item"
           :class="{ 'discount-app-card-expired': isExpired(discount.ngay_ket_thuc) }">
        
        <div class="discount-app-card-header">
          <div class="discount-app-badge">
            <span class="discount-app-coupon-code">{{ discount.ma_giam_gia }}</span>
          </div>
        </div>

        <div class="discount-app-card-body">
          <h3 class="discount-app-value">{{ formatValue(discount) }}</h3>
          <p class="discount-app-program-title">
            Giảm cho đơn hàng có giá trị tối thiểu <br>{{ formatCurrency(discount.gia_tri_don_hang_toi_thieu) }}
          </p>
          <div class="discount-app-card-details">
            <p class="discount-app-expiration-date">Ngày kết thúc: {{ formatDate(discount.ngay_ket_thuc) }}</p>
            <a href="#" @click.prevent="showDetails(discount)" class="discount-app-details-link">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="discount-app-details-icon">
                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75S17.385 21.75 12 21.75 2.25 17.385 2.25 12zm8.706-1.556a.75.75 0 10-1.412 1.03l.439.44a.75.75 0 001.06 1.06l.44-.44a.75.75 0 10-1.06-1.06l-.44.44-.44-.44zm2.844-3.568a.75.75 0 00-.85-.145.75.75 0 00-.638.638.75.75 0 00.145.85l.44.44a.75.75 0 001.06-1.06l-.44-.44zm-2.844 5.378a.75.75 0 00-.85.145.75.75 0 00-.638-.638.75.75 0 00-.145-.85l-.44-.44a.75.75 0 00-1.06 1.06l.44.44.44.44z" clip-rule="evenodd" />
              </svg>
              <span>Điều kiện</span>
            </a>
          </div>
        </div>

        <div class="discount-app-card-actions">
          <div v-if="!isExpired(discount.ngay_ket_thuc)">
            <button v-if="isLoggedIn"
                    @click="claimVoucher(discount)"
                    :disabled="getClaimStatus(discount.giam_gia_id) !== 'idle'"
                    class="discount-app-apply-button"
                    :class="{ 
                      'is-loading': getClaimStatus(discount.giam_gia_id) === 'loading',
                      'is-success': getClaimStatus(discount.giam_gia_id) === 'success' 
                    }">
              <span v-if="getClaimStatus(discount.giam_gia_id) === 'idle'">Lưu mã</span>
              <span v-if="getClaimStatus(discount.giam_gia_id) === 'loading'">Đang lưu...</span>
              <span v-if="getClaimStatus(discount.giam_gia_id) === 'success'">Đã lưu</span>
              <span v-if="getClaimStatus(discount.giam_gia_id) === 'error'">Thử lại</span>
            </button>
            <button v-else
                    @click="copyCode(discount.ma_giam_gia)"
                    class="discount-app-apply-button">
              {{ buttonText[discount.giam_gia_id] || 'Sao chép' }}
            </button>
          </div>
          
          <button v-else disabled class="discount-app-expired-button">
            Hết hạn
          </button>
        </div>
        
        <div v-if="isExpired(discount.ngay_ket_thuc)" class="discount-app-expired-overlay">
          <span>Hết hạn</span>
        </div>
      </div>
    </div>

    <div v-else class="discount-app-loading-state">
      <div v-if="loading" class="discount-app-spinner-container">
        <div class="discount-app-spinner"></div>
        <p class="discount-app-loading-text">Đang tải mã giảm giá...</p>
      </div>
      <p v-else>Không có mã giảm giá nào.</p>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted, reactive } from 'vue';
import axios from 'axios';

// ==================== STATE MANAGEMENT ====================
const discounts = ref([]);
const loading = ref(true);
const buttonText = ref({});
const isLoggedIn = ref(true); // giả lập, thực tế lấy từ store hoặc localStorage
const authToken = ref(localStorage.getItem('token') || ''); // Lấy token từ localStorage
const claimStatuses = reactive({}); // { id: 'idle' | 'loading' | 'success' | 'error' }
const getClaimStatus = (id) => claimStatuses[id] || 'idle';

// ==================== LIFECYCLE HOOKS ====================
onMounted(() => {
  fetchDiscounts();
});

// ==================== METHODS ====================
async function fetchDiscounts() {
  loading.value = true;
  try {
    const apiUrl = `/giam-gia-home`; 
    const response = await axios.get(apiUrl);
    discounts.value = response.data;
  } catch (error) {
    console.error("Lỗi khi tải dữ liệu giảm giá:", error);
  } finally {
    loading.value = false;
  }
}

async function claimVoucher(discount) {
  const discountId = discount.giam_gia_id;
  if (!isLoggedIn.value) {
    alert("Vui lòng đăng nhập để lưu mã giảm giá.");
    return;
  }
  claimStatuses[discountId] = 'loading';
  try {
    // Lấy user từ localStorage
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    const userId = user.nguoi_dung_id;
    if (!userId) throw new Error('Không tìm thấy thông tin người dùng.');

    await axios.post(
      `/giam-gia/${discountId}/claim`,
      { nguoi_dung_id: userId },
      {
        headers: {
          'Authorization': `Bearer ${authToken.value}`,
          'Accept': 'application/json'
        }
      }
    );
    claimStatuses[discountId] = 'success';
  } catch (error) {
    // Nếu lỗi 409 (Conflict) thì thông báo đã lưu rồi
    if (error.response && error.response.status === 409) {
      alert(error.response.data.message || 'Bạn đã lưu mã giảm giá này rồi.');
    }
    claimStatuses[discountId] = 'error';
    setTimeout(() => {
      if (claimStatuses[discountId] === 'error') claimStatuses[discountId] = 'idle';
    }, 3000);
  }
}

async function copyCode(code) {
  try {
    await navigator.clipboard.writeText(code);
    const discountId = getDiscountIdByCode(code);
    if (discountId !== null) {
      buttonText.value[discountId] = 'Đã sao chép!';
      setTimeout(() => {
        buttonText.value[discountId] = 'Sao chép';
      }, 2000);
    }
  } catch (err) {
    console.error("Không thể sao chép mã:", err);
    // Fallback cho các trình duyệt cũ, nếu cần
    const textarea = document.createElement('textarea');
    textarea.value = code;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
  }
}

function getDiscountIdByCode(code) {
  const discount = discounts.value.find(d => d.ma_giam_gia === code);
  return discount ? discount.giam_gia_id : null;
}

function isExpired(endDateString) {
  const now = new Date();
  const endDate = new Date(endDateString);
  return now > endDate;
}

function formatDate(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
}

function formatValue(discount) {
  if (discount.loai_giam_gia === 'percentage') {
    return `${discount.gia_tri}%`;
  }
  if (discount.loai_giam_gia === 'free_ship') {
    return `Miễn phí vận chuyển`;
  }
  if (discount.gia_tri === null) return '0 đ';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(discount.gia_tri);
}

function formatCurrency(value) {
  if (value === null || value === 0) return '0 đ';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
}

function showDetails(discount) {
  const details = `
    Tên chương trình: ${discount.ten_chuong_trinh}
    Mã giảm giá: ${discount.ma_giam_gia}
    Loại giảm giá: ${discount.loai_giam_gia}
    Giá trị: ${formatValue(discount)}
    Số lượng còn lại: ${discount.so_luong}
    Ngày bắt đầu: ${formatDate(discount.ngay_bat_dau)}
    Ngày kết thúc: ${formatDate(discount.ngay_ket_thuc)}
    Đơn hàng tối thiểu: ${formatCurrency(discount.gia_tri_don_hang_toi_thieu)}
    Giảm tối đa: ${formatCurrency(discount.gia_tri_giam_toi_da)}
    Trạng thái: ${discount.trang_thai ? 'Hoạt động' : 'Không hoạt động'}
  `;
  alert('Thông tin chi tiết:\n' + details);
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600;700&display=swap');

/* ==================== GENERAL STYLES ==================== */
.discount-app-container {
  font-family: "Lora", serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  padding: 60px 20px;
  box-sizing: border-box;
  background-color: #f7f9fc;
}

/* ==================== HEADER SECTION ==================== */
.discount-app-header-main {
  max-width: 1280px;
  margin: 0 auto 30px;
  background-color: #ffffff;
  border-radius: 16px;
  padding: 30px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.discount-app-grid-container {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

@media (min-width: 1024px) {
  .discount-app-grid-container {
    grid-template-columns: repeat(4, 1fr);
    padding: 2rem;
  }
}

.discount-app-grid-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.discount-app-icon {
  width: 2.5rem;
  height: 2.5rem;
  color: #007bff;
  flex-shrink: 0;
}

.discount-app-item-title {
  font-weight: 600;
  color: #1a1a1a;
  margin: 0;
  font-size: 1rem;
}

.discount-app-item-subtitle {
  color: #666;
  margin: 0;
  font-size: 0.875rem;
}

/* ==================== DISCOUNT CARDS SECTION ==================== */
.discount-app-cards-grid {
  display: grid;
  grid-template-columns: repeat(1, 1fr);
  gap: 30px;
  max-width: 1280px;
  margin: 0 auto;
}

@media (min-width: 768px) {
  .discount-app-cards-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .discount-app-cards-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

.discount-app-card-item {
  position: relative;
  background-color: #ffffff;
  border-radius: 16px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border: 1px solid #f0f0f0;
}

.discount-app-card-item:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
}

.discount-app-card-item.discount-app-card-expired {
  filter: grayscale(100%);
  pointer-events: none;
}

/* Card Header */
.discount-app-card-header {
  padding: 1.5rem;
  background-color: #f8f9fa;
  position: relative;
  border-bottom: 1px solid #e9ecef;
}

.discount-app-badge {
  background-color: #007bff;
  color: #ffffff;
  font-weight: 700;
  font-size: 14px;
  padding: 5px 12px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  display: inline-block;
  text-align: center;
}

.discount-app-coupon-code {
  font-family: 'Courier New', Courier, monospace;
}

/* Card Body */
.discount-app-card-body {
  flex-grow: 1;
  padding: 20px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
}

.discount-app-value {
  font-size: 32px;
  font-weight: 800;
  color: #ff4500;
  margin: 0;
  line-height: 1.2;
}

.discount-app-program-title {
  font-size: 16px;
  font-weight: 500;
  color: #666;
  margin: 0.5rem 0;
  line-height: 1.4;
  height: 3rem; /* Fixed height to prevent CLS */
}

.discount-app-card-details {
  display: flex;
  flex-direction: column;
  align-items: center;
  font-size: 14px;
  color: #666;
  margin-top: 1rem;
}

.discount-app-expiration-date {
  margin: 0;
}

.discount-app-details-link {
  color: #007bff;
  text-decoration: none;
  display: flex;
  align-items: center;
  margin-top: 0.25rem;
  transition: color 0.3s ease;
  font-weight: 600;
}

.discount-app-details-link:hover {
  color: #0056b3;
}

.discount-app-details-icon {
  width: 1rem;
  height: 1rem;
  margin-right: 0.25rem;
}

/* Card Footer */
.discount-app-card-actions {
  padding: 20px;
  border-top: 1px solid #f0f0f0;
  text-align: center;
  margin-top: auto;
}

.discount-app-apply-button {
  width: 100%;
  font-weight: 600;
  padding: 12px 24px;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  cursor: pointer;
  border: none;
  font-size: 16px;
  background-color: #007bff;
  color: #fff;
}

.discount-app-apply-button:hover {
  background-color: #0056b3;
  transform: translateY(-2px);
  box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
}

.discount-app-expired-button {
  width: 100%;
  font-weight: 600;
  padding: 12px 24px;
  border-radius: 10px;
  background-color: #ccc;
  color: #999;
  cursor: not-allowed;
  border: none;
  font-size: 16px;
}

/* Expired Overlay */
.discount-app-expired-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.85);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 10;
  font-size: 20px;
  font-weight: bold;
  color: #e63946;
  text-transform: uppercase;
  transform: rotate(-15deg);
  border-radius: 16px;
}

/* ==================== LOADING STATE ==================== */
.discount-app-loading-state {
  text-align: center;
  padding: 2rem;
  color: #666;
}

.discount-app-spinner-container {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.discount-app-spinner {
  border: 4px solid rgba(0, 0, 0, 0.1);
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  border-left-color: #007bff;
  animation: spin 1s ease infinite;
}

.discount-app-loading-text {
  margin-top: 1rem;
  font-size: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
