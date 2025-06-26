<template>
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
      <img :src="slide" alt="Slide" class="w-full h-64 object-cover rounded-xl shadow-lg" />
    </swiper-slide>


    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
    <div class="swiper-pagination"></div>
  </swiper>
</template>

<script setup>
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay, Navigation, Pagination } from 'swiper/modules';

const slides = [
  'https://via.placeholder.com/800x300/FF6F61/fff?text=Slide+1',
  'https://via.placeholder.com/800x300/6B5B95/fff?text=Slide+2',
  'https://via.placeholder.com/800x300/88B04B/fff?text=Slide+3',
];
</script>

<style scoped>
.mySwiper {
  width: 100%;
  height: 300px;
}
</style>
