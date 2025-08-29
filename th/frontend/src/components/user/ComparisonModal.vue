<template>
  <div v-if="isVisible" class="modal-overlay" @click.self="closeModal">
    <div class="modal-container">
      <button @click="closeModal" class="modal-close-btn">&times;</button>
      <div class="comparison-container">
        <h1>So sánh sản phẩm</h1>

        <div v-if="isLoading" class="loading-state">...</div>
        <div v-else-if="error" class="error-state">...</div>

        <div v-else-if="products && products.length > 0" class="comparison-table-wrapper">
          <table class="comparison-table">
            <thead>
              <tr>
                <th class="feature-header">Tính năng</th>
                <th v-for="product in products" :key="product.san_pham_id" class="product-header">
                  <div class="product-gallery">
                    <img
                        v-if="product.hinh_anh_san_pham?.[0]?.duongdan"
                        :src="getImageUrl(product.hinh_anh_san_pham[0].duongdan)"
                        :alt="product.ten_san_pham"
                        class="product-image-main"
                      />
                    
                  </div>
                  <h3 class="product-name">{{ product.ten_san_pham }}</h3>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="feature-name">Giá Gốc</td>
                <td v-for="product in products" :key="product.san_pham_id">
                  <p class="price">{{ product.gia_goc_display }}</p>
                </td>
              </tr>
              <tr>
                <td class="feature-name">Mô tả</td>
                <td v-for="product in products" :key="product.san_pham_id">
                  <p class="description">{{ product.Mo_ta_seo }}</p>
                </td>
              </tr>
              <tr v-if="showKichThuocRow">
                <td class="feature-name">Kích thước</td>
                <td v-for="product in products" :key="product.san_pham_id">
                  <p>{{ product.kich_thuoc_display || 'Không ' }}</p>
                </td>
              </tr>
              <tr v-if="showTrongLuongRow">
                <td class="feature-name">Trọng lượng</td>
                <td v-for="product in products" :key="product.san_pham_id">
                  <p>{{ product.trong_luong_display || 'Không ' }}</p>
                </td>
              </tr>
              <tr v-if="showMauSacRow">
                <td class="feature-name">Màu sắc</td>
                <td v-for="product in products" :key="product.san_pham_id">
                  <p>{{ product.mau_sac_display || 'Không ' }}</p>
                </td>
              </tr>
              <tr>
                <td class="feature-name">Tổng Tồn Kho</td>
                <td v-for="product in products" :key="product.san_pham_id">
                  <p>{{ product.so_luong_ton_kho }}</p>
                </td>
              </tr>
              <tr v-if="showVariantsRow">
                <td class="feature-name">Chi tiết phiên bản</td>
                <td v-for="product in products" :key="product.san_pham_id">
                  <ul v-if="product.san_pham_bien_the && product.san_pham_bien_the.length > 0" class="variant-list">
                    <li v-for="variant in product.san_pham_bien_the" :key="variant.bien_the_id" class="variant-item">
                      <strong>{{ variant.ten_bien_the }}:</strong>
                      <span>{{ formatCurrency(variant.gia) }}đ</span>
                      <small>(Còn lại: {{ variant.so_luong_ton_kho }})</small>
                    </li>
                  </ul>
                  <p v-else>Không có</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits, computed } from 'vue';

const props = defineProps({
  isVisible: { type: Boolean, required: true },
  products: { type: Array, required: true },
  isLoading: { type: Boolean, default: false },
  error: { type: String, default: null },
});

const emit = defineEmits(['close']);
const closeModal = () => emit('close');
const hasValue = (field) => props.products.some(p => p[field] && p[field].length > 0);
const showKichThuocRow = computed(() => hasValue('kich_thuoc_display'));
const showTrongLuongRow = computed(() => hasValue('trong_luong_display'));
const showMauSacRow = computed(() => hasValue('mau_sac_display'));
const showVariantsRow = computed(() => props.products.some(p => p.san_pham_bien_the && p.san_pham_bien_the.length > 0));
const getImageUrl = (path) => {
   console.log('Đường dẫn ảnh:', path);
 return `https://api.sieuthi404.io.vn/storage/${path}`;
};
const formatCurrency = (amount) => {
  if (amount == null) return '';
  return Math.round(parseFloat(amount)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};
</script>
<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
  font-family: "Lora", serif !important;
}
.modal-container {
  background-color: white;
  padding: 2rem;
  border-radius: 10px;
  width: 90%;
  max-width: 1200px;
  max-height: 95vh;
  overflow-y: auto;
  position: relative;
}
.modal-close-btn {
  position: absolute;
  top: 15px;
  right: 15px;
  background: none;
  border: none;
  font-size: 2rem;
  cursor: pointer;
  color: #333;
}
.comparison-container {
  width: 100%;
  margin: 0;
  
}
h1 {
  text-align: center;
  margin-bottom: 2rem;
}
.loading-state,
.error-state {
  text-align: center;
  padding: 2rem;
  font-size: 1.2rem;
}
.comparison-table-wrapper {
  overflow-x: auto;
}
.comparison-table {
  width: 100%;
  border-collapse: collapse;
  border: 1px solid #e0e0e0;
  background-color: white !;
}
th,
td {
  border: 1px solid #e0e0e0;
  padding: 1rem;
  text-align: center;
  vertical-align: top;
}
.feature-header,
.feature-name {
  text-align: left;
  font-weight: bold;
  background-color: #f9f9f9;
  width: 15%;
}
.product-header {
  background-color: #f2f8ff;
}
.product-image {
  max-width: 150px;
  height: auto;
  margin-bottom: 1rem;
}
.product-image-main{
  height: 100px;
}
.product-name {
  font-size: 1rem;
  font-weight: 600;
  color: #1663ab;
}
.price {
  font-size: 1.2rem;
  font-weight: bold;
  color: #ff0033;
}
.description {
  font-size: 0.9rem;
  text-align: left;
  line-height: 1.5;
}
tbody tr:nth-child(odd) {
  background-color: #fdfdfd;
}
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