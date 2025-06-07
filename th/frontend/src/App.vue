<template>
  <div id="app">
    <nav class="nav-main">
      <router-link class="a" to="/">Trang chủ </router-link> 
      <router-link  class="a" to="/login">Đăng nhập </router-link> 
      <router-link  class="a" to="/register">Đăng ký</router-link>
      <router-link  class="a" to="/admin">Quản trị</router-link>
    </nav>
    <router-view />
  </div>
</template>

<script setup>
</script>

<style>

</style>
