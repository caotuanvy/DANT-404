<template>
  <section class="fofmart-gioithieu-wrapper">
    <!-- Nội dung chính (giới thiệu) -->
    <div v-if="page" class="section-block fofmart-section-intro">
      <div class="text">
        <h2>{{ page.Tieu_de_trang }}</h2>
        <div v-html="renderedHtml" />
      </div>
      <div class="image">
        <img src="http://localhost:8000/storage/slides/EaPThZeOlCfbUusOHsX3xfvU9bhKL9J9A9Bq502l.png" alt="FoF Mart Giới Thiệu" />
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

import VMC from './VMC.vue'
import Source from './Source.vue'

const page = ref(null)
const renderedHtml = ref('')

const isJSON = (str) => {
  try {
    const parsed = JSON.parse(str)
    return parsed && typeof parsed === 'object'
  } catch {
    return false
  }
}

const renderListItems = (items = []) => {
  return items.map(i => {
    const content = typeof i === 'object' ? i.content : i
    const subItems = i.items && i.items.length > 0
      ? `<ul>${renderListItems(i.items)}</ul>`
      : ''
    return `<li>${content}${subItems}</li>`
  }).join('')
}

const convertBlocksToHtml = async (data) => {
  if (!data || !data.blocks) return ''
  return data.blocks.map(block => {
    switch (block.type) {
      case 'header':
        return `<h${block.data.level}>${block.data.text}</h${block.data.level}>`
      case 'paragraph':
        return `<p>${block.data.text}</p>`
      case 'list':
        const tag = block.data.style === 'ordered' ? 'ol' : 'ul'
        return `<${tag}>${renderListItems(block.data.items)}</${tag}>`
      case 'quote':
        return `<blockquote>${block.data.text}</blockquote>`
      case 'delimiter':
        return `<hr />`
      case 'inlineCode':
        return `<code>${block.data.text}</code>`
      case 'linkTool':
        return `<a href="${block.data.link}" target="_blank">${block.data.meta?.title || block.data.link}</a>`
      case 'marker':
        return `<mark>${block.data.text}</mark>`
      case 'textColor':
        return `<span style="color: ${block.data.color};">${block.data.text}</span>`
      default:
        return ''
    }
  }).join('')
}

onMounted(async () => {
  try {
    const slug = 'gioi-thieu' 
    const res = await axios.get(`http://localhost:8000/api/admin/trang-tinh/${slug}`)
    page.value = res.data

    if (isJSON(page.value.Noi_dung_trang)) {
      const blockData = JSON.parse(page.value.Noi_dung_trang)
      renderedHtml.value = await convertBlocksToHtml(blockData)
    } else {
      renderedHtml.value = page.value.Noi_dung_trang || ''
    }
  } catch (err) {
    console.error('Không thể tải nội dung:', err)
  }
})
</script>

<style scoped>
.fofmart-gioithieu-wrapper {
  background-color: white;
  padding: 3rem 1rem;
   font-family: "Lora", serif;
  line-height: 1.8;
  height: auto;
}

.fofmart-gioithieu-wrapper .section-block {
  max-width: 1100px;
  margin: 0 auto 60px auto;
  height: auto;
  border-radius: 16px;
  padding: 3rem 2rem;
  background-color: white;
 
}


.fofmart-section-intro {
  display: flex;
  align-items: flex-start;
  gap: 3rem;
  flex-wrap: wrap;
}

.fofmart-section-intro .text {
  flex: 1.2;
}

.fofmart-section-intro .image {
  flex: 1;
  text-align: center;
  margin-top: 45px;
}

.fofmart-section-intro .image img {
  max-width: 100%;
  max-height: 580px;
  object-fit: contain;
}

.fofmart-section-intro h2 {
  color: #3b82f6;
  font-family: 'Dancing Script', cursive;
  font-size: 28px;
  font-weight: bold;
  margin-bottom: 16px;
}

.fofmart-section-intro p {
  font-size: 16px;
  margin-bottom: 14px;
  color: #333;
}

/* Responsive */
@media (max-width: 768px) {
  .fofmart-section-intro {
    flex-direction: column;
  }
}

</style>
