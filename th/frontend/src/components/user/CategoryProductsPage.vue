<template>
  <div class="page-wrapper">
    <header class="page-header">
      <h1 v-if="category" class="page-title">{{ category.ten_danh_muc }}</h1>
      <h1 v-else-if="!loading" class="page-title">Sản phẩm</h1>
      <div v-if="maxProductPrice && minProductPrice" class="price-info">
        <p>Giá thấp nhất: <span class="price-value">{{ formatPrice(minProductPrice) }}</span></p>
        <p>Giá cao nhất: <span class="price-value">{{ formatPrice(maxProductPrice) }}</span></p>
      </div>
      <div class="sort-by-container">
          <label for="sort-by" class="sort-by-label">Sắp xếp theo:</label>
          <select id="sort-by" v-model="sortBy" @change="applyFilters" class="sort-by-select">
            <option value="default">Mặc định</option>
            <option value="price_asc">Giá: Thấp đến cao</option>
            <option value="price_desc">Giá: Cao đến thấp</option>
            <option value="newest">Mới nhất</option>
          </select>
        </div>
    </header>

    <div class="main-content-container">
      <aside class="filter-sidebar">
        <h3 class="filter-title">Bộ lọc</h3>

        <div class="filter-group">
          <label for="keyword-search" class="filter-label">Tìm kiếm sản phẩm</label>
          <input
            id="keyword-search"
            type="text"
            v-model="keyword"
            placeholder="Nhập từ khóa..."
            class="filter-input"
            @keyup.enter="applyFilters"
          />
        </div>

        <div class="filter-group">
          <label class="filter-label">Khoảng giá</label>
          <div class="price-range">
            <input type="number" v-model="minPrice" placeholder="Giá thấp nhất" class="filter-input price-input" />
            <span class="price-separator">-</span>
            <input type="number" v-model="maxPrice" placeholder="Giá cao nhất" class="filter-input price-input" />
          </div>
        </div>

        <div class="filter-group" v-if="childCategories.length">
          <label class="filter-label">Danh mục con</label>
          <input
            type="text"
            v-model="childCategoryKeyword"
            placeholder="Tìm kiếm danh mục con..."
            class="filter-input"
          />
          <div class="category-list">
            <div class="category-item">
              <input
                type="radio"
                id="category-all"
                :value="null"
                v-model="selectedChildCategory"
                name="child-category-filter"
                class="category-radio"
              />
              <label for="category-all">Tất cả</label>
            </div>
            <div v-for="child in filteredChildCategories" :key="child.category_id" class="category-item">
              <input
                type="radio"
                :id="`category-${child.category_id}`"
                :value="child.category_id"
                v-model="selectedChildCategory"
                name="child-category-filter"
                class="category-radio"
              />
              <label :for="`category-${child.category_id}`">{{ child.ten_danh_muc }}</label>
            </div>
          </div>
        </div>
        
        <button @click="applyFilters" class="apply-filter-btn">Áp dụng</button>
        <button @click="clearFilters" class="clear-filter-btn">Xóa bộ lọc</button>
      </aside>

      <div class="product-grid-container">
        

        <div v-if="loading" class="text-center">
          <p>Đang tải sản phẩm...</p>
        </div>
        <div v-else-if="error" class="text-center error-message">
          <p>{{ error }}</p>
        </div>
        <div v-else-if="products.length === 0" class="text-center no-data-message">
          <p>Không tìm thấy sản phẩm nào trong danh mục này.</p>
        </div>
        <div v-else class="product-grid">
          <router-link
            v-for="product in products"
            :key="product.id"
            :to="{ name: 'ProductDetailUser', params: { slug: toSlug(product.ten_san_pham) } }"
            class="product-card"
          >
            <div v-if="product.khuyen_mai && product.khuyen_mai > 0" class="discount-badge">
              -{{ product.khuyen_mai }}%
            </div>

            <img :src="product.image_url" :alt="product.ten_san_pham" class="product-image" />
            
            <div class="product-info">
              <span class="product-category" v-if="category">{{ category.ten_danh_muc }}</span>
              
              <h3 class="product-name">{{ product.ten_san_pham }}</h3>
              
              <p class="product-description" v-html=product.mo_ta></p>  
              
              <div class="product-rating">
                ⭐ 4.8 <span class="rating-count">(100 đánh giá)</span>
              </div>

              <div class="price-section">
                <p class="current-price">{{ formatPrice(product.gia_da_giam) }}</p>
                <p v-if="product.gia_goc" class="original-price">{{ formatPrice(product.gia_goc) }}</p>
              </div>

              <button class="add-to-cart-button" @click.prevent="addToCart(product)">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Thêm</span>
              </button>
            </div>
          </router-link>
        </div>
        <div v-if="lastPage > 1" class="pagination-container">
          <button 
            :disabled="currentPage === 1" 
            @click="changePage(currentPage - 1)"
            class="pagination-btn"
          >
            Trang trước
          </button>
          <span class="pagination-info">Trang {{ currentPage }} / {{ lastPage }}</span>
          <button 
            :disabled="currentPage === lastPage" 
            @click="changePage(currentPage + 1)"
            class="pagination-btn"
          >
            Trang sau
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const category = ref(null);
const products = ref([]);
const loading = ref(true);
const error = ref(null);

