<template>
  <div class="coupon-manager">
    <GiamGiaForm 
      v-if="showFormModal" 
      :coupon-to-edit="couponToEdit"
      @close="closeFormModal" 
      @saved="handleFormSaved" 
    />
    <SendCouponModal
      v-if="showSendModal"
      :coupon-to-send="couponToSend"
      @close="showSendModal = false"
    />
    <div class="page-header">
      <h2 class="page-title">Quản lý Mã giảm giá</h2>
      <button class="btn btn-primary" @click="openCreateModal">
        <i class="bi bi-plus-circle me-2"></i>Tạo mã mới
      </button>
    </div>
    <div class="table-container">
      <table class="coupon-table">
        <thead>
          <tr>
            <th>Mã Giảm Giá</th>
            <th>Tên Chương Trình</th>
            <th>Giá Trị</th>
            <th>Đã Dùng / Tổng</th>
            <th>Ngày Bắt Đầu</th>
            <th>Ngày Kết Thúc</th>
            <th class="text-center">Trạng Thái</th>
            <th class="text-center">Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading">
            <td colspan="8" class="text-center p-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
              </div>
            </td>
          </tr>
          <tr v-else-if="coupons.length === 0">
            <td colspan="8" class="text-center p-5">Không có mã giảm giá nào. Hãy tạo một mã mới!</td>
          </tr>
          <tr v-for="coupon in coupons" :key="coupon.giam_gia_id">
            <td><strong class="coupon-code">{{ coupon.ma_giam_gia }}</strong></td>
            <td>{{ coupon.ten_chuong_trinh }}</td>
            <td>{{ formatValue(coupon) }}</td>
            <td>{{ coupon.so_luong_da_dung }} / {{ coupon.so_luong }}</td>
            <td>{{ formatDate(coupon.ngay_bat_dau) }}</td>
            <td>{{ formatDate(coupon.ngay_ket_thuc) }}</td>
            <td class="text-center"><span class="status-badge" :class="getStatusClass(coupon)">{{ getStatusText(coupon) }}</span></td>
            <td class="text-center">
              <div class="action-buttons">
                <button class="btn btn-sm btn-outline-primary" @click="openEditModal(coupon)" title="Sửa"><i class="bi bi-pencil-square"></i></button>
                <button class="btn btn-sm btn-outline-info" @click="openSendModal(coupon)" title="Gửi cho người dùng"><i class="bi bi-send"></i></button>
                <button 
                  class="btn btn-sm"
                  :class="coupon.trang_thai ? 'btn-outline-secondary' : 'btn-outline-success'"
                  @click="toggleCouponStatus(coupon)" 
                  :title="coupon.trang_thai ? 'Vô hiệu hóa' : 'Kích hoạt lại'"
                >
                  <i class="bi" :class="coupon.trang_thai ? 'bi-toggle-on' : 'bi-toggle-off'"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import GiamGiaForm from './GiamGiaForm.vue';
import SendCouponModal from './SendCouponModal.vue';

const coupons = ref([]);
const isLoading = ref(true);
const showFormModal = ref(false);
const showSendModal = ref(false);
const couponToEdit = ref(null);
const couponToSend = ref(null);

const fetchCoupons = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/admin/giam-gia');
    coupons.value = response.data;
  } catch (error) {
    console.error("Lỗi khi tải danh sách mã giảm giá:", error);
    alert('Không thể tải dữ liệu từ máy chủ. Vui lòng kiểm tra lại đường truyền hoặc token xác thực.');
  } finally {
    isLoading.value = false;
  }
};

const toggleCouponStatus = async (coupon) => {
  const actionText = coupon.trang_thai ? 'vô hiệu hóa' : 'kích hoạt';
  if (confirm(`Bạn có chắc muốn ${actionText} mã giảm giá "${coupon.ma_giam_gia}" không?`)) {
    try {
      const response = await axios.patch(`/admin/giam-gia/${coupon.giam_gia_id}/toggle-status`);
      const updatedCoupon = response.data.coupon;
      const index = coupons.value.findIndex(c => c.giam_gia_id === updatedCoupon.giam_gia_id);
      if (index !== -1) {
        coupons.value[index] = updatedCoupon;
      }
      alert(`Đã ${actionText} mã giảm giá thành công!`);
    } catch (error) {
      console.error("Lỗi khi cập nhật trạng thái:", error);
      alert(error.response?.data?.message || 'Có lỗi xảy ra.');
    }
  }
};

