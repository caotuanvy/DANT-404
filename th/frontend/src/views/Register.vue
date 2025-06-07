<template>
  <div>
    <h2>Đăng ký</h2>
    <form @submit.prevent="handleRegister">
      <div>
        <label>Họ tên:</label>
        <input v-model="form.ho_ten" type="text" required />
      </div>
      <div>
        <label>Email:</label>
        <input v-model="form.email" type="email" required />
      </div>
      <div>
        <label>Số điện thoại:</label>
        <input v-model="form.sdt" type="text" required />
      </div>
      <div>
        <label>Mật khẩu:</label>
        <input v-model="form.mat_khau" type="password" required />
      </div>
      <div>
        <label>Xác nhận mật khẩu:</label>
        <input v-model="form.mat_khau_confirmation" type="password" required />
      </div>
      <button type="submit">Đăng ký</button>
      <p style="color: red" v-if="error">{{ error }}</p>
    </form>
  </div>
</template>

<script setup>
import axios from 'axios';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const form = ref({
  ho_ten: '',
  email: '',
  sdt: '',
  mat_khau: '',
  mat_khau_confirmation: '',
});

const error = ref('');
const router = useRouter();

const handleRegister = async () => {
  try {
    const res = await axios.post('/register', form.value);
    localStorage.setItem('token', res.data.token);
    router.push('/');
  } catch (err) {
    error.value = err.response?.data?.message || 'Đăng ký thất bại';
  }
};
</script>
