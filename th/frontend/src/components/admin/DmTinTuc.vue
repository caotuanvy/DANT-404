<template>
  <section class="content">
    <h2>Danh sách danh mục tin tức</h2>

    <!-- Nút thêm danh mục -->
    <router-link to="/admin/danh-muc-tin-tuc/add" class="btn-add">+ Thêm danh mục</router-link>
    <div v-if="loading">Đang tải dữ liệu...</div>
    
      <thead>
    <tr>
      <th>#</th>
      <th>Tên danh mục</th>
      <th>Hình ảnh</th> <!-- Thêm dòng này -->
      <th>Mô tả</th>
      <th>Ngày tạo</th>
      <th>Ngày sửa</th>
      <th>Xem chi tiết</th>
      <th>Hành động</th>
    </tr>
     </thead>
     
  <tbody>
    <tr v-for="(item, index) in danhMuc" :key="item.id_danh_muc_tin_tuc">
      <td>{{ index + 1 }}</td>
      <td>{{ item.ten_danh_muc }}</td>
      <td>
        <img
          v-if="item.hinh_anh"
          :src="item.hinh_anh.startsWith('/storage') ? `http://localhost:8000${item.hinh_anh}` : item.hinh_anh"
          alt="Hình ảnh"
          style="width: 60px; height: auto; object-fit: cover;"
        />
        <img
          v-else
          src="https://via.placeholder.com/60x40?text=No+Image"
          alt="Không có"
          style="width: 60px; height: auto; object-fit: cover;"
        />
      </td>

      <td>{{ item.mo_ta }}</td>
      <td>{{ item.ngay_tao ? new Date(item.ngay_tao).toLocaleString() : '' }}</td>
      <td>{{ item.ngay_sua ? new Date(item.ngay_sua).toLocaleString() : '' }}</td>
      <td>
        <router-link :to="`/admin/danh-muc-tin-tuc/${item.id_danh_muc_tin_tuc}`">Xem chi tiết</router-link>
      </td>
      <td>
        <router-link :to="`/admin/danh-muc-tin-tuc/${item.id_danh_muc_tin_tuc}/edit`" class="btn-edit">Sửa</router-link>
        |
        <button @click="deleteDanhMuc(item.id_danh_muc_tin_tuc)" class="btn-delete">Xóa</button>
      </td>
    </tr>
  </tbody>
    

    <p v-if="!loading && danhMuc.length === 0">Chưa có danh mục nào.</p>
    <p v-if="errorMessage" style="color: red;">{{ errorMessage }}</p>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const danhMuc = ref([]);
const errorMessage = ref('');
const loading = ref(false);

const fetchDanhMuc = async () => {
  loading.value = true;
  errorMessage.value = '';
  try {
    const response = await axios.get('http://localhost:8000/api/danh-muc-tin-tuc', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    danhMuc.value = response.data;
  } catch (error) {
    console.error('Lỗi khi lấy danh mục tin tức:', error);
    errorMessage.value = 'Lỗi khi lấy danh mục: ' + (error.response?.data?.message || error.message);
  } finally {
    loading.value = false;
  }
};

const deleteDanhMuc = async (id) => {
  if (!confirm('Bạn có chắc muốn xóa danh mục này không?')) return;
  try {
    await axios.delete(`http://localhost:8000/api/danh-muc-tin-tuc/${id}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    danhMuc.value = danhMuc.value.filter(dm => dm.id_danh_muc_tin_tuc !== id);
  } catch (error) {
    console.error('Lỗi khi xóa danh mục:', error);
    alert('Xóa danh mục thất bại!');
  }
};

onMounted(() => {
  fetchDanhMuc();
});
</script>

<style scoped>
.content {
  padding: 20px;
}

.btn-add {
  display: inline-block;
  margin-bottom: 10px;
  padding: 6px 12px;
  background-color: #4caf50;
  color: white;
  border-radius: 4px;
  text-decoration: none;
}

.btn-edit {
  color: #2196f3;
  text-decoration: none;
  margin: 0 5px;
}

.btn-delete {
  border: none;
  background: none;
  color: red;
  cursor: pointer;
  padding: 0;
  font-size: 1em;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

th,
td {
  padding: 10px;
  border: 1px solid #ccc;
  text-align: left;
}

th {
  background-color: #f3f4f6;
}

td {
  background-color: #ffffff;
}

button:hover {
  text-decoration: underline;
}
</style>