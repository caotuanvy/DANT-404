<template>
  <section class="content">
    <h2>Danh sách tin tức</h2>

    <!-- Nút thêm tin tức -->
    <router-link to="/admin/tintuc/add" class="btn-add">+ Thêm tin tức</router-link>
    <div v-if="loading">Đang tải dữ liệu...</div>

    <table v-if="!loading && tintucs.length > 0">
      <thead>
        <tr>
          <th>#</th>
          <th>Tiêu đề</th>
          <th>Danh mục</th>
          <th>Ngày đăng</th>
          <th>Nội dung</th>
          <th>Xem chi tiết</th>
          <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(tintuc, index) in tintucs" :key="tintuc.id">
          <td>{{ index + 1 }}</td>
          <td>{{ tintuc.tieude }}</td>
          <td>
            <!-- Sửa tên trường danh mục cho đúng với dữ liệu trả về từ API -->
            {{ tintuc.danhMuc ? tintuc.danhMuc.ten_danh_muc : (tintuc.danh_muc ? tintuc.danh_muc.ten_danh_muc : (tintuc.danhmuc ? tintuc.danhmuc.ten : '-')) }}
          </td>
          <td>{{ tintuc.ngay_dang }}</td>
          <td>
            <div style="max-width: 250px; white-space: pre-line; overflow: hidden; text-overflow: ellipsis;">
              {{ tintuc.noidung }}
            </div>
          </td>
          <td>
            <router-link :to="`/admin/tintuc/${tintuc.id}`" class="btn-detail">Xem chi tiết</router-link>
          </td>
          <td>
            <div class="action-buttons">
              <router-link :to="`/admin/tintuc/${tintuc.id}/edit`" class="btn-edit">Sửa</router-link>
              <button @click="deleteTintuc(tintuc.id)" class="btn-delete">Xóa</button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <p v-if="!loading && tintucs.length === 0">Chưa có tin tức nào.</p>
    <p v-if="errorMessage" style="color: red;">{{ errorMessage }}</p>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const tintucs = ref([]);
const errorMessage = ref('');
const loading = ref(false);

const getTintucs = async () => {
  loading.value = true;
  errorMessage.value = '';
  try {
    const res = await axios.get('http://localhost:8000/api/tintuc', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    tintucs.value = res.data;
    // Xem cấu trúc dữ liệu trả về để xác định đúng trường danh mục
    // console.log(tintucs.value);
  } catch (error) {
    console.error('Lỗi khi lấy tin tức:', error);
    errorMessage.value = 'Lỗi khi lấy tin tức: ' + (error.response?.data?.message || error.message);
  } finally {
    loading.value = false;
  }
};

const deleteTintuc = async (id) => {
  if (!confirm('Bạn có chắc muốn xóa tin tức này không?')) return;

  try {
    await axios.delete(`http://localhost:8000/api/tintuc/${id}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    tintucs.value = tintucs.value.filter(t => t.id !== id);
  } catch (error) {
    console.error('Lỗi khi xóa tin tức:', error);
    alert('Xóa tin tức thất bại!');
  }
};

onMounted(() => {
  getTintucs();
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
  background-color: #4FC3F7;
  color: white;
  border-radius: 4px;
  text-decoration: none;
  transition: background-color 0.3s ease, transform 0.3s ease;
  font-weight: bolder;
}
.btn-add:hover {
  background-color: #039BE5;
  transform: scale(1.05);
}
.btn-edit {
  color: #2196f3;
  text-decoration: none;
  margin: 0 5px;
  font-weight: bolder;
}
.btn-detail {
  color: #009688;
  text-decoration: none;
  margin: 0 5px;
  font-weight: bolder;
}
.btn-delete {
  border: none;
  background: none;
  color: red;
  cursor: pointer;
  padding: 0;
  font-size: 1em;
  font-weight: bolder;
}
button:hover {
  text-decoration: underline;
}
.action-buttons {
  display: flex;
  flex-direction: row;
  gap: 8px;
  align-items: center;
}
</style>