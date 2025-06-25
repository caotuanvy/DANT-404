<template>
  <section class="dmsp-section">
    <h2 class="dmsp-title-main">Danh mục phổ biến</h2>
    <div class="dmsp-grid">
      <div class="dmsp-card" v-for="dm in danhMucList.slice(0, 4)" :key="dm.category_id">
  <div class="dmsp-icon" :style="{ background: dm.bgColor || '#e0f2fe' }">
    <span v-if="dm.icon">{{ dm.icon }}</span>
    <span v-else>{{ dm.ten_danh_muc?.charAt(0) }}</span>
  </div>
  <div class="dmsp-card-name">{{ dm.ten_danh_muc }}</div>
  <div class="dmsp-card-desc">{{ dm.mo_ta }}</div>
  <a class="dmsp-link" @click.prevent="viewProducts(dm.category_id)">Xem thêm &rarr;</a>
</div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const danhMucList = ref([])
const router = useRouter()

const fetchDanhMuc = async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/categories')
    const colors = ['#e0f2fe', '#fef9c3', '#fce7f3', '#f1f5f9', '#fef2f2', '#ede9fe']
    danhMucList.value = res.data.map((dm, i) => ({
      ...dm,
      bgColor: colors[i % colors.length]
    }))
  } catch (err) {
    alert('Không thể tải danh mục sản phẩm!')
  }
}

const viewProducts = (categoryId) => {
  router.push(`/admin/categories/${categoryId}/products`)
}

onMounted(fetchDanhMuc)
</script>

<style scoped>
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
  grid-template-columns: repeat(4, 1fr);
  gap: 32px;
    
  margin-bottom: 40px;
  justify-items: center;
}
.dmsp-card {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 4px 24px 0 rgba(30,41,59,0.07);
  display: flex;
  flex-direction: column;
  align-items: center;
  
  width: 300px;
  /* Tăng chiều cao tối thiểu cho ô dài ra */
  min-height: 220px; /* hoặc 250px, tuỳ ý */
  padding: 28px 24px 20px 24px;
  transition: box-shadow 0.2s;
  border: 1px solid #f1f5f9;
}
.dmsp-card:hover {
  box-shadow: 0 8px 32px 0 rgba(59,130,246,0.13);
  border-color: #bae6fd;
}
.dmsp-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  margin-bottom: 18px;
  color: #2563eb;
  background: #e0f2fe;
}
.dmsp-card-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 0.3rem;
  text-align: left; /* căn lề trái */
}
.dmsp-card-desc {
  color: #64748b;
  font-size: 1rem;
  margin-bottom: 1.1rem;
  text-align: left; /* căn lề trái */
}
.dmsp-link {
  color: #2563eb;
  font-weight: 500;
  text-decoration: none;
  cursor: pointer;
  font-size: 1rem;
  transition: color 0.2s;
  text-align: left; /* căn lề trái */
  display: block;
}
.dmsp-link:hover {
  color: #1e293b;
  text-decoration: underline;
}
</style>