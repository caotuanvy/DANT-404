<template>
  <section class="promotional-products-section">
    <div class="product-grid-container">
      <router-link
        v-for="sp in products.slice(0, 8)"
        :key="sp.san_pham_id"
        :to="`/san-pham/${sp.slug}`"
        class="product-card-link"
      >
        <div class="product-card">
          <div class="card-image-box">
            <div v-if="getValidDiscountPercentage(sp.khuyen_mai) > 0" class="discount-badge">
              -{{ getValidDiscountPercentage(sp.khuyen_mai) }}%
            </div>
            <img :src="getImageUrl(sp.hinh_anh)" :alt="sp.ten_san_pham" class="product-image" />
          </div>
          <div class="card-details">
            <h3 class="product-name"><strong>{{ sp.ten_san_pham }}</strong></h3>
            <p class="product-description" v-html="sp.Mo_ta_seo"></p>
            <div class="price-info-group">
              <p class="current-price">
                <strong>
                {{ formatCurrency(calculateDisplayPrice(sp.gia, sp.khuyen_mai)) }} ₫
                </strong>
              </p>
              <p v-if="getValidDiscountPercentage(sp.khuyen_mai) > 0" class="original-price">
                {{ formatCurrency(sp.gia) }} ₫
              </p>
            </div>
            <div class="card-actions">
              <button class="quick-view-btn-icon" @click.prevent="showQuickViewModal(sp)">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.577 3.01 9.964 7.173a1.012 1.012 0 0 1 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.577-3.01-9.964-7.173Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
              </button>
              <button class="add-to-cart-grid-btn" @click.prevent="addToCart(sp)">Thêm vào giỏ</button>
            </div>
          </div>
        </div>
      </router-link>
    </div>
  </section>

  <teleport to="body">
    <div v-if="showModal" class="product-modal-overlay">
      <div class="product-modal">
        <button class="close-btn" @click="closeModal">&times;</button>
        <div v-if="selectedProduct" class="modal-body">
          <div class="modal-left">
            <img :src="getImageUrl(selectedProduct.hinh_anh)" :alt="selectedProduct.ten_san_pham" class="modal-main-image" />
          </div>
          <div class="modal-right">
            <h3 class="modal-title">{{ selectedProduct.ten_san_pham }}</h3>
            <div class="price-row">
              <p class="price-now">
                {{ formatCurrency(calculateDisplayPrice(selectedProduct.gia, selectedProduct.khuyen_mai)) }} ₫
              </p>
              <p v-if="getValidDiscountPercentage(selectedProduct.khuyen_mai) > 0" class="price-old">
                {{ formatCurrency(selectedProduct.gia) }} ₫
              </p>
              <span v-if="getValidDiscountPercentage(selectedProduct.khuyen_mai) > 0" class="badge-off">
                -{{ getValidDiscountPercentage(selectedProduct.khuyen_mai) }}%
              </span>
            </div>
            <p class="short-desc" v-html="selectedProduct.Mo_ta_seo"></p>
            <div class="qty-row">
              <label>Số lượng:</label>
              <div class="qty">
                <button @click="decrementQty">-</button>
                <input type="text" v-model="quickViewQty" readonly />
                <button @click="incrementQty">+</button>
              </div>
            </div>
            <div class="modal-actions">
              <button class="add-to-cart-grid-btn" @click.prevent="addToCartFromModal">Thêm vào giỏ</button>
              <router-link :to="`/san-pham/${selectedProduct.slug}`" class="view-detail-btn" @click="closeModal">Xem chi tiết</router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const products = ref([])
const showModal = ref(false)
const selectedProduct = ref(null)
const quickViewQty = ref(1)

const getImageUrl = (path) => {
  return path ? `https://api.sieuthi404.io.vn/storage/${path}` : '/images/default-grape.png'
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
    Swal.fire({
      icon: 'warning',
      title: 'Bạn chưa đăng nhập',
      text: 'Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng!',
      confirmButtonColor: '#03A2DC'
    });
    return;
  }

  try {
    const response = await axios.post('https://api.sieuthi404.io.vn/api/cart/add', 
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
    Swal.fire({
      icon: 'success',
      title: 'Thành công',
      text: `Đã thêm "${product.ten_san_pham}" vào giỏ hàng!`,
      confirmButtonColor: '#03A2DC'
    });
    console.log(response.data);
  } catch (err) {
    if (err.response && err.response.status === 401) {
      Swal.fire({
        icon: 'error',
        title: 'Phiên đăng nhập hết hạn',
        text: 'Vui lòng đăng nhập lại.',
        confirmButtonColor: '#03A2DC'
      });
    } else {
      console.error('Lỗi khi thêm vào giỏ hàng:', err);
      Swal.fire({
        icon: 'warning',
        title: 'Hết hàng',
        text: 'Đã hết hàng xin hãy chọn sản phẩm khác.',
        confirmButtonColor: '#03A2DC'
      });
    }
  }
};

