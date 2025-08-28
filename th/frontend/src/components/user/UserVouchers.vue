<template>
  <div class="user-vouchers-container">
    <h2>Mã giảm giá của bạn</h2>
    <div v-if="loading" class="user-vouchers-loading">Đang tải...</div>
    <div v-else-if="vouchers.length === 0" class="user-vouchers-empty">Bạn chưa lưu mã giảm giá nào.</div>
    <div v-else class="user-vouchers-list">
      <div v-for="voucher in vouchers" :key="voucher.giam_gia_id" class="user-voucher-card">
        <div class="voucher-header">
          <span class="voucher-code">{{ voucher.ma_giam_gia }}</span>
          <span class="voucher-status" :class="{ expired: isExpired(voucher.ngay_ket_thuc) }">
            {{ isExpired(voucher.ngay_ket_thuc) ? 'Hết hạn' : 'Còn hạn' }}
          </span>
        </div>
        <div class="voucher-body">
          <div class="voucher-value">{{ formatValue(voucher) }}</div>
          <div class="voucher-title">{{ voucher.ten_chuong_trinh }}</div>
          <div class="voucher-expiry">HSD: {{ formatDate(voucher.ngay_ket_thuc) }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router'; 

const vouchers = ref([]);
const loading = ref(true);
const router = useRouter(); 

// --- Các hàm helper giữ nguyên ---
function isExpired(dateStr) {
  if (!dateStr) return true;
  return new Date() > new Date(dateStr);
}
function formatDate(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return `${d.getDate().toString().padStart(2, '0')}/${(d.getMonth() + 1).toString().padStart(2, '0')}/${d.getFullYear()}`;
}
function formatValue(voucher) {
  if (voucher.loai_giam_gia === 'percentage') return `${voucher.gia_tri}%`;
  if (voucher.loai_giam_gia === 'free_ship') return 'Miễn phí vận chuyển';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(voucher.gia_tri);
}

// --- Phần onMounted đã được cập nhật ---
onMounted(async () => {
  loading.value = true;
  try {
    // Sửa key lấy token từ 'authToken' sang 'token'
    const token = localStorage.getItem('token'); 

    if (!token) {
      console.log("Người dùng chưa đăng nhập. Vui lòng đăng nhập lại.");
      router.push({ name: 'login' }); // Cần đảm bảo tên route đăng nhập là 'login'
      return; 
    }

    const response = await axios.get('/my-vouchers', {
      headers: {
        'Authorization': `Bearer ${token}` 
      }
    });

    vouchers.value = response.data;
    console.log("Voucher của người dùng:", vouchers.value);

  } catch (error) {
    console.error("Lỗi khi tải mã giảm giá của người dùng:", error.response?.data || error.message);
    
    // Nếu token hết hạn hoặc không hợp lệ (lỗi 401 Unauthorized), chuyển hướng người dùng
    if (error.response && error.response.status === 401) {
      console.log("Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.");
      localStorage.removeItem('token'); // Xóa token cũ
      router.push({ name: 'login' });
    }

    vouchers.value = [];
  } finally {
    loading.value = false;
  }
});
</script>
<style scoped>
.user-vouchers-container {
  margin-top: 30px;
  border-radius: 10px;
  padding: 25px;
}
.user-vouchers-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 18px;
}
.user-voucher-card {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 18px;
  background: #f8f9fa;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.voucher-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.voucher-code {
  font-family: 'Courier New', Courier, monospace;
  font-weight: bold;
  color: #007bff;
  font-size: 1.1em;
}
.voucher-status {
  font-size: 0.95em;
  font-weight: 600;
  color: #28a745;
}
.voucher-status.expired {
  color: #e63946;
}
.voucher-body {
  margin-top: 8px;
}
.voucher-value {
  font-size: 1.3em;
  font-weight: bold;
  color: #ff4500;
}
.voucher-title {
  font-size: 1em;
  color: #333;
}
.voucher-expiry {
  font-size: 0.95em;
  color: #666;
}
.user-vouchers-loading,
.user-vouchers-empty {
  color: #888;
  font-size: 1em;
  margin: 20px 0;
}
</style>
