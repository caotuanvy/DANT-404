<template>
  <section class="content">
    <router-link class="btn-add" to="/admin/categories/add">Thêm Danh mục</router-link>
    <h2>Danh sách sản phẩm</h2>
    <table>
      <thead>
        <tr>
          <th>Id Danh Mục</th>
          <th>Tên Danh Mục</th>
          <th>Mô tả</th>
          <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(category, index) in categories" :key="category.category_id">
          <td>{{ category.category_id }}</td>
          <td>{{ category.ten_danh_muc }}</td>
          <td>{{ category.mo_ta }}</td>
          <td>
            <button class="btn-detail" @click="viewCategories(category.category_id)">👁 Xem Sản Phẩm</button>
            <button class="btn-edit" @click="editCategories(category.category_id)">✏️ Sửa</button>
            <button class="btn-delete" @click="deleteCategories(category.category_id)">🗑 Xoá</button>
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
table {
  table-layout: fixed;
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  padding: 12px;
  text-align: center;
  border: 1px solid #ddd;
  word-wrap: break-word;
}

/* Thiết lập tỷ lệ tương ứng giữa các cột */
th:nth-child(1),
td:nth-child(1) {
  width: 10%;
}

th:nth-child(2),
td:nth-child(2) {
  width: 20%;
}

th:nth-child(3),
td:nth-child(3) {
  width: 40%;
}

th:nth-child(4),
td:nth-child(4) {
  width: 30%;
}
</style>
