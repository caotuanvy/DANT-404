<template>
  <div class="page-wrapper">
    <header class="page-header">
      <div>
        <h1 class="page-title">Danh sách danh mục sản phẩm</h1>
      </div>
      <router-link to="/admin/categories/add" class="btn-add">
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
      </div>

      <div class="table-container">
        <table class="danhmuc-table">
          <thead>
            <tr>
              <th class="w-12"><input type="checkbox"></th>
              <th>Tên danh mục</th>
              <th class="text-center">Hình ảnh</th>
              <th class="text-center">Danh mục cha</th>
              <th class="text-center">Mô tả</th>
              <th class="text-center">Trạng thái</th>
              <th class="text-center">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="7" class="text-center py-8">Đang tải dữ liệu...</td>
            </tr>
            <tr v-for="(item,) in filteredCategories" :key="item.category_id" class="table-row">
              <td><input type="checkbox"></td>
              <td>
                <div class="danhmuc-title-cell">
                  <span class="danhmuc-title">{{ truncateText(item.ten_danh_muc, 20) }}</span>
                  <div class="danhmuc-description">{{ truncateText(item.mo_ta || 'Không có mô tả', 30) }}</div>
                </div>
              </td>
              <td class="text-center">
                <img
                  :src="item.image_url"
                  :alt="item.ten_danh_muc"
                  class="danhmuc-thumbnail"
                />
              </td>
              <td class="text-center">
                {{ item.danh_muc_cha_name || '---' }}
              </td>
              <td class="text-center">
                {{ truncateText(item.mo_ta || 'Không có mô tả', 30) }}
              </td>
              <td class="text-center">
                <span class="status-badge" :class="item.trang_thai == 1 ? 'is-active' : 'is-inactive'">
                  {{ item.trang_thai == 1 ? 'Hiển thị' : 'Ẩn' }}
                </span>
              </td>
              <td class="text-center">
                <div class="action-buttons">
                  <button @click="viewCategories(item.category_id)" class="action-icon" title="Xem sản phẩm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                      <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                  <button @click="editCategories(item.category_id)" class="action-icon" title="Sửa">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                      <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                    </svg>
                  </button>

                  <button
                    @click="toggleCategoryStatus(item.category_id)"
                    class="action-icon"
                    :class="item.trang_thai == 1 ? 'text-danger' : 'text-success'"
                    :title="item.trang_thai == 1 ? 'Ẩn danh mục' : 'Hiển thị danh mục'"
                  >
                    <svg v-if="item.trang_thai == 1" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.367zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                      <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-if="!loading && filteredCategories.length === 0" class="no-data-message">Chưa có danh mục nào phù hợp.</p>
      <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const categories = ref([]);
const errorMessage = ref('');
const loading = ref(false);
const searchQuery = ref('');

const router = useRouter();

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

const filteredCategories = computed(() => {
  let current = categories.value;

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    current = current.filter(item => {
      const tenDanhMuc = item.ten_danh_muc ? String(item.ten_danh_muc).toLowerCase() : '';
      const moTa = item.mo_ta ? String(item.mo_ta).toLowerCase() : '';
      return tenDanhMuc.includes(query) || moTa.includes(query);
    });
  }

  return current;
});

const getCategories = async () => {
  loading.value = true;
  errorMessage.value = '';
  try {
    const response = await axios.get('https://api.sieuthi404.io.vn/api/categories', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    categories.value = response.data;
  } catch (error) {
    console.error('Lỗi khi lấy danh mục sản phẩm:', error);
    errorMessage.value = 'Lỗi khi lấy danh mục: ' + (error.response?.data?.message || error.message);
  } finally {
    loading.value = false;
  }
};

const viewCategories = (categoryId) => {
  router.push(`/admin/categories/${categoryId}/products`);
};

const editCategories = (categoryId) => {
  router.push(`/admin/categories/${categoryId}/edit`);
};

// Đã đổi tên từ 'deleteCategories' và thay đổi chức năng thành toggle status
const toggleCategoryStatus = async (categoryId) => {
  const confirmed = confirm('Bạn có chắc muốn thay đổi trạng thái danh mục này?');
  if (!confirmed) {
    return; // Người dùng hủy bỏ
  }

  try {
    const response = await axios.put(`https://api.sieuthi404.io.vn/api/categories/${categoryId}/toggle-status`, {}, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });

    if (response.status === 200) {
      alert(response.data.message); // Hiển thị thông báo thành công từ backend
      getCategories(); // Tải lại danh sách để cập nhật trạng thái hiển thị
    }
  } catch (error) {
    console.error('Lỗi khi thay đổi trạng thái danh mục:', error.response?.data?.message || error.message);
    alert('Có lỗi xảy ra khi thay đổi trạng thái danh mục: ' + (error.response?.data?.message || error.message));
  }
};

onMounted(() => {
  getCategories();
});
</script>

<style scoped>
/* Giữ nguyên phần style của bạn */
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
.action-icon.text-danger:hover svg {
  color: #b91c1c;
}
.action-icon.text-success svg {
  color: #22c55e;
}
.action-icon.text-success:hover svg {
  color: #16a34a;
}
.no-data-message {
  padding: 2rem;
  text-align: center;
  color: #6b7280;
}
.error-message {
  color: #dc2626;
  text-align: center;
  margin-top: 1rem;
}
</style>