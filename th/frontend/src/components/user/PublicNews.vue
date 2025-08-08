<template>
  <div class="vohop-container">
    <div class="vohop-main-content">
      <div class="vohop-main-header">
        <h1 class="vohop-title">Tin tức mới nhất</h1>
        <div class="vohop-search-box">
          <input type="text" placeholder="Q Tìm kiếm tin tức..." v-model="searchQuery" @keyup.enter="handleSearch" />
        </div>
      </div>
      <div class="vohop-news-grid">
        <div class="vohop-news-card" v-for="item in displayedNews" :key="item.id" @click="goToNewsDetail(item)">
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
            <div class="vohop-tags">
              <button
                v-if="getFirstTag(item.tu_khoa_seo)"
                @click.stop="handleSelectTag(getFirstTag(item.tu_khoa_seo))"
              >
                #{{ getFirstTag(item.tu_khoa_seo) }}
              </button>
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
        <div class="vohop-tags-grid">
          <button
            v-for="tag in displayedTags"
            :key="tag"
            @click="handleSelectTag(tag)"
            :class="{ active: activeTag === tag }"
          >
            #{{ tag }}
          </button>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'

const newsList = ref([])
const allNews = ref([]) // Lưu trữ toàn bộ tin tức để xáo trộn
const activeDanhMuc = ref(null)
const danhMucList = ref([])
const tinNoiBatList = ref([])
const tagsList = ref([])
const allTags = ref([]) // Lưu trữ toàn bộ tags để xáo trộn
const activeTag = ref(null)
const searchQuery = ref('')

const router = useRouter()

// Hàm xáo trộn mảng
function shuffleArray(array) {
  for (let i = array.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [array[i], array[j]] = [array[j], array[i]];
  }
  return array;
}

// Computed property để giới hạn số lượng tin tức hiển thị và xáo trộn
const displayedNews = computed(() => shuffleArray([...allNews.value]).slice(0, 10))

// Computed property để giới hạn số lượng tags hiển thị ở sidebar và xáo trộn
const displayedTags = computed(() => shuffleArray([...allTags.value]).slice(0, 10))

onMounted(async () => {
  await fetchDanhMucList()
  await fetchAllNews()
  await fetchTinNoiBat()
  await fetchTags()
})

// Hàm mới: Tải danh sách tags từ API
async function fetchTags() {
  try {
    const res = await fetch('http://localhost:8000/api/tags')
    const data = await res.json()
    allTags.value = data // Lưu toàn bộ tags
  } catch (err) {
    console.error("Lỗi khi tải danh sách tags:", err)
    allTags.value = []
  }
}

// Hàm mới: Xử lý khi click vào tag
async function handleSelectTag(tag) {
  if (activeTag.value === tag) {
    activeTag.value = null
    await fetchAllNews()
  } else {
    activeTag.value = tag
    activeDanhMuc.value = null // Bỏ chọn danh mục khi chọn tag
    try {
      const res = await fetch(`http://localhost:8000/api/tin-tuc-theo-tag/${tag}`)
      allNews.value = await res.json() // Cập nhật allNews thay vì newsList
    } catch (err) {
      console.error("Lỗi khi tải tin tức theo tag:", err)
      allNews.value = []
    }
  }
}

// Hàm mới: Xử lý tìm kiếm
async function handleSearch() {
  if (searchQuery.value.trim() === '') {
    await fetchAllNews();
    return;
  }
  // Giả định có một API tìm kiếm theo tiêu đề hoặc nội dung
  // Hiện tại tôi sẽ sử dụng API lọc theo tag nếu bạn gõ một tag vào
  await handleSelectTag(searchQuery.value);
}

// Hàm mới: Lấy một từ khóa đầu tiên của một bài viết
function getFirstTag(tagsStr) {
  if (!tagsStr) return null;
  const tags = tagsStr.split(',').map(tag => tag.trim()).filter(t => t);
  return tags.length > 0 ? tags[0] : null;
}

// Hàm cũ: Lấy danh mục
async function fetchDanhMucList() {
  try {
    const res = await fetch('http://localhost:8000/api/danh-muc-tin-tuc')
    danhMucList.value = await res.json()
  } catch (err) {
    console.error("Lỗi khi tải danh mục:", err)
    danhMucList.value = []
  }
}

