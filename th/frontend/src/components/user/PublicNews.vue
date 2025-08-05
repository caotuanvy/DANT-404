<template>
  <div class="vohop-container">
    <div class="vohop-main-content">
      <div class="vohop-main-header">
        <h1 class="vohop-title">Tin tức mới nhất</h1>
        <div class="vohop-search-box">
          <input type="text" placeholder="Q Tìm kiếm tin tức..." />
        </div>
      </div>
      <div class="vohop-news-grid">
        <div class="vohop-news-card" v-for="item in newsList" :key="item.id" @click="goToNewsDetail(item)">
          <div class="vohop-card-image-container">
            <img
              :src="item.hinh_anh
                ? (item.hinh_anh.startsWith('http')
                    ? item.hinh_anh
                    : `http://localhost:8000/storage/${item.hinh_anh}`)
                : 'https://via.placeholder.com/400x225'"
              alt="Hình ảnh tin tức"
            />
            <span class="vohop-category-tag">
              {{ getCategoryName(item.id_danh_muc_tin_tuc) }}
            </span>
          </div>
          <div class="vohop-card-body">
            <h3>{{ item.tieude }}</h3>
            <p v-html="getNoiDungSnippet(item.noidung)"></p>
          </div>
          <div class="vohop-card-footer">
            <div class="vohop-footer-info">
              <span><i class="fa-regular fa-calendar"></i> {{ formatDate(item.ngay_dang) }}</span>
              <span><i class="fa-solid fa-eye"></i> {{ item.luot_xem || 0 }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <aside class="vohop-sidebar">
      <div class="vohop-sidebar-section">
        <h3 class="vohop-sidebar-title"><i class="fa-solid fa-bars-staggered"></i> DANH MỤC</h3>
        <ul>
          <li
            v-for="item in danhMucList"
            :key="item.id_danh_muc_tin_tuc"
            @click="handleSelectDanhMuc(item.id_danh_muc_tin_tuc)"
            :class="{ active: activeDanhMuc === item.id_danh_muc_tin_tuc }"
          >
            <i class="fa-solid fa-angle-right"></i> {{ item.ten_danh_muc }}
          </li>
        </ul>
      </div>

      <div class="vohop-sidebar-section">
        <h3 class="vohop-sidebar-title"><i class="fa-solid fa-fire"></i> TIN XEM NHIỀU NHẤT</h3>
        <div
          class="vohop-popular-post"
          v-for="tin in tinNoiBatList"
          :key="tin.id"
          @click="goToNewsDetail(tin)"
        >
          <img :src="tin.hinh_anh ? (tin.hinh_anh.startsWith('http') ? tin.hinh_anh : `http://localhost:8000/storage/${tin.hinh_anh}`) : 'https://via.placeholder.com/60x60'" alt="" />
          <span>{{ tin.tieude }}</span>
        </div>
      </div>

      <div class="vohop-sidebar-section">
        <h3 class="vohop-sidebar-title"><i class="fa-solid fa-tags"></i> TAGS</h3>
        <div class="vohop-tags">
          <button v-for="(tag, index) in tagsList" :key="tag">
            #{{ tag }}
          </button>
        </div>
      </div>
    </aside>  
  </div>
</template>



<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'


const newsList = ref([])
const activeDanhMuc = ref(null)
const danhMucList = ref([])
const tinNoiBatList = ref([])
const tagsList = ref(['burger', 'khuyến mãi', 'pizza', 'gà rán'])

const router = useRouter()

onMounted(async () => {
  await fetchDanhMucList()
  await fetchAllNews()
  await fetchTinNoiBat()
})

async function fetchDanhMucList() {
  try {
    const res = await fetch('http://localhost:8000/api/danh-muc-tin-tuc')
    danhMucList.value = await res.json()
  } catch (err) {
    console.error("Lỗi khi tải danh mục:", err)
    danhMucList.value = []
  }
}

async function fetchAllNews() {
  try {
    const res = await fetch('http://localhost:8000/api/tintuc-cong-khai')
    newsList.value = await res.json()
  } catch (err) {
    console.error("Lỗi khi tải tất cả tin tức công khai:", err)
    newsList.value = []
  }
}

async function handleSelectDanhMuc(id) {
  activeDanhMuc.value = id
  try {
    const res = await fetch(`http://localhost:8000/api/tintuc-cong-khai/danh-muc/${id}`)
    newsList.value = await res.json()
  } catch (err) {
    console.error("Lỗi khi tải tin tức theo danh mục:", err)
    newsList.value = []
  }
}

async function fetchTinNoiBat() {
  try {
    const res = await fetch('http://localhost:8000/api/tin-noi-bat')
    tinNoiBatList.value = await res.json()
  } catch (err) {
    console.error("Lỗi khi tải tin nổi bật:", err)
    tinNoiBatList.value = []
  }
}

function goToNewsDetail(news) {
  router.push({ name: 'ChiTietTinTucCongKhaiSlug', params: { slug: news.slug } })
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, '/')
}

