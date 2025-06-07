<template>
    <div class="product-detail">
      <h2>Chi tiết sản phẩm</h2>
      <div v-if="product">
        <p><strong>Tên:</strong> {{ product.product_name }}</p>
        <p><strong>Giá:</strong> {{ product.price.toLocaleString() }}đ</p>
        <p><strong>Loại:</strong> {{ product.category.category_name }}</p>
        <p><strong>Mô tả:</strong> {{ product.description }}</p>
        <p><strong>Hình ảnh:</strong></p>
        <div v-for="(image, index) in product.images" :key="index">
          <img :src="image.image_url" alt="Product Image" style="width: 100px; height: 100px; object-fit: cover; margin-right: 5px;">
        </div>
      </div>
      <div v-else>
        <p>Đang tải sản phẩm...</p>
      </div>
      <button @click="goBack">Quay lại</button>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  import axios from 'axios';
  import { useRoute, useRouter } from 'vue-router';
  
  const product = ref(null);
  const route = useRoute();
  const router = useRouter();
  
  const getProductDetail = async () => {
    try {
      const res = await axios.get(`/products/${route.params.id}`, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });
      product.value = res.data;
    } catch (err) {
      console.error('Không thể lấy chi tiết sản phẩm', err);
    }
  };
  
  const goBack = () => {
    router.push('/admin/products');
  };
  
  onMounted(() => {
    getProductDetail();
  });
  </script>
  
  