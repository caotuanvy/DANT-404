<template>
  <section v-if="content" class="vision-section">
    <h2 class="vision-title">{{ content.Tieu_de_trang }}</h2>

    <div class="vision-grid">
      <div
        v-for="(item, index) in items"
        :key="index"
        class="vision-card"
      >
        <div class="vision-icon-box">
          <i :class="icons[index % icons.length]" class="vision-icon fa-3x text-primary"></i>
        </div>
        <h3 class="vision-heading" v-html="item.title"></h3>
        <p class="vision-desc" v-html="item.desc"></p>
      </div>
    </div>
  </section>
</template>


<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const content = ref(null)
const items = ref([])

const icons = [
  'fa-solid fa-star',        
  'fa-solid fa-rocket',     
  'fa-solid fa-handshake'   
]


const parse = (str) => {
  try {
    return JSON.parse(str)
  } catch {
    return null
  }
}

const convertToStructuredItems = (blocks) => {
  const result = []
  for (let i = 0; i < blocks.length; i++) {
    const block = blocks[i]
    if (block.type === 'paragraph' && block.data.text.includes('<b>')) {
      const title = block.data.text
      const next = blocks[i + 1]
      const desc = next && next.type === 'paragraph' ? next.data.text : ''
      result.push({ title, desc })
    }
  }
  return result
}

onMounted(async () => {
  const res = await axios.get('https://api.sieuthi404.io.vn/api/admin/trang-tinh/tam-nhin-su-menh')
  content.value = res.data
  const parsed = parse(content.value.Noi_dung_trang)
  if (parsed?.blocks) {
    items.value = convertToStructuredItems(parsed.blocks)
  }
})
</script>

<style scoped>
.vision-section {
  background: #33ccff93;
  padding: 48px 16px;
}

.vision-title {
  color: #ffffff;
  font-size: 28px;
  font-weight: 600;
  text-align: center;
  margin-bottom: 48px;
}

.vision-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 40px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 16px;
}

@media (min-width: 768px) {
  .vision-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

.vision-card {
  text-align: center;
}

.vision-icon-box {
  background: white;
  width: 112px;
  height: 112px;
  border-radius: 9999px;
  margin: 0 auto 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 10px rgba(0,0,0,0.05);
  margin-top: 10px;
}

.vision-icon-box .vision-icon {
  color: #33ccff !important;
  transition: color 0.3s ease;
}

.vision-heading {
  font-weight: 600;
  color: #222;
  margin-bottom: 8px;
  font-size: 16px;
}

.vision-heading b {
  color: #33ccff;
}

.vision-desc {
  color: #000000;
  font-size: 14px;
  line-height: 1.6;
}
</style>

