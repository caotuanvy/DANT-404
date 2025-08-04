<template>
  <section class="home-slide">
    <swiper
      :modules="[Autoplay, Navigation, Pagination]"
      :slides-per-view="1"
      :loop="true"
      :autoplay="{ delay: 3000, disableOnInteraction: false }"
      :pagination="{ clickable: true }"
      :navigation="true"
      class="mySwiper"
    >
      <swiper-slide v-for="(slide, index) in slides" :key="index">
        <div class="slide-wrapper">
          <img
            :src="getImageUrl(slide.duong_dan)"
            alt="Slide"
            loading="lazy"
            class="slide-image"
          />
        </div>
      </swiper-slide>
      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
      <div class="swiper-pagination"></div>
    </swiper>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay, Navigation, Pagination } from 'swiper/modules'
const slides = ref([])
const slideName = ref('')
const getImageUrl = (path) => {
  return path ? `http://localhost:8000/storage/${path}` : ''
}
const fetchSlides = async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/admin/slide-hienthi')
    slides.value = res.data?.hinh_anh || []
    slideName.value = res.data?.ten_slide || ''
  } catch (err) {
    console.error('Lỗi khi tải slide hiển thị:', err)
  }
}
onMounted(fetchSlides)
</script>
<style scoped>
.home-slide {
  width: 100%;
  overflow: hidden;
}
.mySwiper {
  width: 100%;
  height: 70vh; 
  position: relative;
}
.slide-wrapper {
  width: 100%;
  height: 100%;
}
.slide-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  transition: transform 0.3s ease;
}
.slide-image:hover {
  transform: scale(1.01);
}
@media (max-width: 768px) {
  .mySwiper {
     height: auto !important;
    aspect-ratio: 21 / 9 !important; 
  }

  .slide-image {
    object-fit: cover;
  }

  :deep(.swiper-button-next),
  :deep(.swiper-button-prev) {
    --swiper-navigation-size: 28px;
    color: #fff;
    background-color: rgba(0, 0, 0, 0.3);
    width: 32px;
    height: 32px;
    border-radius: 50%;
  }

  :deep(.swiper-pagination-bullet) {
    width: 10px;
    height: 10px;
  }

}
</style>
