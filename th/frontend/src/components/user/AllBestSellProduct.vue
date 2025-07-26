<template>
  <section class="all-products-page">
    <div class="section-header">
      <div class="section-title">
        <h2 class="title">Tất Cả Sản Phẩm Bán Chạy 🔥</h2>
        <p>Danh sách các sản phẩm bán tốt nhất hệ thống</p>
      </div>
      <div class="filter-buttons">
        <button @click="setFilter('all')" :class="{ active: activeFilter === 'all' }">Tất cả</button>
        <button @click="setFilter('sale')" :class="{ active: activeFilter === 'sale' }">Giảm giá</button>
        <button @click="setFilter('newest')" :class="{ active: activeFilter === 'newest' }">Mới nhất</button>
      </div>
    </div>

    <div class="product-grid">
      <router-link
        v-for="sp in filteredProducts"
        :key="sp.san_pham_id"
        :to="`/san-pham/${sp.slug || sp.san_pham_id}`"
        class="product-card-link"
      >
        <div class="product-card">
          <div v-if="getValidDiscountPercentage(sp.khuyen_mai) > 0" class="discount-badge">
            giảm {{ getValidDiscountPercentage(sp.khuyen_mai) }}%
          </div>
          <img :src="getImageUrl(sp.hinh_anh)" :alt="sp.ten_san_pham" class="product-img" />
          <div class="product-info">
            <h3 class="product-name">{{ sp.ten_san_pham }}</h3>
            <p v-if="getValidDiscountPercentage(sp.khuyen_mai) > 0" class="original-price">
              {{ formatCurrency(sp.gia) }}đ
            </p>
            <div class="price-and-icon">
              <p class="current-price">{{ formatCurrency(calculateDisplayPrice(sp.gia, sp.khuyen_mai)) }}đ</p>
            </div>
            <div class="sold-info">
              <span>Đã bán: </span>
              <span class="sold-count">{{ sp.so_luong_ban }}</span>
            </div>
            <button @click.prevent="addToCart(sp)" class="add-to-cart-btn">
              Thêm vào giỏ
            </button>
          </div>
        </div>
      </router-link>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const products = ref([]);
const activeFilter = ref('all');

const setFilter = (filter) => {
  activeFilter.value = filter;
};

const filteredProducts = computed(() => {
  switch (activeFilter.value) {
    case 'sale':
      return products.value.filter(p => parseFloat(p.khuyen_mai) > 0);
    case 'newest':
      return [...products.value].sort((a, b) => b.san_pham_id - a.san_pham_id);
    case 'all':
    default:
      return products.value;
  }
});

const addToCart = async (product) => {
  try {
    const detailRes = await axios.get(`http://localhost:8000/api/user/${product.slug}`);
    const productDetails = detailRes.data;

    if (!productDetails.variants || productDetails.variants.length === 0) {
        alert('Sản phẩm này hiện chưa có phiên bản để mua.');
        return;
    }

    const variantToAdd = productDetails.variants[0];

    if (variantToAdd.so_luong_ton_kho < 1) {
        alert(`Rất tiếc, phiên bản "${variantToAdd.ten_bien_the}" đã hết hàng.`);
        return;
    }

    const payload = {
      san_pham_bien_the_id: variantToAdd.bien_the_id,
      quantity: 1,
    };
    
    const token = localStorage.getItem('token');
    
    const cartRes = await axios.post('http://localhost:8000/api/cart/add', payload, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });

    alert(cartRes.data.message || 'Thêm vào giỏ hàng thành công!');

  } catch (error) {
    const errorMessage = error.response?.data?.message || 'Có lỗi xảy ra, vui lòng thử lại sau.';
    alert(errorMessage);
    console.error("Lỗi khi thêm vào giỏ hàng:", error);
  }
};

onMounted(async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/admin/products-sell-top-all');
    products.value = res.data.map(product => {
      const ton_kho = parseInt(product.so_luong_ton_kho) || 0;
      const da_ban = parseInt(product.so_luong_ban) || 0;
      const tong_so = ton_kho + da_ban;
      const phan_tram = tong_so > 0 ? (da_ban / tong_so) * 100 : 0;
      return {
        ...product,
        gia: parseFloat(product.gia) || 0,
        so_luong_ban: da_ban,
        tong_ton_kho: ton_kho,
        tong_so_luong: tong_so,
        phan_tram_da_ban: Math.round(phan_tram),
      };
    });
  } catch (err) {
    console.error('Lỗi khi tải tất cả sản phẩm bán chạy:', err);
  }
});

