<template>
  <div>
    <h2>Danh sách danh mục tin tức</h2>

    <ul v-if="danhMuc.length">
      <li v-for="item in danhMuc" :key="item.id_danh_muc_tin_tuc">
        <strong>{{ item.ten_danh_muc }}</strong> — {{ item.mo_ta }}
      </li>
    </ul>

    <p v-else>Đang tải hoặc không có dữ liệu.</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const danhMuc = ref([]);

const fetchDanhMuc = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/danh-muc-tin-tuc');
    danhMuc.value = response.data;
  } catch (error) {
    console.error('Lỗi khi lấy danh mục tin tức:', error);
  }
};

onMounted(() => {
  fetchDanhMuc();
});
</script>
