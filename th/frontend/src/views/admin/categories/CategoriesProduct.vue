<template>
  <div class="page-container">
    <header class="page-header">
      <h2 class="page-title">Sản phẩm trong danh mục: <span class="category-name-display">{{ categoryName }}</span></h2>
      <button @click="goBack" class="btn btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="icon">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 6 6v2" />
        </svg>
        Quay lại danh mục
      </button>
    </header>

    <div class="content-card">
      <div v-if="loading" class="loading-message">
        <div class="spinner"></div>
        Đang tải sản phẩm...
      </div>
      <div v-else-if="errorMessage" class="error-message">
        {{ errorMessage }}
      </div>
      <div v-else-if="products.length === 0" class="no-data-message">
        Không có sản phẩm nào trong danh mục này.
      </div>
      <div v-else class="table-responsive">
        <table class="product-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Ảnh</th>
              <th>Tên sản phẩm</th>
              <th class="text-center">Giá</th>
              <th>Mô tả</th>
              <th class="text-center">Trạng thái</th>
              <th class="text-center">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(product, index) in products" :key="product.product_id" :class="{'is-inactive-row': product.trang_thai === 0}">
              <td>{{ index + 1 }}</td>
              <td>
                <img :src="product.image_url || '/placeholder.jpg'" :alt="product.product_name" class="product-thumbnail">
              </td>
              <td>
                <div class="product-name-cell">
                  <span class="product-name">{{ product.product_name }}</span>
                  <span v-if="product.so_bien_the !== undefined" class="product-variant-count">{{ product.so_bien_the || 0 }} biến thể</span>
                </div>
              </td>
              <td class="text-center font-medium">
                <span v-if="product.min_price && product.max_price && parseFloat(product.min_price) !== parseFloat(product.max_price)">
                    {{ formatPrice(product.min_price) }} - {{ formatPrice(product.max_price) }}
                </span>
                <span v-else>
                    {{ formatPrice(product.gia) }}
                </span>
              </td>
              <td class="description-cell">{{ truncateDescription(product.description, 100) }}</td>
              <td class="text-center">
                <span class="status-badge" :class="product.trang_thai === 1 ? 'is-active' : 'is-inactive'">
                  {{ product.trang_thai === 1 ? 'Đang bán' : 'Ngừng bán' }}
                </span>
              </td>
              <td class="text-center">
                <div class="action-buttons">
                  <button @click="removeProductFromCategory(product.product_id)" class="action-icon text-danger" title="Xóa khỏi danh mục">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                      <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.58.275-2.314.593A3.003 3.003 0 0 0 3 9.25V15a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3V9.25c0-1.07-.357-2.043-.936-2.814a6.001 6.001 0 0 0-2.314-.593V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4a1.25 1.25 0 0 0-1.25 1.25v.5c0 .138.112.25.25.25h2a.25.25 0 0 0 .25-.25v-.5A1.25 1.25 0 0 0 10 4Z" clip-rule="evenodd" />
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
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const products = ref([]);
const categoryName = ref('Đang tải...');
const loading = ref(true);
const errorMessage = ref('');

const fetchProductsByCategory = async () => {
  loading.value = true;
  errorMessage.value = '';
  const categoryId = route.params.category_id;

  try {
    const response = await axios.get(`http://localhost:8000/api/categories/${categoryId}/products`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    categoryName.value = response.data.category.ten_danh_muc;
    products.value = response.data.products;
  } catch (error) {
    console.error('Lỗi khi lấy sản phẩm theo danh mục:', error);
    errorMessage.value = 'Không thể tải sản phẩm. Vui lòng thử lại sau.';
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage.value = error.response.data.message;
    } else if (error.message) {
      errorMessage.value = `Lỗi mạng hoặc không phản hồi: ${error.message}`;
    }
  } finally {
    loading.value = false;
  }
};

