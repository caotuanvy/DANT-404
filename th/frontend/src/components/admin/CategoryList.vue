<template>
    <section class="content">
      <router-link class="addProduct" to="/admin/categories/add">Thêm sản phẩm</router-link>
      <h2>Danh sách sản phẩm</h2>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Id Danh Mục</th>
            <th>Tên Danh Mục</th>
            <th>Mô tả</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(category, index) in categories" :key="category.category_id">
            <td>{{ index + 1 }}</td>
            <td>{{ category.category_id }}</td>
            <td>{{ category.category_name }}</td>
            <td>{{ category.description }}</td>
            <td>
              <button class="addProduct" @click="viewCategories(category.category_id)">👁 Xem Sản Phẩm</button>
              <button class="addProduct" @click="editCategories(category.category_id)">✏️ Sửa</button>
              <button class="addProduct" @click="deleteCategories(category.category_id)">🗑 Xoá</button>
            </td>
          </tr>
        </tbody>
      </table>
    </section>
  </template>
  
  <script setup>
  import { onMounted, ref } from 'vue';
  import axios from 'axios';
  import { useRouter } from 'vue-router';
  
  const router = useRouter();
  const categories = ref([]);
  

  const getCategories = async () => {
    try {
      const response = await axios.get('http://localhost:8000/api/categories', {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });
      categories.value = response.data;
    } catch (error) {
      console.error(error);
    }
  };
  

  const viewCategories = (categoryId) => {
    router.push(`/admin/categories/${categoryId}/products`);
  };
  
 
  const editCategories = (categoryId) => {
    router.push(`/admin/categories/${categoryId}/edit`);
  };
  

  const deleteCategories = async (categoryId) => {
  const confirmed = confirm('Bạn có chắc muốn xóa danh mục này?');
  if (confirmed) {
    try {
      const response = await axios.delete(`http://localhost:8000/api/categories/${categoryId}`, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });

      if (response.status === 200) {
        alert('Danh mục đã được xóa thành công!');
        getCategories(); 
      }
    } catch (error) {
      console.error(error);
      alert('Có lỗi xảy ra khi xóa danh mục.');
    }
  }
};
  
  onMounted(() => {
    getCategories();
  });
  </script>
  
  <style scoped>
  .addProduct {
    margin-right: 10px;
    padding: 5px 10px;
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
  }
  
  .addProduct:hover {
    background-color: #0056b3;
  }
  </style>
  