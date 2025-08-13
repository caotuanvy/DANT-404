<template>
  <div class="page-wrapper">
    <header class="page-header">
      <h1 v-if="category" class="page-title"> {{ category.ten_danh_muc }}</h1>
      <h1 v-else-if="!loading" class="page-title">Sản phẩm</h1>
      <div v-if="maxProductPrice && minProductPrice" class="price-info">
        <p>Giá thấp nhất: <span class="price-value">{{ formatPrice(minProductPrice) }}</span></p>
        <p>Giá cao nhất: <span class="price-value">{{ formatPrice(maxProductPrice) }}</span></p>
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
        <div class="sort-by-container">
          <label for="sort-by" class="sort-by-label">Sắp xếp theo:</label>
          <select id="sort-by" v-model="sortBy" @change="applyFilters" class="sort-by-select">
            <option value="default">Mặc định</option>
            <option value="price_asc">Giá: Thấp đến cao</option>
            <option value="price_desc">Giá: Cao đến thấp</option>
            <option value="newest">Mới nhất</option>
          </select>
        </div>

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
            <img :src="product.image_url" :alt="product.ten_san_pham" class="product-image" />
            <div class="product-info">
              <h3 class="product-name">{{ product.ten_san_pham }}</h3>
              <p class="product-price">{{ formatPrice(product.gia) }}</p>
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

// State cho phân trang
const currentPage = ref(1);
const lastPage = ref(1);

// State cho các bộ lọc
const minPrice = ref(null);
const maxPrice = ref(null);
const keyword = ref('');
const sortBy = ref('default'); 

// State cho danh mục con: dùng một biến đơn lẻ vì chỉ chọn 1
const selectedChildCategory = ref(null); 
const childCategoryKeyword = ref('');

// State mới để lưu giá min/max của các sản phẩm trên trang hiện tại
const minProductPrice = ref(null);
const maxProductPrice = ref(null);

// Cập nhật API_BASE_URL của bạn
const API_BASE_URL = 'http://localhost:8000/api';

