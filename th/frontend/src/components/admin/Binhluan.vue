<template>
  <section class="content">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <h2>Danh sách bình luận</h2>

    <!-- Filter Section -->
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

    <!-- Loading and Empty State Messages -->
    <div v-if="loading" class="loading-message">Đang tải dữ liệu...</div>
    <table v-if="!loading && binhLuans.length > 0">
      <thead>
        <tr>
          <th>#</th>
          <th>Nội dung</th>
          <th>Người dùng</th>
          <th>Đối tượng</th>
          <th>Ngày bình luận</th>
          <th class="toggle-cell">Hiển thị</th> <!-- Căn giữa tiêu đề cột Hiển thị -->
          <th class="text-center">Báo cáo</th> <!-- Căn giữa tiêu đề cột Báo cáo -->
          <th class="text-center">Lượt thích</th>
          <th class="text-center">Lượt không thích</th>
          <th class="text-center">Hành động</th> <!-- Căn giữa tiêu đề cột Hành động -->
        </tr>
      </thead>
      <tbody>
        <tr v-for="(binhLuan, index) in binhLuans" :key="binhLuan.binh_luan_id">
          <td>{{ (currentPage - 1) * perPage + index + 1 }}</td>
          <td class="content-cell" @click="showFullContent(binhLuan.noidung)">
            <span v-if="binhLuan.noidung && binhLuan.noidung.length > 6">
              {{ binhLuan.noidung.substring(0, 6) + '...' }}
            </span>
            <span v-else>
              {{ binhLuan.noidung }}
            </span>
          </td>
          <td>{{ binhLuan.nguoi_dung ? binhLuan.nguoi_dung.ho_ten : binhLuan.ho_ten_khach }}</td>
          <td>
            <span v-if="binhLuan.san_pham">{{ binhLuan.san_pham.ten_san_pham }} (SP)</span>
            <span v-else-if="binhLuan.tin_tuc">Tin tức</span>
            <span v-else>-</span>
          </td>
          <td>{{ binhLuan.ngay_binh_luan ? binhLuan.ngay_binh_luan.substring(0, 10) : '' }}</td>
          <td class="toggle-cell"> <!-- Thêm class để căn giữa nút toggle -->
            <span class="switch" @click="toggleTrangThai(binhLuan)">
              <span :class="['slider', binhLuan.trang_thai == 1 ? 'on' : 'off']"></span>
            </span>
          </td>
          <td class="report-status-cell"> <!-- Thêm class để căn giữa icon -->
            <!-- Hiển thị icon dựa trên trạng thái báo cáo, thêm class màu sắc và kích thước -->
            <i :class="getBaoCaoIcon(binhLuan.bao_cao)" :title="getBaoCaoStatus(binhLuan.bao_cao)"></i>
            <!-- Đã bỏ nút "Đặt lại" ở đây -->
          </td>
          <td class="text-center">{{ binhLuan.luot_thich }}</td>
          <td class="text-center">{{ binhLuan.luot_khong_thich }}</td>
          <td class="action-icons-cell">
            <!-- Các icon hành động để thay đổi trạng thái báo cáo -->
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
    <p v-if="!loading && binhLuans.length === 0">Chưa có bình luận nào.</p>
    <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>

    <!-- Pagination -->
    <div class="pagination" v-if="lastPage > 1">
      <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1" class="btn-pagination">Trước</button>
      <span v-for="page in lastPage" :key="page">
        <button @click="goToPage(page)" :class="['btn-pagination', { 'active': page === currentPage }]">{{ page }}</button>
      </span>
      <button @click="goToPage(currentPage + 1)" :disabled="currentPage === lastPage" class="btn-pagination">Sau</button>
    </div>

    <!-- Custom Confirmation Modal -->
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

    <!-- Custom Error Modal -->
    <div v-if="showErrorModal" class="modal-overlay">
      <div class="modal-content">
        <h3>Lỗi</h3>
        <p>{{ errorModalMessage }}</p>
        <div class="modal-actions">
          <button @click="closeErrorModal" class="btn-confirm">Đóng</button>
        </div>
      </div>
    </div>

    <!-- Full Content Display Modal -->
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
const perPage = ref(15); // Default items per page, matches controller's paginate(15)

// Modal variables
const showConfirmModal = ref(false);
const confirmModalMessage = ref('');
let confirmModalResolve = null; // To store the resolve function of the confirmation promise

const showErrorModal = ref(false);
const errorModalMessage = ref('');

// New modal variables for full content
const showFullContentModal = ref(false);
const fullContentMessage = ref('');

/**
 * @description Helper function to get the descriptive status for 'bao_cao' (report status).
 * @param {number} status - The numerical status of the report (0, 1, 2).
 * @returns {string} The descriptive string for the report status.
 */
