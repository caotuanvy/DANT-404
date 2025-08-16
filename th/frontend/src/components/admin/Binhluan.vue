<template>
  <section class="content">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <h2>Danh sách bình luận</h2>

    <div class="filter-section">
      <div class="filter-group">
        <label for="filterLoai">Loại bình luận:</label>
        <select id="filterLoai" v-model="filterLoai" class="filter-select">
          <option value="">Tất cả</option>
          <option value="san_pham">Sản phẩm</option>
          <option value="tin_tuc">Tin tức</option>
        </select>
      </div>
      <div class="filter-group">
        <label for="filterBaoCao">Trạng thái báo cáo:</label>
        <select id="filterBaoCao" v-model="filterBaoCao" class="filter-select">
          <option value="">Tất cả</option>
          <option value="0">Bình thường</option>
          <option value="1">Spam bình luận</option>
          <option value="2">Dùng từ ngữ xúc phạm</option>
        </select>
      </div>
      <button @click="applyFilters" class="btn-primary">Lọc</button>
    </div>

    <div v-if="loading" class="loading-message">Đang tải dữ liệu...</div>
    <div class="table-wrapper" v-if="!loading && binhLuans.length > 0">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Nội dung</th>
            <th>Người dùng</th>
            <th>Đối tượng</th>
            <th>Ngày bình luận</th>
            <th class="toggle-cell">Hiển thị</th> 
            <th class="text-center">Báo cáo</th> 
            <th class="text-center">Lượt thích</th>
            <th class="text-center">Lượt không thích</th>
            <th class="text-center">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(binhLuan, index) in binhLuans" :key="binhLuan.binh_luan_id">
            <td data-label="#">{{ (currentPage - 1) * perPage + index + 1 }}</td>
            <td data-label="Nội dung" class="content-cell" @click="showFullContent(binhLuan.noidung)">
              <span v-if="binhLuan.noidung && binhLuan.noidung.length > 6">
                {{ binhLuan.noidung.substring(0, 6) + '...' }}
              </span>
              <span v-else>
                {{ binhLuan.noidung }}
              </span>
            </td>
            <td data-label="Người dùng">{{ binhLuan.nguoi_dung ? binhLuan.nguoi_dung.ho_ten : binhLuan.ho_ten_khach }}</td>
            <td data-label="Đối tượng">
              <span v-if="binhLuan.san_pham">{{ binhLuan.san_pham.ten_san_pham }} (SP)</span>
              <span v-else-if="binhLuan.tin_tuc">Tin tức</span>
              <span v-else>-</span>
            </td>
            <td data-label="Ngày bình luận">{{ binhLuan.ngay_binh_luan ? binhLuan.ngay_binh_luan.substring(0, 10) : '' }}</td>
            <td data-label="Hiển thị" class="toggle-cell">
              <span class="switch" @click="toggleTrangThai(binhLuan)">
                <span :class="['slider', binhLuan.trang_thai == 1 ? 'on' : 'off']"></span>
              </span>
            </td>
            <td data-label="Báo cáo" class="report-status-cell">
              <i :class="getBaoCaoIcon(binhLuan.bao_cao)" :title="getBaoCaoStatus(binhLuan.bao_cao)"></i>
            </td>
            <td data-label="Lượt thích" class="text-center">{{ binhLuan.luot_thich }}</td>
            <td data-label="Lượt không thích" class="text-center">{{ binhLuan.luot_khong_thich }}</td>
            <td data-label="Hành động" class="action-icons-cell">
              <i class="fas fa-check-circle action-icon action-icon-normal"
                 title="Đặt là Bình thường"
                 @click="showConfirmSetBaoCao(binhLuan, 0)"></i>
              <i class="fas fa-ban action-icon action-icon-spam"
                 title="Đặt là Spam bình luận"
                 @click="showConfirmSetBaoCao(binhLuan, 1)"></i>
              <i class="fas fa-triangle-exclamation action-icon action-icon-offensive"
                 title="Đặt là Dùng từ ngữ xúc phạm"
                 @click="showConfirmSetBaoCao(binhLuan, 2)"></i>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <p v-if="!loading && binhLuans.length === 0">Chưa có bình luận nào.</p>
    <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>

    <div class="pagination" v-if="lastPage > 1">
      <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1" class="btn-pagination">Trước</button>
      <span v-for="page in lastPage" :key="page">
        <button @click="goToPage(page)" :class="['btn-pagination', { 'active': page === currentPage }]">{{ page }}</button>
      </span>
      <button @click="goToPage(currentPage + 1)" :disabled="currentPage === lastPage" class="btn-pagination">Sau</button>
    </div>

    <div v-if="showConfirmModal" class="modal-overlay">
      <div class="modal-content">
        <h3>Xác nhận</h3>
        <p>{{ confirmModalMessage }}</p>
        <div class="modal-actions">
          <button @click="handleConfirmAction(true)" class="btn-confirm">Đồng ý</button>
          <button @click="handleConfirmAction(false)" class="btn-cancel">Hủy</button>
        </div>
      </div>
    </div>

    <div v-if="showNotificationModal" class="modal-overlay">
      <div :class="['modal-content', notificationType === 'success' ? 'modal-success' : 'modal-error']">
        <h3>{{ notificationTitle }}</h3>
        <p>{{ notificationMessage }}</p>
        <div class="modal-actions">
          <button @click="closeNotificationModal" class="btn-confirm">Đóng</button>
        </div>
      </div>
    </div>

    <div v-if="showFullContentModal" class="modal-overlay">
      <div class="modal-content full-content-modal">
        <h3>Nội dung đầy đủ</h3>
        <p class="full-content-text">{{ fullContentMessage }}</p>
        <div class="modal-actions">
          <button @click="closeFullContentModal" class="btn-confirm">Đóng</button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// State variables
