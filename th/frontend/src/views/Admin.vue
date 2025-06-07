<template>
    <div class="admin-container">
      <aside class="sidebar">
        <h2>🛍 ShoeAdmin</h2>
        <nav>
          <ul>
            <li><router-link to="/admin">Dashboard</router-link></li>
            <li><router-link to="/admin/products">Sản phẩm</router-link></li>
            <li><router-link to="/admin/category">Danh Mục</router-link></li>
            <li><router-link to="/admin/orders">Đơn hàng</router-link></li>
            <li><router-link to="/admin/customers">Khách hàng</router-link></li>
            
          </ul>
        </nav>
      </aside>
  
      <main class="main-content">
        <header class="header">
          <h1>🎯 Quản trị web bán giày</h1>
          <button @click="logout">Đăng xuất</button>
        </header>
  
        <router-view /> 
      </main>
    </div>
  </template>
  
  <script setup>
  import { useRouter } from 'vue-router';
  const router = useRouter();
  
  const logout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('role');
    router.push('/login');
  };
  </script>
  
  
  <style scoped>
  
  </style>
  