function getNoiDungSnippet(noidung) {
  if (typeof noidung === 'string' && noidung.startsWith('<')) {
    const doc = new DOMParser().parseFromString(noidung, 'text/html')
    const textContent = doc.body.textContent || ""
    return textContent.length > 80 ? textContent.slice(0, 80) + '...' : textContent
  }
  try {
    const parsed = JSON.parse(noidung)
    if (parsed && typeof parsed === 'object' && parsed.blocks) {
      let snippet = ''
      for (const block of parsed.blocks) {
        if (block.type === 'paragraph' || block.type === 'header') {
          snippet += block.data.text + ' '
          if (snippet.length > 80) break
        }
      }
      return snippet.slice(0, 80) + (snippet.length > 80 ? '...' : '')
    }
  } catch (e) {
    // Không phải JSON hợp lệ
  }
  return noidung ? (noidung.length > 80 ? noidung.slice(0, 80) + '...' : noidung) : ''
}

// Hàm để lấy tên danh mục từ id
function getCategoryName(id) {
  const category = danhMucList.value.find(c => c.id_danh_muc_tin_tuc === id);
  return category ? category.ten_danh_muc : 'Khác';
}

// Hàm lấy màu sắc cho tags
function getTagColor(index) {
  const colors = ['#ADD8E6', '#ADD8E6', '#ADD8E6', '#ADD8E6']; // Thay đổi thành màu xanh nhạt
  return colors[index % colors.length];
}
</script>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css');

:root {
  --primary-color: #0d6efd;
  --secondary-color: #6c757d;
  --background-light: #f8f9fa;
  --border-color: #dee2e6;
  --box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  --text-dark: #212529;
  --text-light: #adb5bd;
}

.vohop-container {
  display: flex;
  max-width: 1200px;
  margin: 30px auto;
  gap: 24px;
  padding: 24px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
}

.vohop-main-content {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.vohop-main-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.vohop-title {
  font-size: 2rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0;
}

.vohop-search-box {
  display: flex;
  align-items: center;
  border-bottom: 1px solid var(--border-color);
  padding: 4px 0;
}

.vohop-search-box input {
  border: none;
  outline: none;
  padding: 4px;
  font-size: 0.9rem;
  flex-grow: 1;
  background: transparent;
}

.vohop-search-box input::placeholder {
  color: var(--text-light);
}

.vohop-news-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 24px;
}

.vohop-news-card {
  border-radius: 8px;
  overflow: hidden;
  box-shadow: var(--box-shadow);
  border: 1px solid var(--border-color);
  display: flex;
  flex-direction: column;
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
  cursor: pointer;
}

.vohop-news-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
}

.vohop-card-image-container {
  position: relative;
}

.vohop-card-image-container img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.vohop-category-tag {
  position: absolute;
  top: 10px;
  left: 10px;
  color: white;
  padding: 5px 12px;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: bold;
  background-color: #33ccff; /* Màu xanh nhạt */
}

.vohop-card-body {
  padding: 16px;
  flex-grow: 1;
}

.vohop-card-body h3 {
  font-size: 1.2rem;
  font-weight: bold;
  margin: 0 0 10px;
  color: var(--text-dark);
}

.vohop-card-body p {
  font-size: 0.9rem;
  color: var(--secondary-color);
  line-height: 1.5;
  margin: 0;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.vohop-card-footer {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  padding: 12px 16px;
  border-top: 1px solid var(--border-color);
  background: #f9f9f9;
}

.vohop-footer-info {
  display: flex;
  gap: 15px;
  font-size: 0.8rem;
  color: var(--text-light);
}

.vohop-footer-info span {
  display: flex;
  align-items: center;
  gap: 5px;
}

.vohop-sidebar {
  width: 300px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.vohop-sidebar-section {
  background: var(--background-light);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 16px;
}

.vohop-sidebar-title {
  font-size: 1rem;
  font-weight: bold;
  margin-top: 0;
  margin-bottom: 12px;
  padding-bottom: 8px;
  border-bottom: 2px solid var(--border-color);
  display: flex;
  align-items: center;
  gap: 10px;
  color: #0d6efd;
}

.vohop-sidebar-title .fa-solid {
  color: #0d6efd;
}

.vohop-sidebar-section ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.vohop-sidebar-section li {
  color: var(--text-dark);
  font-size: 0.9rem;
  padding: 8px 0;
  cursor: pointer;
  border-bottom: 1px solid var(--border-color);
  transition: color 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.vohop-sidebar-section li:last-child {
  border-bottom: none;
}

.vohop-sidebar-section li:hover,
.vohop-sidebar-section li.active {
  color: var(--primary-color);
}

.vohop-popular-post {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
  cursor: pointer;
  transition: opacity 0.2s;
}

.vohop-popular-post:hover {
  opacity: 0.8;
}

.vohop-popular-post img {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 4px;
}

.vohop-popular-post span {
  font-size: 0.9rem;
  color: var(--text-dark);
  line-height: 1.4;
}

.vohop-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.vohop-tags button {
  border: none;
  color: white;
  border-radius: 20px;
  padding: 6px 14px;
  font-size: 0.8rem;
  cursor: pointer;
  transition: opacity 0.2s;
  background-color: #ADD8E6; /* Màu xanh nhạt */
}

.vohop-tags button:hover {
  opacity: 0.8;
}
</style>