<template>
  <section class="main-best-sell">
    <section class="top-products">
      <div class="header-banner">
        <h2 class="title">Sản phẩm bán chạy 🔥</h2>
      </div>

      <div class="product-grid">
        <div v-for="sp in products" :key="sp.san_pham_id" class="product-card-container">
          <div class="compare-container">
            <input
              type="checkbox"
              :id="'compare-' + sp.san_pham_id"
              class="compare-checkbox"
              :checked="isSelectedForCompare(sp)"
              @change="toggleCompare(sp)"
            />
            <label :for="'compare-' + sp.san_pham_id" class="compare-label">So sánh</label>
          </div>

          <router-link :to="`san-pham/${sp.slug}`" class="product-card-link">
             <div class="product-card">
       <div v-if="getValidDiscountPercentage(sp.khuyen_mai) > 0" class="discount-badge">
        giảm {{ getValidDiscountPercentage(sp.khuyen_mai) }}%
       </div>
       <img :src="getImageUrl(sp.hinh_anh)" style="box-shadow: none !important;" :alt="sp.ten_san_pham" class="product-img" />
       <div class="product-info">
        <h3 class="product-name">{{ sp.ten_san_pham }}</h3>
        <p v-if="getValidDiscountPercentage(sp.khuyen_mai) > 0" class="original-price">
         {{ formatCurrency(sp.gia) }}đ
        </p>
        <p class="Product-Mo-ta-seo">{{ sp.Mo_ta_seo }}</p>
        <div class="price-and-icon">
         <p class="current-price">{{ formatCurrency(calculateDisplayPrice(sp.gia, sp.khuyen_mai)) }}đ</p>
        </div>
        <div class="main-prsell">
         <p class="promotion-text">
          Giảm trực tiếp <b class="price-betsell">{{ formatCurrency(calculateMaxDiscountAmount(sp.gia, sp.khuyen_mai)) }}</b> VNĐ khi mua ngay
         </p>
         <div class="sold-progress">
          <div class="sold-segment sold-segment-sold" :style="{ width: Math.min(sp.phan_tram_da_ban, 100) + '%' }"></div>
          <div class="sold-overlay-text">
           <span class="fire-icon">🔥</span>
           <span class="sold-text">Đã bán {{ sp.so_luong_ban }}</span>
          </div>
         </div>
         <p class="progress-summary-text" v-if="sp.tong_ton_kho !== undefined">
          (Còn lại: {{ sp.tong_ton_kho }} / Tổng: {{ sp.tong_so_luong }})
         </p>
        </div>
       </div>
      </div>
          </router-link>
        </div>
      </div>

      <div class="button-container">
  <router-link to="/san-pham-ban-chay" class="view-more-button">
    Xem thêm
  </router-link>
</div>
    </section>

    <div v-if="comparisonList.length > 0" class="comparison-bar">
      <div class="comparison-items">
        <div v-for="item in comparisonList" :key="item.san_pham_id" class="comparison-item">
          <img :src="getImageUrl(item.hinh_anh)" :alt="item.ten_san_pham" class="comparison-img" />
          <p class="comparison-name">{{ item.ten_san_pham }}</p>
          <button @click="toggleCompare(item)" class="remove-compare-item">×</button>
        </div>
        <div v-for="i in (4 - comparisonList.length)" :key="'placeholder-' + i" class="comparison-item-placeholder">
          <p>+</p>
        </div>
      </div>
      <button class="compare-button" @click="handleCompare" :disabled="comparisonList.length < 2">
        So sánh ngay ({{ comparisonList.length }})
      </button>
    </div>

    <ComparisonModal
      :is-visible="isComparisonModalVisible"
      :products="detailedComparisonProducts"
      :is-loading="isComparisonLoading"
      :error="comparisonError"
      @close="closeComparisonModal"
    />
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
// MỚI: Import component Modal
import ComparisonModal from './ComparisonModal.vue';

const products = ref([]);

// --- MỚI: Logic quản lý trạng thái Modal ---
const isComparisonModalVisible = ref(false); // Trạng thái ẩn/hiện của modal
const detailedComparisonProducts = ref([]);   // Dữ liệu chi tiết cho modal
const isComparisonLoading = ref(false);       // Trạng thái tải dữ liệu cho modal
const comparisonError = ref(null);            // Trạng thái lỗi khi tải dữ liệu

const openComparisonModal = () => {
  isComparisonModalVisible.value = true;
};

const closeComparisonModal = () => {
  isComparisonModalVisible.value = false;
  // Xóa dữ liệu cũ khi đóng modal
  detailedComparisonProducts.value = [];
  comparisonError.value = null;
};
// --- Kết thúc Logic quản lý trạng thái Modal ---

// --- Logic cho chức năng so sánh (giữ nguyên và cập nhật handleCompare) ---
const comparisonList = ref([]);
const MAX_COMPARE_ITEMS = 4;

const isSelectedForCompare = (product) => {
  return comparisonList.value.some(item => item.san_pham_id === product.san_pham_id);
};