const fetchProducts = async (categoryId, page = 1) => {
  loading.value = true;
  error.value = null;
  products.value = [];

  if (!categoryId) {
    error.value = 'Không có ID danh mục.';
    loading.value = false;
    return;
  }
  
  currentPage.value = page; // Cập nhật trang hiện tại

  // Xây dựng các tham số truy vấn
  const params = {
    page: currentPage.value,
    per_page: 12, // Đã thay đổi thành 12 sản phẩm trên mỗi trang
  };
  // Chỉ thêm tham số vào params nếu có giá trị
  if (minPrice.value) params.min_price = minPrice.value;
  if (maxPrice.value) params.max_price = maxPrice.value;
  if (keyword.value) params.keyword = keyword.value;
  if (selectedChildCategory.value) params.child_categories = selectedChildCategory.value;
  // Thêm tham số sắp xếp
  params.sort_by = sortBy.value;

  try {
    const response = await axios.get(`${API_BASE_URL}/parent-categories/${categoryId}/products`, { params });
    category.value = response.data.category;
    products.value = response.data.products;
    
    // Cập nhật thông tin phân trang
    lastPage.value = response.data.pagination.last_page;
    
    // Tính toán và cập nhật giá cao nhất và thấp nhất từ danh sách sản phẩm
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
  // Khi áp dụng bộ lọc mới, luôn quay về trang đầu tiên
  fetchProducts(route.params.id, 1);
};

const changePage = (page) => {
  // Chỉ thay đổi trang nếu hợp lệ
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

// Computed property để lấy danh sách danh mục con từ API
const childCategories = computed(() => {
  return category.value && category.value.danh_muc_con ? category.value.danh_muc_con : [];
});

// Computed property để lọc danh sách danh mục con dựa trên từ khóa tìm kiếm
const filteredChildCategories = computed(() => {
  if (!childCategoryKeyword.value) {
    return childCategories.value;
  }
  return childCategories.value.filter(child =>
    child.ten_danh_muc.toLowerCase().includes(childCategoryKeyword.value.toLowerCase())
  );
});

// Theo dõi thay đổi của route để fetch lại sản phẩm khi điều hướng
watch(() => route.params.id, (newId) => {
  if (newId) {
    clearFilters();
    fetchProducts(newId);
  }
});

// Khi component được mount, fetch sản phẩm lần đầu
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

/* HEADER STYLES */
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
  gap: 30px;
}

/* FILTER SIDEBAR */
.filter-sidebar {
  width: 280px;
  flex-shrink: 0;
  padding: 20px;
  background-color: #f9f9f9;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  height: fit-content;
  position: sticky;
  top: 20px;
}
.filter-title {
  font-size: 1.6em;
  font-weight: bold;
  color: #34495e;
  margin-top: 0;
  margin-bottom: 25px;
  border-bottom: 1px solid #ddd;
  padding-bottom: 15px;
}
.filter-group {
  margin-bottom: 25px;
}
.filter-label {
  font-weight: 600;
  color: #555;
  margin-bottom: 10px;
  display: block;
}
.filter-input {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  box-sizing: border-box;
  font-size: 1em;
  transition: border-color 0.3s ease;
}
.filter-input:focus {
  border-color: #007bff;
  outline: none;
}
.price-range {
  display: flex;
  align-items: center;
  gap: 10px;
}
.price-input {
  flex: 1;
}
.price-separator {
  font-size: 1.2em;
  color: #888;
}
.category-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-height: 200px;
  overflow-y: auto;
}
.category-item {
  display: flex;
  align-items: center;
  cursor: pointer;
  padding: 5px;
  border-radius: 4px;
  transition: background-color 0.3s;
}
.category-item:hover {
  background-color: #eaeaea;
}
.category-radio {
  margin-right: 10px;
}
.apply-filter-btn {
    width: 100%;
    padding: 12px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 1.1em;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    margin-top: 15px;
}
.apply-filter-btn:hover {
    background-color: #0056b3;
    transform: translateY(-2px);
}
.clear-filter-btn {
    width: 100%;
    padding: 12px;
    background-color: #e74c3c;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 1.1em;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    margin-top: 10px;
}
.clear-filter-btn:hover {
    background-color: #c0392b;
    transform: translateY(-2px);
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
    margin-bottom: 25px;
    gap: 15px;
}
.sort-by-label {
    font-weight: 600;
    color: #555;
}
.sort-by-select {
    padding: 10px 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
    background-color: #fff;
    font-size: 1em;
    cursor: pointer;
}
.product-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr); /* 3 cột cố định */
  gap: 30px;
}
.product-card {
  display: flex;
  flex-direction: column;
  text-decoration: none;
  color: #333;
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  background-color: #fff;
  position: relative;
  box-shadow: 0 4px 8px rgba(0,0,0,0.05);
}
.product-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 20px rgba(0,0,0,0.1);
}
.product-image {
  width: 100%;
  height: 220px;
  object-fit: cover;
  display: block;
}
.product-info {
  padding: 18px;
  text-align: center;
}
.product-name {
  font-size: 1.2em;
  margin-top: 0;
  margin-bottom: 10px;
  font-weight: 600;
  color: #444;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}
.product-price {
  font-size: 1.3em;
  color: #e74c3c;
  font-weight: bold;
  margin: 0;
}

/* PAGINATION STYLES */
.pagination-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin-top: 30px;
}
.pagination-btn {
    padding: 12px 24px;
    border: 1px solid #ccc;
    background-color: #f0f0f0;
    cursor: pointer;
    border-radius: 6px;
    font-size: 1em;
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
    font-size: 1.1em;
    font-weight: bold;
    color: #555;
}

/* MESSAGE STYLES */
.text-center {
  text-align: center;
  padding: 50px 0;
}
.error-message {
  color: #c0392b;
  font-size: 1.4em;
  font-weight: 500;
}
.no-data-message {
  color: #7f8c8d;
  font-size: 1.4em;
  font-weight: 500;
}

/* RESPONSIVE DESIGN */
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
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 20px;
  }
  .sort-by-container {
    justify-content: space-between;
  }
}
</style>