const getImageUrl = (path) => {
  return path ? `http://localhost:8000/storage/${path}` : '/images/default-grape.png';
};

const getValidDiscountPercentage = (promo) => {
  let percentage = 0;
  if (promo === null || promo === undefined) return 0;
  if (typeof promo === 'number') percentage = promo;
  else if (typeof promo === 'string') {
    const match = promo.match(/(\d+)%/);
    percentage = match && match[1] ? parseInt(match[1]) : parseInt(promo) || 0;
  }
  return Math.max(0, Math.min(100, percentage));
};

const calculateDiscountedPrice = (originalPrice, promo) => {
  const percentage = getValidDiscountPercentage(promo);
  const price = parseFloat(originalPrice);
  return isNaN(price) || percentage === 0 ? price : Math.round(price * (1 - percentage / 100));
};

const calculateDisplayPrice = (originalPrice, promo) => {
  return getValidDiscountPercentage(promo) > 0
    ? calculateDiscountedPrice(originalPrice, promo)
    : parseFloat(originalPrice);
};

const formatCurrency = (amount) => {
  if (amount == null) return '';
  return Math.round(parseFloat(amount)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};
</script>

<style scoped>
.all-products-page {
  width: 90%;
  max-width: 1200px; 
  margin: 40px auto;
  padding: 20px;
  font-family: 'Inter', sans-serif; 
}

/* --- TIÊU ĐỀ VÀ BỘ LỌC --- */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2.5rem;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.section-title .title {
  font-size: 2rem; 
  font-weight: 700;
  color: #1a202c; 
}

.section-title p {
  font-size: 1rem; 
  color: #718096; 
  margin-top: 0.5rem;
}

.filter-buttons {
  display: flex;
  gap: 0.75rem;
}

.filter-buttons button {
  padding: 0.6rem 1.5rem;
  border: 1px solid #e2e8f0;
  background-color: #ffffff;
  color: #4a5568;
  border-radius: 9999px; 
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.filter-buttons button:hover:not(.active) {
  background-color: #f7fafc;
  border-color: #cbd5e0;
}

.filter-buttons button.active {
  border-color: #4299e1;
  background-color: #4299e1;
  color: #ffffff;
  box-shadow: 0 4px 6px -1px rgba(66, 153, 225, 0.3);
}
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
  gap: 1.75rem;
}
.product-card-link {
  text-decoration: none;
  color: inherit;
  display: block; 
}

.product-card {
  background: white;
  border-radius: 16px;
  border: 1px solid #e2e8f0; 
  position: relative;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.product-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1), 0 6px 6px rgba(0, 0, 0, 0.08);
}

.discount-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  background-color: #4299e1; 
  color: white;
  font-weight: 700;
  font-size: 0.8rem;
  padding: 5px 12px;
  border-radius: 9999px;
  z-index: 1;
}
.product-img {
  width: 100%;
  height: 220px;
  object-fit: cover;
  background-color: #f8f9fa;
  transition: transform 0.3s ease;
}

.product-card:hover .product-img {
  transform: scale(1.05); 
}
.product-info {
  padding: 1rem 1.25rem;
  text-align: left;
  display: flex;
  flex-direction: column;
  flex-grow: 1; 
}

.product-name {
  font-size: 1rem;
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 0.5rem;
  line-height: 1.4;
  /* Đảm bảo chiều cao đồng nhất */
  height: 45px;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

/* Giá sản phẩm */
.original-price {
  font-size: 0.9rem;
  text-decoration: line-through;
  color: #a0aec0;
  margin: 0;
  height: 20px; 
}

.price-and-icon {
  display: flex;
  align-items: center;
  margin-bottom: 0.75rem;
}

.current-price {
  font-size: 1.25rem; /* 20px */
  font-weight: 700;
  color: #ff0000; /* Màu cam đậm */
}


.sold-info {
  font-size: 0.875rem;
  color: #718096;
  margin-bottom: 1rem;
}

.sold-count {
  font-weight: 600;
  color: #4a5568;
}
.add-to-cart-btn {
  margin-top: auto;
  padding: 12px;
  border: none;
  border-radius: 12px;
  background-color: #edf2f7; 
  color: #2d3748;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  width: 100%;
  transition: all 0.3s ease;
  opacity: 0; 
  transform: translateY(10px); 
}
.product-card:hover .add-to-cart-btn {
  opacity: 1;
  transform: translateY(0);
  background-color: #4299e1;
  color: white;
}

.add-to-cart-btn:hover {
  background-color: #2b6cb0; 
}
</style>