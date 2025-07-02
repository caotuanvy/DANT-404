<script setup>
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import Swal from 'sweetalert2'

const route = useRoute()
const router = useRouter()

onMounted(async () => {
  const token = route.query.token

  if (token) {
    try {
      const response = await axios.get(`http://localhost:8000/api/kich-hoat/${token}`)

      await Swal.fire({
      icon: 'success',
      title: 'Kích hoạt thành công!',
      text: response.data.message || 'Tài khoản của bạn đã được kích hoạt.',
      timer: 3000,
      showConfirmButton: false,
      timerProgressBar: true
    });

    router.push('/');
  } catch (error) {
    await Swal.fire({
      icon: 'error',
      title: 'Kích hoạt thất bại!',
      text: error.response?.data?.message || 'Đã xảy ra lỗi trong quá trình kích hoạt.',
      timer: 3000,
      showConfirmButton: false,
      timerProgressBar: true
    });
    router.push('/');
  }
} else {
  await Swal.fire({
    icon: 'warning',
    title: 'Thiếu mã kích hoạt',
    text: 'Không tìm thấy token kích hoạt trong đường dẫn.',
    timer: 3000,
    showConfirmButton: false,
    timerProgressBar: true
  });
  router.push('/');
}
})
</script>

<template>
  <div class="text-center mt-20">
    <h1 class="text-xl font-semibold">Đang kích hoạt tài khoản...</h1>
  </div>
</template>
