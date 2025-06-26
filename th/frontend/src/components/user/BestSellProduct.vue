<template>
  <section class="top-products">
    <div class="header-banner">
      <h2 class="title">Sản phẩm bán chạy</h2>
    </div>

    <div class="product-grid">
      <div v-for="sp in products" :key="sp.san_pham_id" class="product-card">
        <div v-if="getValidDiscountPercentage(sp.khuyen_mai) > 0" class="discount-tag" :data-discount="getValidDiscountPercentage(sp.khuyen_mai) + '%'">
          Giảm {{ getValidDiscountPercentage(sp.khuyen_mai) }}%
        </div>
        <img :src="getImageUrl(sp.hinh_anh)" :alt="sp.ten_san_pham" class="product-img" />
        <div class="product-info">
          <h3 class="product-name">{{ sp.ten_san_pham }}</h3>

          <p v-if="getValidDiscountPercentage(sp.khuyen_mai) > 0" class="original-price">{{ formatCurrency(sp.gia) }}đ</p>

          <div class="price-and-icon">
            <p class="current-price">{{ formatCurrency(calculateDisplayPrice(sp.gia, sp.khuyen_mai)) }}đ</p>
            <div class="icon-group">
              </div>
          </div>
          <p class="promotion-text">Giảm trực tiếp {{ formatCurrency(calculateMaxDiscountAmount(sp.gia, sp.khuyen_mai)) }} VNĐ khi mua ngay mặt hàng này vào hôm nay</p>

          <div class="sold-progress">
            <div
              class="sold-segment sold-segment-sold"
              :style="{ width: Math.min(sp.phan_tram_da_ban, 100) + '%' }"
            >
              </div>
            <div class="sold-overlay-text">
              <span class="fire-icon">🔥</span>
              <span class="sold-text">Đã bán {{ sp.so_luong_ban }}</span>
            </div>
          </div>
          <p class="progress-summary-text" v-if="sp.tong_ton_kho !== undefined && sp.tong_ton_kho !== null">
            (Tồn kho: {{ sp.tong_ton_kho }} / Tổng cộng: {{ sp.tong_so_luong }})
          </p>
          <p class="progress-summary-text" v-else>
            (Tồn kho: Đang cập nhật)
          </p>

        </div>
      </div>
    </div>
    <div class="button-container">
      <button class="view-more-button">Xem thêm</button>
    </div>
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
  let percentage = 0;

  if (promo === null || promo === undefined) {
    percentage = 0;
  } else if (typeof promo === 'number') {
    percentage = promo;
  } else if (typeof promo === 'string') {
    const match = promo.match(/(\d+)%/);
    if (match && match[1]) {
      percentage = parseInt(match[1]);
    } else {
      const num = parseInt(promo);
      if (!isNaN(num)) {
        percentage = num;
      }
    }
  }
  return Math.max(0, Math.min(100, percentage));
}

const calculateDiscountedPrice = (originalPrice, promo) => {
  const percentage = getValidDiscountPercentage(promo);
  const price = parseFloat(originalPrice);
  if (isNaN(price) || percentage === 0) {
    return price;
  }
  const discountedPrice = price * (1 - percentage / 100);
  return Math.round(discountedPrice);
}

const calculateMaxDiscountAmount = (originalPrice, promo) => {
  const percentage = getValidDiscountPercentage(promo);
  const price = parseFloat(originalPrice);
  if (isNaN(price) || percentage === 0) {
    return 0;
  }

  const discountAmount = price * (percentage / 100);
  const cap = 600000;
  return Math.min(Math.round(discountAmount), cap);
}

const calculateDisplayPrice = (originalPrice, promo) => {
  const percentage = getValidDiscountPercentage(promo);
  if (percentage > 0) {
    return calculateDiscountedPrice(originalPrice, promo);
  }
  return parseFloat(originalPrice);
}

const formatCurrency = (amount) => {
  if (amount === undefined || amount === null) return '';
  const num = Math.round(parseFloat(amount));
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

onMounted(async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/admin/products-sell-top')
    products.value = res.data.map(product => {
      // Lấy trực tiếp so_luong_ton_kho từ dữ liệu API đã được backend tính tổng
      const so_luong_ton_kho_parsed = parseInt(product.so_luong_ton_kho) || 0;

      const so_luong_ban_parsed = parseInt(product.so_luong_ban) || 0;

      // tong_so_luong_theo_doi là tổng số sản phẩm đã tồn tại (tồn kho hiện tại + đã bán)
      const tong_so_luong_theo_doi = so_luong_ton_kho_parsed + so_luong_ban_parsed;

      // Chỉ tính phần trăm đã bán
      const phan_tram_da_ban = tong_so_luong_theo_doi > 0 ? (so_luong_ban_parsed / tong_so_luong_theo_doi) * 100 : 0;

      return {
        ...product,
        gia: parseFloat(product.gia) || 0,
        khuyen_mai: product.khuyen_mai,
        so_luong_ban: so_luong_ban_parsed,
        tong_ton_kho: so_luong_ton_kho_parsed, // Sử dụng giá trị đã tổng hợp từ backend
        tong_so_luong: tong_so_luong_theo_doi,
        phan_tram_da_ban: Math.round(phan_tram_da_ban),
      }
    })
    console.log('Top sản phẩm bán chạy (đã xử lý):', products.value)
  } catch (error) {
    console.error('Lỗi khi tải sản phẩm bán chạy:', error)
  }
})
</script>

<style scoped>
.top-products {
  padding-bottom: 40px;
  background: #f0f2f5;
  font-family: 'Arial', sans-serif;
  color: #333;
}

