<!-- components/user/GioiThieu.vue -->
<template>
  <section v-if="content" class="block-section">
    <h2 class="section-title">{{ content.Tieu_de_trang }}</h2>
    <div class="section-content" v-html="html"></div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const content = ref(null)
const html = ref('')

const parse = (str) => {
  try {
    return JSON.parse(str)
  } catch {
    return null
  }
}

const convertBlocksToHtml = (data) => {
  if (!data?.blocks) return ''
  return data.blocks.map((block) => {
    switch (block.type) {
      case 'header': return `<h${block.data.level}>${block.data.text}</h${block.data.level}>`
      case 'paragraph': return `<p>${block.data.text}</p>`
      case 'list':
        const tag = block.data.style === 'ordered' ? 'ol' : 'ul'
        const items = block.data.items.map(i => `<li>${i.content || i}</li>`).join('')
        return `<${tag}>${items}</${tag}>`
      default: return ''
    }
  }).join('')
}

onMounted(async () => {
const res = await axios.get('http://localhost:8000/api/admin/trang-tinh/lien-he')
  content.value = res.data
  const parsed = parse(content.value.Noi_dung_trang)
  html.value = convertBlocksToHtml(parsed)
})
</script>
