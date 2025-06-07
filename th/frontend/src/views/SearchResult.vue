<template>
    <section class="content">
      <h2>Kết quả tìm kiếm cho "{{ searchTerm }}"</h2>
  
      <div v-if="results.length === 0">Không tìm thấy kết quả.</div>
  
      <ul v-else>
        <li v-for="(item, index) in results" :key="index">
          {{ item }}
        </li>
      </ul>
    </section>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  import { useRoute } from 'vue-router';
  import axios from 'axios';
  
  const route = useRoute();
  const searchTerm = ref(route.query.q || '');
  const results = ref([]);
  
  onMounted(async () => {
    const token = localStorage.getItem('token');
    const headers = { Authorization: `Bearer ${token}` };
  
    try {
      const [productRes, orderRes, userRes] = await Promise.all([
        axios.get('/products', { headers }),
        axios.get('/orders', { headers }),
        axios.get('/users', { headers }),
      ]);
  
      const keyword = searchTerm.value.toLowerCase();
  
      results.value = [
        ...productRes.data.filter(p => p.product_name.toLowerCase().includes(keyword)).map(p => `Sản phẩm: ${p.product_name}`),
        ...orderRes.data.filter(o => o.status.toLowerCase().includes(keyword)).map(o => `Đơn hàng: ${o.status}`),
        ...userRes.data.filter(u => u.name.toLowerCase().includes(keyword)).map(u => `Người dùng: ${u.name}`),
      ];
    } catch (err) {
      console.error('Lỗi tìm kiếm:', err);
    }
  });
  </script>
  