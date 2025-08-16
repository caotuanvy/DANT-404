<template>
  <div class="product-page" v-if="product">
    <nav class="breadcrumbs">
      <a href="/">Trang chủ</a>
      <span>/</span>
      <a href="/products">{{ product.danh_muc.ten_danh_muc }}</a>
      <span>/</span>
      <span class="current">{{ product.product_name }}</span>
    </nav>

    <div class="product-container">
      <div class="product-images">
        <div class="main-image-wrapper">
          <img v-if="selectedImage" :src="selectedImage.url" :alt="product.product_name" class="main-img" />
        </div>
        <div class="thumbnails">
          <img
            v-for="image in product.images"
            :key="image.id"
            :src="image.url"
            :alt="`Thumbnail ${image.id}`"
            class="thumb"
            :class="{ active: selectedImage && selectedImage.id === image.id }"
            @click="selectImage(image)"
          />
        </div>
      </div>

      <div class="product-info">
        <h1 class="product-name">{{ product.product_name }}</h1>
        
        <div class="meta-info">
          <div class="rating">
            <span class="stars">★★★★☆</span>
            <span class="rating-count">(249 đánh giá)</span>
          </div>
          <span class="featured-tag" v-if="product.noi_bat">Nổi bật</span>
        </div>

        <div class="price-box">
          <template v-if="product.khuyen_mai > 0 && selectedVariant">
            <span class="sale-price">{{ formatCurrency(finalPrice) }}</span>
            <span class="original-price">{{ formatCurrency(selectedVariant.gia) }}</span>
            <span class="discount-badge">-{{ product.khuyen_mai }}%</span>
          </template>
          <template v-else>
            <span class="sale-price">{{ formatCurrency(selectedVariant?.gia || 0) }}</span>
          </template>
        </div>

        <div class="variant-selection">
          <div class="option-group" v-if="uniqueSizes.length > 0">
            <label class="option-label">Kích thước:</label>
            <div class="option-buttons">
              <button 
                v-for="size in uniqueSizes" 
                :key="size" 
                class="option-btn"
                :class="{ active: selectedSize === size }"
                @click="selectSize(size)"
              >
                {{ size }}
              </button>
            </div>
          </div>
          
          <div class="option-group" v-if="uniqueColors.length > 0">
            <label class="option-label">Màu sắc:</label>
            <div class="option-buttons">
              <div 
                v-for="color in uniqueColors" 
                :key="color"
                class="color-swatch"
                :style="{ backgroundColor: convertColor(color) }"
                :class="{ active: selectedColor === color }"
                @click="selectColor(color)"
              ></div>
            </div>
          </div>
        </div>

        <div class="quantity-stock-section">
          <label class="option-label">Số lượng:</label>
          <div class="quantity-selector">
            <button class="quantity-btn" @click="decreaseQuantity">-</button>
            <span class="quantity-display">{{ quantity }}</span>
            <button class="quantity-btn" @click="increaseQuantity">+</button>
          </div>
          <div class="stock-status" v-if="selectedVariant">
            Còn lại: {{ selectedVariant.so_luong_ton_kho }} sản phẩm
          </div>
        </div>

        <div class="action-buttons">
          <button class="add-cart-btn" @click="addToCart" :disabled="!canAddToCart">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
            Thêm vào giỏ hàng
          </button>
          <button class="icon-btn wishlist-btn">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
          </button>
          <button class="icon-btn share-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg>
          </button>
        </div>

        <div class="commitments">
          </div>
      </div>
    </div>

    <div class="product-description-section">
        <h3>Mô tả sản phẩm</h3>
        <div 
            class="description-content" 
            :class="{ 'expanded': showFullDescription }"
            v-html="product.description">
        </div>
        <button 
            v-if="product.description && product.description.length > 500"
            @click="showFullDescription = !showFullDescription"
            class="read-more-btn">
            {{ showFullDescription ? 'Thu gọn' : 'Xem thêm' }}
        </button>
    </div>
  </div>
  <div v-else class="loading-state">
    <p>Đang tải thông tin sản phẩm...</p>
  </div>
</template>
<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router'; // Thêm useRouter
import axios from 'axios';

// --- STATE ---
const product = ref(null);
const selectedImage = ref(null);
const quantity = ref(1);
const route = useRoute();
const router = useRouter(); // Khởi tạo router
const selectedSize = ref(null);
const selectedColor = ref(null);
const showFullDescription = ref(false);
// --- COMPUTED PROPERTIES ---