const binhLuans = ref([]);
const errorMessage = ref('');
const loading = ref(false);

// Filter variables
const filterLoai = ref('');
const filterBaoCao = ref('');

// Pagination variables
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = ref(15);

// Modal variables
const showConfirmModal = ref(false);
const confirmModalMessage = ref('');
let confirmModalResolve = null;

const showNotificationModal = ref(false);
const notificationTitle = ref('');
const notificationMessage = ref('');
const notificationType = ref('');

const showFullContentModal = ref(false);
const fullContentMessage = ref('');

const getBaoCaoStatus = (status) => {
  switch (status) {
    case 0: return 'Bình thường';
    case 1: return 'Spam bình luận';
    case 2: return 'Dùng từ ngữ xúc phạm';
    default: return 'Không xác định';
  }
};

const getBaoCaoIcon = (status) => {
  switch (status) {
    case 0: return 'fas fa-check-circle report-icon-normal';
    case 1: return 'fas fa-ban report-icon-spam';
    case 2: return 'fas fa-exclamation-triangle report-icon-offensive';
    default: return 'fas fa-question-circle report-icon-unknown';
  }
};

const showConfirmation = (message) => {
  confirmModalMessage.value = message;
  showConfirmModal.value = true;
  return new Promise((resolve) => {
    confirmModalResolve = resolve;
  });
};

const handleConfirmAction = (confirmed) => {
  showConfirmModal.value = false;
  if (confirmModalResolve) {
    confirmModalResolve(confirmed);
    confirmModalResolve = null;
  }
};

const showNotification = (title, message, type) => {
  notificationTitle.value = title;
  notificationMessage.value = message;
  notificationType.value = type;
  showNotificationModal.value = true;
};

const closeNotificationModal = () => {
  showNotificationModal.value = false;
  notificationTitle.value = '';
  notificationMessage.value = '';
  notificationType.value = '';
};

const showFullContent = (content) => {
  fullContentMessage.value = content;
  showFullContentModal.value = true;
};

const closeFullContentModal = () => {
  showFullContentModal.value = false;
  fullContentMessage.value = '';
};

