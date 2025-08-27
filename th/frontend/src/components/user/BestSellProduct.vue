<template>
  <section class="promotional-products-section">
    <div class="header-main">
      <h2 class="section-title">Sản Phẩm Khuyến Mãi</h2>
      <div class="tabs-container">
        <button
          class="tab-button"
          :class="{ active: activeTab === 'ongoing' }"
          @click="filterProducts('ongoing')"
        >
          Đang diễn ra
        </button>
        <button
          class="tab-button"
          :class="{ active: activeTab === 'upcoming' }"
          @click="filterProducts('upcoming')"
        >
          Sắp diễn ra
        </button>
        <button
          class="tab-button"
          :class="{ active: activeTab === 'expired' }"
          @click="filterProducts('expired')"
        >
          Đã kết thúc
        </button>
      </div>
    </div>

    <div class="product-grid-container">
      <router-link
        v-for="sp in filteredProducts"
        :key="sp.san_pham_id"
        :to="`/san-pham/${sp.slug}`"
        class="product-card-link"
      >
        <div class="product-card">
          <div class="card-image-box">
            <img :src="getImageUrl(sp.hinh_anh)" :alt="sp.ten_san_pham" class="product-image" />
            <div class="discount-badge" v-if="getPromotionStatus(sp) === 'ongoing'">
              -{{ getValidDiscountPercentage(sp.khuyen_mai) }}%
            </div>
          </div>

          <div class="card-details">
            <h3 class="product-name">{{ sp.ten_san_pham }}</h3>
            <p class="product-description" v-html="sp.Mo_ta_seo"></p>
            <div class="price-info-group">
              <span class="current-price">{{ formatCurrency(calculateDisplayPrice(sp.gia, sp.khuyen_mai)) }}₫</span>
              <span class="original-price" v-if="getPromotionStatus(sp) === 'ongoing'">
                {{ formatCurrency(sp.gia) }}₫
              </span>
            </div>

            <div class="countdown-group" v-if="getPromotionStatus(sp) === 'ongoing'">
              <span class="countdown-label">Kết thúc sau:</span>
              <span class="countdown-timer">{{ countdownTimers[sp.san_pham_id] }}</span>
            </div>
            <div class="countdown-group" v-else-if="getPromotionStatus(sp) === 'upcoming'">
              <span class="countdown-label">Bắt đầu sau:</span>
              <span class="countdown-timer">{{ countdownTimers[sp.san_pham_id] }}</span>
            </div>
            <div class="expired-tag" v-else-if="getPromotionStatus(sp) === 'expired'">
              Khuyến mãi đã hết hạn
            </div>

            <div class="sold-progress-bar">
              <div class="progress-fill" :style="{ width: Math.min(sp.phan_tram_da_ban, 100) + '%' }"></div>
            </div>
            <span class="sold-count">Đã bán: {{ sp.so_luong_ban }}</span>
          </div>

          <div class="card-actions">
            <label class="compare-checkbox-label" @click.stop.prevent="toggleCompare(sp)">
              <input
                type="checkbox"
                :id="'compare-' + sp.san_pham_id"
                class="compare-checkbox"
                :checked="isSelectedForCompare(sp)"
              />
              <span class="custom-checkbox"></span>
              <span class="checkbox-text">So sánh</span>
            </label>

            <button class="quick-view-btn-icon" @click.stop.prevent="openProductModal(sp)">
                <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>
      </router-link>
    </div>

    <div class="footer-actions">
      <router-link to="/san-pham-khuyen-mai" class="view-all-button">
        Xem tất cả
      </router-link>
    </div>

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

    <div v-if="showQuickView" class="product-modal-overlay" @click="closeProductModal">
    <div class="product-modal" @click.stop>
        <button class="close-btn" @click="closeProductModal">×</button>

        <template v-if="isProductLoading">
            <div class="loading-text">Đang tải thông tin sản phẩm…</div>
        </template>

        <template v-else-if="selectedProduct">
            <div class="modal-body">
                <div class="modal-left">
                    <img
                        :src="currentImageUrl"
                        class="modal-main-image"
                        :alt="selectedProduct.ten_san_pham"
                    />

                    <div class="thumbs" v-if="galleryImages.length > 1">
                        <div
                            v-for="(image, index) in galleryImages"
                            :key="index"
                            class="thumb"
                            :class="{ active: currentImageIndex === index }"
                            @click="currentImageIndex = index"
                        >
                            <img :src="image" :alt="selectedProduct.ten_san_pham" />
                        </div>
                    </div>
                </div>
                <div class="modal-right">
                    <h2 class="modal-title">{{ selectedProduct.ten_san_pham }}</h2>

                    <div class="price-row">
                        <span class="price-now">
                            {{ formatCurrency(calculateDisplayPrice(selectedVariant?.gia, selectedProduct.khuyen_mai)) }}₫
                        </span>
                        <span
                            v-if="getValidDiscountPercentage(selectedProduct.khuyen_mai) > 0"
                            class="price-old"
                        >
                            {{ formatCurrency(selectedVariant?.gia) }}₫
                        </span>
                        <span v-if="getValidDiscountPercentage(selectedProduct.khuyen_mai) > 0" class="badge-off">
                            -{{ getValidDiscountPercentage(selectedProduct.khuyen_mai) }}%
                        </span>
                    </div>

                    <div class="stock" :class="{ 'out-stock': selectedVariant?.so_luong_ton_kho <= 0 }">
                        <span v-if="selectedVariant?.so_luong_ton_kho > 0">
                            Còn hàng: {{ selectedVariant.so_luong_ton_kho }}
                        </span>
                        <span v-else>Hết hàng</span>
                    </div>

                    <div class="short-desc" v-html="selectedProduct.Mo_ta_seo || selectedProduct.mo_ta || ''"></div>

                    <div v-if="selectedProduct.variants?.length > 0" class="variants-selection">
                        <p class="variants-title">Lựa chọn biến thể:</p>
                        <div class="variants-grid">
                            <button
                                v-for="variant in selectedProduct.variants"
                                :key="variant.bien_the_id"
                                class="variant-btn"
                                :class="{
                                    active: selectedVariant?.bien_the_id === variant.bien_the_id,
                                    'out-of-stock': variant.so_luong_ton_kho === 0
                                }"
                                @click="selectedVariant = variant"
                            >
                                {{ variant.ten_bien_the }}
                            </button>
                        </div>
                    </div>

                    <div class="qty-row">
                        <label>Số lượng</label>
                        <div class="qty">
                            <button @click="qty = Math.max(1, qty - 1)">−</button>
                            <input type="number" v-model.number="qty" min="1" />
                            <button @click="qty = qty + 1">+</button>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button
                            class="add-to-cart-grid-btn"
                            @click="addToCart"
                            :disabled="selectedVariant?.so_luong_ton_kho <= 0"
                        >
                            Thêm vào giỏ
                        </button>
                        <button class="view-detail-btn" @click="goToProductDetail(selectedProduct.slug)">
                            Xem chi tiết
                        </button>
                    </div>
                </div>
            </div>
        </template>
        
        <template v-else>
            <div class="loading-text">Không tìm thấy thông tin sản phẩm.</div>
        </template>
    </div>