const uniqueSizes = computed(() => {
  if (!product.value?.variants) return [];
  const sizes = product.value.variants.map(v => v.kich_thuoc).filter(Boolean);
  return [...new Set(sizes)];
});

const uniqueColors = computed(() => {
  if (!product.value?.variants) return [];
  const colors = product.value.variants.map(v => v.mau_sac).filter(Boolean);
  return [...new Set(colors)];
});

const selectedVariant = computed(() => {
  if (!product.value?.variants) {
    return null;
  }
  // TRƯỜNG HỢP 1: Sản phẩm chỉ có 1 biến thể (không có size/color để chọn)
  if (product.value.variants.length === 1) {
    return product.value.variants[0];
  }
  // TRƯỜG HỢP 2: Sản phẩm có nhiều biến thể, yêu cầu chọn đủ
  if (uniqueSizes.value.length > 0 && !selectedSize.value) {
    return null;
  }
  if (uniqueColors.value.length > 0 && !selectedColor.value) {
    return null;
  }
  return product.value.variants.find(v => {
    const sizeMatch = uniqueSizes.value.length === 0 || v.kich_thuoc === selectedSize.value;
    const colorMatch = uniqueColors.value.length === 0 || v.mau_sac === selectedColor.value;
    return sizeMatch && colorMatch;
  });
});

const finalPrice = computed(() => {
  if (!selectedVariant.value) return 0;
  const price = Number(selectedVariant.value.gia);
  const discountPercent = Number(product.value?.khuyen_mai);
  return discountPercent > 0 ? price * (1 - discountPercent / 100) : price;
});

const canAddToCart = computed(() => {
  return selectedVariant.value && selectedVariant.value.so_luong_ton_kho > 0;
});

// --- METHODS ---

const formatCurrency = (value) => {
  if (isNaN(value)) return 'N/A';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const selectImage = (image) => {
  selectedImage.value = image;
};

const selectSize = (size) => {
  selectedSize.value = size;
  const availableColors = product.value.variants
    .filter(v => v.kich_thuoc === size)
    .map(v => v.mau_sac);
  if ([...new Set(availableColors)].length === 1) {
    selectedColor.value = availableColors[0];
  }
};

const selectColor = (color) => {
  selectedColor.value = color;
  const availableSizes = product.value.variants
    .filter(v => v.mau_sac === color)
    .map(v => v.kich_thuoc);
  if ([...new Set(availableSizes)].length === 1) {
    selectedSize.value = availableSizes[0];
  }
};

const convertColor = (colorName) => {
  if (typeof colorName !== 'string') return '#ddd';
  switch (colorName.toLowerCase()) {
    case 'đen': return '#000000';
    case 'trắng': return '#ffffff';
    case 'xám': return '#cccccc';
    case 'vàng': return '#facc15';
    case 'xanh': return '#1e40af';
    case 'hồng': return '#f472b6';
    case 'đỏ': return '#dc2626';
    default: return '#e9ecef00';
  }
};

const increaseQuantity = () => {
  const maxStock = selectedVariant.value?.so_luong_ton_kho || 1;
  if (quantity.value < maxStock) {
    quantity.value++;
  }
};

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--;
  }
};

const addToCart = async () => {
    const token = localStorage.getItem('token');
    if (!token) {
        // Nếu chưa đăng nhập, chuyển hướng đến trang đăng nhập
        router.push('/login');
        return;
    }
    if (!selectedVariant.value) {
        alert('Vui lòng chọn đầy đủ thuộc tính của sản phẩm.');
        return;
    }
    if (quantity.value > selectedVariant.value.so_luong_ton_kho) {
        alert('Số lượng yêu cầu vượt quá số lượng tồn kho.');
        return;
    }
    try {
        const payload = {
          san_pham_bien_the_id: selectedVariant.value.bien_the_id,
          quantity: quantity.value,
        };
        const response = await axios.post('/cart/add', payload, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        });
        alert(response.data.message);
    } catch (error) {
        if (error.response && error.response.data && error.response.data.message) {
            alert(error.response.data.message);
        } else {
            alert('Có lỗi xảy ra, vui lòng thử lại sau.');
        }
        console.error("Lỗi khi thêm vào giỏ hàng:", error);
    }
};

