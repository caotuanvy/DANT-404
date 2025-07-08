<template>
  <div class="page-wrapper">
    <header class="page-header">
      <div>
        <h1 class="page-title">Danh sách danh mục tin tức</h1>
      </div>
      <router-link to="/admin/danh-muc-tin-tuc/add" class="btn-add">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
        <span>Thêm danh mục</span>
      </router-link>
    </header>

    <div class="content-card">
      <div class="row mb-3 g-2 filter-bar">
        <div class="col-md-6">
          <div class="search-input-wrapper">
            <input
              type="text"
              class="form-control search-input"
              placeholder="Tìm kiếm theo tên danh mục hoặc mô tả..."
              v-model="searchQuery"
            />
            <button
              v-if="searchQuery"
              class="clear-search-btn"
              @click="clearSearch"
              aria-label="Xóa tìm kiếm"
            >
              &times;
            </button>
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select" v-model="statusFilter">
            <option value="">Tất cả trạng thái</option>
            <option value="1">Hiển thị</option>
            <option value="0">Ẩn</option>
          </select>
        </div>
      </div>

      <div class="table-container">
        <table class="danhmuc-table">
          <thead>
            <tr>
              <th class="w-12"><input type="checkbox"></th>
              <th>Tên danh mục</th>
              <th class="text-center">Hình ảnh</th>
              <th class="text-center">Trạng thái</th>
              <th class="text-center">Ngày tạo</th>
              <th class="text-center">Ngày sửa</th>
              <th class="text-center">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="7" class="text-center py-8">Đang tải dữ liệu...</td>
            </tr>
            <tr v-for="(item, index) in filteredDanhMuc" :key="item.id_danh_muc_tin_tuc" class="table-row" :class="{'is-inactive-row': item.trang_thai != 1}">
              <td><input type="checkbox"></td>
              <td>
                <div class="danhmuc-title-cell">
                  <router-link :to="`/admin/danh-muc-tin-tuc/${item.id_danh_muc_tin_tuc}`" class="danhmuc-title danhmuc-title-link">
                    {{ truncateText(item.ten_danh_muc, 10) }}
                  </router-link>
                  <div class="danhmuc-description">{{ truncateText(item.mo_ta || 'Không có mô tả', 10) }}</div>
                </div>
              </td>
              <td class="text-center">
                <img
                  :src="getImageUrl(item.hinh_anh)"
                  :alt="item.ten_danh_muc"
                  class="danhmuc-thumbnail"
                />
              </td>
              <td class="text-center">
                <span class="status-badge" :class="item.trang_thai == 1 ? 'is-active' : 'is-inactive'">
                  {{ item.trang_thai == 1 ? 'Hiển thị' : 'Ẩn' }}
                </span>
              </td>
              <td class="text-center">{{ item.ngay_tao ? new Date(item.ngay_tao).toLocaleDateString() : 'N/A' }}</td>
              <td class="text-center">{{ item.ngay_sua ? new Date(item.ngay_sua).toLocaleDateString() : 'N/A' }}</td>
              <td class="text-center">
                <div class="action-buttons">
                  <router-link :to="`/admin/danh-muc-tin-tuc/${item.id_danh_muc_tin_tuc}`" class="action-icon" title="Xem chi tiết">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                      <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                    </svg>
                  </router-link>
                  <router-link :to="`/admin/danh-muc-tin-tuc/${item.id_danh_muc_tin_tuc}/edit`" class="action-icon" title="Sửa">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                      <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                    </svg>
                  </router-link>
                  <button @click="toggleTrangThai(item)" class="action-icon" :class="item.trang_thai == 1 ? 'text-danger' : 'text-success'" :title="item.trang_thai == 1 ? 'Ẩn danh mục' : 'Hiện danh mục'">
                    <svg v-if="item.trang_thai == 1" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.367zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-if="!loading && filteredDanhMuc.length === 0" class="no-data-message">Chưa có danh mục nào phù hợp.</p>
      <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const danhMuc = ref([]);
const errorMessage = ref('');
const loading = ref(false);
const searchQuery = ref('');
const statusFilter = ref(''); // Khai báo ref mới cho bộ lọc trạng thái

const clearSearch = () => {
  searchQuery.value = '';
};

const truncateText = (text, maxLength) => {
  if (!text) return '';
  if (text.length > maxLength) {
    return text.substring(0, maxLength) + '...';
  }
  return text;
};