const removeProductFromCategory = async (productId) => {
  const categoryId = route.params.category_id;
  if (!confirm(`Bạn có chắc muốn xóa sản phẩm này khỏi danh mục "${categoryName.value}" không?`)) {
    return;
  }

  try {
    await axios.delete(`http://localhost:8000/api/categories/${categoryId}/products/${productId}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });

    products.value = products.value.filter(p => p.product_id !== productId);
    alert('Đã xóa sản phẩm khỏi danh mục thành công!');

  } catch (error) {
    console.error('Lỗi khi xóa sản phẩm khỏi danh mục:', error);
    let message = 'Không thể xóa sản phẩm khỏi danh mục. Vui lòng thử lại.';
    if (error.response && error.response.data && error.response.data.message) {
      message = error.response.data.message;
    } else if (error.message) {
      message = `Lỗi mạng hoặc không phản hồi: ${error.message}`;
    }
    alert(message);
  }
};

const goBack = () => {
  router.back();
};

const formatPrice = (price) => {
  if (price === null || price === undefined) return 'N/A';
  const numericPrice = parseFloat(price);
  if (isNaN(numericPrice)) return 'N/A';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(numericPrice);
};

const truncateDescription = (description, maxLength) => {
  if (!description) return '';
  if (description.length <= maxLength) {
    return description;
  }
  return description.substring(0, maxLength) + '...';
};

onMounted(() => {
  fetchProductsByCategory();
});
</script>

<style scoped>
/* Định nghĩa các biến CSS - Lấy từ code bạn cung cấp cho component product list */
:root {
  --color-primary: #4FC3F7; /* Màu xanh dương nhạt */
  --color-primary-hover: #3b82f6; /* Màu xanh đậm hơn khi hover */
  --color-bg-page: #f3f4f6; /* Màu nền trang */
  --color-bg-card: #ffffff; /* Màu nền card */
  --color-border: #e5e7eb; /* Màu border (light gray) */
  --color-text-primary: #111827; /* Màu chữ chính (dark gray) */
  --color-text-secondary: #6b7280; /* Màu chữ phụ (medium gray) */
  --color-text-tertiary: #9ca3af; /* Màu chữ xám nhạt hơn */
  --color-green: #16a34a; /* Màu xanh lá */
  --color-red: #dc2626; /* Màu đỏ */
  --color-gray: #4b5563; /* Màu xám đậm */
  --radius: 8px; /* Bo góc chung */
  --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1); /* Bóng đổ chung */
}

/* Reset CSS cơ bản */
body, h1, h2, h3, p, table {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* Page Container (được đổi tên thành page-wrapper trong component kia nhưng giữ nguyên tên ở đây) */
.page-container {
  background-color: var(--color-bg-page);
  padding: 2rem;
  min-height: 100vh;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-title {
  font-size: 1.875rem; /* ~30px */
  font-weight: 700;
  color: var(--color-text-primary);
}

.category-name-display {
  color: var(--color-primary); /* Sử dụng biến màu primary */
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1rem;
  border: 1px solid transparent;
  border-radius: var(--radius);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  height: 40px; /* Chiều cao cố định cho nút */
}

.btn-secondary {
  background-color: var(--color-bg-card);
  color: var(--color-gray);
  border-color: var(--color-border);
}

.btn-secondary:hover {
  background-color: #f9fafb; /* Màu nền nhạt hơn khi hover */
  transform: translateY(-1px); /* Hiệu ứng nhấc nhẹ */
}

.btn-secondary .icon {
  width: 1.25rem;
  height: 1.25rem;
  stroke: var(--color-gray); /* Màu icon */
}

/* Content Card */
.content-card {
  background-color: var(--color-bg-card);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 1.5rem;
}

/* Message Styles (Loading, Error, No Data) */
.loading-message,
.no-data-message {
  text-align: center;
  padding: 2rem;
  font-size: 1.1rem;
  color: var(--color-text-secondary);
}

.error-message {
  color: var(--color-red);
  background-color: #fef2f2; /* Nền đỏ nhạt hơn */
  border: 1px solid #fecaca; /* Border đỏ nhạt hơn */
  border-radius: var(--radius);
  padding: 1rem;
  margin-top: 1rem;
  text-align: center;
}

/* Spinner */
.spinner {
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-top: 4px solid var(--color-primary); /* Sử dụng màu primary cho spinner */
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

/* Table */
.table-responsive { /* Giữ nguyên tên class để không phải sửa HTML */
  overflow-x: auto;
}

.product-table {
  width: 100%;
  border-collapse: collapse;
  white-space: nowrap; /* Ngăn chặn wrap text trong ô bảng */
}

.product-table th,
.product-table td {
  padding: 1rem; /* Consistent padding */
  text-align: left;
  vertical-align: middle; /* Căn giữa theo chiều dọc */
  border-bottom: 1px solid rgba(209, 203, 203, 0.373) !important; /* Màu border khớp */
}

.product-table th {
  background-color: #f9fafb; /* Nền header */
  color: var(--color-text-secondary);
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
}

.product-table tbody tr:last-child td {
  border-bottom: none; /* Không border ở hàng cuối cùng */
}

.product-table tbody tr:hover {
  background-color: #f5f5f5; /* Hiệu ứng hover nhẹ */
}

/* Utility Classes */
.text-center { text-align: center; }
.font-medium { font-weight: 500; }

/* Product Name Cell */
.product-name-cell {
  display: flex;
  flex-direction: column;
}
.product-name {
  font-weight: 500;
  color: var(--color-text-primary);
  text-decoration: none;
}
.product-variant-count {
  font-size: 0.875rem;
  color: var(--color-text-secondary);
}

/* Product Thumbnail */
.product-thumbnail {
  width: 40px; /* Kích thước nhỏ hơn để phù hợp với bảng */
  height: 40px;
  border-radius: 4px;
  object-fit: cover;
  border: 1px solid var(--color-border);
}

/* Description Cell */
.description-cell {
  white-space: normal; /* Cho phép xuống dòng */
  max-width: 250px;
  min-width: 150px;
  overflow: hidden;
  text-overflow: ellipsis; /* Thêm dấu ba chấm nếu tràn */
  color: var(--color-text-secondary);
}

/* Status Badges */
.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px; /* Bo tròn hoàn toàn */
  font-size: 0.8rem;
  font-weight: 500;
  text-transform: capitalize;
}
.status-badge.is-active {
  color: var(--color-green);
  background-color: #dcfce7; /* Nền xanh lá nhạt */
}
.status-badge.is-inactive {
  color: var(--color-text-secondary);
  background-color: #e5e7eb; /* Nền xám nhạt */
}

/* Inactive Row Styling */
.is-inactive-row {
  opacity: 0.6;
  background-color: #fafafa; /* Nền hơi mờ cho hàng không hoạt động */
}
.is-inactive-row .product-name {
  text-decoration: line-through; /* Gạch ngang tên sản phẩm không hoạt động */
}

/* Action Buttons */
.action-buttons {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.75rem;
}
.action-icon {
  width: 32px; /* Tăng kích thước bao của icon để dễ click hơn */
  height: 32px;
  display: flex; /* Dùng flex để căn giữa icon */
  justify-content: center;
  align-items: center;
  background-color: #f3f4f6; /* Nền icon mặc định */
  border-radius: 50%;
  border: none;
  cursor: pointer;
  transition: background-color 0.2s, transform 0.2s;
  flex-shrink: 0; /* Đảm bảo nút không bị co lại */
}
.action-icon svg {
  width: 20px;
  height: 20px;
  color: var(--color-gray); /* Màu icon mặc định */
}
.action-icon:hover {
  background-color: var(--color-primary); /* Màu nền khi hover */
  transform: scale(1.1);
}
.action-icon:hover svg {
    color: white; /* Đổi màu icon thành trắng khi hover */
}
.action-icon.text-danger:hover {
  background-color: var(--color-red);
}
.action-icon.text-danger:hover svg {
  color: white;
}
</style>