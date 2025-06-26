<template>
  <section class="content">
    <h2>Quản lý Slide</h2>

    <table v-if="slides.length > 0" class="table-auto w-full border-collapse border border-gray-300">
      <thead>
        <tr>
          <th class="border border-gray-300 p-2">#</th>
          <th class="border border-gray-300 p-2">Tên Slide</th>
          <th class="border border-gray-300 p-2">Hình ảnh 1</th>
          <th class="border border-gray-300 p-2">Hình ảnh 2</th>
          <th class="border border-gray-300 p-2">Hình ảnh 3</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(slide, index) in slides" :key="slide.Slide_id" class="hover:bg-gray-100">
          <td class="border border-gray-300 p-2 text-center">{{ index + 1 }}</td>
          <td class="border border-gray-300 p-2">{{ slide.Ten_slide }}</td>

          <td class="border border-gray-300 p-2 flex items-center gap-2">
            <img
              :src="getImageUrl(slide.Hinh_anh_1)"
              alt="Hình ảnh 1"
              style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;"
            />
            <button
              @click="editImage(slide.Slide_id, 1)"
              class="btn-edit"
              title="Sửa ảnh 1"
            >
              Sửa
            </button>
          </td>

          <td class="border border-gray-300 p-2 flex items-center gap-2">
            <img
              :src="getImageUrl(slide.Hinh_anh_2)"
              alt="Hình ảnh 2"
              style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;"
            />
            <button
              @click="editImage(slide.Slide_id, 2)"
              class="btn-edit"
              title="Sửa ảnh 2"
            >
              Sửa
            </button>
          </td>

          <td class="border border-gray-300 p-2 flex items-center gap-2">
            <img
              :src="getImageUrl(slide.Hinh_anh_3)"
              alt="Hình ảnh 3"
              style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;"
            />
            <button
              @click="editImage(slide.Slide_id, 3)"
              class="btn-edit"
              title="Sửa ảnh 3"
            >
              Sửa
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <p v-if="slides.length === 0" class="mt-4 text-gray-500">Chưa có slide nào.</p>
    <p v-if="errorMessage" class="mt-4 text-red-500">{{ errorMessage }}</p>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const slides = ref([]);
const errorMessage = ref('');

const getImageUrl = (filename) => {
  if (!filename) return 'https://placehold.co/60x40?text=No+Image';
  return `http://localhost:8000/storage/${filename}`;
};

const fetchSlides = async () => {
  errorMessage.value = '';
  try {
    const res = await axios.get('http://localhost:8000/api/admin/slide');
    slides.value = res.data;
  } catch (err) {
    console.error(err);
    errorMessage.value = 'Lỗi khi tải danh sách slide';
  }
};

const editImage = (slideId, imageNumber) => {
  alert(`Bạn muốn sửa ảnh số ${imageNumber} của slide ID: ${slideId}`);
  
};

onMounted(fetchSlides);
</script>

<style scoped>
.content {
  padding: 20px;
}

.btn-edit {
  background-color: #3b82f6;
  color: white;
  padding: 4px 8px;
  font-size: 0.875rem; 
  border-radius: 0.375rem;
  box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
  border: none;
  cursor: pointer;
  transition: background-color 0.3s ease;
  margin: 10px;
}

.btn-edit:hover {
  background-color: #2563eb; 
}
</style>
