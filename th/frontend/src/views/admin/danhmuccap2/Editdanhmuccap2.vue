<template>
  <div class="edit-wrapper" v-if="loaded">
    <h2>Chỉnh sửa danh mục cấp 2</h2>
    <form @submit.prevent="handleSubmit" class="edit-form">
      <div class="form-group">
        <label for="ten_danh_muc">Tên danh mục <span class="text-danger">*</span></label>
        <input
          id="ten_danh_muc"
          v-model="ten_danh_muc"
          type="text"
          class="form-control"
          required
        />
      </div>
      <div class="form-group">
        <label for="mo_ta">Mô tả</label>
        <textarea
          id="mo_ta"
          v-model="mo_ta"
          class="form-control"
          rows="2"
        ></textarea>
      </div>
      <div class="form-group">
        <label for="image">Hình ảnh</label>
        <input
          id="image"
          type="file"
          class="form-control"
          @change="handleImage"
          accept="image/*"
        />
        <div v-if="preview || image_url" class="preview-img">
          <img :src="preview || image_url" alt="Preview" />
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
      <router-link to="/admin/danh-muc-cap-2" class="btn btn-secondary ms-2">Quay lại</router-link>
    </form>
    <p v-if="error" class="text-danger mt-2">{{ error }}</p>
    <p v-if="success" class="text-success mt-2">{{ success }}</p>
  </div>
  <div v-else class="text-center py-5">Đang tải dữ liệu...</div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const id = route.params.id;

const ten_danh_muc = ref('');
const mo_ta = ref('');
const image = ref(null);
const image_url = ref('');
const preview = ref('');
const error = ref('');
const success = ref('');
const loaded = ref(false);

const fetchData = async () => {
  try {
    const res = await axios.get(`http://localhost:8000/api/danh-muc-cha/${id}`);
    ten_danh_muc.value = res.data.ten_danh_muc;
    mo_ta.value = res.data.mo_ta;
    image_url.value = res.data.image_url;
    loaded.value = true;
  } catch (err) {
    error.value = 'Không tìm thấy danh mục!';
    loaded.value = true;
  }
};

const handleImage = (e) => {
  const file = e.target.files[0];
  image.value = file;
  if (file) {
    const reader = new FileReader();
    reader.onload = (ev) => {
      preview.value = ev.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const handleSubmit = async () => {
  error.value = '';
  success.value = '';
  if (!ten_danh_muc.value) {
    error.value = 'Tên danh mục không được để trống!';
    return;
  }
  const formData = new FormData();
  formData.append('ten_danh_muc', ten_danh_muc.value);
  formData.append('mo_ta', mo_ta.value);
  if (image.value) {
    formData.append('image', image.value);
  }
  try {
    await axios.post(
      `http://localhost:8000/api/danh-muc-cha/${id}?_method=PUT`,
      formData,
      { headers: { 'Content-Type': 'multipart/form-data' } }
    );
    success.value = 'Cập nhật thành công!';
    setTimeout(() => {
      router.push('/admin/danh-muc-cap-2');
    }, 1000);
  } catch (err) {
    error.value = err.response?.data?.message || 'Có lỗi xảy ra!';
  }
};

onMounted(fetchData);
</script>

<style scoped>
.edit-wrapper {
  max-width: 500px;
  margin: 2rem auto;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
  padding: 2rem;
}
.edit-form .form-group {
  margin-bottom: 1.2rem;
}
.preview-img img {
  max-width: 120px;
  max-height: 80px;
  margin-top: 0.5rem;
  border-radius: 4px;
  border: 1px solid #e5e7eb;
}
.btn {
  min-width: 100px;
}
.text-danger {
  color: #dc2626;
}
.text-success {
  color: #16a34a;
}
</style>