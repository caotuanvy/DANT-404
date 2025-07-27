<template>
  <div class="add-wrapper">
    <h2>Thêm danh mục cấp 2</h2>
    <form @submit.prevent="handleSubmit" class="add-form">
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
        <div v-if="preview" class="preview-img">
          <img :src="preview" alt="Preview" />
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Thêm mới</button>
      <router-link to="/admin/categories/1/children" class="btn btn-secondary ms-2">Quay lại</router-link>
    </form>
    <p v-if="error" class="text-danger mt-2">{{ error }}</p>
    <p v-if="success" class="text-success mt-2">{{ success }}</p>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const ten_danh_muc = ref('');
const mo_ta = ref('');
const image = ref(null);
const preview = ref('');
const error = ref('');
const success = ref('');
const router = useRouter();

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
    await axios.post('http://localhost:8000/api/danh-muc-cha', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    success.value = 'Thêm danh mục thành công!';
    setTimeout(() => {
      router.push('/admin/danh-muc-cap-2');
    }, 1000);
  } catch (err) {
    error.value = err.response?.data?.message || 'Có lỗi xảy ra!';
  }
};
</script>

<style scoped>
.add-wrapper {
  max-width: 500px;
  margin: 2rem auto;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
  padding: 2rem;
}
.add-form .form-group {
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
</style>