const currentPage = ref(1);
const lastPage = ref(1);

const minPrice = ref(null);
const maxPrice = ref(null);
const keyword = ref('');
const sortBy = ref('default'); 

const selectedChildCategory = ref(null); 
const childCategoryKeyword = ref('');

const minProductPrice = ref(null);
const maxProductPrice = ref(null);

const API_BASE_URL = 'https://api.sieuthi404.io.vn/api';

const fetchProducts = async (categoryId, page = 1) => {
  loading.value = true;
  error.value = null;
  products.value = [];

  if (!categoryId) {
    error.value = 'Không có ID danh mục.';
    loading.value = false;
    return;
  }
  
  currentPage.value = page;

  const params = {
    page: currentPage.value,
    per_page: 12, // Đã thay đổi thành 12 sản phẩm mỗi trang
  };
  if (minPrice.value) params.min_price = minPrice.value;
  if (maxPrice.value) params.max_price = maxPrice.value;
  if (keyword.value) params.keyword = keyword.value;
  if (selectedChildCategory.value) params.child_categories = selectedChildCategory.value;
  params.sort_by = sortBy.value;

  try {
    const response = await axios.get(`${API_BASE_URL}/parent-categories/${categoryId}/products`, { params });
    category.value = response.data.category;
    products.value = response.data.products;
    lastPage.value = response.data.pagination.last_page;
    
    if (products.value.length > 0) {
      const allPrices = products.value.map(p => p.gia);
      minProductPrice.value = Math.min(...allPrices);
      maxProductPrice.value = Math.max(...allPrices);
    } else {
      minProductPrice.value = null;
      maxProductPrice.value = null;
    }

  } catch (err) {
    console.error('Lỗi khi lấy sản phẩm:', err);
    if (err.response && err.response.status === 404) {
      error.value = 'Danh mục không tồn tại.';
    } else {
      error.value = 'Không thể tải sản phẩm. Vui lòng thử lại sau.';
    }
    category.value = null;
    products.value = [];
    minProductPrice.value = null;
    maxProductPrice.value = null;
  } finally {
    loading.value = false;
  }
};

const applyFilters = () => {
  fetchProducts(route.params.id, 1);
};

const changePage = (page) => {
  if (page >= 1 && page <= lastPage.value) {
    fetchProducts(route.params.id, page);
  }
};

const clearFilters = () => {
  minPrice.value = null;
  maxPrice.value = null;
  keyword.value = '';
  selectedChildCategory.value = null; 
  childCategoryKeyword.value = '';
  sortBy.value = 'default';
  applyFilters();
};

const formatPrice = (price) => {
  if (price && typeof price === 'object') {
    const min = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price.min);
    const max = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price.max);
    if (price.min === price.max) {
      return min;
    }
    return `${min} - ${max}`;
  }
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
};

const toSlug = (str) => {
  if (!str) return '';
  str = str.toLowerCase();
  str = str.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ặ|ẵ|â|ấ|ầ|ẩ|ậ|ẫ/g, "a");
  str = str.replace(/é|è|ẻ|ẹ|ẽ|ê|ế|ề|ể|ệ|ễ/g, "e");
  str = str.replace(/i|í|ì|ỉ|ị|ĩ/g, "i");
  str = str.replace(/ó|ò|ỏ|ọ|õ|ô|ố|ồ|ổ|ộ|ỗ|ơ|ớ|ờ|ở|ợ|ỡ/g, "o");
  str = str.replace(/ú|ù|ủ|ụ|ũ|ư|ứ|ừ|ử|ự|ữ/g, "u");
  str = str.replace(/ý|ỳ|ỷ|ỵ|ỹ/g, "y");
  str = str.replace(/đ/g, "d");
  str = str.replace(/[^a-z0-9 ]/g, "");
  str = str.replace(/\s+/g, '-');
  str = str.replace(/^-+|-+$/g, "");
  return str;
};

