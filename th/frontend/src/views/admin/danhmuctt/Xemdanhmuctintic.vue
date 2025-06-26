<template>
  <div class="xem-danhmuc" v-if="danhmuc">
    <h2 class="title">📂 Chi tiết danh mục tin tức</h2>
    <div class="info-grid">
      <div class="info-label">ID:</div>
      <div class="info-value">{{ danhmuc.id_danh_muc_tin_tuc }}</div>

      <div class="info-label">Tên danh mục:</div>
      <div class="info-value">{{ danhmuc.ten_danh_muc }}</div>

      <div class="info-label">Mô tả:</div>
      <div class="info-value">
        <div class="noidung">{{ danhmuc.mo_ta || 'Không có' }}</div>
      </div>

      <div class="info-label">Hình ảnh:</div>
      <div class="info-value">
        <img
          v-if="danhmuc.hinh_anh"
          :src="danhmuc.hinh_anh.startsWith('http') ? danhmuc.hinh_anh : `http://localhost:8000/storage/${danhmuc.hinh_anh}`"
          alt="Hình ảnh"
          class="img-preview"
        />
        <span v-else class="no-image">Không có</span>
      </div>

      <div class="info-label">Ngày tạo:</div>
      <div class="info-value">{{ danhmuc.ngay_tao ? new Date(danhmuc.ngay_tao).toLocaleString() : '' }}</div>

      <div class="info-label">Ngày sửa:</div>
      <div class="info-value">{{ danhmuc.ngay_sua ? new Date(danhmuc.ngay_sua).toLocaleString() : '' }}</div>
    </div>
    <router-link to="/admin/danhmuctintuc" class="btn-back">← Quay lại danh sách</router-link>
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

const danhmuc = ref(null);
const route = useRoute();

const fetchDanhMuc = async () => {
  try {
    const res = await axios.get(`http://localhost:8000/api/xemdanhmuc-admin/${route.params.id}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    danhmuc.value = res.data;
  } catch (error) {
    alert('Không thể lấy dữ liệu danh mục!');
  }
};

onMounted(() => {
  fetchDanhMuc();
});
</script>

<style scoped>
.xem-danhmuc {
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