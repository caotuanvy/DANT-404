<template>
  <section class="content p-4 max-w-xl mx-auto">
    <h2 class="text-2xl font-bold mb-4">Thêm trang tĩnh mới</h2>

    <form @submit.prevent="addPage">
      <div class="mb-4">
        <label class="block mb-1">Tiêu đề trang:</label>
        <input v-model="tieuDe" type="text" class="border p-2 w-full" required />
      </div>

      <div class="mb-4">
        <label class="block mb-1">Slug (tên trang):</label>
        <input v-model="tenTrang" type="text" class="border p-2 w-full" required />
      </div>

      <button type="submit" class="btn-add">+ Thêm mới</button>
    </form>
  </section>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const tieuDe = ref('')
const tenTrang = ref('')

const addPage = async () => {
  try {
    await axios.post('http://localhost:8000/api/admin/trang-tinh', {
      Tieu_de_trang: tieuDe.value,
      Ten_trang: tenTrang.value
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })

    alert('Thêm trang tĩnh thành công')
    router.back()
  } catch (error) {
    console.error('Lỗi khi thêm trang:', error)
    alert('Thêm không thành công!')
  }
}
</script>

<style scoped>
input {
  border: 1px solid #ccc;
  padding: 4px;
}
</style>
