<template>
  <div class="news-details-layout">
    <div v-if="loading" class="news-details-container loading-message">
      <i class="fa-solid fa-spinner fa-spin"></i> Đang tải tin tức...
    </div>
    <div v-else-if="error" class="news-details-container error-message">
      <i class="fa-solid fa-exclamation-triangle"></i> Lỗi: {{ error }}
    </div>
    <div v-else-if="!news" class="news-details-container no-data-message">
      <i class="fa-solid fa-info-circle"></i> Không tìm thấy tin tức này hoặc tin tức không khả dụng.
    </div>
    <div v-else class="main-content-area">
      <div class="news-main-section">
        <div class="news-header">
          <div class="news-meta-row">
            <span class="meta-date">
              <i class="fa-regular fa-calendar-alt"></i>
              {{ formatDate(news.ngay_dang) }}
            </span>
            <span class="meta-view">
              <i class="fa-solid fa-eye"></i>
              {{ news.luot_xem }} lượt xem
            </span>
          </div>
          <h1 class="news-title-main">
            {{ news.tieude }}
          </h1>
          <div class="like-button-container">
            <button @click="handleLike" class="like-button" :class="{ 'liked': hasLiked }">
              <i :class="hasLiked ? 'fa-solid fa-heart' : 'fa-regular fa-heart'"></i> 
              {{ hasLiked ? 'Đã thích' : 'Yêu thích' }}
            </button>
          </div>
        </div>
        <img
          class="news-image-main"
          :src="news.hinh_anh ? (news.hinh_anh.startsWith('http') ? news.hinh_anh : `http://localhost:8000/storage/${news.hinh_anh}`) : 'https://via.placeholder.com/800x450/f0f0f0/888888?text=Image+Not+Found'"
          alt="Hình ảnh bài viết"
          @error="handleImageError"
        >
        <div class="news-content-area">
          <div class="news-intro" v-if="news.noidung_gioi_thieu">
            <p>{{ news.noidung_gioi_thieu }}</p>
          </div>
          <div class="news-content-blocks" v-html="getNoiDungHtml(news.noidung)"></div>
          <div class="news-keywords" v-if="news.tu_khoa_seo">
            <p><strong>Từ khóa:</strong> {{ news.tu_khoa_seo }}</p>
          </div>
        </div>
      </div>
      <div>
        <BinhLuanTT />
      </div>
    </div>
    
    <aside class="news-sidebar" v-if="news">
      <div class="sidebar-section sidebar-info-box">
        <div class="info-header">
          <h3><i class="fa-solid fa-bell"></i> Tin bài quản trị</h3>
        </div>
        <ul class="info-list">
          <li>
            <span><i class="fa-solid fa-circle"></i> Ngày đăng:</span>
            <span>{{ formatDate(news.ngay_dang) }}</span>
          </li>
          <li>
            <span><i class="fa-solid fa-circle"></i> Lượt xem:</span>
            <span>{{ news.luot_xem }}</span>
          </li>
          <li v-if="news.danh_muc">
            <span><i class="fa-solid fa-circle"></i> Danh mục:</span>
            <span>{{ news.danh_muc.ten_danh_muc }}</span>
          </li>
        </ul>
      </div>

      <div class="sidebar-section related-news">
        <h3><i class="fa-solid fa-fire-alt"></i> Tin liên quan</h3>
        <div v-if="relatedNews.length > 0" class="related-news-list">
          <div
            class="related-news-item"
            v-for="item in relatedNews"
            :key="item.id"
            @click="goToNewsDetail(item.slug)"
          >
            <img
              class="related-news-img"
              :src="item.hinh_anh ? (item.hinh_anh.startsWith('http') ? item.hinh_anh : `http://localhost:8000/storage/${item.hinh_anh}`) : 'https://via.placeholder.com/90x60/e0e0e0/555555?text=No+Image'"
              :alt="item.tieude"
              @error="handleImageError"
            >
            <div class="related-news-info">
              <a href="javascript:void(0);" class="related-news-title">{{ item.tieude }}</a>
            </div>
          </div>
        </div>
        <div v-else class="no-related-news">
          <i class="fa-solid fa-info-circle"></i> Không có tin liên quan nào.
        </div>
      </div>

      <div class="sidebar-section signup-newsletter">
        <p class="signup-title">Đăng ký nhận tin</p>
        <p class="signup-description">Nhận thông báo sớm nhất về sản phẩm và chương trình khuyến mại</p>
        <div class="signup-form">
          <input type="email" placeholder="Email của bạn">
          <button type="submit">Đăng ký ngay</button>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import BinhLuanTT from '@/components/user/BinhLuanTT.vue';

