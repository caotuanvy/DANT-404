<template>
  <div class="product-page" v-if="product">
    <div class="product-container">
      <!-- Hình ảnh chính -->
      <div class="product-images">
        <img :src="imgUrl(product.hinh_anh[0])" alt="Ảnh sản phẩm" class="main-img" v-if="product.hinh_anh && product.hinh_anh.length" />
        <div class="thumbnails">
          <img v-for="(img, idx) in product.hinh_anh" :key="idx" :src="imgUrl(img)" class="thumb" />
        </div>
      </div>

      <!-- Thông tin sản phẩm -->
      <div class="product-info">
        <h2 class="product-name">{{ product.product_name }}</h2>
        <!-- <div class="rating">
          ⭐⭐⭐⭐☆ <span class="rating-count">(128 reviews)</span>
        </div> -->

        <div class="price-box">
          <template v-if="product?.khuyen_mai > 0">
            <span class="sale">{{ formatCurrency(finalPrice) }}</span>
            <span class="original">{{ formatCurrency(selectedVariant.gia) }}</span>
            <span class="discount">
              -{{ product.khuyen_mai }}%
            </span>
          </template>
          <template v-else>
            <span class="sale">{{ formatCurrency(selectedVariant?.gia || 0) }}</span>
          </template>
        </div>

        <div class="stock">🟢 In Stock ({{ selectedVariant?.so_luong_ton_kho || 0 }} available)</div>

        <div class="section">
          <label>Màu sắc:</label>
          <div class="color-options">
            <div
              v-for="variant in product.variants"
              :key="variant.san_pham_bien_the_id"
              class="color-dot"
              :style="{ backgroundColor: convertColor(variant.mau_sac) }"
              :class="{ selected: selectedVariantId === variant.san_pham_bien_the_id }"
              @click="selectedVariantId = variant.san_pham_bien_the_id"
            ></div>
          </div>
        </div>

        <div class="section">
          <label>Kích thước:</label>
          <div class="size-options">
            <div class="size-btn selected">One Size</div>
          </div>
        </div>

        <div class="quantity">
          <label>Số lượng:</label>
          <input type="number" v-model="quantity" min="1" :max="selectedVariant?.so_luong_ton_kho" />
        </div>

        <button class="add-cart-btn" @click="addToCart">🛒 Add to Cart</button>

        <div class="description">
          <h3>Mô tả</h3>
          <p>{{ product.description }}</p>
        </div>
      </div>
    </div>
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
const imgUrl = (img) => {
  return img.startsWith('http') ? img : `http://localhost:8000/storage/${img}`;
};

const finalPrice = computed(() => {
  if (!selectedVariant.value) return 0;
  const price = Number(selectedVariant.value.gia) || 0;
  const percent = Number(product.value?.khuyen_mai) || 0;
  if (percent > 0) {
    return Math.round(price * (1 - percent / 100));
  }
  return price;
});

const convertColor = (colorName) => {
  switch (colorName.toLowerCase()) {
    case 'đen': return '#000';
    case 'trắng': return '#fff';
    case 'xám': return '#ccc';
    case 'xanh': return '#1e40af';
    case 'hồng': return '#f472b6';
    case 'đỏ': return '#dc2626';
    default: return '#ddd';
  }
};

</script>

<style scoped>
.product-page {
  padding: 40px 20px;
  font-family: 'Segoe UI', sans-serif;
}

.product-container {
  display: flex;
  gap: 40px;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
  padding: 32px;
  max-width: 1100px;
  margin: auto;
}

.product-images {
  flex: 1;
}

.main-img {
  width: 100%;
  max-width: 340px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.thumbnails {
  display: flex;
  gap: 8px;
  margin-top: 12px;
}
.thumb {
  width: 64px;
  height: 64px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #ddd;
}

.product-info {
  flex: 1.2;
}

.product-name {
  font-size: 28px;
  font-weight: bold;
  margin-bottom: 8px;
}

.rating {
  font-size: 16px;
  color: #f59e0b;
}
.rating-count {
  color: #555;
  font-size: 14px;
  margin-left: 6px;
}

.price-box {
  margin: 16px 0;
  display: flex;
  align-items: center;
  gap: 12px;
}
.sale {
  font-size: 24px;
  color: #dc2626;
  font-weight: bold;
}
.original {
  text-decoration: line-through;
  color: #888;
}
.discount {
  background: #fee2e2;
  color: #dc2626;
  padding: 2px 6px;
  font-size: 14px;
  border-radius: 4px;
}

.stock {
  color: green;
  font-size: 15px;
  margin-bottom: 12px;
}

.section {
  margin: 16px 0;
}
.color-options {
  display: flex;
  gap: 10px;
  margin-top: 8px;
}
.color-dot {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: 2px solid transparent;
  cursor: pointer;
}
.color-dot.selected {
  border: 2px solid #000;
}

.size-options {
  display: flex;
  gap: 10px;
  margin-top: 8px;
}
.size-btn {
  padding: 8px 16px;
  border: 1px solid #ccc;
  border-radius: 6px;
  cursor: pointer;
}
.size-btn.selected {
  background: #2563eb;
  color: #fff;
  border-color: #2563eb;
}

.quantity {
  margin-top: 12px;
}
.quantity input {
  width: 60px;
  padding: 6px 8px;
  border-radius: 6px;
  border: 1px solid #ccc;
  margin-left: 8px;
}

.add-cart-btn {
  margin-top: 20px;
  padding: 12px 24px;
  background: #2563eb;
  color: white;
  font-weight: 600;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
  transition: background 0.25s;
}
.add-cart-btn:hover {
  background: #1e40af;
}

.description {
  margin-top: 24px;
}
.description h3 {
  font-size: 20px;
  font-weight: bold;
}
.description p {
  margin-top: 8px;
  color: #444;
  line-height: 1.6;
}
</style>