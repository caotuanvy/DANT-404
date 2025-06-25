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
      <div>
        <label>Số lượng: </label>
        <input type="number" v-model="quantity" min="1" :max="selectedVariant.so_luong_ton_kho" style="width:60px" />
      </div>
      <button class="add-cart-btn" @click="addToCart">Thêm vào giỏ hàng</button>
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
const quantity = ref(1);
const route = useRoute();

const formatCurrency = (value) =>
  new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);

const selectedVariant = computed(() => {
  if (!product.value || !product.value.variants) return null;
  return product.value.variants.find(v => v.san_pham_bien_the_id === selectedVariantId.value);
});

const addToCart = async () => {
  if (!selectedVariant.value) return;
  try {
    // Lấy token từ localStorage (nếu dùng JWT)
    const token = localStorage.getItem('token');
    await axios.post(
      '/cart/add',
      {
        san_pham_bien_the_id: selectedVariant.value.san_pham_bien_the_id,
        quantity: Number(quantity.value)
      },
      {
        headers: token ? { Authorization: `Bearer ${token}` } : {},
        withCredentials: true // Nếu backend dùng cookie/session
      }
    );
    alert('Đã thêm vào giỏ hàng!');
  } catch (e) {
    // Debug lỗi BE nếu cần
    if (e.response) {
      console.error('Lỗi BE:', e.response.data);
      if (e.response.data && e.response.data.message) {
        alert('Lỗi: ' + e.response.data.message);
      }
    }
    // Nếu lỗi (chưa đăng nhập), fallback lưu localStorage
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const idx = cart.findIndex(
      item => item.san_pham_bien_the_id === selectedVariant.value.san_pham_bien_the_id
    );
    if (idx !== -1) {
      cart[idx].quantity += Number(quantity.value);
    } else {
      cart.push({
        san_pham_bien_the_id: selectedVariant.value.san_pham_bien_the_id,
        product_name: product.value.product_name,
        mau_sac: selectedVariant.value.mau_sac,
        kich_thuoc: selectedVariant.value.kich_thuoc,
        gia: selectedVariant.value.gia,
        hinh_anh: product.value.hinh_anh,
        quantity: Number(quantity.value)
      });
    }
    localStorage.setItem('cart', JSON.stringify(cart));
    alert('Đã thêm vào giỏ hàng (local)!');
  }
};
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

<style scoped>
.product-detail {
  max-width: 600px;
  margin: 0 auto;
  padding: 24px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px #eee;
}
.product-img {
  max-width: 220px;
  border-radius: 8px;
  box-shadow: 0 1px 4px #ddd;
  margin-bottom: 16px;
}
.add-cart-btn {
  margin-top: 12px;
  padding: 8px 18px;
  background: #007cff;
  color: #fff;
  border: none;
  border-radius: 4px;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.2s;
}
.add-cart-btn:hover {
  background: #005fa3;
}
</style>