const route = useRoute()
const router = useRouter()

const news = ref(null)
const relatedNews = ref([])
const loading = ref(true)
const error = ref(null)
const hasLiked = ref(false)

watch(() => route.params.slug, (newSlug, oldSlug) => {
  if (newSlug && newSlug !== oldSlug) {
    fetchNewsDetails(newSlug);
  }
});

onMounted(() => {
  fetchNewsDetails(route.params.slug)
})

async function fetchNewsDetails(slug) {
  loading.value = true;
  news.value = null;
  error.value = null;
  relatedNews.value = [];
  hasLiked.value = false;

  try {
    const newsResponse = await axios.get(`http://localhost:8000/api/tintuc-cong-khai/slug/${slug}`)
    news.value = newsResponse.data;

    // Kiểm tra trạng thái đã thích từ LocalStorage
    const likedNewsIds = JSON.parse(localStorage.getItem('liked_news') || '[]');
    if (likedNewsIds.includes(news.value.id)) {
      hasLiked.value = true;
    } else {
      hasLiked.value = false;
    }

    updateMetaTags(news.value);

    if (news.value && news.value.id && news.value.id_danh_muc_tin_tuc) {
      await fetchRelatedNews(news.value.id, news.value.id_danh_muc_tin_tuc);
    } else {
      console.warn('Không có ID danh mục để tải tin liên quan.');
    }

    window.scrollTo({ top: 0, behavior: 'auto' });

  } catch (err) {
    console.error("Lỗi khi tải chi tiết tin tức:", err);
    error.value = err.response?.data?.message || err.message || "Không thể tải tin tức. Vui lòng thử lại.";
    news.value = null;
    updateMetaTags(null);
  } finally {
    loading.value = false;
  }
}

async function fetchRelatedNews(currentNewsId, categoryId) {
  try {
    const response = await axios.get(`http://localhost:8000/api/tin-lien-quan/${currentNewsId}/${categoryId}`);
    relatedNews.value = response.data;
  } catch (err) {
    console.error("Lỗi khi tải tin liên quan:", err);
    relatedNews.value = [];
  }
}

function goToNewsDetail(newsSlug) {
  router.push({ name: 'ChiTietTinTucCongKhaiSlug', params: { slug: newsSlug } });
}

function formatDate(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString);
  if (isNaN(date.getTime())) {
    const [year, month, day] = dateString.split('-');
    if (year && month && day) {
      const newDate = new Date(year, month - 1, day);
      if (!isNaN(newDate.getTime())) {
        dateString = newDate.toISOString();
      }
    }
  }
  const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateString).toLocaleDateString('vi-VN', options);
}

function handleImageError(event) {
  event.target.src = 'https://via.placeholder.com/800x350/f0f0f0/888888?text=Image+Not+Found';
  event.target.alt = 'Hình ảnh không khả dụng';
}

async function handleLike() {
  if (!news.value) {
    return;
  }

  // Lấy danh sách ID đã thích từ LocalStorage
  const likedNewsIds = JSON.parse(localStorage.getItem('liked_news') || '[]');

  if (hasLiked.value) {
    console.warn('Bạn đã thích bài viết này rồi trong phiên làm việc này.');
    // Có thể thêm logic hủy thích ở đây nếu bạn muốn
    return;
  }

  try {
    // Gọi API tăng lượt thích
    const response = await axios.post(`http://localhost:8000/api/tin-tuc/tang-like/${news.value.id}`);
    
    if (response.data) {
      // Cập nhật trạng thái và lưu vào LocalStorage
      hasLiked.value = true;
      likedNewsIds.push(news.value.id);
      localStorage.setItem('liked_news', JSON.stringify(likedNewsIds));
      
      console.log('Bạn đã thích bài viết này thành công!');
    }
  } catch (err) {
    console.error("Lỗi khi tăng lượt thích:", err);
  }
}

