<script setup>
import { useRoute } from 'vue-router'
import axios from 'axios'
import { ref, onMounted, watch } from 'vue'

const route = useRoute()
const page = ref(null)

const fetchPageContent = async (slug) => {
  try {
    const res = await axios.get(`http://localhost:8000/api/${slug}`)
    page.value = res.data
  } catch (err) {
    console.error('Không tải được nội dung trang:', err)
    page.value = null // Đặt về null nếu có lỗi
  }
}

onMounted(() => {
  fetchPageContent(route.params.slug)
})

// Quan trọng: Sử dụng watch để tải lại nội dung nếu slug trên URL thay đổi
watch(() => route.params.slug, (newSlug) => {
    fetchPageContent(newSlug)
})
</script>

<template>
  <div v-if="page">
    <h1>{{ page.Tieu_de_trang }}</h1>
    <div v-html="page.Noi_dung_trang"></div>
  </div>
  <div v-else-if="page === null">Không tìm thấy trang hoặc lỗi tải dữ liệu.</div>
  <div v-else>Đang tải trang...</div>
</template>