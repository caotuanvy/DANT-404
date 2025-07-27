<template>
  <div class="page-container">
    <header class="page-header">
      <h2 class="page-title">Danh mục con của: <span class="category-name-display">{{ parentCategoryName }}</span></h2>
      <div class="action-buttons-header">
        <button @click="goBack" class="btn btn-secondary mr-2">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="icon">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 6 6v2" />
          </svg>
          Quay lại danh mục cha
        </button>
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
      <div v-else class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Tên danh mục con</th>
              <th>Slug</th>
              <th class="text-center">Trạng thái</th>
              <th class="text-center">Nổi bật</th>
              <th class="text-center">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(subcategory, index) in subcategories" :key="subcategory.category_id" :class="{'is-inactive-row': subcategory.trang_thai === 0}">
              <td>{{ index + 1 }}</td>
              <td>{{ subcategory.ten_danh_muc }}</td>
              <td>{{ subcategory.slug }}</td>
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
                    class="action-icon text-info"
                    title="Xem sản phẩm của danh mục này"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                      <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                    </svg>
                  </router-link>

                  <button @click="editSubcategory(subcategory.category_id)" class="action-icon text-primary" title="Sửa danh mục con">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                      <path d="M5.433 13.917v.936a2.25 2.25 0 0 0 2.25 2.25h4.129a2.25 2.25 0 0 0 2.25-2.25v-.936m-9.825-1.077L9.432 2.664a2.25 2.25 0 0 1 3.182 0l1.295 1.295a2.25 2.25 0 0 1 0 3.182l-5.694 5.694m-9.825-1.077L9.432 2.664a2.25 2.25 0 0 1 3.182 0l1.295 1.295a2.25 2.25 0 0 1 0 3.182l-5.694 5.694a2.25 2.25 0 0 1-3.182 0L5.433 13.917Z" />
                    </svg>
                  </button>
                  <button @click="detachSubcategory(subcategory.category_id)" class="action-icon text-danger" title="Gỡ bỏ danh mục con khỏi danh mục cha">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                      <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.58.275-2.314.593A3.003 3003 0 0 0 3 9.25V15a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3V9.25c0-1.07-.357-2.043-.936-2.814a6.001 6.001 0 0 0-2.314-.593V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4a1.25 1.25 0 0 0-1.25 1.25v.5c0 .138.112.25.25.25h2a.25.25 0 0 0 .25-.25v-.5A1.25 1.25 0 0 0 10 4Z" clip-rule="evenodd" />
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
    const response = await axios.get(`http://localhost:8000/api/categories/${categoryId}/children`, {
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

watch(() => route.params.categoryId, (newId, oldId) => {
  if (newId !== oldId) {
    fetchSubcategories();
  }
});

const goBack = () => {
  router.back();
};

const editSubcategory = (subcategoryId) => {
  // Thay đổi dòng này để chuyển hướng đến URL mong muốn
  router.push(`/admin/categories/${subcategoryId}/edit`);
};

const detachSubcategory = async (subcategoryId) => {
  if (!confirm('Bạn có chắc chắn muốn gỡ bỏ danh mục con này khỏi danh mục cha không? (Danh mục con sẽ không bị xóa khỏi hệ thống)')) {
    return;
  }

  try {
    // Gọi API PATCH để cập nhật danh_muc_cha_id thành NULL
    await axios.patch(`http://localhost:8000/api/child-categories/${subcategoryId}/detach`, {}, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    // Sau khi gỡ bỏ thành công, loại bỏ nó khỏi danh sách hiển thị
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
/* CSS cho ChildCategoryList.vue (giữ nguyên từ file bạn cung cấp, hoặc tối ưu nếu cần) */
.page-container {
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
  color: #4FC3F7;
}
.action-buttons-header {
  display: flex;
  gap: 1rem;
}
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1rem;
  border: 1px solid transparent;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  height: 40px;
}
.btn-secondary {
  background-color: #ffffff;
  color: #4b5563;
  border-color: #e5e7eb;
}
.btn-secondary:hover {
  background-color: #f9fafb;
  transform: translateY(-1px);
}
.btn .icon {
  width: 1.25rem;
  height: 1.25rem;
  stroke: #4b5563;
}
.content-card {
  background-color: #ffffff;
  border-radius: 8px;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  padding: 1.5rem;
}
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
.table-responsive {
  overflow-x: auto;
}
.data-table {
  width: 100%;
  border-collapse: collapse;
  white-space: nowrap;
}
.data-table th, .data-table td {
  padding: 1rem;
  text-align: left;
  vertical-align: middle;
  border-bottom: 1px solid rgba(209, 203, 203, 0.373);
}
.data-table th {
  background-color: #f9fafb;
  color: #6b7280;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
}
.data-table tbody tr:last-child td {
  border-bottom: none;
}
.data-table tbody tr:hover {
  background-color: #f5f5f5;
}
.text-center { text-align: center; }
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
.is-inactive-row {
  opacity: 0.6;
  background-color: #fafafa;
}
.is-inactive-row td {
  color: #6b7280;
}
.action-buttons {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.75rem;
}
.action-icon {
  width: 32px;
  height: 32px;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #f3f4f6;
  border-radius: 50%;
  border: none;
  cursor: pointer;
  transition: background-color 0.2s, transform 0.2s;
  flex-shrink: 0;
}
.action-icon svg {
  width: 20px;
  height: 20px;
  color: #4b5563;
}
.action-icon:hover {
  background-color: #4FC3F7;
  transform: scale(1.1);
}
.action-icon:hover svg {
  color: white;
}
.action-icon.text-danger:hover {
  background-color: #dc2626;
}
.action-icon.text-danger:hover svg {
  color: white;
}
.action-icon.text-primary:hover {
    background-color: #3b82f6;
}
.action-icon.text-primary:hover svg {
    color: white;
}
.action-icon.text-info:hover {
    background-color: #1a73e8;
}
.action-icon.text-info svg {
    color: #4b5563; /* Giữ màu ban đầu cho icon info */
}
.action-icon.text-info:hover svg {
    color: white; /* Thay đổi màu khi hover */
}
</style>