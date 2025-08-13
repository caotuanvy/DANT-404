<template>
  <div class="floating-container">
    <transition-group name="fade" tag="div" class="social-links-list">
      <a 
        v-if="isOpen"
        v-for="link in activeLinks" 
        :key="link.Mang_Xa_Hoi_id"
        :href="link.Link_mang_xa_hoi" 
        target="_blank" 
        rel="noopener noreferrer"
        class="social-icon"
        :title="link.Ten_mang_xa_hoi"
      >
        <SocialIcon :iconName="link.Icon" />
      </a>
    </transition-group>

    <button @click="toggleMenu" class="trigger-button" :class="{ 'is-open': isOpen }">
      <div class="trigger-icon-container">
        <SocialIcon iconName="plus" />
      </div>
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import SocialIcon from '../icons/SocialIcon.vue'; // Import component SVG

const isOpen = ref(false);
const activeLinks = ref([]);

const toggleMenu = () => {
  isOpen.value = !isOpen.value;
};

const fetchActiveLinks = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/social-links/active');
    activeLinks.value = response.data;
  } catch (error) {
    console.error('Không thể tải danh sách mạng xã hội:', error);
  }
};

onMounted(fetchActiveLinks);
</script>

<style scoped>
.floating-container {
  position: fixed;
  bottom: 25px;
  right: 25px;
  z-index: 1000;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.social-links-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 15px;
}
.social-icon {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 48px;
  height: 48px;
  padding: 10px;
  background-color: white;
  color: #333;
  border-radius: 50%;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  text-decoration: none;
  transition: transform 0.2s ease-out, background-color 0.2s;
}
.social-icon:hover {
  transform: scale(1.15);
  background-color: #f0f0f0;
}
.trigger-button {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background-color: #4FC3F7;
  border: none;
  color: white;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  transition: background-color 0.3s ease, transform 0.2s ease;
  padding: 15px;
}
.trigger-icon-container {
  transition: transform 0.3s ease;
}
.trigger-button:hover {
  background-color: #039BE5;
  transform: scale(1.1);
}
.trigger-button.is-open {
  background-color: #f44336;
}
.trigger-button.is-open .trigger-icon-container {
  transform: rotate(45deg);
}
.fade-enter-active,
.fade-leave-active {
  transition: all 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
</style>