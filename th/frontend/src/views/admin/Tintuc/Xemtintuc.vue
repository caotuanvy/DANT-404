<template>
  <div class="xem-tintuc" v-if="tintuc">
    <h2 class="title">📰 Chi tiết tin tức</h2>
    <div class="info-grid">
      <div class="info-label">ID:</div>
      <div class="info-value">{{ tintuc.id }}</div>

      <div class="info-label">Tiêu đề:</div>
      <div class="info-value">{{ tintuc.tieude }}</div>

      <div class="info-label">Slug:</div>
      <div class="info-value slug">{{ tintuc.slug }}</div>

      <div class="info-label">Danh mục:</div>
      <div class="info-value">{{ tintuc.danhMuc ? tintuc.danhMuc.ten_danh_muc : '' }}</div>

      <div class="info-label">Ngày đăng:</div>
      <div class="info-value">{{ tintuc.ngay_dang ? new Date(tintuc.ngay_dang).toLocaleString() : '' }}</div>

      <div class="info-label">Hiển thị trang chủ:</div>
      <div class="info-value">
        <span :class="tintuc.noi_bat == 1 ? 'yes' : 'no'">
          {{ tintuc.noi_bat == 1 ? 'Có' : 'Không' }}
        </span>
      </div>

      <div class="info-label">Duyệt:</div>
      <div class="info-value">
        <span :class="tintuc.duyet_tin_tuc == 1 ? 'yes' : 'no'">
          {{ tintuc.duyet_tin_tuc == 1 ? 'Đã duyệt' : 'Chưa duyệt' }}
        </span>
      </div>

      <div class="info-label">Hình ảnh:</div>
      <div class="info-value">
        <img
          v-if="tintuc.hinh_anh"
          :src="tintuc.hinh_anh.startsWith('http') ? tintuc.hinh_anh : `https://api.sieuthi404.io.vn/storage/${tintuc.hinh_anh}`"
          alt="Hình ảnh"
          class="img-preview"
        />
        <span v-else class="no-image">Không có</span>
      </div>

      <div class="info-label">Nội dung:</div>
      <div class="info-value">
        <div class="noidung" v-html="renderedHtml"></div>
      </div>
    </div>
    <router-link to="/admin/tintuc" class="btn-back">← Quay lại danh sách</router-link>
  </div>
  <div v-else class="loading">
    <span class="loader"></span>
    Đang tải dữ liệu...
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const tintuc = ref(null);
const renderedHtml = ref('');
const route = useRoute();

function isJSON(str) {
  try {
    const parsed = JSON.parse(str);
    return parsed && typeof parsed === 'object';
  } catch {
    return false;
  }
}

function convertBlocksToHtml(data) {
  if (!data || !data.blocks) return '';
  return data.blocks.map(block => {
    switch (block.type) {
      case 'header':
        return `<h${block.data.level}>${block.data.text}</h${block.data.level}>`;
      case 'paragraph':
        return `<p>${block.data.text}</p>`;
      case 'list':
        const tag = block.data.style === 'ordered' ? 'ol' : 'ul';
        const renderList = (items = []) => {
          return items.map(i => {
            const content = typeof i === 'object' ? i.content : i;
            const subItems = i.items && i.items.length
              ? `<ul>${renderList(i.items)}</ul>`
              : '';
            return `<li>${content}${subItems}</li>`;
          }).join('');
        };
        return `<${tag}>${renderList(block.data.items)}</${tag}>`;
      case 'quote':
        return `<blockquote>${block.data.text}</blockquote>`;
      case 'delimiter':
        return `<hr />`;
      case 'inlineCode':
        return `<code>${block.data.text}</code>`;
      case 'linkTool':
        return `<a href="${block.data.link}" target="_blank">${block.data.meta?.title || block.data.link}</a>`;
      case 'marker':
        return `<mark>${block.data.text}</mark>`;
      default:
        return '';
    }
  }).join('');
}

const fetchTintuc = async () => {
  try {
    const res = await axios.get(`https://api.sieuthi404.io.vn/api/xemtintuc-admin/${route.params.id}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    tintuc.value = res.data;
    // Xử lý nội dung EditorJS
    if (isJSON(tintuc.value.noidung)) {
      const blockData = JSON.parse(tintuc.value.noidung);
      renderedHtml.value = convertBlocksToHtml(blockData);
    } else {
      renderedHtml.value = tintuc.value.noidung || '';
    }
  } catch (error) {
    alert('Không thể lấy dữ liệu tin tức!');
  }
};

onMounted(() => {
  fetchTintuc();
});
</script>

<style scoped>
.xem-tintuc {
  background: #fff;
  padding: 32px 36px 28px 36px;
  border-radius: 16px;
  max-width: 700px;
  margin: 40px auto 32px auto;
  box-shadow: 0 4px 32px rgba(0,0,0,0.10);
  animation: fadeIn 0.5s;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(30px);}
  to { opacity: 1; transform: none;}
}
.title {
  text-align: center;
  font-size: 2rem;
  margin-bottom: 28px;
  color: #1976d2;
  letter-spacing: 1px;
  font-weight: 700;
}
.info-grid {
  display: grid;
  grid-template-columns: 160px 1fr;
  row-gap: 16px;
  column-gap: 18px;
  margin-bottom: 18px;
}
.info-label {
  font-weight: 600;
  color: #444;
  align-self: start;
  padding-top: 2px;
}
.info-value {
  color: #222;
  word-break: break-word;
}
.slug {
  font-family: 'Fira Mono', 'Consolas', monospace;
  color: #1976d2;
  background: #f3f7fa;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 1em;
  display: inline-block;
}
.img-preview {
  max-width: 220px;
  max-height: 140px;
  border-radius: 8px;
  border: 1px solid #eee;
  background: #fafbfc;
  margin-top: 2px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.no-image {
  color: #aaa;
  font-style: italic;
}
.noidung {
  background: #f8f8f8;
  padding: 12px 14px;
  border-radius: 6px;
  margin-top: 2px;
  white-space: pre-line;
  font-size: 1.08em;
  min-height: 40px;
  border: 1px solid #f0f0f0;
}
.yes {
  color: #388e3c;
  font-weight: bold;
}
.no {
  color: #d32f2f;
  font-weight: bold;
}
.btn-back {
  display: inline-block;
  margin-top: 28px;
  padding: 9px 26px;
  background: linear-gradient(90deg, #1976d2 60%, #42a5f5 100%);
  color: #fff;
  border-radius: 6px;
  text-decoration: none;
  font-weight: bold;
  font-size: 1.08em;
  box-shadow: 0 2px 8px rgba(25, 118, 210, 0.08);
  transition: background 0.2s, box-shadow 0.2s;
}
.btn-back:hover {
  background: linear-gradient(90deg, #1565c0 60%, #1976d2 100%);
  box-shadow: 0 4px 16px rgba(25, 118, 210, 0.13);
}
.loading {
  text-align: center;
  margin-top: 80px;
  font-size: 1.2em;
  color: #888;
}
.loader {
  display: inline-block;
  width: 28px;
  height: 28px;
  border: 3px solid #1976d2;
  border-top: 3px solid #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  margin-bottom: 8px;
  vertical-align: middle;
}
@keyframes spin {
  to { transform: rotate(360deg);}
}
</style>