</div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import moment from 'moment'
import 'moment/dist/locale/vi'
import ComparisonModal from './ComparisonModal.vue'
import Swal from 'sweetalert2';
moment.locale('vi')
const router = useRouter()

/** ====== CONFIG ====== */
const API_BASE = 'http://localhost:8000'

/** ====== STATE (LIST) ====== */
const allProducts = ref([])
const filteredProducts = ref([])
const activeTab = ref('ongoing')
const countdownTimers = ref({})
let countdownInterval = null

/** ====== COMPARE ====== */
const isComparisonModalVisible = ref(false)
const detailedComparisonProducts = ref([])
const isComparisonLoading = ref(false)
const comparisonError = ref(null)
const comparisonList = ref([])
const MAX_COMPARE_ITEMS = 4

/** ====== QUICK VIEW ====== */
const showQuickView = ref(false)
const selectedProduct = ref(null)
const isProductLoading = ref(false)
const qty = ref(1)
// THAY ĐỔI: Biến mới để lưu biến thể đang được chọn trong modal
const selectedVariant = ref(null);

/** ====== HELPERS ====== */
const isUrl = (p) => /^https?:\/\//i.test(String(p || ''))
const getImageUrl = (path) => {
 if (!path) return '/images/default-grape.png'
 return isUrl(path) ? path : `${API_BASE}/storage/${path}`
}
const formatCurrency = (amount) => {
 if (amount == null) return ''
 return Math.round(parseFloat(amount)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}
const getValidDiscountPercentage = (promo) => {
 let percentage = 0
 if (promo === null || promo === undefined) return 0
 if (typeof promo === 'number') percentage = promo
 else if (typeof promo === 'string') {
  const m = promo.match(/(\d+)%/)
  percentage = m && m[1] ? parseInt(m[1]) : parseInt(promo) || 0
 }
 return Math.max(0, Math.min(100, percentage))
}
const calculateDiscountedPrice = (originalPrice, promo) => {
 const percentage = getValidDiscountPercentage(promo)
 const price = parseFloat(originalPrice)
 return isNaN(price) || percentage === 0 ? price : Math.round(price * (1 - percentage / 100))
}
const calculateDisplayPrice = (originalPrice, promo) => {
 return getValidDiscountPercentage(promo) > 0
  ? calculateDiscountedPrice(originalPrice, promo)
  : parseFloat(originalPrice)
}
const addToCartDirectly = async (product) => {
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

  const variantId = product.san_pham_bien_the_id;
  if (!variantId) {
    Swal.fire({
      icon: 'error',
      title: 'Lỗi',
      text: 'Sản phẩm này không có biến thể để thêm vào giỏ hàng.',
      confirmButtonColor: '#03A2DC'
    });
    return;
  }

  const payload = {
    san_pham_bien_the_id: variantId,
    quantity: 1, 
  };

  try {
    await axios.post('http://localhost:8000/api/cart/add',
      payload,
      { headers: { Authorization: `Bearer ${token}` } }
    );
    Swal.fire({
      icon: 'success',
      title: 'Thành công',
      text: `Đã thêm "${product.ten_san_pham}" vào giỏ hàng!`,
      confirmButtonColor: '#03A2DC'
    });
  } catch (err) {
    console.error('Lỗi khi thêm vào giỏ hàng:', err.response || err);
    Swal.fire({ 
      icon: 'error',
      title: 'Lỗi',
      text: err.response?.data?.message || 'Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng.',
      confirmButtonColor: '#03A2DC'
    });
  }
};
const getPromotionStatus = (product) => {
 const startDate = product.ngay_bat_dau_giam_gia ? moment(product.ngay_bat_dau_giam_gia) : null
 const endDate = product.ngay_ket_thuc_giam_gia ? moment(product.ngay_ket_thuc_giam_gia) : null
 const now = moment()
 if (product.khuyen_mai === 0 || product.khuyen_mai === null || !startDate || !endDate) return 'none'
 if (now.isAfter(endDate)) return 'expired'
 if (now.isBetween(startDate, endDate, null, '[]')) return 'ongoing'
 if (now.isBefore(startDate)) return 'upcoming'
 return 'none'
}

const updateCountdown = () => {
 if (!filteredProducts.value) return
 filteredProducts.value.forEach((p) => {
  const status = getPromotionStatus(p)
  let targetDate
  if (status === 'ongoing') targetDate = moment(p.ngay_ket_thuc_giam_gia)
  else if (status === 'upcoming') targetDate = moment(p.ngay_bat_dau_giam_gia)
  else return
  const now = moment()
  const diff = targetDate.diff(now)
  if (diff <= 0) {
   countdownTimers.value[p.san_pham_id] = '00:00:00'
  } else {
   const d = moment.duration(diff)
   const days = Math.floor(d.asDays())
   const pad = (n) => String(n).padStart(2, '0')
   countdownTimers.value[p.san_pham_id] =
    days > 0
     ? `${days} ngày ${pad(d.hours())}:${pad(d.minutes())}:${pad(d.seconds())}`
     : `${pad(d.hours())}:${pad(d.minutes())}:${pad(d.seconds())}`
  }
 })
}

const filterProducts = (tab) => {
 activeTab.value = tab
 filteredProducts.value = allProducts.value.filter((p) => {
  const s = getPromotionStatus(p)
  if (tab === 'ongoing') return s === 'ongoing'
  if (tab === 'upcoming') return s === 'upcoming'
  if (tab === 'expired') return s === 'expired'
  return false
 })
 updateCountdown()
}

/** ====== COMPARE ====== */
const isSelectedForCompare = (product) =>
 comparisonList.value.some((i) => i.san_pham_id === product.san_pham_id)

const toggleCompare = (product) => {
 const idx = comparisonList.value.findIndex((i) => i.san_pham_id === product.san_pham_id)
 if (idx !== -1) {
  comparisonList.value.splice(idx, 1)
 } else {
  if (comparisonList.value.length < MAX_COMPARE_ITEMS) {
   comparisonList.value.push(product)
  } else {
   alert(`Bạn chỉ có thể so sánh tối đa ${MAX_COMPARE_ITEMS} sản phẩm.`)
   const checkbox = document.getElementById('compare-' + product.san_pham_id)
   if (checkbox) checkbox.checked = false
  }
 }
}

const openComparisonModal = () => (isComparisonModalVisible.value = true)
const closeComparisonModal = () => {
 isComparisonModalVisible.value = false
 detailedComparisonProducts.value = []
 comparisonError.value = null
}

const handleCompare = async () => {
 if (comparisonList.value.length < 2) {
  alert('Bạn cần chọn ít nhất 2 sản phẩm để so sánh.')
  return
 }
 isComparisonLoading.value = true
 comparisonError.value = null
 openComparisonModal()
 try {
  const slugs = comparisonList.value.map((p) => p.slug).join(',')
  const res = await axios.get(`${API_BASE}/api/user/product/details-by-slugs`, { params: { slugs } })
  detailedComparisonProducts.value = res.data
 } catch (err) {
  comparisonError.value = err.response?.data?.message || err.message || 'Đã có lỗi xảy ra.'
  console.error('Lỗi khi lấy dữ liệu so sánh:', err)
 } finally {
  isComparisonLoading.value = false
 }
}

/** ====== QUICK VIEW ====== */
const showQuickViewModal = () => (showQuickView.value = true)
const hideQuickViewModal = () => (showQuickView.value = false)

const normalizeGallery = (detail, fallbackCover) => {
    const arr = [];
    
    // THAY ĐỔI: Ưu tiên ảnh chính của sản phẩm (từ bảng hinh_anh_san_pham)
    if (detail?.hinhAnhSanPham && Array.isArray(detail.hinhAnhSanPham)) {
        detail.hinhAnhSanPham.forEach(img => img.duongdan && arr.push(getImageUrl(img.duongdan)));
    }
    
    // Thêm các ảnh từ các trường khác nếu có
    if (detail?.hinh_anh_phu && typeof detail.hinh_anh_phu === 'string') {
        detail.hinh_anh_phu.split(',').map(x => x.trim()).filter(Boolean).forEach(p => arr.push(getImageUrl(p)));
    }
    
    // Thêm ảnh chính nếu chưa có (phòng trường hợp API không trả về hinhAnhSanPham)
    if (fallbackCover && !arr.includes(getImageUrl(fallbackCover))) {
        arr.unshift(getImageUrl(fallbackCover));
    }
    
    // Loại bỏ trùng lặp và trả về
    return Array.from(new Set(arr));
};

const openProductModal = async (sp) => {
  qty.value = 1;
  selectedProduct.value = { ...sp };
  showQuickView.value = true;
  isProductLoading.value = true;
  try {
    // Gọi API mới để lấy chi tiết sản phẩm bao gồm tất cả biến thể
    const { data } = await axios.get(`${API_BASE}/api/san-pham-cong-khai/slug/${sp.slug}`);
    
    // Cập nhật selectedProduct với toàn bộ dữ liệu từ API
    selectedProduct.value = { ...sp, ...data };

    // Tự động chọn biến thể đầu tiên có sẵn
    selectedVariant.value =
      (data.variants && data.variants.length > 0)
        ? data.variants[0]
        : null;

    // THAY ĐỔI: Sử dụng ảnh chính của sản phẩm làm ảnh đầu tiên trong bộ sưu tập
    const mainImage = selectedProduct.value.hinhAnhSanPham?.[0]?.duongdan || selectedProduct.value.hinh_anh;
    
    // THAY ĐỔI: Cập nhật normalizeGallery để ưu tiên ảnh chính của sản phẩm
    galleryImages.value = normalizeGallery(selectedProduct.value, mainImage);
    currentImageIndex.value = 0;
  } catch (err) {
    console.error(err);
    selectedProduct.value = null; // Set null nếu lỗi để hiển thị thông báo
  } finally {
    isProductLoading.value = false;
  }
};
const closeProductModal = () => {
 hideQuickViewModal()
 selectedProduct.value = null
 galleryImages.value = []
 currentImageIndex.value = 0
}

const goToProductDetail = (slug) => {
 router.push(`/san-pham/${slug}`)
 closeProductModal()
}

const addToCart = async () => {
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

  const product = selectedProduct.value;
  const quantity = qty.value;

  if (!product || !selectedVariant.value) {
    Swal.fire({
      icon: 'error',
      title: 'Lỗi',
      text: 'Không có biến thể nào được chọn.',
      confirmButtonColor: '#03A2DC'
    });
    return;
  }

  // THAY ĐỔI: Lấy ID biến thể từ biến `selectedVariant`
  const variantId = selectedVariant.value.bien_the_id;
  
  // Kiểm tra số lượng tồn kho của biến thể
  if (quantity > selectedVariant.value.so_luong_ton_kho) {
    Swal.fire({
      icon: 'error',
      title: 'Lỗi',
      text: 'Số lượng bạn chọn vượt quá số lượng tồn kho.',
      confirmButtonColor: '#03A2DC'
    });
    return;
  }

  const payload = {
    san_pham_bien_the_id: variantId,
    quantity: quantity,
  };
  
  try {
    const response = await axios.post('http://localhost:8000/api/cart/add',
      payload,
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
    closeProductModal();

  } catch (err) {
    console.error('Lỗi khi thêm vào giỏ hàng:', err.response || err);

    let errorMessage = 'Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng.';
    if (err.response && err.response.data && err.response.data.message) {
      errorMessage = err.response.data.message;
    } else if (err.message) {
      errorMessage = err.message;
    }

    Swal.fire({
      icon: 'error',
      title: 'Lỗi',
      text: errorMessage,
      confirmButtonColor: '#03A2DC'
    });
  }
};

/** ====== GALLERY (modal) ====== */
const galleryImages = ref([])
const currentImageIndex = ref(0)
const currentImageUrl = computed(() => {
 if (!galleryImages.value.length) return getImageUrl(selectedProduct.value?.hinh_anh)
 return galleryImages.value[currentImageIndex.value] || galleryImages.value[0]
})

/** Thông số kỹ thuật nhanh (nếu backend trả về object) */
const safeSpecs = computed(() => {
 const raw = selectedProduct.value?.thong_so_ky_thuat
 if (!raw) return {}
 if (Array.isArray(raw)) return {}
 if (typeof raw === 'object') return raw
 try {
  const parsed = JSON.parse(raw)
  return typeof parsed === 'object' && parsed ? parsed : {}
 } catch {
  return {}
 }
})

/** ====== LIFECYCLE ====== */
onMounted(async () => {
 try {
  const res = await axios.get(`${API_BASE}/api/admin/products-sell-top`)
  allProducts.value = res.data.map((p) => {
   // SỬA: Sử dụng tên trường 'tong_ton_kho' đã được tổng hợp từ backend
   const ton_kho = parseInt(p.tong_ton_kho) || 0
   const da_ban = parseInt(p.so_luong_ban) || 0
   const tong_so = ton_kho + da_ban
   const phan_tram = tong_so > 0 ? (da_ban / tong_so) * 100 : 0
   return {
    ...p,
    gia: parseFloat(p.gia) || 0,
    so_luong_ban: da_ban,
    tong_ton_kho: ton_kho,
    tong_so_luong: tong_so,
    phan_tram_da_ban: Math.round(phan_tram),
    san_pham_bien_the_id: p.san_pham_bien_the_id || null
   }
  })
  filterProducts('ongoing')
  countdownInterval = setInterval(updateCountdown, 1000)
 } catch (err) {
  console.error('Lỗi khi tải sản phẩm bán chạy:', err)
 }
})

onUnmounted(() => {
 if (countdownInterval) clearInterval(countdownInterval)
})
</script>
<style scoped>
.promotional-products-section {
  font-family: "Lora", serif;
  padding: 60px 20px;
  box-sizing: border-box;
}

.header-main {
  max-width: 1280px;
  margin: 0 auto 30px;
  background-color: #ffffff;
  border-radius: 16px;
  padding: 30px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.section-title { font-size: 32px; font-weight: 700; color: #1a1a1a; margin: 0; text-align: center; }

.tabs-container {
  display: flex; justify-content: center; gap: 20px; margin-top: 25px;
  border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;
}
.tab-button {
  background-color: transparent; border: none; font-size: 18px; font-weight: 600; color: #888;
  padding: 10px 15px; cursor: pointer; position: relative; transition: all 0.3s ease-in-out;
}
.tab-button:hover { color: #007bff; }
.tab-button.active { color: #007bff; }
.tab-button.active::after {
  content: ''; position: absolute; bottom: -12px; left: 0; width: 100%; height: 4px;
  background-color: #007bff; border-radius: 2px;
}

.product-grid-container {
  max-width: 1280px; margin: 0 auto;
  display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px;
}

.product-card-link { text-decoration: none; color: inherit; display: block; }

.product-card {
  background-color: #ffffff; border-radius: 16px; overflow: hidden; display: flex; flex-direction: column;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); transition: transform 0.3s ease, box-shadow 0.3s ease;
  border: 1px solid #f0f0f0;
}
.product-card:hover { transform: translateY(-8px); box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15); }

.card-image-box { position: relative; width: 100%; height: 250px; overflow: hidden; }
.product-image { width: 100%; height: 100%; object-fit: contain; transition: transform 0.4s ease-in-out; }
.product-card:hover .product-image { transform: scale(1.1); }

.discount-badge {
  position: absolute; top: 15px; left: 15px; background-color: #007bff; color: #ffffff;
  font-weight: 700; font-size: 14px; padding: 5px 12px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.card-details { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
.product-name {
  font-size: 18px; font-weight: 700; color: #1a1a1a; min-height: 40px; margin: 0 0 8px;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.product-description {
  font-size: 14px; color: #666; line-height: 1.5; min-height: 4.5em; margin: 0 0 15px;
  display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
}

.price-info-group { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.current-price { font-size: 24px; font-weight: 800; color: #ff4500; }
.original-price { font-size: 16px; color: #999; text-decoration: line-through; }

.countdown-group { display: flex; align-items: center; gap: 8px; margin-bottom: 15px; }
.countdown-label { font-size: 14px; color: #666; font-weight: 500; }
.countdown-timer { font-size: 16px; font-weight: 700; color: #007bff; }

.expired-tag { font-size: 14px; color: #999; font-style: italic; margin-bottom: 15px; }

.sold-progress-bar { background-color: #e9ecef; height: 10px; border-radius: 5px; overflow: hidden; margin-bottom: 8px; }
.progress-fill { background: linear-gradient(to right, #007bff, #2ec8ff); height: 100%; transition: width 0.5s ease-out; }
.sold-count { font-size: 13px; color: #555; font-weight: 500; }

.card-actions {
  padding: 10px 20px 20px; border-top: 1px solid #f0f0f0; margin-top: auto;
  display: flex; justify-content: flex-end; align-items: center; gap: 10px;
}
.compare-checkbox-label { display: flex; align-items: center; cursor: pointer; }
.compare-checkbox { display: none; }
.custom-checkbox {
  width: 20px; height: 20px; border: 2px solid #ccc; border-radius: 6px; display: inline-flex; justify-content: center; align-items: center; margin-right: 8px; transition: all 0.2s;
}
.compare-checkbox:checked + .custom-checkbox { background-color: #007bff; border-color: #007bff; }
.compare-checkbox:checked + .custom-checkbox::after { content: '✓'; color: #fff; font-size: 14px; font-weight: 700; }
.checkbox-text { font-size: 14px; color: #666; }

/* Nút xem nhanh */
.quick-view-btn-icon {
    background-color: transparent;
    border: 1px solid #ccc;
    color: #555;
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%; /* Tạo hình tròn */
    cursor: pointer;
    font-size: 16px;
    transition: all 0.3s ease;
}

.quick-view-btn-icon:hover {
    background-color: #f0f0f0;
    border-color: #888;
    transform: scale(1.1); /* Hiệu ứng phóng to khi di chuột qua */
}
.footer-actions { text-align: center; margin-top: 40px; }
.view-all-button {
  background-color: #007bff; color: #ffffff; padding: 15px 40px; border: none; border-radius: 10px; font-size: 18px; font-weight: 600;
  text-decoration: none; transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
  box-shadow: 0 8px 15px rgba(0, 123, 255, 0.2);
}
.view-all-button:hover { background-color: #0056b3; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0, 123, 255, 0.3); }

.comparison-bar {
  position: fixed; bottom: 0; left: 0; width: 100%; background-color: #fff; box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
  display: flex; align-items: center; justify-content: space-between; padding: 15px 30px; z-index: 1000;
  border-top-left-radius: 16px; border-top-right-radius: 16px;
}
.comparison-items { display: flex; gap: 15px; }
.comparison-item {
  position: relative; border: 1px solid #eee; padding: 8px; border-radius: 8px; width: 110px; text-align: center;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}
.comparison-item-placeholder {
  display: flex; align-items: center; justify-content: center; border: 2px dashed #ddd; border-radius: 8px; width: 110px; height: 100px; color: #ccc; font-size: 30px;
}
.comparison-img { width: 60px; height: 60px; object-fit: contain; }
.comparison-name {
  font-size: 12px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 5px 0 0;
}
.remove-compare-item {
  position: absolute; top: -10px; right: -10px; background-color: #ff4500; color: white; border: none; border-radius: 50%;
  width: 24px; height: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 700; line-height: 1;
}
.compare-button {
  padding: 12px 30px; font-size: 16px; font-weight: bold; color: white; background-color: #007bff; border: none; border-radius: 8px; cursor: pointer; transition: background-color 0.3s, transform 0.2s;
}
.compare-button:hover { background-color: #0056b3; transform: translateY(-2px); }
.compare-button:disabled { background-color: #ccc; cursor: not-allowed; transform: none; }

/* ===== Modal Quick View ===== */
.product-modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.7);
  display: flex; justify-content: center; align-items: center; z-index: 2000;
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
  position: absolute; top: 8px; right: 10px; background: transparent; border: none; font-size: 24px; cursor: pointer;
}

.modal-body { display: grid; grid-template-columns: 1.2fr 1fr; gap: 24px; }
.modal-left { display: flex; flex-direction: column; gap: 12px; }
.modal-main-image {
    width: 100%;
    height: auto;
    object-fit: cover; 
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}
.thumbs { display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; }
.thumb {
  border: 1px solid #eee; border-radius: 8px; padding: 4px; background: #fff; cursor: pointer; transition: transform .15s ease, box-shadow .15s ease;
}
.thumb:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(0,0,0,.06); }
.thumb.active { border-color: #007bff; }
.thumb img { width: 100%; height: 70px; object-fit: contain; }

.modal-right { flex: 1; }
.modal-title { margin: 0 0 6px; }
.price-row { display: flex; align-items: baseline; gap: 10px; margin: 8px 0 12px; }
.price-now { color: #e63946; font-size: 24px; font-weight: 800; }
.price-old { color: #999; text-decoration: line-through; }
.badge-off { background: #ffe5e5; color: #e63946; border-radius: 6px; padding: 2px 8px; font-weight: 700; font-size: 12px; }

.stock { margin: 4px 0 12px; color: #2a9d8f; font-weight: 600; }
.stock.out-stock { color: #e63946; }

.short-desc { color: #444; line-height: 1.6; margin-bottom: 12px; }
.qty-row { display: flex; align-items: center; gap: 10px; margin: 12px 0; }
.qty-row label { font-weight: 600; color: #333; }
.qty { display: inline-flex; border: 1px solid #e5e5e5; border-radius: 8px; overflow: hidden; }
.qty button { width: 36px; height: 36px; border: none; background: #f6f6f6; cursor: pointer; font-size: 18px; }
.qty input { width: 60px; height: 36px; border: none; text-align: center; outline: none; }

.modal-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 8px; }
.view-detail-btn {
  padding: 10px 16px; background-color: #f7f7f7; color: #222222; border: solid 1px #a7a7a7; border-radius: 8px; cursor: pointer; transition: 0.2s;
}
.view-detail-btn:hover { background-color: #9c9a9abd; }
.add-to-cart-grid-btn {
  padding: 10px 16px; background-color: #007aff; color: white; border: none; border-radius: 8px; cursor: pointer; transition: 0.2s;
}
.add-to-cart-grid-btn:hover { background-color: #005ecb; }

.specs { margin-top: 14px; }
.specs h4 { margin: 0 0 8px; }
.specs ul { padding-left: 16px; margin: 0; }
.specs li { margin: 4px 0; }

.loading-text { text-align: center; color: #333; font-weight: bold; }

/* Responsive modal */
@media (max-width: 900px) {
  .modal-body { grid-template-columns: 1fr; }
  .modal-main-image { height: 300px; }
}

/* Animation */
@keyframes fadeIn {
  from { opacity: 0; transform: scale(.98) }
  to { opacity: 1; transform: scale(1) }
}
.variants-selection {
  margin-top: 20px;
}
.variants-title {
  font-size: 16px;
  font-weight: 600;
  color: #333;
  margin-bottom: 10px;
}
.variants-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}
.variant-btn {
  background-color: #f7f7f7;
  color: #555;
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 8px 16px;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
  outline: none;
}
.variant-btn:hover {
  background-color: #e9ecef;
  border-color: #999;
}
/* Style cho nút biến thể đang được chọn */
.variant-btn.active {
  background-color: #007bff;
  color: #fff;
  border-color: #007bff;
  box-shadow: 0 4px 10px rgba(0, 123, 255, 0.2);
  transform: translateY(-2px);
}
/* Style cho nút biến thể hết hàng */
.variant-btn.out-of-stock {
  background-color: #f0f0f0;
  color: #aaa;
  border-color: #ddd;
  cursor: not-allowed;
  text-decoration: line-through;
  opacity: 0.7;
}
.variant-btn.out-of-stock:hover {
  background-color: #f0f0f0;
  border-color: #ddd;
  transform: none;
  box-shadow: none;
}
</style>