const getBaoCaoStatus = (status) => {
  switch (status) {
    case 0: return 'Bình thường';
    case 1: return 'Spam bình luận'; // Cập nhật ý nghĩa
    case 2: return 'Dùng từ ngữ xúc phạm'; // Cập nhật ý nghĩa
    default: return 'Không xác định';
  }
};

/**
 * @description Helper function to get the Font Awesome icon class and color for 'bao_cao' (report status).
 * @param {number} status - The numerical status of the report (0, 1, 2).
 * @returns {string} The Font Awesome icon class and color class.
 */
const getBaoCaoIcon = (status) => {
  switch (status) {
    case 0: return 'fas fa-check-circle report-icon-normal'; // Bình thường (màu xanh lá)
    case 1: return 'fas fa-ban report-icon-spam'; // Spam bình luận (màu đỏ)
    case 2: return 'fas fa-exclamation-circle report-icon-offensive'; // Dùng từ ngữ xúc phạm (màu cam)
    default: return 'fas fa-question-circle report-icon-unknown'; // Không xác định (màu xám)
  }
};

/**
 * @description Displays a custom confirmation modal to the user.
 * @param {string} message - The message to display in the confirmation modal.
 * @returns {Promise<boolean>} A promise that resolves to true if confirmed, false otherwise.
 */
const showConfirmation = (message) => {
  confirmModalMessage.value = message;
  showConfirmModal.value = true;
  return new Promise((resolve) => {
    confirmModalResolve = resolve;
  });
};

/**
 * @description Handles the action taken in the confirmation modal (confirm/cancel).
 * @param {boolean} confirmed - True if the user confirmed, false if canceled.
 */
const handleConfirmAction = (confirmed) => {
  showConfirmModal.value = false;
  if (confirmModalResolve) {
    confirmModalResolve(confirmed);
    confirmModalResolve = null;
  }
};

/**
 * @description Displays a custom error modal to the user.
 * @param {string} message - The error message to display.
 */
const showErrorMessage = (message) => {
  errorModalMessage.value = message;
  showErrorModal.value = true;
};

/**
 * @description Closes the error modal.
 */
const closeErrorModal = () => {
  showErrorModal.value = false;
  errorModalMessage.value = '';
};

/**
 * @description Displays the full content in a modal.
 * @param {string} content - The full content string to display.
 */
const showFullContent = (content) => {
  fullContentMessage.value = content;
  showFullContentModal.value = true;
};

/**
 * @description Closes the full content modal.
 */
const closeFullContentModal = () => {
  showFullContentModal.value = false;
  fullContentMessage.value = '';
};

/**
 * @description Fetches the list of comments from the API, with optional filters and pagination.
 * @param {number} [page=1] - The page number to fetch.
 */
const getBinhLuans = async (page = 1) => {
  loading.value = true;
  errorMessage.value = '';
  try {
    const params = { page: page };
    if (filterLoai.value) {
      params.loai = filterLoai.value;
    }
    // Check for empty string to include '0' as a valid filter value
    if (filterBaoCao.value !== '') {
      params.bao_cao = filterBaoCao.value;
    }

    const res = await axios.get('http://localhost:8000/api/admin/binhluan', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
      params: params,
    });
    // Assuming the API returns paginated data under a 'data' key
    binhLuans.value = res.data.data;
    currentPage.value = res.data.current_page;
    lastPage.value = res.data.last_page;
    perPage.value = res.data.per_page;
  } catch (error) {
    console.error('Lỗi khi lấy bình luận:', error);
    showErrorMessage('Lỗi khi lấy bình luận: ' + (error.response?.data?.message || error.message));
  } finally {
    loading.value = false;
  }
};

/**
 * @description Applies the selected filters and reloads the comments from the first page.
 */
const applyFilters = () => {
  currentPage.value = 1; // Reset to first page when applying filters
  getBinhLuans();
};

/**
 * @description Changes the current page for pagination.
 * @param {number} page - The page number to navigate to.
 */
const goToPage = (page) => {
  if (page >= 1 && page <= lastPage.value) {
    getBinhLuans(page);
  }
};

/**
 * @description Toggles the 'trang_thai' (display status) of a comment.
 * @param {object} binhLuan - The comment object to update.
 */
