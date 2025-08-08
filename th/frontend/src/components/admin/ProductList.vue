<template>
  <div class="page-wrapper">
    <header class="page-header">
      <div>
        <h1 class="page-title">Danh sách sản phẩm</h1>
      </div>
      <router-link to="/admin/products/add" class="btn-add">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
        <span>Thêm sản phẩm</span>
      </router-link>
    </header>

    <div class="content-card">
      <div class="filter-bar">
        <div class="search-box">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
          <input type="text" v-model="searchQuery" placeholder="Tìm kiếm sản phẩm..." class="search-input">
        </div>
      </div>

      <div class="table-container">
        <table class="product-table">
          <thead>
            <tr>
              <th class="w-12"><input type="checkbox"></th>
              <th>Tên sản phẩm</th>
              <th class="text-center">Hình ảnh</th>
              <th class="text-center">Ghim </th>
              <th class="text-center">Giá </th>
              <th class="text-center">Danh mục</th>
              <th class="text-center">Khuyến mãi</th>
              <th class="text-center">Trạng thái</th>
              <th class="text-center">Hành động</th>
            </tr>
          </thead>
          <tbody>
  <tr v-if="loading">
    <td colspan="9" class="text-center py-8">Đang tải dữ liệu...</td>
  </tr>
  <tr v-else-if="filteredProducts.length === 0">
    <td colspan="9" class="text-center py-8">Không tìm thấy sản phẩm nào.</td>
  </tr>
  <tr v-for="product in filteredProducts" :key="product.product_id" class="table-row" :class="{'is-inactive-row': !product.trang_thai}">
    <td data-label="Chọn"><input type="checkbox"></td>
    <td data-label="Tên sản phẩm" class="product-title-cell">
      <div class="product-name-cell">
          <router-link :to="`/san-pham/${product.slug}`" class="product-name product-name-link">
              {{ product.product_name }}
          </router-link>
          <span class="product-variant-count">{{ product.so_bien_the || 0 }} biến thể</span>
      </div>
    </td>
    <td data-label="Hình ảnh">
      <div class="image-list-cell">
        <img
          v-for="(image, i) in product.images"
          :key="i"
          :src="getImageUrl(image)"
          :alt="product.product_name"
          class="product-thumbnail"
        />
      </div>
    </td>
    <td data-label="Ghim">
        <button @click="toggleNoiBat(product)" class="toggle-switch" :class="{ 'is-active': product.noi_bat == 1 }">
          <span class="toggle-circle"></span>
        </button>
    </td>
    <td data-label="Giá" class="font-medium">
      <span v-if="product.min_price !== undefined && product.max_price !== undefined && parseFloat(product.min_price) !== parseFloat(product.max_price)">
        {{ formatPrice(product.min_price) }} - {{ formatPrice(product.max_price) }}
      </span>
      <span v-else-if="product.min_price !== undefined">
        {{ formatPrice(product.min_price) }}
      </span>
      <span v-else>N/A</span>
    </td>
    <td data-label="Danh mục">
      <span v-if="product.danh_muc" class="badge">
        {{ product.danh_muc.ten_danh_muc }}
      </span>
        <span v-else class="text-secondary text-sm">N/A</span>
    </td>
    <td data-label="Khuyến mãi" class="font-medium text-red-600">
      {{ product.khuyen_mai ? `${parseInt(product.khuyen_mai)}%` : '—' }}
    </td>
    <td data-label="Trạng thái">
      <span class="status-badge" :class="product.trang_thai ? 'is-active' : 'is-inactive'">
        {{ product.trang_thai ? 'Hoạt động' : 'Vô hiệu hóa' }}
      </span>
    </td>
    <td data-label="Hành động">
      <div class="action-buttons">
        <router-link :to="`/admin/products/${product.product_id}/variants`" class="action-icon" title="Quản lý biến thể">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                <path fill-rule="evenodd" d="M3 5.25a.75.75 0 01.75-.75h12.5a.75.75 0 010 1.5H3.75a.75.75 0 01-.75-.75zm0 4a.75.75 0 01.75-.75h12.5a.75.75 0 010 1.5H3.75a.75.75 0 01-.75-.75zm0 4a.75.75 0 01.75-.75h12.5a.75.75 0 010 1.5H3.75a.75.75 0 01-.75-.75z" clip-rule="evenodd" />
            </svg>
        </router-link>
        <router-link :to="`/admin/products/${product.product_id}/edit`" class="action-icon" title="Sửa">
           <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
        </router-link>
        <button v-if="product.trang_thai" @click="toggleProductStatus(product)" class="action-icon text-danger" title="Vô hiệu hóa">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.367zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" />
          </svg>
        </button>
        <button v-else @click="toggleProductStatus(product)" class="action-icon text-success" title="Kích hoạt lại">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" >
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
        </button>
      </div>
    </td>
  </tr>
