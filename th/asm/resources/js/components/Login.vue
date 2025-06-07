<template>
    <div>
      <h2>Đăng nhập</h2>
      <form @submit.prevent="login">
        <input v-model="username" placeholder="Username" />
        <input v-model="password" type="password" placeholder="Mật khẩu" />
        <button type="submit">Đăng nhập</button>
      </form>
      <p>{{ message }}</p>
    </div>
  </template>

  <script>
  import axios from 'axios'

  export default {
    data() {
      return {
        username: '',
        password: '',
        message: ''
      }
    },
    methods: {
      async login() {
        try {
          const response = await axios.post('http://localhost:8000/api/login', {
            username: this.username,
            password: this.password
          })
          localStorage.setItem('token', response.data.token)
          this.message = response.data.message
          // Nếu muốn chuyển hướng theo role:
          if (response.data.user.role === 'admin') {
            this.$router.push('/admin')
          } else {
            this.$router.push('/home')
          }
        } catch (error) {
          this.message = error.response.data.message
        }
      }
    }
  }
  </script>
