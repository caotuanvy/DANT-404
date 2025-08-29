<template>
  <section class="main-best-sell">
  <section class="top-products">
    <div class="header-banner">
      <h2 class="title">Sản phẩm bán chạy 🔥</h2>
    </div>

    <div class="product-grid">
      <router-link
        v-for="sp in products"
        :key="sp.san_pham_id"
        :to="`/san-pham/${sp.slug || sp.san_pham_id}`"
        class="product-card-link"
      >
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

            <div class="price-and-icon">
              <p class="current-price">{{ formatCurrency(calculateDisplayPrice(sp.gia, sp.khuyen_mai)) }}đ</p>
            </div>

            <div class="main-prsell">
              <p class="promotion-text">
                Giảm trực tiếp <b class="price-betsell">{{ formatCurrency(calculateMaxDiscountAmount(sp.gia, sp.khuyen_mai)) }}</b> VNĐ khi mua ngay mặt hàng này vào hôm nay
              </p>

              <div class="sold-progress">
                <div
                  class="sold-segment sold-segment-sold"
                  :style="{ width: Math.min(sp.phan_tram_da_ban, 100) + '%' }"
                ></div>
                <div class="sold-overlay-text">
                  <span class="fire-icon">🔥</span>
                  <span class="sold-text">Đã bán {{ sp.so_luong_ban }}</span>
                </div>
              </div>

              <p class="progress-summary-text" v-if="sp.tong_ton_kho !== undefined">
                (Sản phẩm còn lại: {{ sp.tong_ton_kho }} / Tổng: {{ sp.tong_so_luong }})
              </p>
            </div>
          </div>
        </div>
      </router-link>
    </div>

    <div class="button-container">
  <router-link to="/san-pham-ban-chay" class="view-more-button">
    Xem thêm
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
  return path ? `https://api.sieuthi404.io.vn/storage/${path}` : '/images/default-grape.png'
}

const getValidDiscountPercentage = (promo) => {
  let percentage = 0
  if (promo === null || promo === undefined) return 0
  if (typeof promo === 'number') percentage = promo
  else if (typeof promo === 'string') {
    const match = promo.match(/(\d+)%/)
    percentage = match && match[1] ? parseInt(match[1]) : parseInt(promo) || 0
  }
  return Math.max(0, Math.min(100, percentage))
}

const calculateDiscountedPrice = (originalPrice, promo) => {
  const percentage = getValidDiscountPercentage(promo)
  const price = parseFloat(originalPrice)
  return isNaN(price) || percentage === 0 ? price : Math.round(price * (1 - percentage / 100))
}

const calculateMaxDiscountAmount = (originalPrice, promo) => {
  const percentage = getValidDiscountPercentage(promo)
  const price = parseFloat(originalPrice)
  const discountAmount = isNaN(price) ? 0 : price * (percentage / 100)
  return Math.min(Math.round(discountAmount), 600000)
}

const calculateDisplayPrice = (originalPrice, promo) => {
  return getValidDiscountPercentage(promo) > 0
    ? calculateDiscountedPrice(originalPrice, promo)
    : parseFloat(originalPrice)
}

const formatCurrency = (amount) => {
  if (amount == null) return ''
  return Math.round(parseFloat(amount)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}

onMounted(async () => {
  try {
    const res = await axios.get('https://api.sieuthi404.io.vn/api/admin/products-sell-top')
    products.value = res.data.map(product => {
      const ton_kho = parseInt(product.so_luong_ton_kho) || 0
      const da_ban = parseInt(product.so_luong_ban) || 0
      const tong_so = ton_kho + da_ban
      const phan_tram = tong_so > 0 ? (da_ban / tong_so) * 100 : 0
      return {
        ...product,
        gia: parseFloat(product.gia) || 0,
        so_luong_ban: da_ban,
        tong_ton_kho: ton_kho,
        tong_so_luong: tong_so,
        phan_tram_da_ban: Math.round(phan_tram),
      }
    })
  } catch (err) {
    console.error('Lỗi khi tải sản phẩm bán chạy:', err)
  }
})
</script>

<style scoped>

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
  height: 460px;
  padding: 15px;
  position: relative;
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
.price-betsell{
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
.main-prsell{
  position: absolute;
  top: 330px;
  left: 5px;
  margin-right: 5px;
}
.promotion-text {
  font-size: 12px;
  color: rgb(30, 29, 29);
  background-color: rgba(0, 0, 0, 0.108);
  padding: 8px;
  border-radius: 6px;
  line-height: 1.4;
  margin-bottom: 10px;
}

.sold-progress {
  background-color: #e6e6e6;
  border-radius: 20px;
  height: 22px;
  width: 100%;
  margin-left: 10%;
  margin-right: 10%;
  max-width: 220px;
  overflow: hidden;
  position: relative;
  margin-top: 10px;
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
  transform: translateY(-50%);
  display: flex;
  align-items: center;
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

</style>