</tbody>
        </table>
      </div>
      <p v-if="!loading && filteredProducts.length === 0" class="no-data-message">Không có sản phẩm nào phù hợp.</p>
      <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const products = ref([]);
const errorMessage = ref('');
const loading = ref(false);
const searchQuery = ref('');

const filteredProducts = computed(() => {
  if (!searchQuery.value) {
    return products.value;
  }
  return products.value.filter(p =>
    p.product_name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const getProducts = async () => {
  loading.value = true;
  errorMessage.value = '';
  try {
    const res = await axios.get('http://localhost:8000/api/products', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    products.value = res.data;
  } catch (error) {
    console.error('Lỗi khi lấy sản phẩm:', error);
    errorMessage.value = 'Lỗi khi lấy sản phẩm: ' + (error.response?.data?.message || error.message);
  } finally {
    loading.value = false;
  }
};

const toggleNoiBat = async (product) => {
  const newStatus = product.noi_bat === 1 ? 2 : 1;
  try {
    await axios.put(`http://localhost:8000/api/products/${product.product_id}/toggle-noi-bat`, {
      noi_bat: newStatus,
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    product.noi_bat = newStatus;
  } catch (error) {
    console.error('Lỗi khi cập nhật trạng thái nổi bật:', error);
    alert('Cập nhật trạng thái nổi bật thất bại!');
  }
};

const toggleProductStatus = async (product) => {
  const action = product.trang_thai ? 'vô hiệu hóa' : 'kích hoạt';
  if (!confirm(`Bạn có chắc muốn ${action} sản phẩm này?`)) return;
  try {
    await axios.put(`http://localhost:8000/api/products/${product.product_id}/toggle-status`, {}, {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
    });

    product.trang_thai = !product.trang_thai;
    
    alert(`Đã ${action} sản phẩm thành công!`);
  } catch (error) {
    console.error(`Lỗi khi ${action} sản phẩm:`, error);
    alert(`Thao tác thất bại!`);
  }
};


const getImageUrl = (url) => {
  if (!url) return 'https://placehold.co/60x60?text=No+Image';
  return url;
};

const formatPrice = (value) => {
    if (value === undefined || value === null) return 'N/A';
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};


onMounted(() => {
  getProducts();
});
</script>

<style scoped>



.page-wrapper {
  background-color: var(--color-bg-page);
  margin-left: -0px !important;
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
  color: var(--color-text-primary);
}
.page-subtitle {
  color: var(--color-text-secondary);
  margin-top: 0.25rem;
}
.content-card {
  background-color: var(--color-bg-card);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 1.5rem;
}
.error-message {
    color: var(--color-red);
    margin-top: 1rem;
}
.no-data-message {
    padding: 2rem;
    text-align: center;
    color: var(--color-text-secondary);
}

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
}
.btn-add {
  background-color: #4FC3F7;
  color: white;
  transition: background-color 0.3s ease, transform 0.3s ease;
  width: 180px;
  height: 40px;
}
.btn-add svg {
  width: 20px;
  height: 20px;
}
.btn-add:hover {
  background-color:#3b82f6;
  transform: scale(1.05);
}
.search-input {
  width: 100%;
  max-width: 400px;
  padding: 0.6rem 1rem;
  border-radius: 5px !important;
  border: 1px solid gray !important;
  font-size: 0.875rem;
}
.btn-secondary {
  background-color: var(--color-bg-card);
  color: var(--color-gray);
  border-color: var(--color-border);
}
.btn-secondary:hover {
  background-color: #f9fafb;
}

.filter-bar {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}
.search-box {
  position: relative;
}
.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  width: 1.25rem;
  height: 1.25rem;
  color: var(--color-text-tertiary);
}
.search-input, .filter-select {
  border: 1px solid var(--color-border);
  border-radius: var(--radius);
  padding: 0.6rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.search-input {
  padding-left: 2.5rem;
  min-width: 300px;
}
.search-input:focus, .filter-select:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-primary) 20%, transparent);
}

.table-container {
  overflow-x: auto;
}
.product-table {
  width: 100%;
  border-collapse: collapse;
 
  table-layout: fixed;
}
.product-table th, .product-table td {
  padding: 1rem;
  text-align: left;
  vertical-align: middle;
  border-bottom: 1px solid rgba(209, 203, 203, 0.373) !important;
}
.product-table th {
  background-color: #f9fafb;
  color: var(--color-text-secondary);
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  font-weight: 600;
}
.product-table tbody tr:last-child td {
  border-bottom: none;
}
.w-12 { width: 3rem; }
.text-center { text-align: center; }
.py-8 { padding-top: 2rem; padding-bottom: 2rem; }
.font-medium { font-weight: 500; }
.text-red-600 { color: var(--color-red); }
.text-sm { font-size: 0.875rem; }

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

.image-list-cell {
  display: flex;
  gap: 5px;
  flex-wrap: wrap;
  justify-content: center;
  max-width: 140px;
}
.product-thumbnail {
  width: 40px;
  height: 40px;
  border-radius: 4px;
  object-fit: cover;
  border: 1px solid var(--color-border);
}
.description-cell {
  white-space: normal;
  max-width: 250px;
  min-width: 150px;
  overflow: hidden;
  text-overflow: ellipsis;
  color: var(--color-text-secondary);
}

.toggle-switch {
  position: relative;
  display: inline-block;
  width: 36px;
  height: 20px;
  background-color: #ccc;
  border-radius: 9999px;
  transition: background-color 0.2s;
  cursor: pointer;
  border: none;
  padding: 0;
}
.toggle-switch.is-active {
  background-color: #16a34a;
}
.toggle-circle {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 16px;
  height: 16px;
  background-color: white;
  border-radius: 50%;
  transition: transform 0.2s;
}
.toggle-switch.is-active .toggle-circle {
  transform: translateX(16px);
}

.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.8rem;
  font-weight: 500;
  background-color: #e0e7ff;
  color: #3730a3;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.8rem;
  font-weight: 500;
  text-transform: capitalize;
}
.status-badge.is-active {
  color: var(--color-green);
  background-color: #5ed389;
}
.status-badge.is-inactive {
  color: var(--color-text-secondary);
  background-color: var(--color-border);
}
.is-inactive-row {
  opacity: 0.6;
  background-color: #fafafa;
}
.is-inactive-row .product-name {
  text-decoration: line-through;
}


.action-buttons {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.75rem;
}
.action-icon {
  width: 20px;
  padding: 0;
  background-color: var(--color-text-primary);
  border-radius: 50%;
  border: none;
  cursor: pointer;
  transition: background-color 0.2s, transform 0.2s;
}
.action-icon svg {
    width: 20px;
    height: 20px;
    color: rgb(0, 0, 0);
}
.action-icon:hover {
  background-color: var(--color-primary);
  transform: scale(1.1);
}
.action-icon.text-danger:hover {
  background-color: var(--color-red);
}
.action-icon.text-success:hover {
  background-color: var(--color-green);
}
@media (max-width: 768px) {
    /* --- Điều chỉnh Header --- */
    .page-header {
        flex-direction: column; /* Xếp chồng tiêu đề và nút */
        align-items: flex-start; /* Căn lề trái */
        gap: 1rem;               /* Tạo khoảng cách */
        margin-bottom: 1.5rem;
    }

    .page-title {
        font-size: 1.5rem;
    }
    .btn-add{
        width: 100%; /* Nút thêm chiếm toàn bộ chiều rộng */
        justify-content: center;
    }

    /* --- Điều chỉnh thanh tìm kiếm --- */
    .filter-bar {
        flex-direction: column;
    }

    .search-box, .search-input {
        width: 100%;
        min-width: unset;
        max-width: unset;
    }

    /* --- Biến bảng thành dạng Card --- */
    .table-container {
        overflow-x: hidden; /* Tắt cuộn ngang */
    }

    .product-table thead {
        display: none; /* Ẩn tiêu đề gốc của bảng */
    }

    .product-table tbody, .product-table tr, .product-table td {
        display: block; /* Hiển thị các element dưới dạng block */
        width: 100% !important;
    }

    .product-table tr {
        margin-bottom: 1.5rem;
        border: 1px solid #e5e7eb;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 0;
    }

    .product-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f3f4f6;
        text-align: right; /* Căn phải nội dung của ô */
        position: relative;
        white-space: normal; /* Cho phép chữ xuống dòng */
    }

    .product-table td:last-child {
        border-bottom: none;
    }

    /* Đây là phần quan trọng để hiển thị lại tiêu đề */
    .product-table td::before {
        content: attr(data-label); /* Lấy nội dung từ thuộc tính data-label */
        position: absolute;
        left: 1rem;
        font-weight: 600;
        color: var(--color-text-primary);
    }
    
    /* --- Tinh chỉnh lại các ô cụ thể cho đẹp hơn --- */
    .product-table td[data-label="Chọn"],
    .product-table td[data-label="Ghim"] {
        /* Cho checkbox và nút ghim nằm giữa */
        display: flex;
        justify-content: flex-end;
    }

    .product-table td[data-label="Tên sản phẩm"] {
        background-color: #f9fafb;
        font-size: 1.1rem;
        text-align: left; /* Tên sản phẩm nên căn trái */
    }
    
    .product-table td[data-label="Tên sản phẩm"]::before {
        display: none; /* Ẩn label "Tên sản phẩm:" đi vì nó đã rõ ràng */
    }
    
    .image-list-cell {
        justify-content: flex-end; /* Căn phải các hình ảnh */
    }

    .action-buttons {
        justify-content: flex-end;
    }
}
</style>