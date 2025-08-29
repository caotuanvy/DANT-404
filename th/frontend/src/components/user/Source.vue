<template>
  <section v-if="content" class="brand-story-section">
    <div class="content-split-wrapper">
      <div class="visual-side-block">
        
         <img class="cover-image-fit" src="https://api.sieuthi404.io.vn/storage/slides/omqc4DxXDEMD5tpNlDTTwhuF8JKK80m4YHbauknD.png?t=1751008577270" alt="FoF Mart Giới Thiệu" />
      </div>
      <div class="textual-info-box">
        <h2 class="section-title-text">{{ content.Tieu_de_trang }}</h2>
        <div class="dynamic-html-display" v-html="html"></div>
      </div>
    </div>
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
      case 'header':
        return `<h${block.data.level}>${block.data.text}</h${block.data.level}>`
      case 'paragraph':
        return `<p>${block.data.text}</p>`
      case 'list':
        const tag = block.data.style === 'ordered' ? 'ol' : 'ul'
        const items = block.data.items.map(i => `<li>${i.content || i}</li>`).join('')
        return `<${tag}>${items}</${tag}>`
      default:
        return ''
    }
  }).join('')
}

onMounted(async () => {
  const res = await axios.get('https://api.sieuthi404.io.vn/api/admin/trang-tinh/khoi-nguon')
  content.value = res.data
  const parsed = parse(content.value.Noi_dung_trang)
  html.value = convertBlocksToHtml(parsed)
})
</script>

<style scoped>
.brand-story-section {
  
  padding: 64px 16px;
}

.content-split-wrapper {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  border-radius: 20px;
  overflow: hidden;
  background-color: white;
}

@media (min-width: 768px) {
  .content-split-wrapper {
    flex-direction: row;
  }
}

.visual-side-block {
  flex: 1;
  background-color: #fdfdfd;
}

.cover-image-fit {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.textual-info-box {
  flex: 1;
  background-color: #33ccff;
  color: white;
  padding: 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.section-title-text {
  font-size: 20px;
  font-weight: bold;
  margin-bottom: 16px;
}

.dynamic-html-display p {
  margin-bottom: 12px;
  line-height: 1.6;
  font-size: 15px;
}
</style>