const childCategories = computed(() => {
  return category.value && category.value.danh_muc_con ? category.value.danh_muc_con : [];
});

const filteredChildCategories = computed(() => {
  if (!childCategoryKeyword.value) {
    return childCategories.value;
  }
  return childCategories.value.filter(child =>
    child.ten_danh_muc.toLowerCase().includes(childCategoryKeyword.value.toLowerCase())
  );
});

const addToCart = (product) => {
  // Logic thêm vào giỏ hàng của bạn
  console.log(`Đã thêm sản phẩm ${product.ten_san_pham} vào giỏ hàng.`);
  // Thay thế bằng logic API thực tế của bạn
};

watch(() => route.params.id, (newId) => {
  if (newId) {
    clearFilters();
    fetchProducts(newId);
  }
});

onMounted(() => {
  if (route.params.id) {
    fetchProducts(route.params.id);
  }
});
</script>

<style scoped>
/* GENERAL STYLES */
.page-wrapper {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #333;
}
.page-header {
  text-align: center;
  margin-bottom: 40px;
  border-bottom: 2px solid #e0e0e0;
  padding-bottom: 20px;
}
.page-title {
  font-size: 2.8em;
  font-weight: 600;
  color: #2c3e50;
  margin: 0;
}
.price-info {
  text-align: center;
  margin-top: 15px;
  font-size: 1.2em;
  color: #555;
  display: flex;
  justify-content: center;
  gap: 30px;
}
.price-value {
  font-weight: bold;
  color: #e74c3c;
}

/* MAIN CONTENT LAYOUT */
.main-content-container {
  display: flex;
  gap: 20px;
}

/* FILTER SIDEBAR */
.filter-sidebar {
  width: 200px;
  flex-shrink: 0;
  padding: 15px;
  background-color: #f9f9f9;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  height: fit-content;
  position: sticky;
  top: 20px;
}
.filter-title {
  font-size: 1.4em;
  font-weight: bold;
  color: #34495e;
  margin-top: 0;
  margin-bottom: 20px;
  border-bottom: 1px solid #ddd;
  padding-bottom: 10px;
}
.filter-group {
  margin-bottom: 20px;
}
.filter-label {
  font-weight: 600;
  color: #555;
  margin-bottom: 8px;
  display: block;
}
.filter-input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 6px;
  box-sizing: border-box;
  font-size: 0.9em;
  transition: border-color 0.3s ease;
}
.filter-input:focus {
  border-color: #007bff;
  outline: none;
}
.price-range {
  display: flex;
  align-items: center;
  gap: 8px;
}
.price-input {
  flex: 1;
}
.price-separator {
  font-size: 1em;
  color: #888;
}
.category-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 200px;
  overflow-y: auto;
}
.category-item {
  display: flex;
  align-items: center;
  cursor: pointer;
  padding: 3px;
  border-radius: 4px;
  transition: background-color 0.3s;
}
.category-item:hover {
  background-color: #eaeaea;
}
.category-radio {
  margin-right: 8px;
}
.apply-filter-btn {
  width: 100%;
  padding: 10px;
  background-color: #007bff;
  color: #fff;
  border: none;
  border-radius: 6px;
  font-size: 1em;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.2s ease;
  margin-top: 10px;
}
.apply-filter-btn:hover {
  background-color: #0056b3;
  transform: translateY(-1px);
}
.clear-filter-btn {
  width: 100%;
  padding: 10px;
  background-color: #e74c3c;
  color: #fff;
  border: none;
  border-radius: 6px;
  font-size: 1em;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.2s ease;
  margin-top: 8px;
}
.clear-filter-btn:hover {
  background-color: #c0392b;
  transform: translateY(-1px);
}

/* PRODUCT GRID AND SORTING */
.product-grid-container {
  flex-grow: 1;
  padding: 20px;
}
.sort-by-container {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  margin-bottom: 2px;
  gap: 15px;
}
.sort-by-label {
  font-weight: 600;
  color: #555;
}
.sort-by-select {
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  background-color: #fff;
  font-size: 0.9em;
  cursor: pointer;
}
.product-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr); /* Đã thay đổi thành 4 cột */
  gap: 20px;
}
.product-card {
  display: flex;
  flex-direction: column;
  text-decoration: none;
  color: #333;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  background-color: #fff;
  position: relative;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}