const toggleCompare = (product) => {
  const index = comparisonList.value.findIndex(item => item.san_pham_id === product.san_pham_id);
  if (index !== -1) {
    comparisonList.value.splice(index, 1);
  } else {
    if (comparisonList.value.length < MAX_COMPARE_ITEMS) {
      comparisonList.value.push(product);
    } else {
      alert(`Bạn chỉ có thể so sánh tối đa ${MAX_COMPARE_ITEMS} sản phẩm.`);
      const checkbox = document.getElementById('compare-' + product.san_pham_id);
      if (checkbox) checkbox.checked = false;
    }
  }
};

// MỚI: Cập nhật hàm handleCompare để mở Modal
const handleCompare = async () => {
  if (comparisonList.value.length < 2) {
    alert('Bạn cần chọn ít nhất 2 sản phẩm để so sánh.');
    return;
  }

  // Bắt đầu quá trình tải và mở modal ở trạng thái loading
  isComparisonLoading.value = true;
  comparisonError.value = null;
  openComparisonModal();

  try {
    const slugs = comparisonList.value.map(p => p.slug).join(',');
    // Gọi API để lấy thông tin chi tiết của các sản phẩm đã chọn
    const response = await axios.get(`http://localhost:8000/api/user/product/details-by-slugs?slugs=${slugs}`);
    detailedComparisonProducts.value = response.data;
  } catch (err) {
    comparisonError.value = err.response?.data?.message || err.message || 'Đã có lỗi xảy ra.';
    console.error('Lỗi khi lấy dữ liệu so sánh:', err);
  } finally {
    // Kết thúc quá trình tải
    isComparisonLoading.value = false;
  }
};

// Các hàm tiện ích và onMounted giữ nguyên
const getImageUrl = (path) => {
  return path ? `http://localhost:8000/storage/${path}` : '/images/default-grape.png';
};

const formatCurrency = (amount) => {
  if (amount == null) return '';
  return Math.round(parseFloat(amount)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

const getValidDiscountPercentage = (promo) => {
  let percentage = 0
  if (promo === null || promo === undefined) return 0
  if (typeof promo === 'number') percentage = promo
  else if (typeof promo === 'string') {
    const match = promo.match(/(\d+)%/)
    percentage = match && match[1] ? parseInt(match[1]) : parseInt(promo) || 0
  }
  return Math.max(0, Math.min(100, percentage))
};

const calculateDiscountedPrice = (originalPrice, promo) => {
  const percentage = getValidDiscountPercentage(promo);
  const price = parseFloat(originalPrice);
  return isNaN(price) || percentage === 0 ? price : Math.round(price * (1 - percentage / 100));
};

const calculateMaxDiscountAmount = (originalPrice, promo) => {
  const percentage = getValidDiscountPercentage(promo);
  const price = parseFloat(originalPrice);
  const discountAmount = isNaN(price) ? 0 : price * (percentage / 100);
  return Math.min(Math.round(discountAmount), 600000);
};

const calculateDisplayPrice = (originalPrice, promo) => {
  return getValidDiscountPercentage(promo) > 0
    ? calculateDiscountedPrice(originalPrice, promo)
    : parseFloat(originalPrice);
};

onMounted(async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/admin/products-sell-top');
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
    console.error('Lỗi khi tải sản phẩm bán chạy:', err);
  }
});
</script>

