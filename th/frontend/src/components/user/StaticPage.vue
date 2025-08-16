<script setup>
import { useRoute } from 'vue-router'
import axios from 'axios'
import { ref, onMounted, watch } from 'vue'

const route = useRoute()
const page = ref(null) // đây là state lưu nội dung trang

async function fetchPageContent(slug) {
  try {
    const token = localStorage.getItem('token');
    const res = await axios.get('http://localhost:8000/api/user', {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });
    page.value = res.data; // gán vào ref, không dùng this
  } catch (err) {
    console.error('Không tải được nội dung trang:', err);
    page.value = null;
  }
}

onMounted(() => {
  fetchPageContent(route.params.slug)
})

// Khi slug thay đổi, load lại nội dung
watch(() => route.params.slug, (newSlug) => {
  fetchPageContent(newSlug)
})
</script>

<template>
  <div v-if="page">
    <h1>{{ page.Tieu_de_trang }}</h1>
    <div v-html="page.Noi_dung_trang"></div>
  </div>
  <div v-else-if="page === null">
    Không tìm thấy trang hoặc lỗi tải dữ liệu.
  </div>
  <div v-else>
    Đang tải trang...
  </div>
</template>