function updateMetaTags(newsData) {
  document.title = newsData && newsData.tieu_de_seo ? newsData.tieu_de_seo : (newsData ? newsData.tieude : 'Tin tức chung');

  let descriptionMeta = document.querySelector('meta[name="description"]');
  if (!descriptionMeta) {
    descriptionMeta = document.createElement('meta');
    descriptionMeta.setAttribute('name', 'description');
    document.head.appendChild(descriptionMeta);
  }
  descriptionMeta.setAttribute('content', newsData && newsData.mo_ta_seo ? newsData.mo_ta_seo : (newsData ? getNoiDungSnippet(newsData.noidung, 160) : 'Trang chi tiết tin tức.'));

  let keywordsMeta = document.querySelector('meta[name="keywords"]');
  if (!keywordsMeta) {
    keywordsMeta = document.createElement('meta');
    keywordsMeta.setAttribute('name', 'keywords');
    document.head.appendChild(keywordsMeta);
  }
  keywordsMeta.setAttribute('content', newsData && newsData.tu_khoa_seo ? newsData.tu_khoa_seo : (newsData ? newsData.tieude : 'tin tức, bài viết, thông tin'));

  updateSocialMetaTags(newsData);
}

function updateSocialMetaTags(newsData) {
  const existingOgTags = document.querySelectorAll('[property^="og:"], [name^="twitter:"]');
  existingOgTags.forEach(tag => tag.remove());

  if (!newsData) {
    return;
  }

  const baseUrl = 'http://localhost:8000';
  const currentUrl = window.location.href;

  const ogTags = [
    { property: 'og:title', content: newsData.tieu_de_seo || newsData.tieude },
    { property: 'og:description', content: newsData.mo_ta_seo || getNoiDungSnippet(newsData.noidung, 160) },
    { property: 'og:type', content: 'article' },
    { property: 'og:url', content: currentUrl },
    { property: 'og:image', content: newsData.hinh_anh ? (newsData.hinh_anh.startsWith('http') ? news.hinh_anh : `${baseUrl}/storage/${newsData.hinh_anh}`) : 'https://via.placeholder.com/1200x630/f0f0f0/888888?text=Image+Not+Found' },
    { property: 'og:site_name', content: 'Tên Trang Web Của Bạn' },
    { property: 'og:locale', content: 'vi_VN' }
  ];

  const twitterTags = [
    { name: 'twitter:card', content: 'summary_large_image' },
    { name: 'twitter:title', content: newsData.tieu_de_seo || newsData.tieude },
    { name: 'twitter:description', content: newsData.mo_ta_seo || getNoiDungSnippet(newsData.noidung, 160) },
    { name: 'twitter:image', content: newsData.hinh_anh ? (newsData.hinh_anh.startsWith('http') ? news.hinh_anh : `${baseUrl}/storage/${newsData.hinh_anh}`) : 'https://via.placeholder.com/1200x630/f0f0f0/888888?text=Image+Not+Found' },
  ];

  ogTags.forEach(tagData => {
    const meta = document.createElement('meta');
    meta.setAttribute('property', tagData.property);
    meta.setAttribute('content', tagData.content);
    document.head.appendChild(meta);
  });

  twitterTags.forEach(tagData => {
    const meta = document.createElement('meta');
    meta.setAttribute('name', tagData.name);
    meta.setAttribute('content', tagData.content);
    document.head.appendChild(meta);
  });
}

function isJSON(str) {
  if (typeof str === 'object' && str !== null) return true
  try {
    const parsed = JSON.parse(str)
    return parsed && typeof parsed === 'object' && parsed !== null
  } catch {
    return false
  }
}

