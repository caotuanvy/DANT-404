<template>
  <section class="content">
    <router-link class="btn-add" to="/admin/categories/add">Thêm Danh mục</router-link>
    <h2>Danh sách sản phẩm</h2>
    <table>
      <thead>
        <tr>
          <th>Id Danh Mục</th>
          <th>Hình ảnh</th> <!-- Thêm cột hình ảnh -->
          <th>Tên Danh Mục</th>
          <th>Mô tả</th>
          <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(category, index) in categories" :key="category.category_id">
          <td>{{ category.category_id }}</td>
          <td>
            <img
              :src="category.slug 
                ? `/uploads/categories/${category.slug}.jpg` 
                : '/uploads/categories/placeholder.jpg'"
              alt="category image"
              style="width: 60px; height: 60px; object-fit: cover;"
            />
          </td>
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
  background: #fff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  border-radius: 8px;
  overflow: hidden;
}

th,
td {
  padding: 12px 8px;
  text-align: center;
  border: 1px solid #eee;
  word-wrap: break-word;
  vertical-align: middle;
}

th {
  background: #f7f7f7;
  font-weight: 600;
}

td img {
  display: block;
  margin: 0 auto;
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #ddd;
  background: #fafafa;
}

/* Căn chỉnh lại tỷ lệ các cột cho hợp lý */
th:nth-child(1),
td:nth-child(1) {
  width: 8%;
}

th:nth-child(2),
td:nth-child(2) {
  width: 14%;
}

th:nth-child(3),
td:nth-child(3) {
  width: 28%;
}

th:nth-child(4),
td:nth-child(4) {
  width: 30%;
}

th:nth-child(5),
td:nth-child(5) {
  width: 20%;
}

.btn-detail,
.btn-edit,
.btn-delete {
  margin: 0 2px;
  padding: 6px 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.btn-detail {
  background: #e3f2fd;
  color: #1976d2;
}

.btn-edit {
  background: #fffde7;
  color: #fbc02d;
}

.btn-delete {
  background: #ffebee;
  color: #d32f2f;
}
</style>
