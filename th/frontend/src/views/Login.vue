<template>
  <div>
    <h2>Đăng nhập</h2>
    <form @submit.prevent="handleLogin">
      <div>
        <label>Email:</label>
        <input v-model="form.email" type="email" required />
      </div>
      <div>
        <label>Mật khẩu:</label>
        <input v-model="form.mat_khau" type="password" required />
      </div>
      <button type="submit">Đăng nhập</button>
      <p style="color: red" v-if="error">{{ error }}</p>
    </form>
  </div>
</template>

<script setup>
import axios from 'axios';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const form = ref({
  email: '',
  mat_khau: '',
});

const error = ref('');
const router = useRouter();

const handleLogin = async () => {
  try {
    const res = await axios.post('/login', form.value);

    const user = res.data.user;
    const token = res.data.token;

    localStorage.setItem('token', token);
    localStorage.setItem('user', JSON.stringify(user));
    localStorage.setItem('vai_tro_id', user.vai_tro_id); // Sửa theo vai_tro_id thay vì role

    // Điều hướng theo vai_tro_id (ví dụ: 1 là admin, 2 là user)
    if (user.vai_tro_id === 1) {
      router.push('/admin');
    } else {
      router.push('/');
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Đăng nhập thất bại';
  }
};
</script>

  