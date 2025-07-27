<template>
  <section class="news-section">
    <h2 class="news-title">Tin tức mới nhất</h2>
    <div class="news-grid">
      <router-link
        v-for="news in newsList"
        :key="news.id"
        :to="{ name: 'ChiTietTinTucCongKhai', params: { id: news.id } }"
        class="news-card-link"
      >
        <div class="news-card">
          <div class="news-img-wrapper">
            <img :src="getImageUrl(news.hinh_anh)" :alt="news.tieude" class="news-img" />
            <span class="news-category">{{ news.danhMuc?.ten_danh_muc || 'New' }}</span>
          </div>
          <div class="news-info">
            <h3 class="news-headline">{{ news.tieude }}</h3>
            <p class="news-description">{{ news.mo_ta_seo || news.noidung?.slice(0, 80) + '...' }}</p>
            <div class="news-date">
              🗓 {{ formatDate(news.ngay_dang) }}
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
  return path ? `http://localhost:8000/storage/${path}` : '/images/default-news.png'
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('vi-VN')
}

onMounted(async () => {
  const res = await axios.get('http://localhost:8000/api/tintuc-cong-khai')
  newsList.value = res.data.slice(0, 6)
})
</script>

<style scoped>
.news-section {
  padding: 40px 20px;
  max-width: 1280px;
  margin: auto;
}
.news-title {
  font-size: 2rem;
  font-weight: bold;
  margin-bottom: 30px;
  color: #1663AB;
  text-align: left;
  letter-spacing: 1px;
}
.news-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 32px;
}
.news-card-link {
  text-decoration: none;
  color: inherit;
  display: block;
  transition: transform 0.2s, box-shadow 0.2s;
}
.news-card-link:hover .news-card {
  transform: translateY(-8px) scale(1.03);
  box-shadow: 0 8px 32px rgba(22, 99, 171, 0.15);
}
.news-card {
  background: #fff;
  border-radius: 16px;
  padding: 0;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  display: flex;
  flex-direction: column;
  border: 1px solid #e0e0e0;
  transition: transform 0.2s, box-shadow 0.2s;
  height: 100%;
  overflow: hidden;
}
.news-img-wrapper {
  position: relative;
  width: 100%;
  height: 180px;
  overflow: hidden;
}
.news-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
  transition: transform 0.3s;
}
.news-card-link:hover .news-img {
  transform: scale(1.07);
}
.news-category {
  position: absolute;
  top: 12px;
  left: 12px;
  background: #1663AB;
  color: #fff;
  font-size: 13px;
  font-weight: 500;
  padding: 4px 14px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(22,99,171,0.08);
  z-index: 2;
}
.news-info {
  padding: 18px 16px 16px 16px;
  display: flex;
  flex-direction: column;
  flex: 1;
}
.news-headline {
  font-size: 18px;
  font-weight: 700;
  margin-bottom: 8px;
  color: #222;
  line-height: 1.3;
  min-height: 48px;
}
.news-description {
  font-size: 14px;
  color: #555;
  min-height: 36px;
  margin-bottom: 12px;
  line-height: 1.5;
}
.news-date {
  font-size: 13px;
  color: #888;
  margin-top: auto;
  text-align: right;
}
</style>    