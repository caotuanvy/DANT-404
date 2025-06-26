<template>
  <div class="space-y-6">
    <h2 class="text-2xl font-bold">Quản lý Slide</h2>

    <!-- Danh sách các slide -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div
        v-for="s in slideList"
        :key="s.Slide_id"
        class="border p-4 rounded shadow hover:bg-gray-50 cursor-pointer"
        @click="selectSlide(s)"
      >
        <h3 class="font-semibold">{{ s.Ten_slide }}</h3>
        <img :src="getImage(s.Hinh_anh_1 || s.Hinh_anh_2 || s.Hinh_anh_3)" class="h-32 object-cover rounded mt-2" />
        <p class="text-sm text-gray-600">ID: {{ s.Slide_id }}</p>
      </div>
    </div>

    <!-- Form cập nhật ảnh khi chọn 1 slide -->
    <div v-if="selectedSlide" class="mt-8 border-t pt-6">
      <h3 class="text-xl font-semibold mb-4">Cập nhật ảnh cho: {{ selectedSlide.Ten_slide }}</h3>
      <form @submit.prevent="updateSlide">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div
            v-for="(img, index) in ['Hinh_anh_1', 'Hinh_anh_2', 'Hinh_anh_3']"
            :key="index"
          >
            <label :for="img" class="block mb-1">Ảnh {{ index + 1 }}</label>
            <img :src="getImage(selectedSlide[img])" alt="" class="h-40 object-cover rounded mb-2" v-if="selectedSlide[img]" />
            <input type="file" :id="img" @change="handleFileChange($event, img)" />
          </div>
        </div>
        <button type="submit" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
          Cập nhật Slide
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const slideList = ref([]);
const selectedSlide = ref(null);
const formData = new FormData();

const getImage = (path) => {
  return path ? `http://localhost:8000/storage/${path}` : 'https://via.placeholder.com/200x100?text=No+Image';
};

const fetchSlides = async () => {
  const res = await axios.get(`http://localhost:8000/api/admin/slide`);
  slideList.value = res.data;
};

const selectSlide = (slide) => {
  selectedSlide.value = { ...slide }; 
  formData.set('Slide_id', slide.Slide_id);
};

const handleFileChange = (event, key) => {
  const file = event.target.files[0];
  if (file) {
    formData.set(key, file);
  }
};

const updateSlide = async () => {
  try {
    await axios.post(`http://localhost:8000/api/admin/slide/${selectedSlide.value.Slide_id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    alert('Cập nhật thành công!');
    await fetchSlides();
    selectedSlide.value = null;
  } catch (error) {
    console.error(error);
    alert('Lỗi khi cập nhật slide!');
  }
};

onMounted(fetchSlides);
</script>