<style scoped>
/* Toàn bộ CSS cũ của bạn giữ nguyên ở đây */
/* Các style cũ của bạn giữ nguyên */
.main-best-sell {
 background-color: #ffffff !important;
}
.top-products {
 background: linear-gradient(to bottom, #1663AB, #03A2DC);
 border-radius: 20px;
 padding: 40px 0;
 width: 80%;
 margin: auto;
 color: #333;
}
.header-banner {
 text-align: center;
 margin-bottom: 30px;
}
.title {
 color: white;
 font-size: 32px;
 font-weight: bolder;
 font-family: 'Montserrat', sans-serif;
 text-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
 text-align: left;
 margin-left: 30px;
 margin-top: -10px;
}
.product-grid {
 display: grid;
 grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
 gap: 20px;
 max-width: 1200px;
 margin: auto;
 padding: 0 20px;
}
.product-card {
 background: white;
 border-radius: 12px;
 height: 480px;
 padding: 15px;
 box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
 transition: transform 0.2s ease;
 display: flex;
 flex-direction: column;
 align-items: center;
}
.product-card:hover {
 transform: translateY(-5px);
}
.discount-badge {
 position: absolute;
 top: 10px;
 left: 10px;
 background-color: #03A2DC;
 color: white;
 font-weight: 600;
 font-size: 13px;
 padding: 4px 10px;
 border-radius: 8px;
 z-index: 5;
}
.product-img {
 width: 100%;
 height: 180px;
 object-fit: contain;
 border-radius: 8px;
 margin-bottom: 10px;
}
.product-info {
 width: 100%;
}
.price-betsell {
 color: #FF0033;
}
.product-name {
 font-size: 16px;
 font-weight: 600;
 color: #111;
 margin-bottom: -15px;
 min-height: 40px;
}
.original-price {
 font-size: 14px;
 text-decoration: line-through;
 color: #999;
 margin-bottom: 0px;
}
.current-price {
 font-size: 20px;
 font-weight: bold;
 color: #FF0033;
}
.price-and-icon {
 display: flex;
 justify-content: space-between;
 align-items: center;
 margin-bottom: 10px;
}
.main-prsell {
 position: absolute;
 top: 330px;
 left: 5px;
 right: 5px; /* Thêm right để căn đều */
}
.promotion-text {
 font-size: 12px;
 color: rgb(30, 29, 29);
 background-color: rgba(0, 0, 0, 0.108);
 padding: 8px;
 border-radius: 6px;
 line-height: 1.4;
 margin-top: 25px;
 margin-bottom: -20px;
}
.sold-progress {
 background-color: #e6e6e6;
 border-radius: 20px;
 height: 22px;
 width: calc(100% - 20px); /* Điều chỉnh lại chiều rộng */
 margin: 35px auto 0;
 overflow: hidden;
 position: relative;
}
.sold-segment-sold {
 background: linear-gradient(to right, #008CFF, #2EC8FF);
 height: 100%;
 border-top-left-radius: 20px;
 border-bottom-left-radius: 20px;
 transition: width 0.3s ease;
}
.sold-overlay-text {
 position: absolute;
 top: 50%;
 left: 0;
 width: 100%;
 transform: translateY(-50%);
 display: flex;
 align-items: center;
 justify-content: center; /* Căn giữa text */
 padding: 0 10px;
 font-size: 14px;
 font-weight: 600;
 color: #333;
}
.fire-icon {
 font-size: 18px;
 margin-right: 6px;
}
.progress-summary-text {
 font-size: 13px;
 color: #666;
 text-align: center;
 margin-top: 5px;
 font-style: italic;
}
.button-container {
 text-align: center;
 margin-top: 40px;
}
.view-more-button {
 background-color: #007bff00;
 color: #ffffff;
 padding: 10px 30px;
 border: 1px solid white;
 border-radius: 10px;
 font-size: 17px;
 cursor: pointer;
 font-weight: bold;
 transition: background-color 0.3s ease, transform 0.2s ease;
 margin-bottom: -15px;
 margin-top: -10px;
}
.view-more-button:hover {
 background-color: #0056b3;
 transform: translateY(-2px);
}
.product-card-link {
 text-decoration: none;
 color: inherit;
 display: block;
}
.Product-Mo-ta-seo {
 font-size: 12px;
 line-height: 1.2;
 color: gray;
 margin-bottom: 0px;
 height: 3.6em; /* Giới hạn 3 dòng */
 overflow: hidden;
}

/* --- MỚI: CSS cho chức năng so sánh --- */
.product-card-container {
 position: relative; /* Container cần có position relative */
}
.compare-container {
 position: absolute;
 top: 10px;
 right: 10px;
 z-index: 10;
 background-color: rgba(255, 255, 255, 0.8);
 padding: 5px;
 border-radius: 5px;
 display: flex;
 align-items: center;
}
.compare-checkbox {
 margin-right: 5px;
 cursor: pointer;
}
.compare-label {
 font-size: 12px;
 cursor: pointer;
 user-select: none; /* Tránh chọn text khi click */
}

.comparison-bar {
 position: fixed;
 bottom: 0;
 left: 0;
 width: 100%;
 background-color: #fff;
 box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
 display: flex;
 align-items: center;
 justify-content: space-between;
 padding: 10px 20px;
 z-index: 1000;
}
.comparison-items {
 display: flex;
 gap: 10px;
}
.comparison-item {
 position: relative;
 border: 1px solid #ddd;
 padding: 5px;
 border-radius: 4px;
 width: 100px; /* Độ rộng cố định */
 text-align: center;
}
.comparison-item-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px dashed #ccc;
  border-radius: 4px;
  width: 100px;
  height: 90px; /* Chiều cao tương đương item thật */
  color: #ccc;
  font-size: 24px;
}
.comparison-img {
 width: 50px;
 height: 50px;
 object-fit: contain;
}
.comparison-name {
 font-size: 12px;
 white-space: nowrap;
 overflow: hidden;
 text-overflow: ellipsis;
 margin: 5px 0 0;
}
.remove-compare-item {
 position: absolute;
 top: -10px;
 right: -10px;
 background-color: #666;
 color: white;
 border: none;
 border-radius: 50%;
 width: 20px;
 height: 20px;
 cursor: pointer;
 display: flex;
 align-items: center;
 justify-content: center;
 font-size: 14px;
 line-height: 20px;
}
.compare-button {
 padding: 10px 20px;
 font-size: 16px;
 font-weight: bold;
 color: white;
 background-color: #1663AB;
 border: none;
 border-radius: 5px;
 cursor: pointer;
 transition: background-color 0.3s;
}
.compare-button:hover {
 background-color: #03A2DC;
}
.compare-button:disabled {
 background-color: #ccc;
 cursor: not-allowed;
}
</style>