const toggleTrangThai = async (binhLuan) => {
  const newTrangThai = binhLuan.trang_thai == 1 ? 0 : 1;
  try {
    // The PUT request body is empty as per the controller's toggleTrangThai method
    await axios.put(`http://localhost:8000/api/admin/binhluan/${binhLuan.binh_luan_id}/toggle`, {}, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    binhLuan.trang_thai = newTrangThai; // Update locally on successful API call
  } catch (error) {
    console.error('Lỗi khi cập nhật trạng thái hiển thị:', error);
    showErrorMessage('Cập nhật trạng thái hiển thị thất bại: ' + (error.response?.data?.message || error.message));
  }
};

/**
 * @description Shows a confirmation modal before setting the report status of a comment.
 * @param {object} binhLuan - The comment object to set report status for.
 * @param {number} newStatus - The new report status to set (0, 1, or 2).
 */
const showConfirmSetBaoCao = async (binhLuan, newStatus) => {
  const statusText = getBaoCaoStatus(newStatus);
  const confirmed = await showConfirmation(`Bạn có chắc muốn đặt trạng thái báo cáo của bình luận này thành "${statusText}" không?`);
  if (confirmed) {
    await setBaoCao(binhLuan, newStatus);
  }
};

/**
 * @description Sets the 'bao_cao' (report status) of a comment and updates 'trang_thai' accordingly.
 * @param {object} binhLuan - The comment object to update.
 * @param {number} newStatus - The new report status to set (0, 1, or 2).
 */
const setBaoCao = async (binhLuan, newStatus) => {
  try {
    // First, update the bao_cao status
    await axios.put(`http://localhost:8000/api/admin/binhluan/${binhLuan.binh_luan_id}/set-bao-cao`, {
      bao_cao: newStatus
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    binhLuan.bao_cao = newStatus; // Update locally on successful API call

    // Now, determine the target 'trang_thai' based on the new 'bao_cao' status
    let targetTrangThai;
    if (newStatus === 0) { // If setting to 'Bình thường'
      targetTrangThai = 1; // 'Hiển thị' should be ON
    } else { // If setting to 'Spam bình luận' or 'Dùng từ ngữ xúc phạm'
      targetTrangThai = 0; // 'Hiển thị' should be OFF
    }

    // Check if the current 'trang_thai' is different from the target 'trang_thai'
    // If they are different, call toggleTrangThai to flip the state
    if (binhLuan.trang_thai !== targetTrangThai) {
      // Call the existing toggleTrangThai function.
      // Since toggleTrangThai flips the state, if current is 1 and target is 0, it will flip to 0.
      // If current is 0 and target is 1, it will flip to 1.
      // This works because we only call it if it needs to be flipped.
      await toggleTrangThai(binhLuan); // This will make another API call to flip 'trang_thai'
    }

    // No need for a separate success message here, as toggleTrangThai already handles it.
    // If you want a combined message, you'd need to modify toggleTrangThai to return a promise
    // and handle messages here, or make setBaoCao responsible for all messages.
  } catch (error) {
    console.error('Lỗi khi cập nhật báo cáo:', error);
    showErrorMessage('Cập nhật báo cáo thất bại: ' + (error.response?.data?.message || error.message));
  }
};

// Fetch comments when the component is mounted
onMounted(() => {
  getBinhLuans();
});
</script>

<style scoped>
/* General content padding */
.content {
  padding: 20px;
  font-family: 'Inter', sans-serif; /* Using Inter font */
}

/* Filter Section Styling */
.filter-section {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin-bottom: 20px;
  padding: 15px;
  background-color: #f9f9f9;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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

/* Primary Button Styling */
.btn-primary {
  padding: 8px 15px;
  background-color: #4CAF50; /* Green */
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

/* Reset Button Styling (now used for general set status) */
.btn-reset {
  padding: 5px 10px;
  background-color: #f44336; /* Red */
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.85em;
  margin-left: 8px;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

.btn-reset:hover {
  background-color: #d32f2f;
  transform: translateY(-1px);
}

/* Table Styling */
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  border-radius: 8px;
  overflow: hidden; /* Ensures rounded corners apply to content */
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
}

tr:nth-child(even) {
  background-color: #f8f8f8;
}

tr:hover {
  background-color: #f1f1f1;
}

/* Toggle Switch Styling */
.switch {
  display: flex;
  align-items: center;
  gap: 0;
  cursor: pointer;
  user-select: none;
  transition: background 0.2s ease; /* Giảm thời gian chuyển đổi rất nhanh */
  justify-content: center;
}

.slider {
  width: 40px;
  height: 22px;
  border-radius: 11px;
  background: #ccc;
  position: relative;
  transition: background 0.2s ease; /* Giảm thời gian chuyển đổi rất nhanh */
  flex-shrink: 0;
  display: inline-block;
}

.slider::before {
  content: "";
  position: absolute;
  top: 2px;
  left: 2px;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #fff;
  transition: left 0.3s ease, background 0.2s ease; /* Giảm thời gian chuyển đổi rất nhanh */
  box-shadow: 0 1px 4px rgba(0,0,0,0.12);
}

.slider.on {
  background: #4CAF50; /* Green */
}
.slider.on::before {
  left: 20px;
  background: #fff;
}

/* Căn giữa nút toggle trong ô */
.toggle-cell {
  text-align: center;
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

.btn-pagination:hover:not(:disabled) {
  background-color: #f0f0f0;
}

.btn-pagination.active {
  background-color: #4FC3F7; /* Light Blue */
  color: white;
  border-color: #4FC3F7;
}

.btn-pagination:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Modal Styling */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
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
  width: 90%;
  max-width: 400px;
  text-align: center;
  transform: scale(0.95);
  animation: modal-open 0.3s forwards ease-out;
}

@keyframes modal-open {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

.modal-content h3 {
  margin-top: 0;
  color: #333;
  font-size: 1.5em;
  margin-bottom: 15px;
}

.modal-content p {
  color: #555;
  margin-bottom: 25px;
}

.modal-actions {
  display: flex;
  justify-content: center;
  gap: 15px;
}

.btn-confirm, .btn-cancel {
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-weight: bold;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

.btn-confirm {
  background-color: #4CAF50; /* Green */
  color: white;
}

.btn-confirm:hover {
  background-color: #45a049;
  transform: translateY(-1px);
}

.btn-cancel {
  background-color: #f44336; /* Red */
  color: white;
}

.btn-cancel:hover {
  background-color: #d32f2f;
  transform: translateY(-1px);
}

/* Loading and Error Messages */
.loading-message, .error-message {
  text-align: center;
  margin-top: 20px;
  font-size: 1.1em;
  padding: 10px;
  border-radius: 5px;
  font-weight: bold;
}

.loading-message {
  background-color: #e0f7fa; /* Light Cyan */
  color: #00796b; /* Dark Cyan */
  border: 1px solid #b2ebf2;
}

.error-message {
  background-color: #ffebee; /* Light Red */
  color: #c62828; /* Dark Red */
  border: 1px solid #ef9a9a;
}

/* Custom styles for report status icons */
.report-status-cell {
  text-align: center; /* Căn giữa nội dung ô */
}

.report-icon-normal {
  color: #4CAF50; /* Green */
  font-size: 1.5em; /* Kích thước lớn hơn */
}

.report-icon-spam { /* New class for spam icon */
  color: #F44336; /* Red */
  font-size: 1.5em;
}

.report-icon-offensive { /* New class for offensive icon */
  color: #FF9800; /* Orange */
  font-size: 1.5em;
}

.report-icon-unknown {
  color: #9E9E9E; /* Gray */
  font-size: 1.5em; /* Kích thước lớn hơn */
}

/* Styles for action icons in the "Hành động" column */
.action-icons-cell {
  text-align: center; /* Căn giữa các icon */
  display: flex; /* Sử dụng flexbox để căn giữa và tạo khoảng cách */
  justify-content: center; /* Căn giữa theo chiều ngang */
  align-items: center; /* Căn giữa theo chiều dọc */
  gap: 10px; /* Khoảng cách giữa các icon */
  border: none; /* Loại bỏ khung viền cho ô này */
  padding-top: 20px; /* Thêm khoảng đệm trên để đẩy icon xuống */
}

.action-icon {
  font-size: 1.3em; /* Kích thước icon hành động */
  cursor: pointer;
  transition: transform 0.2s ease;
}

.action-icon:hover {
  transform: scale(1.2); /* Hiệu ứng phóng to khi hover */
}

.action-icon-normal {
  color: #4CAF50; /* Green */
}

.action-icon-spam {
  color: #F44336; /* Red */
}

.action-icon-offensive {
  color: #FF9800; /* Orange */
}


/* Styles for full content modal */
.full-content-modal {
  max-width: 600px; /* Tăng chiều rộng tối đa cho modal nội dung */
  text-align: left; /* Căn trái nội dung */
}

.full-content-modal .full-content-text {
  white-space: pre-wrap; /* Giữ định dạng xuống dòng và khoảng trắng */
  word-wrap: break-word; /* Ngắt từ nếu quá dài */
  max-height: 400px; /* Giới hạn chiều cao */
  overflow-y: auto; /* Thêm thanh cuộn nếu nội dung quá dài */
  background-color: #f5f5f5;
  padding: 15px;
  border-radius: 5px;
  line-height: 1.6;
}

.content-cell {
  cursor: pointer; /* Thêm con trỏ để chỉ ra có thể nhấp */
  text-decoration: none; /* Bỏ gạch chân */
  color: inherit; /* Sử dụng màu chữ mặc định của bảng */
}

.content-cell:hover {
  color: inherit; /* Giữ nguyên màu khi hover */
  text-decoration: underline; /* Thêm gạch chân khi hover để chỉ ra có thể nhấp */
}
</style>
