<template>
  <div class="comparison-container">
    <h1>So sánh sản phẩm</h1>

    <div v-if="isLoading" class="loading-state">
      <p>Đang tải dữ liệu so sánh...</p>
    </div>

    <div v-else-if="error" class="error-state">
      <p>Lỗi: {{ error }}</p>
      <router-link to="/">Quay về trang chủ</router-link>
    </div>

    <div v-else-if="products.length > 0" class="comparison-table-wrapper">
      <table class="comparison-table">
        <thead>
  <tr>
    <th class="feature-header">Tính năng</th>
    <th v-for="product in products" :key="product.san_pham_id" class="product-header">
      <div class="product-gallery">
        <img :src="getPrimaryImageUrl(product)" :alt="product.ten_san_pham" class="product-image-main" />

        <div class="product-thumbnails">
          <img
            v-for="image in product.hinh_anh_san_pham"
            :key="image.hinh_anh_id"
            :src="getImageUrl(image.duongdan)"
            :alt="product.ten_san_pham"
            class="thumbnail"
          />
        </div>
      </div>
      <h3 class="product-name">{{ product.ten_san_pham }}</h3>
    </th>
  </tr>
</thead>

        <tbody>
          <tr>
            <td class="feature-name">Giá</td>
            <td v-for="product in products" :key="product.san_pham_id">
              <p class="price">{{ formatCurrency(product.gia) }}đ</p>
            </td>
          </tr>
          <tr>
            <td class="feature-name">Mô tả</td>
            <td v-for="product in products" :key="product.san_pham_id">
              <p class="description">{{ product.Mo_ta_seo }}</p>
            </td>
          </tr>
          <tr>
            <td class="feature-name">Thương hiệu</td>
            <td v-for="product in products" :key="product.san_pham_id">
                <p>{{ product.thuong_hieu ? product.thuong_hieu.ten_thuong_hieu : 'N/A' }}</p>
            </td>
          </tr>
          <tr>
            <td class="feature-name">Tồn kho</td>
            <td v-for="product in products" :key="product.san_pham_id">
                <p>{{ product.so_luong_ton_kho }}</p>
            </td>
          </tr>
          
          <tr>
            <td class="feature-name">Các phiên bản</td>
            <td v-for="product in products" :key="product.san_pham_id">
              <ul v-if="product.san_pham_bien_the && product.san_pham_bien_the.length > 0" class="variant-list">
                <li v-for="variant in product.san_pham_bien_the" :key="variant.id" class="variant-item">
                  <strong>{{ variant.ten_bien_the }}:</strong>
                  <span>{{ formatCurrency(variant.gia_bien_the) }}đ</span>
                  <small>(Còn lại: {{ variant.so_luong_ton }})</small>
                </li>
              </ul>
              <p v-else>Không có</p>
            </td>
          </tr>

        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const products = ref([]);
const isLoading = ref(true);
const error = ref(null);
const getImageUrl = (path) => {
return `https://api.sieuthi404.io.vn/storage/${path}`;
};
const formatCurrency = (amount) => {
  if (amount == null) return '';
  return Math.round(parseFloat(amount)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};
const getPrimaryImageUrl = (product) => {
  if (product.hinh_anh_san_pham && product.hinh_anh_san_pham.length > 0) {
    return getImageUrl(product.hinh_anh_san_pham[0].duongdan);
  }
  if (product.hinh_anh) {
    return getImageUrl(product.hinh_anh);
  }
  return getImageUrl(null);
};
onMounted(async () => {
  try {
    const slugs = route.query.products;
    if (!slugs) {
      throw new Error('Không tìm thấy sản phẩm để so sánh.');
    }
    const response = await axios.get(`https://api.sieuthi404.io.vn/api/user/product/details-by-slugs?slugs=${slugs}`);
    products.value = response.data;

  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Đã có lỗi xảy ra.';
    console.error('Lỗi khi lấy dữ liệu so sánh:', err);
  } finally {
    isLoading.value = false;
  }
});
</script>

<style scoped>
.comparison-container {
  width: 90%;
  margin: 2rem auto;
  font-family: 'Montserrat', sans-serif;
}
h1 {
  text-align: center;
  margin-bottom: 2rem;
}
.loading-state, .error-state {
  text-align: center;
  padding: 2rem;
  font-size: 1.2rem;
}
.comparison-table-wrapper {
  overflow-x: auto; /* Cho phép cuộn ngang trên màn hình nhỏ */
}
.comparison-table {
  width: 100%;
  border-collapse: collapse;
  border: 1px solid #e0e0e0;
}
th, td {
  border: 1px solid #e0e0e0;
  padding: 1rem;
  text-align: center;
  vertical-align: top;
}
.feature-header, .feature-name {
  text-align: left;
  font-weight: bold;
  background-color: #f9f9f9;
  width: 15%; /* Cố định độ rộng cột tính năng */
}
.product-header {
  background-color: #f2f8ff;
}
.product-image {
  max-width: 150px;
  height: auto;
  margin-bottom: 1rem;
}
.product-name {
  font-size: 1rem;
  font-weight: 600;
  color: #1663AB;
}
a {
  text-decoration: none;
  color: inherit;
}
.price {
  font-size: 1.2rem;
  font-weight: bold;
  color: #FF0033;
}
.description {
  font-size: 0.9rem;
  text-align: left;
  line-height: 1.5;
}
tbody tr:nth-child(odd) {
  background-color: #fdfdfd;
}

/* MỚI: CSS cho danh sách biến thể */
.variant-list {
  list-style-type: none;
  padding: 0;
  margin: 0;
  text-align: left;
}

.variant-item {
  padding: 5px 0;
  border-bottom: 1px solid #f0f0f0;
}

.variant-item:last-child {
  border-bottom: none;
}

.variant-item span {
  font-weight: 500;
  color: #c82333;
}

.variant-item small {
  display: block;
  color: #6c757d;
  font-size: 0.8em;
}
</style>