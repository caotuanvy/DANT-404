<template>
  <section class="content">
    <div class="header-container">
      <h2>Danh sách tin tức</h2>
      <router-link to="/admin/tintuc/add" class="btn-add">+ Thêm tin tức</router-link>
    </div>
    <div v-if="loading" class="loading-message">Đang tải dữ liệu...</div>

    <div class="table-container" v-if="!loading && tintucs.length > 0">
      <table class="tintuc-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Tiêu đề</th>
            <th>Danh mục</th>
            <th>Ngày đăng</th>
            <th>Hình ảnh</th>
            <th class="text-center">Hiển thị trang chủ</th>
            <th class="text-center">Duyệt</th>
            <th class="text-center">Xem chi tiết</th>
            <th class="text-center">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(tintuc, index) in tintucs" :key="tintuc.id">
            <td data-label="#">{{ index + 1 }}</td>
            <td data-label="Tiêu đề">{{ tintuc.tieude }}</td>
            <td data-label="Danh mục">
              {{ tintuc.danhMuc?.ten_danh_muc || tintuc.danh_muc?.ten_danh_muc || (tintuc.danhmuc ? tintuc.danhmuc.ten : '-') }}
            </td>
            <td data-label="Ngày đăng">{{ tintuc.ngay_dang ? tintuc.ngay_dang.substring(0, 10) : '' }}</td>
            <td data-label="Hình ảnh">
              <img
                v-if="tintuc.hinh_anh"
                :src="tintuc.hinh_anh.startsWith('http') ? tintuc.hinh_anh : `http://localhost:8000/storage/${tintuc.hinh_anh}`"
                alt="Hình ảnh"
                class="tintuc-thumbnail"
              />
              <img
                v-else
                src="https://via.placeholder.com/60x40?text=No+Image"
                alt="Không có"
                class="tintuc-thumbnail"
              />
            </td>
            <td data-label="Hiển thị trang chủ" class="text-center">
              <span class="switch" @click="toggleNoiBat(tintuc)">
                <span :class="['slider', tintuc.noi_bat == 1 ? 'on' : 'off']"></span>
              </span>
            </td>
            <td data-label="Duyệt" class="text-center">
              <span class="switch" @click="toggleDuyet(tintuc)">
                <span :class="['slider', tintuc.duyet_tin_tuc == 1 ? 'on' : 'off']"></span>
              </span>
            </td>
            <td data-label="Xem chi tiết" class="text-center">
              <router-link :to="`/admin/tintuc/${tintuc.id}`" class="btn-detail">Xem chi tiết</router-link>
            </td>
            <td data-label="Hành động" class="text-center">
              <div class="action-buttons">
                <router-link :to="`/admin/tintuc/${tintuc.id}/edit`" class="btn-edit">Sửa</router-link>
                <button @click="deleteTintuc(tintuc.id)" class="btn-delete">Xóa</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <p v-if="!loading && tintucs.length === 0" class="empty-message">Chưa có tin tức nào.</p>
    <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
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

