<template>
  <section class="main-2">
    <section class="product-section">
      <div class="product-grid">
        <router-link 
          v-for="sp in products.slice(0, 8)" 
          :key="sp.san_pham_id" 
          :to="`/san-pham/${sp.slug}`" 
          class="product-card-link"
        >
          <div class="product-card">
            <div v-if="getValidDiscountPercentage(sp.khuyen_mai) > 0" class="discount-badge">
              -{{ getValidDiscountPercentage(sp.khuyen_mai) }}%
            </div>

            <img :src="getImageUrl(sp.hinh_anh)" :alt="sp.ten_san_pham" class="product-img" />

            <div class="product-info">
              <span class="product-category">{{ sp.ten_danh_muc || 'Danh mục' }}</span>
              <h3 class="product-name">{{ sp.ten_san_pham }}</h3>
              <p class="product-description">{{ sp.Mo_ta_seo || '...' }}</p>

              <div class="product-rating">
                ⭐ {{ sp.diem_danh_gia || '4.8' }} 
                <span class="rating-count">({{ sp.so_danh_gia || 100 }} đánh giá)</span>
              </div>

              <div class="price-section">
                <p class="current-price">
                  {{ formatCurrency(calculateDisplayPrice(sp.gia, sp.khuyen_mai)) }} ₫
                </p>
                <p v-if="getValidDiscountPercentage(sp.khuyen_mai) > 0" class="original-price">
                  {{ formatCurrency(sp.gia) }} ₫
                </p>
              </div>

              <button class="add-to-cart-button" @click.prevent="addToCart(sp)">
                🛒 Thêm
              </button>
            </div>
          </div>
        </router-link>
      </div>
    </section>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const products = ref([])

const getImageUrl = (path) => {
  return path ? `http://localhost:8000/storage/${path}` : '/images/default-grape.png'
}

const getValidDiscountPercentage = (promo) => {
  let percentage = 0
  if (!promo) return 0
  if (typeof promo === 'number') percentage = promo
  else {
    const match = promo.match(/(\d+)%/)
    percentage = match ? parseInt(match[1]) : parseInt(promo) || 0
  }
  return Math.max(0, Math.min(100, percentage))
}

const calculateDisplayPrice = (originalPrice, promo) => {
  const price = parseFloat(originalPrice)
  const discount = getValidDiscountPercentage(promo)
  return discount > 0 ? Math.round(price * (1 - discount / 100)) : price
}

const formatCurrency = (amount) => {
  return Math.round(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}

const addToCart = async (product) => {
  const token = localStorage.getItem('token');
  if (!token) {
    alert('Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng!');
    return;
  }

  try {
    const response = await axios.post('http://localhost:8000/api/cart/add', 
      {
        san_pham_bien_the_id: product.san_pham_bien_the_id,
        quantity: 1                   
      }, 
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    );
    alert(`Đã thêm "${product.ten_san_pham}" vào giỏ hàng thành công!`);
    console.log(response.data); 
  } catch (err) {
    if (err.response && err.response.status === 401) {
      alert('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
    } else {
      console.error('Lỗi khi thêm vào giỏ hàng:', err);
      alert('Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng.');
    }
  }
};

onMounted(async () => {
  const res = await axios.get('http://localhost:8000/api/admin/products-featured')
  products.value = res.data
})
</script>

<style scoped>
.product-section {
  padding: 40px 20px;
  max-width: 1280px;
  margin: auto;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 25px;
  margin-top: 60px;
}

.product-card {
  background: white;
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  border: 1px solid #e0e0e0;
  transition: transform 0.2s ease;
  height: 480px;
}

.product-card:hover {
  transform: translateY(-4px);
}

.discount-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  background-color: #03A2DC;
  color: white;
  font-size: 13px;
  font-weight: bold;
  padding: 4px 10px;
  border-radius: 6px;
}
.product-info{
  width: 100%;
  position: relative;
  
}
.product-img {
  width: 100%;
  height: 180px;
  object-fit: contain;
  border-radius: 8px;
  margin-bottom: 12px;
  box-shadow: none !important;
}

.product-category {
  font-size: 12px;
  font-weight: 500;
  color: #1663AB;
  background-color: #1663ab48;
  padding: 5px 12px;
  border-radius: 6px;
  margin-bottom: 8px;
}

.product-name {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 4px;
 
}

.product-description {
  font-size: 13px;
  color: #555;
  
  min-height: 36px;
  margin-bottom: 6px;
}

.product-rating {
  font-size: 14px;
  margin-bottom: 6px;
  color: #fbc02d;
}

.rating-count {
  font-size: 12px;
  color: #888;
}

.price-section {
  position: absolute;
  top: 190px;

}

.original-price {
  font-size: 14px;
  color: #999;
  text-decoration: line-through;
  margin-top: -10px;
  
}

.current-price {
  font-size: 18px;
  font-weight: bold;
  color: red;
  
}

.add-to-cart-button {
  background-color: #1663AB;
  color: white;
  border: none;
  padding:  10px;
  font-size: 14px;
  border-radius: 8px;
  width: 100px;
  transition: background 0.3s ease;
  position: absolute;
  right: 5px;
  top: 210px;
  
}

.add-to-cart-button:hover {
  background-color: #135593;
}
.main-2{
  background-color: white !important;
  
}
.product-card-link {
  text-decoration: none; 
  color: inherit;
  display: block;
}
</style>



