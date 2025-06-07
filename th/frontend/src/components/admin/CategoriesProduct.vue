<template>
    <section class="content">
      <h2>Sản phẩm trong danh mục: {{ categoryName }}</h2>
      <table v-if="products.length">
        <thead>
          <tr>
            <th>#</th>
            <th>ID</th>
            <th>Tên sản phẩm</th>
            <th>Giá</th>
            <th>Mô tả</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(product, index) in products" :key="product.product_id">
            <td>{{ index + 1 }}</td>
            <td>{{ product.product_id }}</td>
            <td>{{ product.product_name }}</td>
            <td>{{ product.price }}</td>
            <td>{{ product.description }}</td>
          </tr>
        </tbody>
      </table>
      <p v-else>Không có sản phẩm nào trong danh mục này.</p>
  
      <button @click="goBack" class="btn">⬅ Quay lại</button>
    </section>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue'
  import { useRoute, useRouter } from 'vue-router'
  import axios from 'axios'
  
  const route = useRoute()
  const router = useRouter()
  const products = ref([])
  const categoryName = ref('')
  
  const getProductsByCategory = async () => {
    try {
      const response = await axios.get(`http://localhost:8000/api/categories/${route.params.category_id}/products`, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      })
      products.value = response.data.products
      categoryName.value = response.data.category_name
    } catch (error) {
      console.error('Lỗi khi lấy sản phẩm theo danh mục:', error)
    }
  }
  
  const goBack = () => {
    router.back()
  }
  
  onMounted(() => {
    getProductsByCategory()
  })
  </script>
  
  <style scoped>
  .content {
    padding: 20px;
  }
  .btn {
    margin-top: 20px;
    padding: 8px 16px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
  }
  .btn:hover {
    background-color: #0056b3;
  }
  </style>
  