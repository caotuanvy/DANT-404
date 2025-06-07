<template>
  <div id="app"> 
    <nav class="nav-main" v-if="!isAdminRoute">
      <router-link class="a" to="/">Trang chủ</router-link>
      <router-link class="a" to="/login">Đăng nhập</router-link>
      <router-link class="a" to="/register">Đăng ký</router-link>
      
    </nav>
    <router-view />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()


const isAdminRoute = computed(() => route.path.startsWith('/admin'))
</script>

<style>

</style>