.header-banner {
  background-color: #007bff;
  padding: 20px;
  text-align: center;
  margin-bottom: 30px;
  border-bottom-left-radius: 15px;
  border-bottom-right-radius: 15px;
}

.title {
  color: white;
  font-size: 32px;
  margin: 0;
  font-weight: bold;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 25px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 15px;
}

.product-card {
  background: white;
  padding: 15px;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  text-align: left;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  align-items: center;
  border: 1px solid #007bff;
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.discount-tag {
  position: absolute;
  top: 0;
  right: 0;
  background-color: #dc3545;
  color: white;
  padding: 5px 10px;
  font-size: 12px;
  font-weight: bold;
  z-index: 10;
  clip-path: polygon(100% 0, 0 0, 100% 100%);
  padding-top: 20px;
  padding-right: 20px;
  transform: translate(30%, -30%) rotate(45deg);
  transform-origin: top right;
  width: 80px;
  height: 80px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.discount-tag::after {
  content: attr(data-discount);
  position: absolute;
  bottom: 5px;
  left: 50%;
  transform: translateX(-50%);
  white-space: nowrap;
  font-size: 14px;
}

.discount-tag {
  position: absolute;
  top: -10px;
  right: -10px;
  background-color: #dc3545;
  color: white;
  padding: 8px 12px;
  font-size: 14px;
  font-weight: bold;
  z-index: 10;
  clip-path: polygon(100% 0, 0% 100%, 100% 100%);
  padding-top: 20px;
  padding-right: 20px;
  text-align: center;
}

.discount-tag::after {
  content: attr(data-discount);
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) rotate(45deg);
  white-space: nowrap;
  font-size: 14px;
  width: 100%;
}

.product-img {
  width: 100%;
  height: 180px;
  object-fit: contain;
  border-radius: 8px;
  margin-bottom: 15px;
  background-color: #f8f9fa;
}

.product-info {
  width: 100%;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.product-name {
  font-weight: 700;
  font-size: 17px;
  margin-bottom: 8px;
  color: #333;
  min-height: 40px;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.original-price {
  text-decoration: line-through;
  color: #888;
  font-size: 15px;
  margin-bottom: 5px;
}

.price-and-icon {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
  flex-wrap: wrap;
}

.current-price {
  font-size: 24px;
  font-weight: bold;
  color: #dc3545;
  margin-right: 10px;
}

.icon-group {
  display: flex;
  gap: 8px;
}

.action-icon {
  width: 24px;
  height: 24px;
  cursor: pointer;
  padding: 3px;
  border-radius: 50%;
  transition: background-color 0.2s;
}

.action-icon:hover {
  background-color: #e9ecef;
}

.promotion-text {
  font-size: 13px;
  color: #c0392b;
  background-color: #fde6e6;
  padding: 8px;
  border-radius: 6px;
  margin-top: auto;
  margin-bottom: 15px;
  line-height: 1.4;
}

.sold-progress {
  background-color: #e6e6e6; /* Màu xám cho phần tồn kho/nền của thanh */
  border-radius: 20px; /* Bo tròn toàn bộ thanh */
  height: 26px;
  width: 100%;
  max-width: 220px;
  overflow: hidden; /* Giữ hidden để thanh vẫn gọn gàng, text là overlay */
  position: relative; /* Quan trọng để .sold-overlay-text định vị tương đối với nó */
  display: flex; /* Giữ flex để căn chỉnh .sold-segment-sold */
  align-items: center;
  margin-top: 10px;
  box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
}

.sold-segment {
  height: 100%;
  transition: width 0.3s ease;
  /* Các thuộc tính flex, padding, text-related đã được chuyển sang .sold-overlay-text */
}

.sold-segment-sold {
  background: linear-gradient(to right, #008CFF, #2EC8FF); /* Màu xanh cho phần đã bán */
  border-top-left-radius: 20px;
  border-bottom-left-radius: 20px;
  /* Nếu width là 100%, nó sẽ bo tròn bởi .sold-progress */
}

.sold-overlay-text {
  position: absolute; /* Định vị tuyệt đối trong .sold-progress */
  top: 50%; /* Căn giữa theo chiều dọc */
  left: 0; /* Bắt đầu từ bên trái của sold-progress */
  transform: translateY(-50%); /* Dịch lên 50% chiều cao của chính nó để căn giữa hoàn hảo */
  display: flex;
  align-items: center;
  padding: 0 10px; /* Padding để text không dính sát lề trái */
  font-size: 14px;
  font-weight: 600;
  color: #333; /* Đổi màu chữ sang màu đậm hơn để dễ đọc trên nền xám khi thanh xanh nhỏ */
  white-space: nowrap; /* Ngăn text xuống dòng */
  z-index: 1; /* Đảm bảo nó nằm trên thanh màu xanh và xám */
}

.fire-icon {
  font-size: 18px;
  line-height: 1;
  margin-right: 6px;
  filter: drop-shadow(0 0 3px rgba(255, 165, 0, 0.8));
  flex-shrink: 0; /* Ngăn icon bị co lại */
}

.sold-text {
  /* Không cần các thuộc tính co giãn vì nó không phải là flex item co giãn trong ngữ cảnh này */
}

.button-container {
  text-align: center;
  margin-top: 40px;
}

.view-more-button {
  background-color: #007bff;
  color: white;
  padding: 12px 35px;
  border: none;
  border-radius: 8px;
  font-size: 17px;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.2s ease;
  font-weight: bold;
}

.view-more-button:hover {
  background-color: #0056b3;
  transform: translateY(-2px);
}

.progress-summary-text {
  font-size: 13px;
  color: #666;
  text-align: center;
  margin-top: 5px;
  font-style: italic;
}
</style>