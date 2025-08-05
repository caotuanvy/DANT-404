<template>
  <section class="news-section">
    <h2 class="news-title">Tin tức mới nhất</h2>
    <div class="news-grid">
      <router-link
        v-for="news in newsList"
        :key="news.id"
        :to="{ name: 'ChiTietTinTucCongKhaiSlug', params: { slug: news.slug } }"
        class="news-card-link"
      >
        <div class="news-card">
          <div class="news-img-wrapper">
            <img :src="getImageUrl(news.hinh_anh)" :alt="news.tieude" class="news-img" />
            <div class="news-img-overlay"></div>
            <span class="news-category">{{ news.danhMuc?.ten_danh_muc || 'New' }}</span>
          </div>
          <div class="news-info">
            <h3 class="news-headline">{{ news.tieude }}</h3>
            <p class="news-description">{{ news.mo_ta_seo || getNoiDungSnippet(news.noidung, 80) }}</p>
            <div class="news-meta">
              <span class="meta-item">🗓 {{ formatDate(news.ngay_dang) }}</span>
              <span class="meta-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon-eye"><path d="M2.064 12.316a11.536 11.536 0 0 1 19.872.018c.026.046.046.096.064.144-.018.048-.038.098-.064.144a11.536 11.536 0 0 1-19.872.018A.61.61 0 0 0 2 12.5c-.004-.06.002-.12.064-.184Z"/><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg>
                {{ news.luot_xem || 0 }} lượt xem
              </span>
              <span class="meta-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon-heart"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5a5.49 5.49 0 0 1 5.5-5.5c1.54 0 3.04.7 4.04 1.83C12.46 3.7 13.96 3 15.5 3a5.49 5.49 0 0 1 5.5 5.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                {{ news.luot_thich || 0 }}
              </span>
            </div>
          </div>
        </div>
      </router-link>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const newsList = ref([])

const getImageUrl = (path) => {
  if (!path) return 'https://via.placeholder.com/400x225/f0f0f0/888888?text=Image+Not+Found'
  return path.startsWith('http') ? path : `http://localhost:8000/storage/${path}`
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('vi-VN', { year: 'numeric', month: 'numeric', day: 'numeric' })
}

function getNoiDungSnippet(noidung, maxLength = 100) {
  let textContent = '';
  if (typeof noidung === 'string' && noidung.startsWith('<')) {
    const doc = new DOMParser().parseFromString(noidung, 'text/html');
    textContent = doc.body.textContent || "";
  } else if (typeof noidung === 'string') {
    textContent = noidung;
  }
  const trimmedText = textContent.replace(/\s+/g, ' ').trim();
  return trimmedText.length > maxLength ? trimmedText.slice(0, maxLength).trim() + '...' : trimmedText;
}

onMounted(async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/tintuc-cong-khai');
    // Giả định API trả về thêm luot_xem và luot_thich
    newsList.value = res.data.slice(0, 3).map(news => ({
      ...news,
      luot_xem: news.luot_xem || Math.floor(Math.random() * 500) + 100,
      luot_thich: news.luot_thich || Math.floor(Math.random() * 50) + 5
    }));
  } catch (error) {
    console.error('Lỗi khi tải tin tức:', error);
  }
})
</script>

<style scoped>
/* Import font Montserrat cho đồng nhất với các component khác */
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&display=swap');

.news-section {
  padding: 40px 20px;
  max-width: 1200px;
  margin: auto;
  font-family: 'Montserrat', sans-serif;
}

.news-title {
  color: #1663AB;
  font-size: 32px;
  font-weight: bolder;
  text-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
  text-align: left;
  margin-bottom: 30px;
}

.news-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 25px;
}

.news-card-link {
  text-decoration: none;
  color: inherit;
  display: block;
}

.news-card {
  background: #fff;
  border-radius: 12px;
  padding: 0;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  border: none;
  transition: transform 0.3s, box-shadow 0.3s;
  height: 100%;
  overflow: hidden;
}

.news-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.news-img-wrapper {
  position: relative;
  width: 100%;
  height: 200px;
  overflow: hidden;
}

.news-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 12px 12px 0 0;
  transition: transform 0.5s ease, filter 0.5s ease;
}

.news-img-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.2);
  opacity: 0;
  transition: opacity 0.5s ease;
  border-radius: 12px 12px 0 0;
}

.news-card:hover .news-img {
  transform: scale(1.1);
  filter: brightness(0.8);
}

.news-card:hover .news-img-overlay {
  opacity: 1;
}

.news-category {
  position: absolute;
  top: 15px;
  left: 15px;
  background: #1663AB;
  color: #fff;
  font-size: 13px;
  font-weight: 600;
  padding: 6px 12px;
  border-radius: 6px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  z-index: 2;
  text-transform: uppercase;
}

.news-info {
  padding: 18px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.news-headline {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 4px;
  color: #2c3e50;
  line-height: 1.4;
  overflow: hidden;
}

.news-description {
  font-size: 13px;
  color: #6c757d;
  height: 90px;
  overflow: hidden;
  margin-bottom: 15px;
  line-height: 1.5;
}

.news-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
  padding-top: 10px;
  border-top: 1px solid #e0e0e0;
}

.meta-item {
  font-size: 12px;
  color: #888;
  display: flex;
  align-items: center;
  gap: 5px;
}

.icon-eye,
.icon-heart {
  color: #1663AB;
  stroke: currentColor;
}
</style>