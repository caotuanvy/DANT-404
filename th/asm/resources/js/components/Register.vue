<template>
    <div>
      <h2>Đăng ký</h2>
      <form @submit.prevent="register">
        <input v-model="form.username" placeholder="Username" />
        <input v-model="form.email" type="email" placeholder="Email" />
        <input v-model="form.password" type="password" placeholder="Mật khẩu" />
        <input v-model="form.password_confirmation" type="password" placeholder="Xác nhận mật khẩu" />
        <select v-model="form.role">
          <option value="user">User</option>
          <option value="admin">Admin</option>
        </select>
        <button type="submit">Đăng ký</button>
      </form>
      <p>{{ message }}</p>
    </div>
  </template>

  <script>
  import axios from 'axios'

  export default {
    data() {
      return {
        form: {
          username: '',
          email: '',
          password: '',
          password_confirmation: '',
          role: 'user'
        },
        message: ''
      }
    },
    methods: {
      async register() {
        try {
          const response = await axios.post('http://localhost:8000/api/register', this.form)
          this.message = response.data.message
        } catch (error) {
          this.message = error.response.data.message
        }
      }
    }
  }
  </script>