function convertBlocksToHtml(data) {
  if (!data || !data.blocks || !Array.isArray(data.blocks)) return ''
  return data.blocks.map(block => {
    switch (block.type) {
      case 'header':
        return `<h${block.data.level}>${block.data.text}</h${block.data.level}>`
      case 'paragraph':
        return `<p>${block.data.text}</p>`
      case 'list':
        const tag = block.data.style === 'ordered' ? 'ol' : 'ul'
        if (!block.data.items || !Array.isArray(block.data.items)) return '';
        return `<${tag}>${block.data.items.map(item => {
          if (typeof item === 'string') return `<li>${item}</li>`;
          if (typeof item === 'object' && item !== null && item.content) return `<li>${item.content}</li>`;
          return '';
        }).join('')}</${tag}>`;
      case 'quote':
        const citation = block.data.caption ? `<footer class="quote-caption">&mdash; ${block.data.caption}</footer>` : '';
        return `<blockquote class="news-quote"><p>${block.data.text}</p>${citation}</blockquote>`;
      case 'delimiter':
        return `<hr class="news-hr" />`
      case 'image':
        const imageUrl = block.data.file?.url || block.data.url;
        const imageCaption = block.data.caption ? `<figcaption class="image-caption">${block.data.caption}</figcaption>` : '';
        if (imageUrl) {
          return `<figure class="news-image-figure"><img src="${imageUrl}" alt="${block.data.caption || 'Hình ảnh'}" class="news-embedded-image"/>${imageCaption}</figure>`;
        }
        return '';
      case 'raw':
        return block.data.html || '';
      case 'code':
        return `<pre class="news-code-block"><code>${block.data.code}</code></pre>`;
      case 'embed':
        if (block.data.embed) {
          return `<div class="news-embed-container"><iframe src="${block.data.embed}" frameborder="0" allowfullscreen></iframe></div>`;
        }
        return '';
      default:
        console.warn('Unknown EditorJS block type:', block.type, block);
        return ''
    }
  }).join('')
}

function getNoiDungHtml(noidung) {
  if (typeof noidung === 'string' && isJSON(noidung)) {
    try {
      const blockData = JSON.parse(noidung);
      if (blockData && blockData.blocks && Array.isArray(blockData.blocks)) {
        return convertBlocksToHtml(blockData);
      }
    } catch (e) {
      console.warn("Error parsing JSON content, falling back to raw HTML:", e);
      return noidung || '';
    }
  }
  if (typeof noidung === 'object' && noidung !== null && noidung.blocks) {
    return convertBlocksToHtml(noidung);
  }
  return noidung || '';
}

function getNoiDungSnippet(noidung, maxLength = 100) {
  let textContent = '';

  if (typeof noidung === 'string') {
    if (noidung.startsWith('<')) {
      const doc = new DOMParser().parseFromString(noidung, 'text/html');
      textContent = doc.body.textContent || "";
    } else if (isJSON(noidung)) {
      try {
        const parsed = JSON.parse(noidung);
        if (parsed && parsed.blocks && Array.isArray(parsed.blocks)) {
          for (const block of parsed.blocks) {
            if (block.data && block.data.text) {
              textContent += block.data.text + ' ';
            } else if (block.data && block.data.items) {
              textContent += block.data.items.map(item => (typeof item === 'string' ? item : item.content || '')).join(' ') + ' ';
            } else if (block.data && block.data.caption) {
                textContent += block.data.caption + ' ';
            }
            if (textContent.length > maxLength * 1.5) break;
          }
        }
      } catch (e) {
        textContent = noidung;
      }
    } else {
      textContent = noidung;
    }
  } else if (typeof noidung === 'object' && noidung !== null && noidung.blocks) {
    for (const block of noidung.blocks) {
      if (block.data && block.data.text) {
        textContent += block.data.text + ' ';
      } else if (block.data && block.data.items) {
        textContent += block.data.items.map(item => (typeof item === 'string' ? item : item.content || '')).join(' ') + ' ';
      } else if (block.data && block.data.caption) {
        textContent += block.data.caption + ' ';
      }
      if (textContent.length > maxLength * 1.5) break;
    }
  }

  const trimmedText = textContent.replace(/\s+/g, ' ').trim();
  return trimmedText.length > maxLength ? trimmedText.slice(0, maxLength).trim() + '...' : trimmedText;
}
</script>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css');
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

/*
  ========================================================================
  CÁC KIỂU DÁNG CHÍNH ĐƯỢC CẢI THIỆN
  ========================================================================
*/
.news-details-layout {
  display: flex;
  justify-content: center;
  gap: 24px;
  max-width: 1200px;
  margin: 32px auto;
  font-family: 'Roboto', sans-serif;
  padding: 0 15px;
}

.main-content-area {
  flex: 1;
  max-width: 800px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  padding: 25px;
}

.news-header {
  border-bottom: 1px dashed #ddd;
  padding-bottom: 20px;
  margin-bottom: 20px;
}

.news-title-main {
  font-size: 28px;
  font-weight: 700;
  color: #333;
  line-height: 1.4;
  margin: 10px 0;
}

