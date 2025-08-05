<template>
  <div>
    <ImageSlider />
    <IntroduceView />
    <Category/>
    <BestSellProduct />
    <EightProductGrid />
    <TintucHome />
  </div>
</template>

<script setup>
import Category from '@/components/user/Category.vue';
import BestSellProduct from '@/components/user/BestSellProduct.vue';
import EightProductGrid from '@/components/user/EightProductGrid.vue';
import IntroduceView from '@/components/user/IntroduceView.vue';
import ImageSlider from '@/components/user/Slide.vue'; 
import TintucHome from '@/components/user/TintucHome.vue';
</script>
