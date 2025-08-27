<template>
  <transition name="modal-fade">
    <div v-if="isVisible" class="modal-overlay" @click.self="close">
      <div class="modal-container">
        <button class="modal-close" @click="close">&times;</button>

        <!-- Loader -->
        <div v-if="isLoading" class="modal-loader">Đang tải...</div>

        <!-- Nội dung sản phẩm -->
        <div v-else-if="product" class="modal-content">
          <div class="product-header">
            <h2 class="product-title">{{ product.ten_san_pham }}</h2>
          </div>

          <div class="product-body">
            <div class="product-images">
              <img
                v-if="product.hinh_anh_san_pham?.length > 0"
                :src="getImageUrl(product.hinh_anh_san_pham[0].duongdan)"
                :alt="product.ten_san_pham"
                class="main-image"
              />

              <div class="thumbnail-list">
                <img
                  v-for="(image, index) in product.hinh_anh_san_pham"
                  :key="index"
                  :src="getImageUrl(image.duongdan)"
                  :alt="product.ten_san_pham"
                  class="thumbnail-image"
                />
              </div>
            </div>

            <div class="product-details">
              <p class="product-price">
                Giá: {{ formatCurrency(product.gia) }}₫
              </p>
              <p class="product-description" v-html="product.Mo_ta_seo"></p>
            </div>
          </div>
        </div>

        <!-- Không có dữ liệu -->
        <div v-else class="modal-empty">
          Không tìm thấy thông tin sản phẩm.
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { defineProps, defineEmits } from "vue";

const props = defineProps({
  isVisible: Boolean,
  product: Object,
  isLoading: Boolean,
});

const emit = defineEmits(["close"]);

const close = () => {
  emit("close");
};

const getImageUrl = (path) => {
  return path ? `http://localhost:8000/storage/${path}` : "/images/default-grape.png";
};

const formatCurrency = (amount) => {
  if (amount == null) return "";
  return Math.round(parseFloat(amount))
    .toString()
    .replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};
</script>

<style scoped>
/* Overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

/* Container */
.modal-container {
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  width: 800px;
  max-width: 95%;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  position: relative;
  animation: fadeIn 0.3s ease-in-out;
}

/* Close button */
.modal-close {
  position: absolute;
  top: 10px;
  right: 15px;
  background: transparent;
  border: none;
  font-size: 26px;
  cursor: pointer;
  color: #333;
  transition: color 0.3s ease;
}

.modal-close:hover {
  color: #ff4500;
}

/* Product images */
.product-images {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.main-image {
  width: 300px;
  height: 300px;
  object-fit: contain;
  margin-bottom: 15px;
}

.thumbnail-list {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.thumbnail-image {
  width: 70px;
  height: 70px;
  object-fit: contain;
  border: 2px solid #eee;
  border-radius: 8px;
  cursor: pointer;
  transition: border-color 0.3s ease;
}

.thumbnail-image:hover {
  border-color: #007bff;
}

/* Product details */
.product-details {
  margin-top: 15px;
}

.product-price {
  font-size: 20px;
  color: #ff4500;
  font-weight: bold;
  margin-bottom: 10px;
}

.product-description {
  font-size: 14px;
  color: #555;
  line-height: 1.6;
}

/* Animation */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