// --- WATCHERS & LIFECYCLE ---

watch(selectedVariant, (newVariant) => {
  if (newVariant && quantity.value > newVariant.so_luong_ton_kho) {
    quantity.value = newVariant.so_luong_ton_kho > 0 ? 1 : 0;
  } else if (!newVariant || newVariant.so_luong_ton_kho === 0) {
    quantity.value = 0;
  } else if (quantity.value === 0 && newVariant?.so_luong_ton_kho > 0) {
    quantity.value = 1;
  }
});

onMounted(async () => {
  const productSlug = route.params.slug;
  try {
    const response = await axios.get(`/user/${productSlug}`);
    product.value = response.data;

    if (product.value.images?.length > 0) {
      selectedImage.value = product.value.images[0];
    }
    // Tự động chọn nếu có lựa chọn
    if (uniqueSizes.value.length > 0) {
      selectSize(uniqueSizes.value[0]);
    }
    if (uniqueColors.value.length > 0) {
      selectColor(uniqueColors.value[0]);
    }
  } catch (error) {
    console.error("Lỗi khi tải dữ liệu sản phẩm:", error);
    product.value = null;
  }
});
</script>
<style scoped>
/* === General & Layout === */

.product-page {
  font-family: "Lora", serif;
  padding: 2rem 1rem;
  max-width: 1200px;
  margin: auto;
  
}
.loading-state {
  text-align: center;
  padding: 5rem 1rem;
  font-size: 1.25rem;
  color: #6c757d;
}
.product-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3rem;
  background: #fff;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 25px rgba(0,0,0,0.05);
}

/* === Breadcrumbs === */
.breadcrumbs {
  margin-bottom: 1.5rem;
  color: #6c757d;
  font-size: 0.9rem;
}
.breadcrumbs a {
  color: #495057;
  text-decoration: none;
  transition: color 0.2s;
}
.breadcrumbs a:hover {
  color: #0d6efd;
}
.breadcrumbs span {
  margin: 0 0.5rem;
}
.breadcrumbs .current {
  color: #212529;
  font-weight: 500;
}