.news-meta-row {
  font-size: 14px;
  color: #888;
  display: flex;
  align-items: center;
  gap: 20px;
}

.news-meta-row i {
  color: #1a73e8; /* Màu xanh của Google */
  margin-right: 5px;
}

/* Kiểu dáng cho nút "Yêu thích" mới */
.like-button-container {
  margin-top: 15px;
}

.like-button {
  background-color: transparent;
  border: 1px solid #ff5252;
  color: #ff5252;
  padding: 10px 20px;
  border-radius: 8px; /* Bo tròn góc */
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
}

.like-button:hover {
  background-color: #ff5252; /* Màu đỏ đậm hơn khi hover */
  color: #fff;
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.like-button.liked {
  background-color: #e04040;
  border-color: #e04040;
  color: #fff;
  transform: none;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.like-button i {
  font-size: 18px;
  transition: all 0.2s;
}

.like-button.liked i {
  color: #fff;
  transform: scale(1.1);
  animation: pulse-effect 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
}

@keyframes pulse-effect {
  0% { transform: scale(1); }
  50% { transform: scale(1.2); }
  100% { transform: scale(1.1); }
}

.like-count {
  font-size: 14px;
  background-color: rgba(255, 255, 255, 0.2);
  padding: 2px 8px;
  border-radius: 4px;
}

.news-image-main {
  width: 100%;
  max-height: 450px;
  object-fit: cover;
  border-radius: 8px;
  margin-top: 20px;
  border: 1px solid #eee;
}

.news-content-area {
  margin-top: 20px;
}

.news-intro p {
  font-size: 16px;
  font-weight: 500;
  color: #555;
  line-height: 1.6;
  padding: 15px;
  background-color: #f8f9fa;
  border-left: 4px solid #1a73e8;
  margin-bottom: 20px;
  border-radius: 4px;
}

/*
  ========================================================================
  KIỂU DÁNG CỦA SIDEBAR
  ========================================================================
*/
.news-sidebar {
  flex: 0 0 300px;
  position: sticky;
  top: 30px;
  height: fit-content;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.sidebar-section {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  padding: 20px;
}

.sidebar-section h3 {
  font-size: 18px;
  color: #333;
  font-weight: 700;
  margin-bottom: 15px;
  border-bottom: 2px solid #1a73e8;
  padding-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.sidebar-section h3 i {
  color: #1a73e8;
}

.info-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.info-list li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
  font-size: 14px;
  color: #555;
}

.info-list li i {
  font-size: 8px;
  color: #1a73e8;
  margin-right: 8px;
}

.related-news-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.related-news-item {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
  background: #f9f9f9;
  padding: 8px;
  border-radius: 6px;
  border: 1px solid #eee;
}

.related-news-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
}

.related-news-img {
  width: 80px;
  height: 55px;
  object-fit: cover;
  border-radius: 4px;
  flex-shrink: 0;
}

.related-news-info {
  flex: 1;
}

.related-news-title {
  font-size: 15px;
  font-weight: 600;
  color: #333;
  text-decoration: none;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.related-news-title:hover {
  color: #1a73e8;
}

.signup-newsletter {
  background-color: #1a73e8;
  color: #fff;
  text-align: center;
  padding: 25px 20px;
}

.signup-title {
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 5px;
}

.signup-description {
  font-size: 14px;
  margin-bottom: 20px;
}

.signup-form {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.signup-form input {
  padding: 10px;
  border-radius: 4px;
  border: none;
  font-size: 14px;
}

.signup-form button {
  padding: 10px;
  background-color: #fbbc05; /* Màu vàng của Google */
  color: #333;
  font-weight: 700;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.signup-form button:hover {
  background-color: #f9ab00;
}

/*
  ========================================================================
  MEDIA QUERIES (RESPONSIVE)
  ========================================================================
*/
@media (max-width: 992px) {
  .news-details-layout {
    flex-direction: column;
    gap: 20px;
  }
  .news-sidebar {
    position: static;
    flex: 1;
    width: 100%;
  }
  .main-content-area {
    max-width: none;
  }
}

@media (max-width: 768px) {
  .news-details-layout {
    padding: 0 10px;
  }
  .news-title-main {
    font-size: 24px;
  }
  .news-meta-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
  }
  .related-news-item {
    flex-direction: row;
    align-items: center;
  }
  .related-news-title {
    font-size: 14px;
  }
}
</style>