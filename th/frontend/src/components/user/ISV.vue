<template>
  <section >
    <IntroduceView />
    <VMC />
   <Source />
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

import VMC from './VMC.vue'
import Source from './Source.vue'
import IntroduceView from './IntroduceView.vue'


</script>

<style scoped>

</style>