const openCreateModal = () => { couponToEdit.value = null; showFormModal.value = true; };
const openEditModal = (coupon) => { couponToEdit.value = { ...coupon }; showFormModal.value = true; };
const closeFormModal = () => { showFormModal.value = false; couponToEdit.value = null; };
const handleFormSaved = () => { fetchCoupons(); closeFormModal(); };
const openSendModal = (coupon) => { couponToSend.value = coupon; showSendModal.value = true; };

const formatValue = (coupon) => {
  if (coupon.loai_giam_gia === 'percentage') return `${coupon.gia_tri}%`;
  if (coupon.loai_giam_gia === 'fixed_amount') return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(coupon.gia_tri);
  if (coupon.loai_giam_gia === 'free_ship') return 'Miễn phí vận chuyển';
  return coupon.gia_tri;
};
const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('vi-VN', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
};
const getStatusText = (coupon) => {
  if (!coupon.trang_thai) return 'Không hoạt động';
  if (new Date(coupon.ngay_ket_thuc) < new Date()) return 'Đã hết hạn';
  return 'Đang hoạt động';
};
const getStatusClass = (coupon) => {
  const status = getStatusText(coupon);
  if (status === 'Đang hoạt động') return 'status-active';
  if (status === 'Đã hết hạn') return 'status-expired';
  return 'status-inactive';
};

onMounted(fetchCoupons);
</script>


<style scoped>
/* Tổng thể layout */
.coupon-manager {
  padding: 1.5rem;
  background-color: #f9f9f9;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 600;
  color: #333;
}

/* Tùy chỉnh button */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  border: 1px solid transparent;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
}
.btn-primary {
  background-color: #0d6efd;
  color: white;
}
.btn-primary:hover {
  background-color: #0b5ed7;
}

/* Container và Bảng */
.table-container {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.05);
  overflow-x: auto;
}

.coupon-table {
  width: 100%;
  border-collapse: collapse;
}

.coupon-table th, .coupon-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #dee2e6;
  vertical-align: middle;
  white-space: nowrap;
}

.coupon-table thead th {
  background-color: #f8f9fa;
  font-weight: 600;
  color: #495057;
}

.coupon-table tbody tr:hover {
  background-color: #f1f3f5;
}

.coupon-code {
  font-family: 'Courier New', Courier, monospace;
  background-color: #e9ecef;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  color: #d63384;
}

/* Trạng thái */
.status-badge {
  display: inline-block;
  padding: 0.3em 0.6em;
  font-size: 0.85em;
  font-weight: 700;
  line-height: 1;
  text-align: center;
  white-space: nowrap;
  vertical-align: baseline;
  border-radius: 0.375rem;
}
.status-active {
  color: #0f5132;
  background-color: #d1e7dd;
}
.status-inactive {
  color: #41464b;
  background-color: #e2e3e5;
}
.status-expired {
  color: #664d03;
  background-color: #fff3cd;
}

/* Nút hành động */
.action-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}
.action-buttons .btn-sm {
  padding: 0.25rem 0.5rem;
}

.text-center { text-align: center; }
.p-5 { padding: 3rem !important; }
.me-2 { margin-right: 0.5rem; }

/* Bootstrap icons (giả định bạn đã import CSS của bootstrap-icons) */
.spinner-border {
    display: inline-block;
    width: 2rem;
    height: 2rem;
    vertical-align: -0.125em;
    border: 0.25em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    -webkit-animation: .75s linear infinite spinner-border;
    animation: .75s linear infinite spinner-border;
}
.visually-hidden {
    position: absolute!important;
    width: 1px!important;
    height: 1px!important;
    padding: 0!important;
    margin: -1px!important;
    overflow: hidden!important;
    clip: rect(0,0,0,0)!important;
    white-space: nowrap!important;
    border: 0!important;
}
</style>