const filteredDanhMuc = computed(() => {
  let currentDanhMuc = danhMuc.value;

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    currentDanhMuc = currentDanhMuc.filter(item => {
      const tenDanhMuc = item.ten_danh_muc ? String(item.ten_danh_muc).toLowerCase() : '';
      const moTa = item.mo_ta ? String(item.mo_ta).toLowerCase() : '';
      return tenDanhMuc.includes(query) || moTa.includes(query);
    });
  }

  // Áp dụng bộ lọc trạng thái
  if (statusFilter.value !== '') {
    currentDanhMuc = currentDanhMuc.filter(item => String(item.trang_thai) === statusFilter.value);
  }

  return currentDanhMuc;
});

const fetchDanhMuc = async () => {
  loading.value = true;
  errorMessage.value = '';
  try {
    const response = await axios.get('http://localhost:8000/api/danh-muc-tin-tuc', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    danhMuc.value = response.data;
  } catch (error) {
    console.error('Lỗi khi lấy danh mục tin tức:', error);
    errorMessage.value = 'Lỗi khi lấy danh mục: ' + (error.response?.data?.message || error.message);
  } finally {
    loading.value = false;
  }
};

const toggleTrangThai = async (item) => {
  const oldStatus = item.trang_thai;
  const newStatus = oldStatus === 1 ? 0 : 1;
  const actionText = newStatus === 1 ? 'hiện' : 'ẩn';
  const confirmMessage = newStatus === 1
    ? `Bạn có chắc muốn hiện lại danh mục "${item.ten_danh_muc}" này không?`
    : `Bạn có chắc muốn ẩn danh mục "${item.ten_danh_muc}" này không?`;

  if (!confirm(confirmMessage)) {
    return;
  }

  item.trang_thai = newStatus;

  try {
    await axios.put(`http://localhost:8000/api/danh-muc-tin-tuc/${item.id_danh_muc_tin_tuc}/toggle-status`, {
      trang_thai: newStatus,
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });

    alert(`Đã ${actionText} danh mục "${item.ten_danh_muc}" thành công!`);

  } catch (error) {
    item.trang_thai = oldStatus;
    console.error('Lỗi khi cập nhật trạng thái danh mục:', error);
    alert(`Thao tác ${actionText} danh mục "${item.ten_danh_muc}" thất bại: ` + (error.response?.data?.message || error.message));
  }
};

const getImageUrl = (url) => {
  if (!url) return 'https://placehold.co/80x60?text=No+Image';
  return url.startsWith('http') ? url : `http://localhost:8000/storage/${url}`;
};

onMounted(() => {
  fetchDanhMuc();
});
</script>

<style scoped>
/*
  Base styles for page layout and common elements to ensure consistency
  across your admin pages.
*/
.page-wrapper {
  background-color: #f3f4f6;
  padding: 2rem;
  min-height: 100vh;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}
.page-title {
  font-size: 1.875rem; /* Equivalent to H1 */
  font-weight: 700;
  color: #111827;
}
.content-card {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  padding: 1.5rem;
}

/* --- Buttons & Inputs --- */
.btn-add {
  background-color: #4FC3F7; /* Light blue, can be adjusted */
  color: white;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1rem;
  border: 1px solid transparent;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.3s;
  text-decoration: none;
  height: 40px;
  width: 180px;
  justify-content: center;
}
.btn-add svg {
  width: 20px;
  height: 20px;
}
.btn-add:hover {
  background-color:#3b82f6; /* Darker blue on hover */
  transform: scale(1.05);
}

.filter-bar {
  display: flex;
  justify-content: flex-start; /* Align search to start */
  margin-bottom: 1.5rem;
  gap: 1rem; /* Space between filter elements if more are added */
}
.search-input-wrapper {
  position: relative;
  width: 100%; /* Allow input to fill its column */
  max-width: 400px; /* Max width for search input */
}
.form-control { /* Basic styling for form inputs */
  display: block;
  width: 100%;
  padding: 0.375rem 0.75rem;
  font-size: 1rem;
  font-weight: 400;
  line-height: 1.5;
  color: #495057;
  background-color: #fff;
  background-clip: padding-box;
  border: 1px solid #ced4da;
  border-radius: 0.25rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}
.form-select { /* Styles for select elements */
  display: block;
  width: 100%;
  padding: 0.375rem 2.25rem 0.375rem 0.75rem;
  -moz-padding-start: calc(0.75rem - 3px);
  font-size: 1rem;
  font-weight: 400;
  line-height: 1.5;
  color: #495057;
  background-color: #fff;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 16px 12px;
  border: 1px solid #ced4da;
  border-radius: 0.25rem;
  appearance: none;
}
.form-select:focus {
  border-color: #80bdff;
  outline: 0;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
.search-input {
  padding-left: 2.5rem; /* Space for a potential search icon if added */
  min-width: 300px; /* Adjust as needed */
}
.clear-search-btn {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: transparent;
  border: none;
  font-size: 1.2rem;
  color: #888;
  cursor: pointer;
  padding: 0 6px;
}
.clear-search-btn:hover {
  color: #dc2626; /* Red on hover */
}
.col-md-3 {
    flex: 0 0 auto;
    width: 25%; /* Điều chỉnh độ rộng cột cho phù hợp với 3 cột (tìm kiếm, trạng thái, và có thể thêm cột khác) */
}


/* --- Table Styles --- */
.table-container {
  overflow-x: auto; /* Ensures table is scrollable on small screens */
}
.danhmuc-table { /* Renamed from news-table for clarity */
  width: 100%;
  border-collapse: collapse;
  white-space: nowrap; /* Prevent text wrapping in cells */
}
.danhmuc-table th, .danhmuc-table td {
  padding: 1rem;
  text-align: left;
  vertical-align: middle;
  border-bottom: 1px solid #e5e7eb; /* Light gray border */
}
.danhmuc-table th {
  background-color: #f9fafb; /* Lighter background for headers */
  color: #6b7280; /* Gray text */
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
}
.danhmuc-table tbody tr:last-child td {
  border-bottom: none; /* No border for the last row */
}

/* --- Table Cell Specific Styles --- */
.w-12 { width: 3rem; } /* For checkbox column */
.text-center { text-align: center; }
.py-8 { padding-top: 2rem; padding-bottom: 2rem; } /* For loading/empty states */

.danhmuc-title-cell {
  display: flex;
  flex-direction: column;
}
.danhmuc-title {
  font-weight: 500;
  color: #111827; /* Dark gray */
  text-decoration: none;
  white-space: nowrap;          
  overflow: hidden;             
  text-overflow: ellipsis;      
  max-width: 220px;             
  display: block;               
}
.danhmuc-title-link:hover {
  text-decoration: underline;
  color: #2563eb; /* Blue on hover */
}
.danhmuc-description { /* Used for mo_ta */
  font-size: 0.85rem;
  color: #6b7280; /* Gray text */
  white-space: nowrap;          
  overflow: hidden;             
  text-overflow: ellipsis;      
  max-width: 220px;             
  display: block;
}
.danhmuc-thumbnail {
  width: 80px;
  height: 60px;
  border-radius: 4px;
  object-fit: cover; /* Ensures image fills the area without distortion */
  border: 1px solid #e5e7eb;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px; /* Pill shape */
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: capitalize;
}
.status-badge.is-active {
  color: #16a34a; /* Green text */
  background-color: #dcfce7; /* Light green background */
}
.status-badge.is-inactive {
  color: #6b7280; /* Gray text */
  background-color: #e5e7eb; /* Light gray background */
}

/* --- Action Buttons (Icons) --- */
.action-buttons {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.25rem; /* Đã thay đổi khoảng cách ở đây từ 0.75rem xuống 0.25rem */
  flex-wrap: nowrap;
}
.action-icon {
  width: 32px;
  height: 32px;
  padding: 0;
  background-color: transparent;
  border-radius: 50%; /* Circular button */
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s, transform 0.2s;
}
.action-icon svg {
  width: 20px;
  height: 20px;
  color: #111827; /* Default icon color */
  transition: color 0.2s;
}
.action-icon:hover {
  background-color: #e0e7ff; /* Light blue background on hover */
  transform: scale(1.1);
}
.action-icon.text-danger svg {
  color: #dc2626; /* Red for delete/hide icons */
}
.action-icon.text-success svg {
  color: #16a34a; /* Green for show/activate icons */
}
.action-icon.text-danger:hover svg {
  color: #b91c1c; /* Darker red on hover */
}
.action-icon.text-success:hover svg {
  color: #15803d; /* Darker green on hover */
}

/* --- Inactive Row Styling --- */
.is-inactive-row {
  background-color: #f8f8f8; /* Slightly darker background for inactive rows */
  opacity: 0.8; /* Dim inactive rows slightly */
}
.is-inactive-row .danhmuc-title {
  color: #9ca3af; /* Lighter text for inactive titles */
}

/* --- Message Styles (Loading, Error, No Data) --- */
.error-message {
  color: #dc2626; /* Red text */
  margin-top: 1rem;
  text-align: center;
  padding: 1rem;
}
.no-data-message {
  padding: 2rem;
  text-align: center;
  color: #6b7280; /* Gray text */
}
</style>