const toggleNoiBat = async (tintuc) => {
  const newNoiBat = tintuc.noi_bat == 1 ? 0 : 1;
  try {
    await axios.put(`http://localhost:8000/api/tintuc/${tintuc.id}`, {
      noi_bat: newNoiBat
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    tintuc.noi_bat = newNoiBat;
  } catch (error) {
    alert('Cập nhật nổi bật thất bại!');
  }
};

const toggleDuyet = async (tintuc) => {
  const newDuyet = tintuc.duyet_tin_tuc == 1 ? 0 : 1;
  try {
    await axios.put(`http://localhost:8000/api/tintuc/${tintuc.id}`, {
      duyet_tin_tuc: newDuyet
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    tintuc.duyet_tin_tuc = newDuyet;
  } catch (error) {
    alert('Cập nhật duyệt thất bại!');
  }
};

onMounted(() => {
  getTintucs();
});
</script>

<style scoped>
/*
 * BASE STYLES
 */
.content {
  padding: 20px;
  font-family: Arial, sans-serif;
  max-width: 1200px;
  margin: 0 auto;
}

.header-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 10px;
}

h2 {
  font-size: 24px;
  color: #333;
  margin: 0;
}

.btn-add {
  display: inline-block;
  padding: 8px 16px;
  background-color: #4FC3F7;
  color: white;
  border-radius: 4px;
  text-decoration: none;
  transition: background-color 0.3s ease, transform 0.3s ease;
  font-weight: bold;
  white-space: nowrap;
}

.btn-add:hover {
  background-color: #039BE5;
  transform: scale(1.05);
}

.table-container {
  overflow-x: auto;
  width: 100%;
}

.tintuc-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.tintuc-table th, .tintuc-table td {
  border: 1px solid #ddd;
  padding: 12px 8px;
  text-align: left;
  vertical-align: middle;
}

.tintuc-table th {
  background-color: #f2f2f2;
  font-weight: bold;
  white-space: nowrap;
}

.tintuc-table tbody tr:hover {
  background-color: #f5f5f5;
}

.tintuc-thumbnail {
  width: 60px;
  height: 40px;
  object-fit: cover;
  border-radius: 4px;
  display: block;
  margin: 0 auto;
}

.text-center {
  text-align: center;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  justify-content: center;
}

.btn-edit, .btn-delete, .btn-detail {
  padding: 6px 10px;
  border-radius: 4px;
  text-decoration: none;
  font-weight: bold;
  transition: opacity 0.3s;
  white-space: nowrap;
  font-size: 0.9em;
}

.btn-edit {
  color: white;
  background-color: #2196f3;
  border: 1px solid #2196f3;
}

.btn-detail {
  color: white;
  background-color: #009688;
  border: 1px solid #009688;
}

.btn-delete {
  background-color: #f44336;
  color: white;
  border: 1px solid #f44336;
  cursor: pointer;
}

.btn-edit:hover, .btn-detail:hover, .btn-delete:hover {
  opacity: 0.8;
}

/* Toggle switch style */
.switch {
  display: inline-block;
  position: relative;
  width: 54px;
  height: 30px;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
}

.slider {
  width: 54px;
  height: 30px;
  border-radius: 15px;
  background: #ccc;
  position: absolute;
  transition: background 0.3s;
}

.slider::before {
  content: "";
  position: absolute;
  top: 3px;
  left: 3px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #fff;
  transition: left 0.3s, background 0.3s;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
}

.slider.on {
  background: #4CAF50;
}

.slider.on::before {
  left: 27px;
}

.loading-message, .empty-message, .error-message {
  text-align: center;
  margin-top: 20px;
  font-size: 1.1em;
}

.error-message {
  color: red;
}

/* * RESPONSIVE MOBILE STYLES (Dưới 768px)
 */
@media (max-width: 768px) {
  h2 {
    font-size: 20px;
    text-align: center;
    margin-bottom: 15px;
  }
  .header-container {
    flex-direction: column;
    align-items: stretch;
    gap: 15px;
  }
  .btn-add {
    width: 100%;
    text-align: center;
    margin-bottom: 0;
  }
  
  .table-container {
    overflow-x: visible;
  }

  /* Ẩn header của bảng và biến mỗi hàng thành một "thẻ" */
  table {
    border: none;
    width: 100%;
  }
  thead {
    display: none;
  }
  tr {
    display: block;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }
  td {
    display: block;
    text-align: left;
    padding: 10px 15px;
    border-bottom: 1px solid #eee;
    position: relative;
    padding-left: 140px; /* Thêm khoảng trống cho nhãn */
    font-size: 14px;
  }
  td:last-child {
    border-bottom: none;
  }
  
  td::before {
    content: attr(data-label);
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    font-weight: bold;
    color: #555;
    white-space: nowrap;
    width: 120px;
  }

  /* Ẩn cột # trên mobile */
  td:nth-child(1) {
    display: none;
  }

  /* Căn chỉnh lại các phần tử bên trong cột */
  .tintuc-thumbnail {
    display: block;
    margin-left: 0;
  }
  
  .action-buttons {
    flex-direction: column;
    gap: 5px;
    align-items: stretch;
    margin-top: 10px;
  }
  .action-buttons > * {
    width: 100%;
    text-align: center;
    padding: 8px 10px;
    box-sizing: border-box;
  }

  /* Đảm bảo các nút switch được căn chỉnh đúng */
  .switch {
    display: flex;
    justify-content: flex-end;
  }
  td[data-label="Hiển thị trang chủ"], td[data-label="Duyệt"], td[data-label="Xem chi tiết"], td[data-label="Hành động"] {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-left: 15px; /* Điều chỉnh padding cho các cột này */
  }
  td[data-label="Hành động"] .action-buttons {
    margin-top: 0;
  }
}
</style>