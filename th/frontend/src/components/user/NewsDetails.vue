<template>
  <div class="news-details-layout">
    <!-- Bên trái: Nội dung chi tiết tin tức -->
    <div class="news-details-container" v-if="news">
      <div class="news-banner"></div>
      <h1 class="news-title">
        <i class="fa-solid fa-bullhorn"></i>
        {{ news.tieude }}
      </h1>
      <div class="news-meta">
        <span class="meta-date">
          <i class="fa-regular fa-calendar"></i>
          {{ news.ngay_dang }}
        </span>
        <span class="meta-view">
          <i class="fa-solid fa-eye"></i>
          {{ news.luot_xem }} lượt xem
        </span>
      </div>
      <img
        class="news-image"
        :src="news.hinh_anh ? (news.hinh_anh.startsWith('http') ? news.hinh_anh : `http://localhost:8000/storage/${news.hinh_anh}`) : 'https://via.placeholder.com/800x350'"
        alt="Hình ảnh"
      >
      <div class="news-content" v-html="getNoiDungHtml(news.noidung)"></div>
    </div>

    <!-- Bên phải: Tin liên quan có hình và nội dung -->
    <aside class="news-sidebar">
      <div class="sidebar-section">
        <h3><i class="fa-solid fa-link"></i> Tin liên quan</h3>
        <div class="related-news-list">
          <div class="related-news-item" v-for="i in 3" :key="i">
            <img class="related-news-img" src="https://via.placeholder.com/90x60" alt="Hình liên quan">
            <div class="related-news-info">
              <a href="#" class="related-news-title">Tiêu đề tin liên quan {{ i }}</a>
              <p class="related-news-desc">
                Mô tả ngắn gọn về tin liên quan số {{ i }}. Nội dung này giúp người đọc biết sơ lược về tin.
              </p>
            </div>
          </div>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const news = ref(null)

onMounted(() => {
  fetchNewsDetails()
})

async function fetchNewsDetails() {
  try {
    const res = await fetch(`http://localhost:8000/api/tintuc-cong-khai/${route.params.id}`)
    news.value = await res.json()
  } catch {
    news.value = null
  }
}

// Hàm chuyển EditorJS sang HTML nếu cần
function isJSON(str) {
  if (typeof str === 'object' && str !== null) return true
  try {
    const parsed = JSON.parse(str)
    return parsed && typeof parsed === 'object'
  } catch {
    return false
  }
}
function convertBlocksToHtml(data) {
  if (!data || !data.blocks) return ''
  return data.blocks.map(block => {
    switch (block.type) {
      case 'header':
        return `<h${block.data.level}>${block.data.text}</h${block.data.level}>`
      case 'paragraph':
        return `<p>${block.data.text}</p>`
      case 'list':
        const tag = block.data.style === 'ordered' ? 'ol' : 'ul'
        return `<${tag}>${block.data.items.map(i => {
          if (typeof i === 'string') return `<li>${i}</li>`
          if (typeof i === 'object' && i !== null) {
            // Lấy tất cả value là string trong object, nối lại
            const values = Object.values(i).filter(v => typeof v === 'string')
            if (values.length) return `<li>${values.join(' ')}</li>`
            return ''
          }
          return ''
        }).join('')}</${tag}>`
      case 'quote':
        return `<blockquote>${block.data.text}</blockquote>`
      case 'delimiter':
        return `<hr />`
      default:
        return ''
    }
  }).join('')
}
function getNoiDungHtml(noidung) {
  // Nếu là object (EditorJS), dùng luôn
  if (typeof noidung === 'object' && noidung !== null && noidung.blocks) {
    return convertBlocksToHtml(noidung)
  }
  // Nếu là string JSON, parse ra object
  if (typeof noidung === 'string' && isJSON(noidung)) {
    const blockData = JSON.parse(noidung)
    if (blockData.blocks) return convertBlocksToHtml(blockData)
  }
  // Nếu là object nhưng không phải EditorJS, trả về rỗng (không hiển thị gì)
  if (typeof noidung === 'object') {
    return ''
  }
  // Nếu là string HTML thường
  return noidung || ''
}
</script>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css');
.news-details-layout {
  display: flex;
  gap: 36px;
  max-width: 1200px;
  margin: 40px auto 32px auto;
  align-items: flex-start;
}
.news-sidebar {
  flex: 1.1;
  min-width: 280px;
  background: #f7f7f7;
  border-radius: 12px;
  box-shadow: 0 2px 12px #0001;
  padding: 24px 18px 18px 18px;
  position: sticky;
  top: 32px;
  height: fit-content;
}
.sidebar-section {
  margin-bottom: 28px;
}
.sidebar-section h3 {
  font-size: 1.08rem;
  color: #444;
  font-weight: 700;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 7px;
}
.related-news-list {
  display: flex;
  flex-direction: column;
  gap: 18px;
}
.related-news-item {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 1px 6px #0001;
  padding: 10px 10px 10px 10px;
  transition: box-shadow 0.2s;
}
.related-news-item:hover {
  box-shadow: 0 4px 16px #1976d211;
}
.related-news-img {
  width: 90px;
  height: 60px;
  object-fit: cover;
  border-radius: 6px;
  background: #e3eaf2;
  flex-shrink: 0;
}
.related-news-info {
  flex: 1;
  min-width: 0;
}
.related-news-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1976d2;
  text-decoration: none;
  margin-bottom: 4px;
  display: block;
  transition: color 0.2s;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.related-news-title:hover {
  color: #0d47a1;
}
.related-news-desc {
  font-size: 0.97rem;
  color: #444;
  margin: 0;
  line-height: 1.5;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}
