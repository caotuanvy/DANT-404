<template>
  <div class="product-detail" v-if="product">
    <h2>{{ product.product_name }}</h2>
    <img :src="product.hinh_anh" alt="Ảnh sản phẩm" class="product-img" />
    <p><b>Mô tả:</b> {{ product.description }}</p>
    <p><b>Danh mục: </b> {{ product.danh_muc.ten_danh_muc }}</p>

    <h3>Chọn biến thể:</h3>
    <select v-model="selectedVariantId">
      <option v-for="variant in product.variants" :key="variant.san_pham_bien_the_id" :value="variant.san_pham_bien_the_id">
        {{ variant.mau_sac }} - {{ variant.kich_thuoc }}
      </option>
    </select>

    <div v-if="selectedVariant">
      <h4>Thông tin biến thể</h4>
      <p><b>Màu sắc:</b> {{ selectedVariant.mau_sac }}</p>
      <p><b>Kích thước:</b> {{ selectedVariant.kich_thuoc }}</p>
      <p><b>Giá:</b> {{ formatCurrency(selectedVariant.gia) }}</p>
      <p><b>Tồn kho:</b> {{ selectedVariant.so_luong_ton_kho }}</p>
    </div>
  </div>
  <div v-else>
    Đang tải chi tiết sản phẩm...
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const product = ref(null);
const selectedVariantId = ref(null);
const route = useRoute();

const formatCurrency = (value) =>
  new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);

const selectedVariant = computed(() => {
  if (!product.value || !product.value.variants) return null;
  return product.value.variants.find(v => v.san_pham_bien_the_id === selectedVariantId.value);
});

onMounted(async () => {
  const id = route.params.id;
  const res = await axios.get(`/products/${id}`);
  product.value = res.data;
  // Chọn biến thể đầu tiên mặc định
  if (product.value.variants && product.value.variants.length > 0) {
    selectedVariantId.value = product.value.variants[0].san_pham_bien_the_id;
  }
});
</script>