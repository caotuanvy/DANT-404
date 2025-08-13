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
              <p class="product-description" v-html="sp.Mo_ta_seo"></p>

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
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Thêm</span>
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
  border: 1px solid #e0e0e0;
  transition: transform 0.2s ease;
  height: 100%; /* Cho phép thẻ co giãn */
  position: relative; /* Rất quan trọng cho discount-badge */
  
  /* === THAY ĐỔI CHÍNH: DÙNG FLEXBOX === */
  display: flex;
  flex-direction: column;
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
.product-info {
  width: 100%;
  display: flex;
  flex-direction: column;
  flex-grow: 1; /* Quan trọng: Giúp đẩy phần dưới cùng xuống */
}
.product-img {
  width: 100%;
  height: 180px;
  object-fit: contain;
  margin-bottom: 12px;
}
.product-category {
  font-size: 12px;
  font-weight: 500;
  color: #1663AB;
  background-color: #1663ab48;
  padding: 5px 12px;
  border-radius: 6px;
  margin-bottom: 8px;
  align-self: flex-start;
}
.product-name {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 4px;
  min-height: 48px;
}
.product-description {
  font-size: 13px;
  color: #555;
  min-height: 39px;
  margin-bottom: 6px;
  flex-grow: 1; /* Đẩy phần giá và nút xuống dưới */
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
  position: static; /* Reset lại position */
  display: flex;
  align-items: baseline;
  gap: 8px;
  margin-top: auto; /* Quan trọng: Tự động đẩy xuống dưới cùng của thẻ */
}

.current-price {
  font-size: 18px;
  font-weight: bold;
  color: red;
}

.original-price {
  font-size: 14px;
  color: #999;
  text-decoration: line-through;
}

.add-to-cart-button {
  /* Cải tiến: Dùng Flexbox để căn chỉnh icon và chữ */
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px; /* Khoảng cách giữa icon và chữ */

  width: 100%;
  border: none;
  padding: 12px; /* Tăng padding một chút */
  font-size: 16px; /* Tăng cỡ chữ cho dễ đọc */
  font-weight: 600; /* Làm chữ đậm hơn */
  border-radius: 8px;
  margin-top: 10px;
  
  background-color: #03A2DC;
  color: white;
  
  /* Cải tiến: Thêm cursor và hiệu ứng transform */
  cursor: pointer;
  transition: background-color 0.2s ease, transform 0.1s ease;
}

/* Thêm CSS cho SVG bên trong nút */
.add-to-cart-button svg {
  width: 20px;
  height: 20px;
}

.add-to-cart-button:hover {
  background-color: #028ec4; /* Màu hover nhẹ nhàng hơn */
}

/* Cải tiến: Thêm hiệu ứng khi nhấn nút */
.add-to-cart-button:active {
  transform: scale(0.98); /* Nút hơi thu nhỏ lại khi nhấn */
}
.main-2{
  background-color: white !important;
  
}
.product-card-link {
  text-decoration: none; 
  color: inherit;
  display: block;
}
@media (max-width: 768px) {
    .product-section {
        padding: 20px 10px; 
    }

    .product-grid {
        
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-top: 20px;
    }

    .product-card {
        /* Chiều cao tự động, giảm padding */
        height: auto;
        padding: 10px;
    }
    
    .product-img {
        height: 120px; /* Giảm chiều cao ảnh */
    }

    .product-name {
        font-size: 14px;
        min-height: 42px; /* Đủ chỗ cho 2 dòng */
    }

    .product-description {
        display: none; /* Ẩn mô tả ngắn trên mobile để tiết kiệm không gian */
    }

    .current-price {
        font-size: 16px;
    }

    .original-price {
        font-size: 12px;
    }
}
</style>