const getBinhLuans = async (page = 1) => {
  loading.value = true;
  errorMessage.value = '';
  try {
    const params = { page: page };
    if (filterLoai.value) {
      params.loai = filterLoai.value;
    }
    if (filterBaoCao.value !== '') {
      params.bao_cao = filterBaoCao.value;
    }

    const res = await axios.get('http://localhost:8000/api/admin/binhluan', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
      params: params,
    });
    binhLuans.value = res.data.data;
    currentPage.value = res.data.current_page;
    lastPage.value = res.data.last_page;
    perPage.value = res.data.per_page;
  } catch (error) {
    console.error('Lỗi khi lấy bình luận:', error);
    showNotification('Lỗi', 'Lỗi khi lấy bình luận: ' + (error.response?.data?.message || error.message), 'error');
  } finally {
    loading.value = false;
  }
};

const applyFilters = () => {
  currentPage.value = 1;
  getBinhLuans();
};

const goToPage = (page) => {
  if (page >= 1 && page <= lastPage.value) {
    getBinhLuans(page);
  }
};

const toggleTrangThai = async (binhLuan) => {
  const newTrangThai = binhLuan.trang_thai == 1 ? 0 : 1;
  const originalTrangThai = binhLuan.trang_thai;
  try {
    await axios.put(`http://localhost:8000/api/admin/binhluan/${binhLuan.binh_luan_id}/toggle`, {}, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    binhLuan.trang_thai = newTrangThai;
    showNotification('Thành công', 'Đã cập nhật trạng thái hiển thị.', 'success');
  } catch (error) {
    console.error('Lỗi khi cập nhật trạng thái hiển thị:', error);
    showNotification('Lỗi', 'Cập nhật trạng thái hiển thị thất bại: ' + (error.response?.data?.message || error.message), 'error');
    binhLuan.trang_thai = originalTrangThai;
  }
};

const showConfirmSetBaoCao = async (binhLuan, newStatus) => {
  const statusText = getBaoCaoStatus(newStatus);
  const confirmed = await showConfirmation(`Bạn có chắc muốn đặt trạng thái báo cáo của bình luận này thành "${statusText}" không?`);
  if (confirmed) {
    await setBaoCao(binhLuan, newStatus);
  }
};

const setBaoCao = async (binhLuan, newStatus) => {
  const originalBaoCao = binhLuan.bao_cao;
  try {
    await axios.put(`http://localhost:8000/api/admin/binhluan/${binhLuan.binh_luan_id}/set-bao-cao`, {
      bao_cao: newStatus
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    binhLuan.bao_cao = newStatus;
    showNotification('Thành công', `Đã cập nhật trạng thái báo cáo thành "${getBaoCaoStatus(newStatus)}".`, 'success');
  } catch (error) {
    console.error('Lỗi khi cập nhật báo cáo:', error);
    showNotification('Lỗi', 'Cập nhật báo cáo thất bại: ' + (error.response?.data?.message || error.message), 'error');
    binhLuan.bao_cao = originalBaoCao;
  }
};

onMounted(() => {
  getBinhLuans();
});
</script>

<style scoped>
/*
 * BASE STYLES
 */
.content {
  padding: 20px;
  font-family: 'Inter', sans-serif;
  max-width: 1200px;
  margin: 0 auto;
}

h2 {
  text-align: center;
  font-size: 24px;
  margin-bottom: 20px;
}

/* Filter Section Styling */
.filter-section {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  margin-bottom: 20px;
  padding: 15px;
  background-color: #f9f9f9;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  justify-content: flex-start;
  align-items: center;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 10px;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1em;
  min-width: 150px;
}

.btn-primary {
  padding: 8px 15px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

.btn-primary:hover {
  background-color: #45a049;
  transform: translateY(-1px);
}

/* Table and Responsive Card Styles */
.table-wrapper {
  overflow-x: auto;
  width: 100%;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  border-radius: 8px;
  overflow: hidden;
}

th, td {
  border: 1px solid #ddd;
  padding: 12px 15px;
  text-align: left;
  vertical-align: middle;
}

th {
  background-color: #f2f2f2;
  font-weight: bold;
  color: #333;
  white-space: nowrap; /* Prevent headers from wrapping */
}

tr:nth-child(even) {
  background-color: #f8f8f8;
}

tr:hover {
  background-color: #f1f1f1;
}

.toggle-cell { text-align: center; }
.report-status-cell { text-align: center; }
.text-center { text-align: center; }
.action-icons-cell {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  white-space: nowrap;
}

/* Icon and Button Styles */
.switch {
  display: flex;
  align-items: center;
  cursor: pointer;
  user-select: none;
}
.slider {
  width: 40px; height: 22px;
  border-radius: 11px;
  background: #ccc;
  position: relative;
  transition: background 0.05s ease;
}
.slider::before {
  content: "";
  position: absolute; top: 2px; left: 2px;
  width: 18px; height: 18px;
  border-radius: 50%;
  background: #fff;
  transition: left 0.05s ease;
  box-shadow: 0 1px 4px rgba(0,0,0,0.12);
}
.slider.on { background: #4CAF50; }
.slider.on::before { left: 20px; }

.report-icon-normal { color: #4CAF50; font-size: 1.5em; }
.report-icon-spam { color: #F44336; font-size: 1.5em; }
.report-icon-offensive { color: #FF9800; font-size: 1.5em; }
.report-icon-unknown { color: #9E9E9E; font-size: 1.5em; }

.action-icon {
  font-size: 1.3em;
  cursor: pointer;
  transition: transform 0.2s ease;
}
.action-icon:hover { transform: scale(1.2); }
.action-icon-normal { color: #4CAF50; }
.action-icon-spam { color: #F44336; }
.action-icon-offensive { color: #FF9800; }

.content-cell {
  cursor: pointer;
  text-decoration: none;
  color: inherit;
}
.content-cell:hover {
  color: inherit;
  text-decoration: underline;
}

/* Pagination Styling */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
  gap: 5px;
}
.btn-pagination {
  padding: 8px 12px;
  border: 1px solid #ddd;
  background-color: #fff;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}
.btn-pagination:hover:not(:disabled) { background-color: #f0f0f0; }
.btn-pagination.active {
  background-color: #4FC3F7;
  color: white;
  border-color: #4FC3F7;
}
.btn-pagination:disabled { opacity: 0.6; cursor: not-allowed; }

/* Modal Styling (unchanged, as it's already responsive) */
.modal-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}
.modal-content {
  background-color: white;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  width: 90%; max-width: 400px;
  text-align: center;
  transform: scale(0.95);
  animation: modal-open 0.3s forwards ease-out;
}
.full-content-modal {
  max-width: 600px;
  text-align: left;
}
.full-content-modal .full-content-text {
  white-space: pre-wrap;
  word-wrap: break-word;
  max-height: 400px;
  overflow-y: auto;
  background-color: #f5f5f5;
  padding: 15px;
  border-radius: 5px;
  line-height: 1.6;
}

/*
 * RESPONSIVE MOBILE STYLES (Dưới 768px)
 */
@media (max-width: 768px) {
  .content {
    padding: 10px;
  }
  h2 {
    font-size: 20px;
    margin-bottom: 15px;
  }

  .filter-section {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
    padding: 10px;
  }
  .filter-group {
    flex-direction: column;
    align-items: flex-start;
  }
  .filter-select {
    width: 100%;
    min-width: unset;
  }
  .btn-primary {
    width: 100%;
    margin-top: 10px;
  }

  /* Ẩn header của bảng trên mobile */
  table {
    border: none;
  }
  thead {
    display: none;
  }
  tr {
    display: block;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }
  td {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 15px;
    border: none; /* Loại bỏ viền ô */
    border-bottom: 1px solid #eee;
  }
  td:last-child {
    border-bottom: none;
  }
  td::before {
    content: attr(data-label);
    font-weight: bold;
    color: #555;
    flex-shrink: 0;
    width: 120px;
    text-align: left;
  }
  /* Ẩn cột # trên mobile */
  td:first-child {
    display: none;
  }

  .action-icons-cell {
    justify-content: flex-end; /* Đẩy các icon sang phải */
    gap: 12px;
  }

  .toggle-cell .switch {
    margin-left: auto; /* Đẩy switch sang phải */
  }

  .report-status-cell i {
    font-size: 1.2em; /* Giảm kích thước icon báo cáo */
  }
  
  .action-icon {
    font-size: 1.1em; /* Giảm kích thước icon hành động */
  }
}
</style>