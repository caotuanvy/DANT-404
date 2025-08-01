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
    <div class="news-details-container" v-else>
      <div class="news-banner"></div>
      <h1 class="news-title">
        <i class="fa-solid fa-bullhorn"></i>
        {{ news.tieude }}
      </h1>
      <div class="news-meta">
        <span class="meta-date">
          <i class="fa-regular fa-calendar"></i>
          {{ formatDate(news.ngay_dang) }}
        </span>
        <span class="meta-view">
          <i class="fa-solid fa-eye"></i>
          {{ news.luot_xem }} lượt xem
        </span>
        <span class="meta-category" v-if="news.danh_muc">
            <i class="fa-solid fa-tag"></i>
            {{ news.danh_muc.ten_danh_muc }}
          </span>
      </div>
      <img
        class="news-image"
        :src="news.hinh_anh ? (news.hinh_anh.startsWith('http') ? news.hinh_anh : `http://localhost:8000/storage/${news.hinh_anh}`) : 'https://via.placeholder.com/800x350/f0f0f0/888888?text=Image+Not+Found'"
        alt="Hình ảnh"
        @error="handleImageError"
      >
      <div class="news-content" v-html="getNoiDungHtml(news.noidung)"></div>
    </div>

    <aside class="news-sidebar">
      <div class="sidebar-section">
        <h3><i class="fa-solid fa-fire"></i> Tin liên quan</h3> <div v-if="relatedNews.length > 0" class="related-news-list">
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
              <p class="related-news-desc">
                {{ getNoiDungSnippet(item.noidung, 70) }}
              </p>
            </div>
          </div>
        </div>
        <div v-else class="no-related-news">
          <i class="fa-solid fa-info-circle"></i> Không có tin liên quan nào.
        </div>
      </div>
    </aside>
  </div>
  <div>
    <BinhLuanTT />
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

  try {
    const newsResponse = await axios.get(`http://localhost:8000/api/tintuc-cong-khai/slug/${slug}`)
    news.value = newsResponse.data;

    // --- Cập nhật SEO sau khi tải tin tức ---
    updateMetaTags(news.value);
    // ----------------------------------------

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
    // --- Xóa hoặc đặt lại các thẻ meta khi có lỗi ---
    updateMetaTags(null);
    // ------------------------------------------------
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

// --- HÀM MỚI ĐỂ CẬP NHẬT CÁC THẺ META SEO ---
function updateMetaTags(newsData) {
  // Đặt tiêu đề trang
  document.title = newsData && newsData.tieu_de_seo ? newsData.tieu_de_seo : (newsData ? newsData.tieude : 'Tin tức chung');

  // Lấy hoặc tạo thẻ meta description
  let descriptionMeta = document.querySelector('meta[name="description"]');
  if (!descriptionMeta) {
    descriptionMeta = document.createElement('meta');
    descriptionMeta.setAttribute('name', 'description');
    document.head.appendChild(descriptionMeta);
  }
  descriptionMeta.setAttribute('content', newsData && newsData.mo_ta_seo ? newsData.mo_ta_seo : (newsData ? getNoiDungSnippet(newsData.noidung, 160) : 'Trang chi tiết tin tức.'));

  // Lấy hoặc tạo thẻ meta keywords
  let keywordsMeta = document.querySelector('meta[name="keywords"]');
  if (!keywordsMeta) {
    keywordsMeta = document.createElement('meta');
    keywordsMeta.setAttribute('name', 'keywords');
    document.head.appendChild(keywordsMeta);
  }
  keywordsMeta.setAttribute('content', newsData && newsData.tu_khoa_seo ? newsData.tu_khoa_seo : (newsData ? newsData.tieude : 'tin tức, bài viết, thông tin'));

  // Cập nhật Open Graph (OG) và Twitter Card tags cho chia sẻ trên mạng xã hội
  updateSocialMetaTags(newsData);
}

// --- HÀM MỚI CHO CÁC THẺ META MẠNG XÃ HỘI (OPEN GRAPH & TWITTER CARD) ---
function updateSocialMetaTags(newsData) {
  // Xóa các thẻ OG/Twitter cũ để tránh trùng lặp khi chuyển trang
  const existingOgTags = document.querySelectorAll('[property^="og:"], [name^="twitter:"]');
  existingOgTags.forEach(tag => tag.remove());

  if (!newsData) {
    return; // Không có dữ liệu tin tức, không tạo thẻ OG/Twitter
  }

  const baseUrl = 'http://localhost:8000'; // Đặt base URL của ứng dụng Laravel của bạn
  const currentUrl = window.location.href; // URL hiện tại của trang tin tức

  // Thẻ Open Graph (Facebook, Zalo, LinkedIn, v.v.)
  const ogTags = [
    { property: 'og:title', content: newsData.tieu_de_seo || newsData.tieude },
    { property: 'og:description', content: newsData.mo_ta_seo || getNoiDungSnippet(newsData.noidung, 160) },
    { property: 'og:type', content: 'article' },
    { property: 'og:url', content: currentUrl },
    { property: 'og:image', content: newsData.hinh_anh ? (newsData.hinh_anh.startsWith('http') ? newsData.hinh_anh : `${baseUrl}/storage/${newsData.hinh_anh}`) : 'https://via.placeholder.com/1200x630/f0f0f0/888888?text=Image+Not+Found' },
    { property: 'og:site_name', content: 'Tên Trang Web Của Bạn' }, // Thay bằng tên website của bạn
    { property: 'og:locale', content: 'vi_VN' }
  ];

  // Thẻ Twitter Card
  const twitterTags = [
    { name: 'twitter:card', content: 'summary_large_image' },
    { name: 'twitter:title', content: newsData.tieu_de_seo || newsData.tieude },
    { name: 'twitter:description', content: newsData.mo_ta_seo || getNoiDungSnippet(newsData.noidung, 160) },
    { name: 'twitter:image', content: newsData.hinh_anh ? (newsData.hinh_anh.startsWith('http') ? newsData.hinh_anh : `${baseUrl}/storage/${newsData.hinh_anh}`) : 'https://via.placeholder.com/1200x630/f0f0f0/888888?text=Image+Not+Found' },
    // { name: 'twitter:creator', content: '@tencuaban' }, // Nếu có tài khoản Twitter của bạn
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

// --- Các hàm xử lý nội dung EditorJS/HTML ---
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
  cursor: pointer;
}
.related-news-item:hover {
  box-shadow: 0 4px 16px #1976d211;
  transform: translateY(-2px);
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