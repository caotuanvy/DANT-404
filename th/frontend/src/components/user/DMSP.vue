<template>
  <section class="dmsp-section">
    <h2 class="dmsp-title-main">Danh mục phổ biến</h2>
    <div v-if="loading" class="text-center">
      <p>Đang tải danh mục...</p>
    </div>
    <div v-else-if="error" class="text-center error-message">
      <p>{{ error }}</p>
    </div>
    <div v-else-if="danhMucList.length === 0" class="text-center no-data-message">
      <p>Không tìm thấy danh mục nào.</p>
    </div>
    <div v-else class="dmsp-grid">
      <div class="dmsp-card" v-for="cat in danhMucList" :key="cat.id">
        <div class="dmsp-image-container">
          <img v-if="cat.image_url" :src="cat.image_url" :alt="cat.ten_danh_muc" class="dmsp-image">
          <div v-else class="dmsp-icon" :style="{ background: cat.bgColor || '#e0f2fe' }">
            <span v-if="cat.icon">{{ cat.icon }}</span>
            <span v-else>{{ cat.ten_danh_muc?.charAt(0) }}</span>
          </div>
        </div>
        <div class="dmsp-card-name">{{ cat.ten_danh_muc }}</div>
        <div class="dmsp-card-desc">{{ cat.mo_ta }}</div>
        <a class="dmsp-link" @click.prevent="viewProducts(cat.id)">Xem thêm &rarr;</a>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const danhMucList = ref([])
const loading = ref(true)
const error = ref(null)
const router = useRouter()

const API_BASE_URL = 'https://api.sieuthi404.io.vn/api'

/**
 * Hàm gọi API để lấy danh sách danh mục cha
 */
const fetchDanhMuc = async () => {
  loading.value = true
  error.value = null
  try {
    const res = await axios.get(`${API_BASE_URL}/danh-muc-cha`)
    const colors = ['#e0f2fe', '#fef9c3', '#fce7f3', '#f1f5f9', '#fef2f2', '#ede9fe']
    danhMucList.value = res.data.map((cat, i) => ({
      ...cat,
      bgColor: colors[(i + 4) % colors.length]
    }))
  } catch (err) {
    console.error('Lỗi khi tải danh mục sản phẩm:', err)
    error.value = 'Không thể tải danh mục sản phẩm. Vui lòng thử lại sau!'
  } finally {
    loading.value = false
  }
}

/**
 * Hàm điều hướng đến trang sản phẩm của danh mục
 * @param {number} categoryId - ID của danh mục
 */
const viewProducts = (categoryId) => {
  router.push({ name: 'CategoryProductsUser', params: { id: categoryId, slug: 'danh-muc-san-pham' } })
}

onMounted(fetchDanhMuc)
</script>

<style scoped>
/* MESSAGE STYLES */
.text-center {
  text-align: center;
  padding: 20px;
}
.error-message {
  color: #c0392b;
  font-weight: 500;
}
.no-data-message {
  color: #7f8c8d;
  font-weight: 500;
}

.dmsp-section {
  width: 100%;
  padding: 32px 0 0 0;
  background: #f9fafb;
}
.dmsp-title-main {
  text-align: center;
  font-size: 2rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 2.5rem;
}
.dmsp-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px; /* Giảm khoảng cách giữa các card */
  margin: 0 auto 40px;
  padding: 0 20px;
  max-width: 1200px;
}
.dmsp-card {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 4px 24px 0 rgba(30,41,59,0.07);
  
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 8px; /* Giảm khoảng cách giữa các phần tử bên trong card */
  
  min-height: 200px;
  padding: 15px 20px; /* Giảm padding bên trong card */
  transition: box-shadow 0.2s, border-color 0.2s;
  border: 1px solid #f1f5f9;
}
.dmsp-card:hover {
  box-shadow: 0 8px 32px 0 rgba(59,130,246,0.13);
  border-color: #bae6fd;
}
.dmsp-image-container {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}
.dmsp-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.dmsp-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: #2563eb;
  background: #e0f2fe;
}
.dmsp-card-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1e293b;
  text-align: center;
}
.dmsp-card-desc {
  color: #64748b;
  font-size: 1rem;
  text-align: center;
  margin-bottom: 0;
  flex-grow: 0;
}
.dmsp-link {
  color: #2563eb;
  font-weight: 500;
  text-decoration: none;
  cursor: pointer;
  font-size: 1rem;
  transition: color 0.2s;
  text-align: center;
  margin-top: 0;
  display: block;
}
.dmsp-link:hover {
  color: #1e293b;
  text-decoration: underline;
}

/* RESPONSIVE DESIGN */
@media (max-width: 1024px) {
  .dmsp-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 640px) {
  .dmsp-grid {
    grid-template-columns: 1fr;
  }
  .dmsp-card {
    width: 100%;
  }
}
</style>