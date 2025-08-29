<template>
  <div class="page-wrapper">
    <header class="page-header">
      <div>
        <h2 class="page-title">Danh mục con của: <span class="category-name-display">{{ parentCategoryName }}</span></h2>
      </div>
      <div class="action-buttons-header">
        <button @click="goBack" class="btn-back">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
            <path fill-rule="evenodd" d="M11.72 7.28a.75.75 0 0 0-1.06 0l-7.25 7.25a.75.75 0 1 0 1.06 1.06L12 9.31l6.72 6.72a.75.75 0 1 0 1.06-1.06L13.06 8l7.25-7.25a.75.75 0 0 0-1.06-1.06L12 6.94l-6.72-6.72a.75.75 0 0 0-1.06 1.06L10.94 8l-7.25 7.25a.75.75 0 0 0 1.06 1.06L12 9.31l6.72 6.72a.75.75 0 1 0 1.06-1.06L13.06 8Z" clip-rule="evenodd" />
          </svg>
          <span>Quay lại</span>
          <br>
        </button>
        <router-link to="/admin/categories/add" class="btn-add">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
        <span>Thêm danh mục</span>
      </router-link>
      </div>
       
    </header>

    <div class="content-card">
      <div v-if="loading" class="loading-message">
        <div class="spinner"></div>
        Đang tải danh mục con...
      </div>
      <div v-else-if="errorMessage" class="error-message">
        {{ errorMessage }}
      </div>
      <div v-else-if="subcategories.length === 0" class="no-data-message">
        Không có danh mục con nào trong danh mục này.
      </div>
      <div v-else class="table-container">
        <table class="danhmuc-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Tên danh mục con</th>
              <th class="text-center">Hình ảnh</th>
              <th class="text-center">Trạng thái</th>
              <th class="text-center">Nổi bật</th>
              <th class="text-center">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(subcategory, index) in subcategories" :key="subcategory.category_id" :class="{'is-inactive-row': subcategory.trang_thai === 0}">
              <td>{{ index + 1 }}</td>
              <td>
                <div class="danhmuc-title-cell">
                  <span class="danhmuc-title">{{ subcategory.ten_danh_muc }}</span>
                </div>
              </td>
              <td class="text-center">
                <img
                  :src="subcategory.image_url"
                  :alt="subcategory.ten_danh_muc"
                  class="danhmuc-thumbnail"
                />
              </td>
              <td class="text-center">
                <span class="status-badge" :class="subcategory.trang_thai === 1 ? 'is-active' : 'is-inactive'">
                  {{ subcategory.trang_thai === 1 ? 'Hoạt động' : 'Không hoạt động' }}
                </span>
              </td>
              <td class="text-center">
                <span class="status-badge" :class="subcategory.noi_bat === 1 ? 'is-active' : 'is-inactive'">
                  {{ subcategory.noi_bat === 1 ? 'Có' : 'Không' }}
                </span>
              </td>
              <td class="text-center">
                <div class="action-buttons">
                  <router-link
                    :to="{ name: 'CategoryProducts', params: { category_id: subcategory.category_id } }"
                    class="action-icon"
                    title="Xem sản phẩm của danh mục này"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                      <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                    </svg>
                  </router-link>

                  <button @click="editSubcategory(subcategory.category_id)" class="action-icon" title="Sửa danh mục con">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                      <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                      <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                    </svg>
                  </button>

                  <button
                    @click="toggleSubcategoryStatus(subcategory.category_id)"
                    class="action-icon"
                    :class="subcategory.trang_thai === 1 ? 'text-danger' : 'text-success'"
                    :title="subcategory.trang_thai === 1 ? 'Ẩn danh mục con' : 'Hiển thị danh mục con'"
                  >
                    <svg v-if="subcategory.trang_thai === 1" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
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
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const subcategories = ref([]);
const parentCategoryName = ref('Đang tải...');
const loading = ref(true);
const errorMessage = ref('');