// Hàm cũ: Lấy tất cả tin tức
async function fetchAllNews() {
  try {
    const res = await fetch('http://localhost:8000/api/tintuc-cong-khai')
    allNews.value = await res.json() // Lưu toàn bộ tin tức
    activeDanhMuc.value = null
    activeTag.value = null
  } catch (err) {
    console.error("Lỗi khi tải tất cả tin tức công khai:", err)
    allNews.value = []
  }
}

// Hàm cũ: Xử lý khi chọn danh mục
async function handleSelectDanhMuc(id) {
  activeDanhMuc.value = id
  activeTag.value = null // Bỏ chọn tag khi chọn danh mục
  try {
    const res = await fetch(`http://localhost:8000/api/tintuc-cong-khai/danh-muc/${id}`)
    allNews.value = await res.json() // Cập nhật allNews thay vì newsList
  } catch (err) {
    console.error("Lỗi khi tải tin tức theo danh mục:", err)
    allNews.value = []
  }
}

// Hàm cũ: Lấy tin nổi bật
async function fetchTinNoiBat() {
  try {
    const res = await fetch('http://localhost:8000/api/tin-noi-bat')
    tinNoiBatList.value = await res.json()
  } catch (err) {
    console.error("Lỗi khi tải tin nổi bật:", err)
    tinNoiBatList.value = []
  }
}

// Hàm cũ: Chuyển đến trang chi tiết
function goToNewsDetail(news) {
  router.push({ name: 'ChiTietTinTucCongKhaiSlug', params: { slug: news.slug } })
}

// Hàm cũ: Định dạng ngày
function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, '/')
}

// Hàm cũ: Lấy đoạn trích nội dung
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

// Hàm cũ: Lấy tên danh mục
function getCategoryName(id) {
  const category = danhMucList.value.find(c => c.id_danh_muc_tin_tuc === id);
  return category ? category.ten_danh_muc : 'Khác';
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
  font-family: 'Arial', sans-serif;
}

@media (max-width: 992px) {
  .vohop-container {
    flex-direction: column;
    padding: 16px;
  }
  .vohop-sidebar {
    width: 100%;
  }
  .vohop-main-header {
    flex-direction: column;
    align-items: flex-start;
  }
  .vohop-search-box {
    width: 100%;
    margin-top: 10px;
  }
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
  opacity: 0.5;
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
  height: 200px;
}

.vohop-card-image-container img {
  width: 100%;
  height: 100%;
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
  background-color: #33ccff;
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
  justify-content: space-between;
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

/* Hiệu ứng mới cho "TIN XEM NHIỀU NHẤT" */
.vohop-popular-post {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
  cursor: pointer;
  transition: transform 0.2s ease-in-out, opacity 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
  border-radius: 8px; 
  padding: 8px; 
}

.vohop-popular-post:hover {
  transform: translateY(-3px);
  opacity: 0.9;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.vohop-popular-post img {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 4px;
  transition: transform 0.2s ease-in-out;
}

.vohop-popular-post:hover img {
  transform: scale(1.05);
}

.vohop-popular-post span {
  font-size: 0.9rem;
  color: var(--text-dark);
  line-height: 1.4;
  transition: color 0.2s ease-in-out;
}

.vohop-popular-post:hover span {
  color: var(--primary-color);
}
/* Kết thúc hiệu ứng mới */

.vohop-tags-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  padding-top: 8px;
}

.vohop-tags-grid button {
  border: 1px solid #cceeff;
  background-color: #f0f8ff;
  color: #004080;
  border-radius: 20px;
  padding: 8px 16px;
  font-size: 0.85rem;
  cursor: pointer;
  transition: background-color 0.2s, color 0.2s, transform 0.2s;
}

.vohop-tags-grid button:hover {
  background-color: #cceeff;
  transform: translateY(-2px);
}

.vohop-tags-grid button.active {
  background-color: #03A2DC;
  color: white;
  border-color: var(--primary-color);
}

/* Căn chỉnh lại tags trong card */
.vohop-news-card .vohop-tags {
  display: flex;
  gap: 8px;
  margin-top: 10px;
}

.vohop-news-card .vohop-tags button {
  border: none;
  background-color: #e2f5ff;
  color: #0d6efd;
  border-radius: 20px;
  padding: 4px 10px;
  font-size: 0.75rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.vohop-news-card .vohop-tags button:hover {
  background-color: #cceeff;
}
</style>