.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 8px 16px rgba(0, 0, 0, 0.1);
}
.discount-badge {
  position: absolute;
  top: 8px;
  left: 8px;
  background-color: #03A2DC;
  color: white;
  font-size: 11px;
  font-weight: bold;
  padding: 3px 8px;
  border-radius: 6px;
  z-index: 10;
}
.product-image {
  width: 100%;
  height: 120px;
  object-fit: contain;
  padding: 10px;
  box-sizing: border-box;
}
.product-info {
  padding: 10px;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}
.product-category {
  font-size: 10px;
  font-weight: 500;
  color: #1663AB;
  background-color: #1663ab48;
  padding: 3px 8px;
  border-radius: 6px;
  margin-bottom: 6px;
  align-self: flex-start;
  white-space: nowrap;
}
.product-name {
  font-size: 14px;
  font-weight: 600;
  margin: 0 0 4px 0;
  min-height: 36px;
  overflow: hidden;
  display: -webkit-box;
  -webkit-box-orient: vertical;
}
.product-description {
  font-size: 12px;
  color: #555;
  min-height: 33px;
  margin-bottom: 6px;
  flex-grow: 1;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 3; /* Thêm dòng này để giới hạn chỉ 2 dòng */
  -webkit-box-orient: vertical;
}
.product-rating {
  font-size: 12px;
  margin-bottom: 6px;
  color: #fbc02d;
  display: flex;
  align-items: center;
  gap: 4px;
}
.rating-count {
  font-size: 10px;
  color: #888;
}
.price-section {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 2px;
  margin-top: auto;
}
.current-price {
  font-size: 16px;
  font-weight: bold;
  color: red;
  margin: 0;
}
.original-price {
  font-size: 12px;
  color: #999;
  text-decoration: line-through;
  margin: 0;
}
.add-to-cart-button {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  border: none;
  padding: 8px;
  font-size: 14px;
  font-weight: 600;
  border-radius: 8px;
  margin-top: 10px;
  background-color: #03A2DC;
  color: white;
  cursor: pointer;
  transition: background-color 0.2s ease, transform 0.1s ease;
}
.add-to-cart-button:hover {
  background-color: #028ec4;
}
.add-to-cart-button:active {
  transform: scale(0.98);
}
.add-to-cart-button svg {
  width: 16px;
  height: 16px;
}
/* PAGINATION STYLES */
.pagination-container {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;
  margin-top: 25px;
}
.pagination-btn {
  padding: 10px 20px;
  border: 1px solid #ccc;
  background-color: #f0f0f0;
  cursor: pointer;
  border-radius: 6px;
  font-size: 0.9em;
  font-weight: 600;
  transition: background-color 0.3s ease;
}
.pagination-btn:hover:not(:disabled) {
  background-color: #e0e0e0;
}
.pagination-btn:disabled {
  background-color: #ddd;
  cursor: not-allowed;
  color: #888;
}
.pagination-info {
  font-size: 1em;
  font-weight: bold;
  color: #555;
}
/* MESSAGE STYLES */
.text-center {
  text-align: center;
  padding: 40px 0;
}
.error-message {
  color: #c0392b;
  font-size: 1.2em;
  font-weight: 500;
}
.no-data-message {
  color: #7f8c8d;
  font-size: 1.2em;
  font-weight: 500;
}
/* RESPONSIVE DESIGN */
@media (max-width: 1200px) {
  .product-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}
@media (max-width: 992px) {
  .product-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}
@media (max-width: 768px) {
  .page-title {
    font-size: 2em;
  }
  .main-content-container {
    flex-direction: column;
  }
  .filter-sidebar {
    width: 100%;
    position: static;
    margin-bottom: 20px;
  }
  .product-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
  }
  .sort-by-container {
    justify-content: space-between;
  }
  .product-image {
    height: 100px;
  }
  .product-name {
    font-size: 12px;
    min-height: 36px;
  }
  .price-section {
    flex-direction: column;
    align-items: flex-start;
  }
  .current-price {
    font-size: 14px;
  }
  .original-price {
    font-size: 10px;
  }
}
</style>