const showQuickViewModal = (product) => {
  selectedProduct.value = product;
  quickViewQty.value = 1;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedProduct.value = null;
};

const incrementQty = () => {
  quickViewQty.value++;
};

const decrementQty = () => {
  if (quickViewQty.value > 1) {
    quickViewQty.value--;
  }
};

const addToCartFromModal = () => {
  if (selectedProduct.value) {
    addToCart({
      ...selectedProduct.value,
      quantity: quickViewQty.value
    });
    closeModal();
  }
};

onMounted(async () => {
  const res = await axios.get('https://api.sieuthi404.io.vn/api/admin/products-featured')
  products.value = res.data
})
</script>

<style scoped>
/* Thêm font-family vào style tổng thể để đồng nhất */

body {
  font-family: "Lora", serif;
}

.promotional-products-section {
  font-family: "Lora", serif;
  padding: 60px 20px;
  box-sizing: border-box;
}

/* Header section - if you have one */
.header-main {
  max-width: 1280px;
  margin: 0 auto 30px;
  background-color: #ffffff;
  border-radius: 16px;
  padding: 30px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.section-title {
  font-size: 32px;
  
  color: #1a1a1a;
  margin: 0;
  text-align: center;
}

.tabs-container {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-top: 25px;
  border-bottom: 2px solid #e0e0e0;
  padding-bottom: 10px;
}
.tab-button {
  background-color: transparent;
  border: none;
  font-size: 18px;
  font-weight: bolder;
  color: #888;
  padding: 10px 15px;
  cursor: pointer;
  position: relative;
  transition: all 0.3s ease-in-out;
}
.tab-button:hover {
  color: #007bff;
}
.tab-button.active {
  color: #007bff;
}
.tab-button.active::after {
  content: '';
  position: absolute;
  bottom: -12px;
  left: 0;
  width: 100%;
  height: 4px;
  background-color: #007bff;
  border-radius: 2px;
}

.product-grid-container {
  max-width: 1280px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 30px;
}

.product-card-link {
  text-decoration: none;
  color: inherit;
  display: block;
}

.product-card {
  background-color: #ffffff;
  border-radius: 16px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border: 1px solid #f0f0f0;
}
.product-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
}

.card-image-box {
  position: relative;
  width: 100%;
  height: 250px;
  overflow: hidden;
}
.product-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
  transition: transform 0.4s ease-in-out;
}
.product-card:hover .product-image {
  transform: scale(1.1);
}

