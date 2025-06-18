<template>
  <div class="edit-tintuc">
    <h2>Chỉnh sửa tin tức</h2>
    <form @submit.prevent="updateTintuc">
      <div>
        <label>Tiêu đề:</label>
        <input v-model="tintuc.tieude" required />
      </div>

      <div>
        <label>Danh mục:</label>
        <select v-model="tintuc.id_danh_muc_tin_tuc" required>
          <option value="">-- Chọn danh mục --</option>
          <option
            v-for="dm in danhMucs"
            :key="dm.id_danh_muc_tin_tuc"
            :value="dm.id_danh_muc_tin_tuc"
          >
            {{ dm.ten_danh_muc }}
          </option>
        </select>
      </div>

      <div>
        <label>Ngày đăng:</label>
        <input type="date" v-model="tintuc.ngay_dang" />
      </div>

      <div>
        <label>Nội dung:</label>
        <textarea v-model="tintuc.noidung" required></textarea>
      </div>

      <button type="submit" style="margin-top: 1rem;">Cập nhật</button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const tintuc = ref({
  tieude: '',
  id_danh_muc_tin_tuc: '',
  ngay_dang: '',
  noidung: '',
});
const danhMucs = ref([]);
const route = useRoute();
const router = useRouter();

const getTintuc = async () => {
  try {
    const res = await axios.get(`http://localhost:8000/api/tintuc/${route.params.id}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    // Nếu ngày đăng có dạng datetime, chỉ lấy phần ngày
    if (res.data.ngay_dang) {
      res.data.ngay_dang = res.data.ngay_dang.substring(0, 10);
    }
    // Đảm bảo id_danh_muc_tin_tuc là số hoặc chuỗi số
    res.data.id_danh_muc_tin_tuc = res.data.id_danh_muc_tin_tuc ? String(res.data.id_danh_muc_tin_tuc) : '';
    tintuc.value = res.data;
  } catch (err) {
    alert('Không thể lấy dữ liệu tin tức!');
  }
};

const getDanhMucs = async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/danh-muc-tin-tuc');
    danhMucs.value = res.data;
  } catch (err) {
    alert('Không thể lấy danh mục!');
  }
};

const updateTintuc = async () => {
  try {
    // Đảm bảo id_danh_muc_tin_tuc là số khi gửi lên backend
    tintuc.value.id_danh_muc_tin_tuc = Number(tintuc.value.id_danh_muc_tin_tuc);
    await axios.put(`http://localhost:8000/api/tintuc/${route.params.id}`, tintuc.value, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    alert('Cập nhật thành công!');
    router.push('/admin/tintuc');
  } catch (err) {
    alert('Cập nhật thất bại!');
  }
};

onMounted(() => {
  getTintuc();
  getDanhMucs();
});
</script>

<style scoped>
.edit-tintuc {
  background: #fff;
  padding: 20px;
  border-radius: 10px;
  max-width: 600px;
  margin: auto;
}
.edit-tintuc form div {
  margin-bottom: 1rem;
}
</style>