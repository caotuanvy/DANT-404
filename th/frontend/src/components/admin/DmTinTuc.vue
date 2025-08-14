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
            <tr v-for="(item,) in filteredDanhMuc" :key="item.id_danh_muc_tin_tuc" class="table-row" :class="{'is-inactive-row': item.trang_thai != 1}">
              <td data-label="Chọn"><input type="checkbox"></td>
              <td data-label="Tên danh mục">
                <div class="danhmuc-title-cell">
                  <router-link :to="`/admin/danh-muc-tin-tuc/${item.id_danh_muc_tin_tuc}`" class="danhmuc-title danhmuc-title-link">
                    {{ truncateText(item.ten_danh_muc, 10) }}
                  </router-link>
                  <div class="danhmuc-description">{{ truncateText(item.mo_ta || 'Không có mô tả', 10) }}</div>
                </div>
              </td>
              <td data-label="Hình ảnh" class="text-center">
                <img
                  :src="getImageUrl(item.hinh_anh)"
                  :alt="item.ten_danh_muc"
                  class="danhmuc-thumbnail"
                />
              </td>
              <td data-label="Trạng thái" class="text-center">
                <span class="status-badge" :class="item.trang_thai == 1 ? 'is-active' : 'is-inactive'">
                  {{ item.trang_thai == 1 ? 'Hiển thị' : 'Ẩn' }}
                </span>
              </td>
              <td data-label="Ngày tạo" class="text-center">{{ item.ngay_tao ? new Date(item.ngay_tao).toLocaleDateString() : 'N/A' }}</td>
              <td data-label="Ngày sửa" class="text-center">{{ item.ngay_sua ? new Date(item.ngay_sua).toLocaleDateString() : 'N/A' }}</td>
              <td data-label="Hành động" class="text-center">
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
const statusFilter = ref('');

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
 * BASE STYLES & LAYOUT
 */
.page-wrapper {
  background-color: #f3f4f6;
  padding: 2rem;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}
.page-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: #111827;
}
.content-card {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  padding: 1.5rem;
}

/* --- BUTTONS & INPUTS --- */
.btn-add {
  background-color: #4FC3F7;
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
  background-color:#3b82f6;
  transform: scale(1.05);
}
.filter-bar {
  display: flex;
  justify-content: flex-start;
  margin-bottom: 1.5rem;
  gap: 1rem;
}
.search-input-wrapper {
  position: relative;
  width: 100%;
  max-width: 400px;
}
.form-control {
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
.form-select {
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
  padding-left: 2.5rem;
  min-width: 300px;
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
  color: #dc2626;
}
.col-md-3 {
  flex: 0 0 auto;
  width: 25%;
}

/* --- TABLE STYLES --- */
.table-container {
  overflow-x: auto;
}
.danhmuc-table {
  width: 100%;
  border-collapse: collapse;
  white-space: nowrap;
}
.danhmuc-table th, .danhmuc-table td {
  padding: 1rem;
  text-align: left;
  vertical-align: middle;
  border-bottom: 1px solid #e5e7eb;
}
.danhmuc-table th {
  background-color: #f9fafb;
  color: #6b7280;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
}
.danhmuc-table tbody tr:last-child td {
  border-bottom: none;
}
.w-12 { width: 3rem; }
.text-center { text-align: center; }
.py-8 { padding-top: 2rem; padding-bottom: 2rem; }
.danhmuc-title-cell {
  display: flex;
  flex-direction: column;
}
.danhmuc-title {
  font-weight: 500;
  color: #111827;
  text-decoration: none;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 220px;
  display: block;
}
.danhmuc-title-link:hover {
  text-decoration: underline;
  color: #2563eb;
}
.danhmuc-description {
  font-size: 0.85rem;
  color: #6b7280;
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
  object-fit: cover;
  border: 1px solid #e5e7eb;
}
.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: capitalize;
}
.status-badge.is-active {
  color: #16a34a;
  background-color: #dcfce7;
}
.status-badge.is-inactive {
  color: #6b7280;
  background-color: #e5e7eb;
}
.action-buttons {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.25rem;
  flex-wrap: nowrap;
}
.action-icon {
  width: 32px;
  height: 32px;
  padding: 0;
  background-color: transparent;
  border-radius: 50%;
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
  color: #111827;
  transition: color 0.2s;
}
.action-icon:hover {
  background-color: #e0e7ff;
  transform: scale(1.1);
}
.action-icon.text-danger svg {
  color: #dc2626;
}
.action-icon.text-success svg {
  color: #16a34a;
}
.action-icon.text-danger:hover svg {
  color: #b91c1c;
}
.action-icon.text-success:hover svg {
  color: #15803d;
}
.is-inactive-row {
  background-color: #f8f8f8;
  opacity: 0.8;
}
.is-inactive-row .danhmuc-title {
  color: #9ca3af;
}
.error-message {
  color: #dc2626;
  margin-top: 1rem;
  text-align: center;
  padding: 1rem;
}
.no-data-message {
  padding: 2rem;
  text-align: center;
  color: #6b7280;
}

/* * MOBILE STYLES 
 */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  .page-title {
    font-size: 1.5rem;
  }
  .btn-add {
    width: 100%;
  }

  .filter-bar {
    flex-direction: column;
    gap: 0.5rem;
  }
  .search-input-wrapper {
    max-width: 100%;
  }
  .search-input {
    min-width: unset;
  }
  .col-md-3 {
    width: 100%;
  }

  /* * TABLE RESPONSIVE STYLES 
   */
  .danhmuc-table thead {
    display: none; /* Ẩn header của bảng */
  }
  .danhmuc-table, .danhmuc-table tbody, .danhmuc-table tr, .danhmuc-table td {
    display: block; /* Hiển thị các thành phần như block để chúng xuống dòng */
    width: 100%;
  }
  .danhmuc-table tr {
    margin-bottom: 1.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background-color: #fff;
    padding: 1rem;
  }
  .danhmuc-table td {
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
    padding: 0.75rem 0;
    position: relative;
    padding-left: 120px; /* Tạo khoảng trống cho label */
  }
  .danhmuc-table td:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }
  .danhmuc-table td::before {
    content: attr(data-label);
    position: absolute;
    left: 1rem;
    width: 100px;
    font-weight: 600;
    color: #4b5563;
  }

  /* Ẩn checkbox trên mobile nếu không cần thiết */
  .danhmuc-table td:first-child {
    display: none;
  }
  .danhmuc-title-cell {
    padding-left: 0;
  }
  .danhmuc-thumbnail {
    width: 100px;
    height: 75px;
    margin: 0 auto;
    display: block;
  }
  .action-buttons {
    justify-content: flex-start;
    margin-left: 1rem;
  }
}
</style>