const fetchSubcategories = async () => {
  loading.value = true;
  errorMessage.value = '';
  const categoryId = route.params.categoryId;

  if (!categoryId) {
    errorMessage.value = 'Không tìm thấy ID danh mục cha.';
    loading.value = false;
    return;
  }
  console.log('Fetching subcategories for categoryId:', categoryId);

  try {
    const response = await axios.get(`https://api.sieuthi404.io.vn/api/categories/${categoryId}/children`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    parentCategoryName.value = response.data.parent_category.ten_danh_muc;
    subcategories.value = response.data.subcategories;
  } catch (error) {
    console.error('Lỗi khi lấy danh mục con:', error);
    errorMessage.value = 'Không thể tải danh mục con. Vui lòng thử lại sau.';
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage.value = error.response.data.message;
    } else if (error.message) {
      errorMessage.value = `Lỗi mạng hoặc không phản hồi: ${error.message}`;
    }
  } finally {
    loading.value = false;
  }
};

const toggleSubcategoryStatus = async (subcategoryId) => {
  const confirmed = confirm('Bạn có chắc muốn thay đổi trạng thái danh mục con này không?');
  if (!confirmed) {
    return; // Người dùng hủy bỏ
  }

  try {
    const response = await axios.put(`https://api.sieuthi404.io.vn/api/categories/${subcategoryId}/toggle-status`, {}, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });

    if (response.status === 200) {
      alert(response.data.message);
      // Cập nhật trạng thái trong danh sách hiện tại mà không cần tải lại toàn bộ
      const index = subcategories.value.findIndex(cat => cat.category_id === subcategoryId);
      if (index !== -1) {
        subcategories.value[index].trang_thai = subcategories.value[index].trang_thai === 1 ? 0 : 1;
      }
    }
  } catch (error) {
    console.error('Lỗi khi thay đổi trạng thái danh mục con:', error.response?.data?.message || error.message);
    alert('Có lỗi xảy ra khi thay đổi trạng thái danh mục con: ' + (error.response?.data?.message || error.message));
  }
};

watch(() => route.params.categoryId, (newId, oldId) => {
  if (newId !== oldId) {
    fetchSubcategories();
  }
});

const goBack = () => {
  router.back();
};

const editSubcategory = (subcategoryId) => {
  router.push(`/admin/categories/${subcategoryId}/edit`);
};

const detachSubcategory = async (subcategoryId) => {
  if (!confirm('Bạn có chắc chắn muốn gỡ bỏ danh mục con này khỏi danh mục cha không? (Danh mục con sẽ không bị xóa khỏi hệ thống)')) {
    return;
  }

  try {
    await axios.patch(`https://api.sieuthi404.io.vn/api/child-categories/${subcategoryId}/detach`, {}, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    subcategories.value = subcategories.value.filter(cat => cat.category_id !== subcategoryId);
    alert('Danh mục con đã được gỡ bỏ khỏi danh mục cha thành công!');

  } catch (error) {
    console.error('Lỗi khi gỡ bỏ danh mục con:', error);
    let message = 'Không thể gỡ bỏ danh mục con. Vui lòng thử lại.';
    if (error.response && error.response.data && error.response.data.message) {
      message = error.response.data.message;
    }
    alert(message);
  }
};

onMounted(() => {
  fetchSubcategories();
});
</script>

<style scoped>
/* Giữ nguyên phần style hiện có của bạn, chỉ thêm các class cho nút toggle status nếu chúng chưa có */

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
  font-size: 1.875rem;
  font-weight: 700;
  color: #111827;
}

.category-name-display {
  color: #4FC3F7; /* Giữ màu đặc trưng cho tên danh mục cha */
}

.action-buttons-header {
  display: flex;
  gap: 1rem; /* Khoảng cách giữa các nút */
}

/* NÚT QUAY LẠI (btn-back) - MỚI THÊM VÀO TEMPLATE */
.btn-back {
  background-color: #ffffff;
  color: #4b5563;
  border: 1px solid #e5e7eb;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1rem;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  height: 40px;
}

.btn-back:hover {
  background-color: #f9fafb;
  transform: translateY(-1px);
  color: #000; /* Làm chữ đậm hơn khi hover */
}

