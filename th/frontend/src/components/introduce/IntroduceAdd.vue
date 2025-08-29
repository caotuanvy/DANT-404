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
import Swal from 'sweetalert2';
const router = useRouter()
const tieuDe = ref('')
const tenTrang = ref('')

const addPage = async () => {
  try {
    await axios.post('https://api.sieuthi404.io.vn/api/admin/trang-tinh', {
      Tieu_de_trang: tieuDe.value,
      Ten_trang: tenTrang.value
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })

    Swal.fire({
    toast: true,
    position: 'top-end',
    icon: 'success',
    title: `Đã thêm trang tĩnh thành công!`,
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
  });
    router.back()
  } catch (error) {
    console.error('Lỗi khi thêm trang:', error)
    Swal.fire({
        icon: 'error',
        title: 'Thao tác thất bại',
        text: 'Đã có lỗi xảy ra, vui lòng thử lại.',
        confirmButtonColor: '#d33'
    });
  }
}
</script>

<style scoped>
input {
  border: 1px solid #ccc;
  padding: 4px;
}
</style>