/* === Images Section === */
.product-images {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.main-image-wrapper {
  border-radius: 12px;
  overflow: hidden;

}
.main-img {
  width: 100%;
  height: auto;
  aspect-ratio: 1 / 1;
  object-fit: cover;
  display: block;
}
.thumbnails {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
  gap: 0.75rem;
}
.thumb {
  width: 100%;
  height: 70px;
  object-fit: cover;
  border-radius: 8px;
  border: 2px solid #e9ecef;
  cursor: pointer;
  transition: all 0.2s;
  opacity: 0.7;
}
.thumb:hover {
  opacity: 1;
  border-color: #adb5bd;
}
.thumb.active {
  border-color: #0d6efd;
  box-shadow: 0 0 0 2px #0d6efd;
  opacity: 1;
}

/* === Info Section === */
.product-info {
  display: flex;
  flex-direction: column;
}
.product-name {
  font-weight: 700;
  margin-bottom: 0.5rem;
  color: #212529;
  line-height: 1.2;
}
.meta-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}
.rating {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.stars { color: #ffc107; font-size: 1.1rem; }
.rating-count { color: #6c757d; font-size: 0.9rem; }
.featured-tag {
  background-color: #198754;
  color: white;
  padding: 0.2rem 0.6rem;
  font-size: 0.8rem;
  font-weight: 600;
  border-radius: 4px;
}
.price-box {
  display: flex;
  align-items: baseline;
  gap: 1rem;
  background-color: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
  margin: 0.5rem 0;
}
.sale-price {
  font-size: 2rem;
  color: #dc3545;
  font-weight: 700;
}
.original-price {
  text-decoration: line-through;
  color: #6c757d;
  font-size: 1.1rem;
}
.discount-badge {
  background: #0d6efd;
  color: white;
  padding: 0.25rem 0.5rem;
  font-size: 0.8rem;
  border-radius: 4px;
  font-weight: 600;
}
.vat-shipping-note {
  font-size: 0.85rem;
  color: #6c757d;
  margin-bottom: 1.5rem;
}
.option-group {
  margin-bottom: 1.5rem;
}
.option-label {
  font-weight: 600;
  margin-bottom: 0.75rem;
  display: block;
  color: #495057;
}
.option-buttons {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}
.option-btn {
  padding: 0.5rem 1rem;
  border: 1px solid #ced4da;
  border-radius: 6px;
  background-color: #fff;
  cursor: pointer;
  transition: all 0.2s;
  font-weight: 500;
}
.option-btn:hover {
  background-color: #f1f3f5;
  border-color: #adb5bd;
}
.option-btn.active {
  background-color: #e7f1ff;
  border-color: #0d6efd;
  color: #0d6efd;
  font-weight: 600;
}
.color-swatch {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 2px solid #e9ecef;
  cursor: pointer;
  transition: all 0.2s;
}
.color-swatch.active {
  border-color: #0d6efd;
  box-shadow: 0 0 0 1px #0d6efd;
}
.quantity-stock-section {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 2rem;
}
.quantity-selector {
  display: flex;
  align-items: center;
  border: 1px solid #ced4da;
  border-radius: 6px;
}
.quantity-btn {
  width: 40px;
  height: 40px;
  border: none;
  background: #f8f9fa;
  font-size: 1.5rem;
  cursor: pointer;
  color: #495057;
}
.quantity-btn:first-child { border-radius: 5px 0 0 5px; }
.quantity-btn:last-child { border-radius: 0 5px 5px 0; }
.quantity-display {
  width: 50px;
  text-align: center;
  font-size: 1.1rem;
  font-weight: 600;
}
.stock-status {
  font-size: 0.9rem;
  color: #198754;
  font-weight: 500;
}
.action-buttons {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 2rem;
}
.add-cart-btn {
  flex-grow: 1;
  padding: 0.85rem 1rem;
  background: #0d6efd;
  color: white;
  font-weight: 600;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}
.add-cart-btn:hover:not(:disabled) {
  background: #0b5ed7;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(13,110,253,0.2);
}
.add-cart-btn:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
  opacity: 0.7;
}
.icon-btn {
  width: 50px;
  height: 50px;
  border: 1px solid #ced4da;
  border-radius: 8px;
  background: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #495057;
  transition: all 0.2s;
}
.icon-btn:hover {
  background-color: #f1f3f5;
  color: #0d6efd;
}

/* === Commitments & Description === */
.commitments {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  border-top: 1px solid #e9ecef;
  padding-top: 1.5rem;
  margin-top: auto; /* Pushes to the bottom */
}
.commitment-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.9rem;
  color: #495057;
}
.commitment-item svg {
  color: #0d6efd;
  flex-shrink: 0;
}
.product-description-section {
  max-width: 1400px;
  margin: 2rem auto 0 auto;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 25px rgba(0,0,0,0.05);
  padding: 2rem;
}
.product-description-section h3 {
  font-size: 1.75rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e9ecef;
}
.description-content {
  color: #495057;
  line-height: 1.7;
  font-size: 16px;
  width: 90%;
  margin: auto;
}
.description-content p img {
  box-shadow: 0 !important;
}
.description-content :deep(*) {
  margin-bottom: 1em;
}
.description-content {
    max-height: 250px; /* Chiều cao ban đầu của mô tả bị cắt */
    overflow: hidden;
    position: relative;
    transition: max-height 0.5s ease-in-out;
}

.description-content.expanded {
    max-height: none; /* Chiều cao không giới hạn khi được mở rộng */
}

/* Thêm một gradient mờ dần để tạo hiệu ứng đẹp mắt */
.description-content:not(.expanded)::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 100px; /* Độ cao của hiệu ứng mờ */
    background: linear-gradient(to top, white, rgba(255, 255, 255, 0));
    pointer-events: none; /* Đảm bảo gradient không chặn các tương tác */
}

.read-more-btn {
    display: block;
    width: 150px;
    margin: 1.5rem auto 0;
    padding: 0.75rem 1rem;
    font-weight: 600;
    background-color: #f1f3f5;
    color: #495057;
    border: 1px solid #ced4da;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.read-more-btn:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
}
/* === Responsive adjustments === */
@media (max-width: 992px) {
  .product-container {
    grid-template-columns: 1fr;
  }
}
@media (max-width: 576px) {
  .product-page {
    padding: 1rem;
  }
  .product-container, .product_description-section {
    padding: 1.5rem;
  }
  .product-name {
    font-size: 1.75rem;
  }
  .commitments {
    grid-template-columns: 1fr;
  }
}
</style>