.discount-badge {
  position: absolute;
  top: 15px;
  left: 15px;
  background-color: #007bff;
  color: #ffffff;
  
  font-size: 14px;
  padding: 5px 12px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.card-details {
  padding: 20px;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.product-name {
  font-size: 18px;
  
  color: #1a1a1a;
  min-height: 40px;
  margin: 0 0 8px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-description {
  font-size: 14px;
  color: #666;
  line-height: 1.5;
  min-height: 4.5em;
  margin: 0 0 15px;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  font-weight: normal; /* Đặt lại trọng lượng font chữ */
}

.price-info-group {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: auto;
}
.current-price {
  font-size: 24px;
  
  color: #ff4500;
}
.original-price {
  font-size: 16px;
  color: #999;
  text-decoration: line-through;
  font-weight: normal; /* Đặt lại trọng lượng font chữ */
}

/* Các thành phần bổ sung */
.countdown-group,
.expired-tag,
.sold-progress-bar,
.sold-count,
.footer-actions,
.view-all-button,
.comparison-bar,
.comparison-items,
.comparison-item,
.comparison-item-placeholder,
.comparison-img,
.comparison-name,
.remove-compare-item,
.compare-button,
.compare-button:disabled,
.loading-text,
.variants-selection,
.variants-title,
.variants-grid,
.variant-btn,
.variant-btn.active,
.variant-btn.out-of-stock {
  /* Giữ nguyên style đã có */
  font-weight: normal;
}
.countdown-timer,
.compare-checkbox:checked + .custom-checkbox::after {
   /* Các phần cần in đậm */
}

.card-actions {
  padding: 10px 20px 20px;
  border-top: 1px solid #f0f0f0;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 10px;
}

.quick-view-btn-icon {
  background-color: transparent;
  border: 1px solid #ccc;
  color: #555;
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  cursor: pointer;
  font-size: 16px;
  transition: all 0.3s ease;
  font-weight: normal; /* Đặt lại trọng lượng font chữ */
}

.quick-view-btn-icon:hover {
  background-color: #f0f0f0;
  border-color: #888;
  transform: scale(1.1);
}

.add-to-cart-grid-btn {
  padding: 10px 16px;
  background-color: #007aff;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: 0.2s;
   font-weight: bolder;
}

.add-to-cart-grid-btn:hover {
  background-color: #005ecb;
}

/* ===== Modal Quick View ===== */
.product-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
  animation: fadeIn .3s ease-out;
}
.product-modal {
  position: relative;
  background: #fff;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
  width: 90%;
  max-width: 900px;
  min-height: 500px;
  display: flex;
  flex-direction: column;
  max-height: 90vh;
  overflow-y: auto;
  z-index: 1001;
}
.close-btn {
  position: absolute;
  top: 8px;
  right: 10px;
  background: transparent;
  border: none;
  font-size: 24px;
  cursor: pointer;
  font-weight: normal; /* Đặt lại trọng lượng font */
}
.modal-body {
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 24px;
}
.modal-left {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.modal-main-image {
  width: 100%;
  height: auto;
  object-fit: cover;
  border-radius: 8px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}
.modal-right {
  flex: 1;
}
.modal-title {
  margin: 0 0 6px;
  font-size: 24px;
   /* Chữ in đậm */
}
.price-row {
  display: flex;
  align-items: baseline;
  gap: 10px;
  margin: 8px 0 12px;
}
.price-now {
  color: #e63946;
  font-size: 24px;
   /* Chữ in đậm */
}
.price-old {
  color: #999;
  text-decoration: line-through;
  font-weight: normal; /* Chữ bình thường */
}
.badge-off {
  background: #ffe5e5;
  color: #e63946;
  border-radius: 6px;
  padding: 2px 8px;
   /* Chữ in đậm */
  font-size: 12px;
}
.short-desc {
  color: #444;
  line-height: 1.6;
  margin-bottom: 12px;
  font-weight: normal; /* Chữ bình thường */
}
.qty-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 12px 0;
}
.qty-row label {
   font-weight: bolder; /* Chữ in đậm */
  color: #333;
}
.qty {
  display: inline-flex;
  border: 1px solid #e5e5e5;
  border-radius: 8px;
  overflow: hidden;
}
.qty button {
  width: 36px;
  height: 36px;
  border: none;
  background: #f6f6f6;
  cursor: pointer;
  font-size: 18px;
  font-weight: normal; /* Đặt lại trọng lượng font */
}
.qty input {
  width: 60px;
  height: 36px;
  border: none;
  text-align: center;
  outline: none;
  font-weight: normal; /* Đặt lại trọng lượng font */
}
.modal-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 8px;
}
.view-detail-btn {
  padding: 10px 16px;
  background-color: #f7f7f7;
  color: #222222;
  border: solid 1px #a7a7a7;
  border-radius: 8px;
  cursor: pointer;
  transition: 0.2s;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
   font-weight: bolder;
}
.view-detail-btn:hover {
  background-color: #9c9a9abd;
}
.add-to-cart-grid-btn {
  padding: 10px 16px;
  background-color: #007aff;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: 0.2s;
   font-weight: bolder;
}
.add-to-cart-grid-btn:hover {
  background-color: #005ecb;
}

@media (max-width: 900px) {
  .modal-body {
    grid-template-columns: 1fr;
  }
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(.98)
  }
  to {
    opacity: 1;
    transform: scale(1)
  }
}
@media (max-width: 768px) {
  .product-grid-container {
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 15px;
  }
  .card-image-box {
    height: 180px;
  }
  .card-details {
    padding: 15px;
  }
  .product-name {
    font-size: 16px;
    min-height: 36px;
  }
  .product-description {
    display: none;
  }
  .current-price {
    font-size: 20px;
  }
  .original-price {
    font-size: 14px;
  }
  .card-actions {
    padding: 10px 15px 15px;
  }
}
</style>