.btn-back svg {
  width: 1.25rem;
  height: 1.25rem;
  stroke: #4b5563; /* Mặc định icon màu xám */
}

.btn-back:hover svg {
  stroke: #000; /* Icon đen hơn khi hover */
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

.content-card {
  background-color: #ffffff;
  border-radius: 8px;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  padding: 1.5rem;
}

/* MESSAGE STYLES */
.loading-message, .no-data-message {
  text-align: center;
  padding: 2rem;
  font-size: 1.1rem;
  color: #6b7280;
}

.error-message {
  color: #dc2626;
  background-color: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 8px;
  padding: 1rem;
  margin-top: 1rem;
  text-align: center;
}

.spinner {
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-top: 4px solid #4FC3F7;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin: 0 auto 15px auto;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* TABLE STYLES - ĐIỀU CHỈNH ĐỂ GIỐNG danhmuc-table */
.table-container { /* Đổi từ .table-responsive thành .table-container */
  overflow-x: auto;
}

.danhmuc-table { /* Đổi từ .data-table thành .danhmuc-table */
  width: 100%;
  border-collapse: collapse;
  white-space: nowrap;
}

.danhmuc-table th, .danhmuc-table td {
  padding: 1rem;
  text-align: left;
  vertical-align: middle;
  border-bottom: 1px solid #e5e7eb; /* Màu border dưới khớp với CategoryList */
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

.danhmuc-table tbody tr:hover {
  background-color: #f5f5f5;
}

.text-center { text-align: center; }

/* ROW STATUS (IS-INACTIVE-ROW) */
.is-inactive-row {
  opacity: 0.7; /* Giảm độ mờ một chút */
  background-color: #fafafa;
}

.is-inactive-row td {
  color: #6b7280;
}

/* BADGE STYLES */
.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.8rem;
  font-weight: 500;
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

/* ACTION BUTTONS - ĐIỀU CHỈNH ĐỂ GIỐNG CategoryList action buttons */
.action-buttons {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.25rem; /* Khoảng cách nhỏ hơn giữa các icon */
  flex-wrap: nowrap; /* Ngăn các nút bị xuống dòng */
}

.action-icon {
  width: 32px;
  height: 32px;
  padding: 0; /* Loại bỏ padding để icon chiếm toàn bộ nút */
  background-color: transparent; /* Nền trong suốt */
  border-radius: 50%;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s, transform 0.2s;
  flex-shrink: 0; /* Ngăn nút bị co lại */
}

.action-icon svg {
  width: 20px;
  height: 20px;
  color: #111827; /* Màu icon mặc định đen */
  transition: color 0.2s;
}

/* Hover states for all action icons */
.action-icon:hover {
  background-color: #e0e7ff; /* Màu nền xanh nhạt khi hover */
  transform: scale(1.1);
}

.action-icon:hover svg {
  color: #3b82f6; /* Màu icon xanh khi hover */
}

/* Specific colors for danger icons (detach button and toggle off) */
.action-icon.text-danger svg {
  color: #dc2626; /* Màu đỏ mặc định */
}

.action-icon.text-danger:hover svg {
  color: #b91c1c; /* Đỏ đậm hơn khi hover */
}

/* Specific colors for success icons (toggle on) */
.action-icon.text-success svg {
  color: #22c55e; /* Màu xanh lá mặc định */
}

.action-icon.text-success:hover svg {
  color: #16a34a; /* Xanh lá đậm hơn khi hover */
}


/* DANH MỤC TITLE/DESCRIPTION CELL - MỚI THÊM VÀO TEMPLATE CHO CỘT TÊN */
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
  max-width: 220px; /* Đảm bảo đủ rộng để hiển thị tên */
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

/* Thêm style cho hình ảnh thumbnail */
.danhmuc-thumbnail {
  width: 80px; /* Kích thước cố định */
  height: 60px; /* Kích thước cố định */
  border-radius: 4px;
  object-fit: cover; /* Đảm bảo ảnh không bị méo */
  border: 1px solid #e5e7eb;
}
</style>