.news-details-container {
  flex: 2.2;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 24px #0001;
  padding: 0 0 32px 0;
  position: relative;
  overflow: hidden;
}
.news-banner {
  height: 48px;
  background: #e3eaf2;
  border-radius: 12px 12px 0 0;
  box-shadow: 0 2px 8px #0001;
}
.news-title {
  font-size: 2rem;
  font-weight: 700;
  color: #222;
  margin: 28px 0 12px 0;
  letter-spacing: 1px;
  display: flex;
  align-items: center;
  gap: 12px;
  padding-left: 36px;
}
.news-meta {
  color: #888;
  font-size: 15px;
  margin-bottom: 18px;
  display: flex;
  gap: 28px;
  align-items: center;
  font-weight: 500;
  padding-left: 36px;
}
.news-meta i {
  margin-right: 6px;
}
.news-image {
  width: calc(100% - 72px);
  max-height: 340px;
  object-fit: cover;
  border-radius: 8px;
  margin: 0 36px 22px 36px;
  border: 1px solid #e3eaf2;
  background: #f7f7f7;
  box-shadow: 0 2px 8px #0001;
  transition: transform 0.3s;
  display: block;
}
.news-image:hover {
  transform: scale(1.01) rotate(-1deg);
  box-shadow: 0 8px 24px #0002;
}
.news-content {
  font-size: 1.08rem;
  color: #222;
  line-height: 1.8;
  background: #fff;
  border-radius: 8px;
  padding: 28px 36px 18px 36px;
  box-shadow: 0 2px 8px #0001;
  margin-top: 10px;
}
.news-content h2 {
  color: #1976d2;
  margin-top: 16px;
  font-size: 1.15rem;
  font-weight: 700;
  margin-bottom: 10px;
  letter-spacing: 0.5px;
}
.news-content ul {
  margin: 12px 0 12px 24px;
  padding-left: 0;
}
.news-content ul li {
  margin-bottom: 7px;
  position: relative;
  padding-left: 18px;
}
.news-content ul li::before {
  content: '';
  position: absolute;
  left: 0;
  top: 10px;
  width: 7px;
  height: 7px;
  background: #b0bec5;
  border-radius: 50%;
}
.news-content blockquote {
  border-left: 4px solid #b0bec5;
  padding-left: 16px;
  color: #1976d2;
  font-style: italic;
  margin: 18px 0;
  background: #f7f7f7;
  border-radius: 6px;
  font-size: 1.02rem;
  box-shadow: 0 1px 6px #0001;
}
.news-content hr {
  border: none;
  border-top: 1px dashed #b0bec5;
  margin: 22px 0;
}
@media (max-width: 1100px) {
  .news-details-layout {
    flex-direction: column;
    gap: 0;
  }
  .news-sidebar {
    margin-bottom: 24px;
    position: static;
    width: 100%;
  }
  .news-details-container {
    width: 100%;
  }
}
@media (max-width: 900px) {
  .news-details-container {
    padding: 0 0 14px 0;
  }
  .news-title,
  .news-meta,
  .news-content {
    padding-left: 6vw;
    padding-right: 6vw;
  }
  .news-image {
    margin-left: 6vw;
    margin-right: 6vw;
    width: calc(100% - 12vw);
  }
  .news-content {
    padding: 16px 6vw 10px 6vw;
  }
}
</style>