<template>
  <div class="page-wrapper">
    <header class="page-header">
      <div>
        <h2 class="page-title">Sản phẩm trong danh mục: <span class="category-name-display">{{ categoryName }}</span></h2>
      </div>
      <div class="action-buttons-header">
        <button @click="goBack" class="btn-back">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
            <path fill-rule="evenodd" d="M11.72 7.28a.75.75 0 0 0-1.06 0l-7.25 7.25a.75.75 0 1 0 1.06 1.06L12 9.31l6.72 6.72a.75.75 0 1 0 1.06-1.06L13.06 8l7.25-7.25a.75.75 0 0 0-1.06-1.06L12 6.94l-6.72-6.72a.75.75 0 0 0-1.06 1.06L10.94 8l-7.25 7.25a.75.75 0 0 0 1.06 1.06L12 9.31l6.72 6.72a.75.75 0 1 0 1.06-1.06L13.06 8Z" clip-rule="evenodd" />
          </svg>
          <span>Quay lại</span>
        </button>
      </div>
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
              <th class="text-center">Ảnh</th>
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
              <td class="text-center">
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
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.367zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" />
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
    await axios.delete(`http://localhost:8000/api/categories/${categoryId}/products`, {
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
/* Định nghĩa các biến CSS - Lấy từ code CategoryList */
/* Đã điều chỉnh các biến và giá trị để khớp chặt chẽ với ảnh SubCategoryList hơn */
:root {
  --color-primary-blue: #4FC3F7; /* Màu xanh dương nhạt cho tiêu đề category name */
  --color-primary-blue-dark: #3b82f6; /* Màu xanh dương đậm hơn */
  --color-bg-page: #f3f4f6; /* Nền trang */
  --color-bg-card: #ffffff; /* Nền card nội dung */
  --color-border-light: #e5e7eb; /* Màu đường viền nhạt */
  --color-text-dark: #111827; /* Màu chữ chính (đen đậm) */
  --color-text-gray-medium: #4b5563; /* Màu chữ xám trung bình (cho nút back, tiêu đề table) */
  --color-text-gray-light: #6b7280; /* Màu chữ xám nhạt (mô tả, thông báo) */
  --color-text-gray-lighter: #9ca3af; /* Màu chữ xám nhạt hơn (cho inactive row) */
  --color-green-active: #22c55e; /* Màu xanh lá cho trạng thái hoạt động */
  --color-green-active-bg: #dcfce7; /* Nền xanh lá nhạt cho trạng thái hoạt động (từ SubCategoryList) */
  --color-red-danger: #dc2626; /* Màu đỏ cho hành động nguy hiểm */
  --color-red-danger-hover: #b91c1c; /* Màu đỏ đậm hơn khi hover */
  --color-action-icon-default: #111827; /* Màu icon hành động mặc định */
  --color-action-icon-hover-bg: #e0e7ff; /* Nền hover cho icon hành động */
  --color-action-icon-hover-blue: #3b82f6; /* Màu icon xanh khi hover */
  --radius-default: 8px; /* Bo góc mặc định */
  --shadow-default: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1); /* Đổ bóng mặc định */
}

/* Reset CSS cơ bản */
body, h1, h2, h3, p, table {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* Page Container (được đổi tên thành page-wrapper trong CategoryList nhưng giữ nguyên tên ở đây) */
.page-wrapper { /* Đổi từ .page-container thành .page-wrapper để thống nhất */
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
  font-size: 1.875rem; /* ~30px - Đảm bảo kích thước lớn */
  font-weight: 700;
  color: var(--color-text-dark);
}

.category-name-display {
  color: var(--color-primary-blue); /* Sử dụng biến màu primary-blue cho tên danh mục */
}

.action-buttons-header {
  display: flex;
  gap: 1rem; /* Khoảng cách giữa các nút */
}

/* NÚT QUAY LẠI (btn-back) - CHỈNH SỬA ĐỂ GIỐNG SubCategoryList */
.btn-back {
  background-color: var(--color-bg-card); /* Nền trắng */
  color: var(--color-text-gray-medium); /* Chữ xám đậm */
  border: 1px solid var(--color-border-light); /* Border xám */
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1rem;
  border-radius: var(--radius-default);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  height: 40px;
  justify-content: center;
  text-decoration: none;
  box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); /* Shadow nhẹ nhàng hơn */
}

.btn-back:hover {
  background-color: #f9fafb; /* Nền sáng hơn khi hover */
  transform: translateY(-1px); /* Hiệu ứng nhấc lên nhẹ */
  color: var(--color-text-dark); /* Chữ đen hơn khi hover */
}

.btn-back svg {
  width: 1.25rem;
  height: 1.25rem;
  color: var(--color-text-gray-medium); /* Icon màu xám mặc định */
  transition: color 0.2s;
}

.btn-back:hover svg {
  color: var(--color-text-dark); /* Icon đen hơn khi hover */
}


/* Content Card */
.content-card {
  background-color: var(--color-bg-card);
  border-radius: var(--radius-default);
  box-shadow: var(--shadow-default);
  padding: 1.5rem;
  border: 1px solid var(--color-border-light); /* Thêm border để giống ảnh SubCategoryList */
}

/* MESSAGE STYLES */
.loading-message, .no-data-message {
  text-align: center;
  padding: 2rem;
  font-size: 1.1rem;
  color: var(--color-text-gray-light);
}

.error-message {
  color: var(--color-red-danger);
  /* background-color: #fef2f2; */ /* Bỏ nền và border để giống ảnh SubCategoryList, chỉ giữ chữ đỏ */
  /* border: 1px solid #fecaca; */
  border-radius: var(--radius-default);
  padding: 1rem;
  margin-top: 1rem;
  text-align: center;
}

.spinner {
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-top: 4px solid var(--color-primary-blue);
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

/* TABLE STYLES - ĐIỀU CHỈNH ĐỂ GIỐNG SubCategoryList */
.table-responsive {
  overflow-x: auto;
}

.product-table { /* Đổi tên class để dễ quản lý, nhưng vẫn giữ cấu trúc giống danhmuc-table */
  width: 100%;
  border-collapse: collapse;
  white-space: nowrap;
}

.product-table th, .product-table td {
  padding: 1rem;
  text-align: left; /* Mặc định căn trái */
  vertical-align: middle;
  border-bottom: 1px solid var(--color-border-light);
}

.product-table th {
  background-color: #f9fafb;
  color: var(--color-text-gray-light);
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
}
/* Căn giữa cho các cột cụ thể trong bảng sản phẩm */
.product-table th.text-center,
.product-table td.text-center {
    text-align: center;
}


.product-table tbody tr:last-child td {
  border-bottom: none;
}

.product-table tbody tr:hover {
  background-color: #f5f5f5;
}

/* Utility Classes */
/* .text-center { text-align: center; } */ /* Đã chuyển định nghĩa cụ thể cho th và td */
.font-medium { font-weight: 500; }

/* Product Name Cell */
.product-name-cell {
  display: flex;
  flex-direction: column;
}

.product-name {
  font-weight: 500;
  color: var(--color-text-dark);
  text-decoration: none;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 250px; /* Đảm bảo đủ rộng để hiển thị tên */
  display: block;
}

.product-variant-count {
  font-size: 0.875rem;
  color: var(--color-text-gray-light);
  white-space: nowrap;
}

/* Product Thumbnail */
.product-thumbnail {
  width: 80px;
  height: 60px;
  border-radius: 4px;
  object-fit: cover;
  border: 1px solid var(--color-border-light);
}

/* Description Cell */
.description-cell {
  white-space: normal; /* Cho phép xuống dòng */
  max-width: 250px;
  min-width: 150px;
  overflow: hidden;
  text-overflow: ellipsis;
  color: var(--color-text-gray-light);
}

/* BADGE STYLES - ĐIỀU CHỈNH ĐỂ GIỐNG SubCategoryList */
.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.8rem;
  font-weight: 500;
  text-transform: capitalize;
}

.status-badge.is-active {
  color: var(--color-green-active);
  background-color: var(--color-green-active-bg); /* Màu nền xanh lá nhạt từ ảnh SubCategoryList */
}

.status-badge.is-inactive {
  color: var(--color-text-gray-light);
  background-color: #e5e7eb; /* Màu nền xám nhạt */
}

/* ROW STATUS (IS-INACTIVE-ROW) */
.is-inactive-row {
  opacity: 0.7; /* Giảm độ mờ một chút */
  background-color: #fafafa;
}

.is-inactive-row td {
  color: var(--color-text-gray-lighter); /* Chữ xám nhạt hơn */
}
.is-inactive-row .product-name {
  text-decoration: line-through; /* Gạch ngang tên sản phẩm không hoạt động */
}
/* Đảm bảo badge vẫn hiển thị màu chuẩn */
.is-inactive-row .status-badge.is-active {
    color: var(--color-green-active);
    background-color: var(--color-green-active-bg);
}
.is-inactive-row .status-badge.is-inactive {
    color: var(--color-text-gray-light);
    background-color: #e5e7eb;
}


/* ACTION BUTTONS - ĐIỀU CHỈNH ĐỂ GIỐNG SubCategoryList */
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
  flex-shrink: 0;
}

.action-icon svg {
  width: 20px;
  height: 20px;
  color: var(--color-action-icon-default); /* Màu icon mặc định đen */
  transition: color 0.2s;
}

/* Hover states for all action icons */
.action-icon:hover {
  background-color: var(--color-action-icon-hover-bg); /* Nền xanh nhạt khi hover */
  transform: scale(1.1);
}

.action-icon:hover svg {
  color: var(--color-action-icon-hover-blue); /* Màu icon xanh khi hover */
}

/* Specific colors for danger icons (detach button and toggle off) */
.action-icon.text-danger svg {
  color: var(--color-red-danger); /* Màu đỏ mặc định */
}

.action-icon.text-danger:hover svg {
  color: var(--color-red-danger-hover); /* Đỏ đậm hơn khi hover */
}

/* Specific colors for success icons (toggle on) */
.action-icon.text-success svg {
  color: var(--color-green-active); /* Màu xanh lá mặc định */
}

.action-icon.text-success:hover svg {
  color: var(--color-green-active); /* Giữ nguyên màu xanh lá đậm khi hover cho nút active */
}
</style>