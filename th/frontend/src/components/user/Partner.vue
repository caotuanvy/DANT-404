<template>
  <div class="partner-section">
    <h2 class="section-heading">Đối tác của chúng tôi</h2>
    <div v-if="partners && partners.length" class="partners-list">
      <div v-for="partner in partners" :key="partner.id" class="partner-item">
        <img :src="partner.Thuong_hieu_doi_tac" :alt="partner.Ten_doi_tac" class="partner-logo">
      </div>
    </div>
    <div v-else class="empty-state">
      <p>Không có đối tác nào đang hoạt động.</p>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
  setup() {
    const partners = ref([]);
    const fetchPartners = async () => {
      try {
        const response = await axios.get('https://api.sieuthi404.io.vn/api/partners/active');
        partners.value = response.data;
      } catch (error) {
        console.error("Lỗi khi tải đối tác:", error);
      }
    };

    onMounted(() => {
      fetchPartners();
    });

    return {
      partners
    };
  }
};
</script>

<style scoped>
/* Code CSS bạn có thể dán vào đây */
.partner-section {
  text-align: center;
  padding: 25px 15px;
  background-color: #f8f9fa;
  font-family: "Lora", serif;
}

.section-heading {
  font-size: 2.5rem;
  font-weight: 600;
  color: #343a40;
  margin-bottom: 30px;
  position: relative;
  display: inline-block;
}

.section-heading::after {
  content: '';
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 80px;
  height: 4px;
  background-color: #007bff;
  border-radius: 2px;
}

.partners-list {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 20px;
  margin-top: 50px;
}

.partner-item {
  width: 150px;
  height: 100px;
  background-color: #fff;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 15px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease-in-out;
  cursor: pointer;
}

.partner-item:hover {
  transform: translateY(-5px) scale(1.05);
  box-shadow: 0 8px 12px rgba(0, 0, 0, 0.1);
  border-color: #007bff;
}

.partner-logo {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  filter: grayscale(100%);
  opacity: 0.7;
  transition: all 0.3s ease-in-out;
}

.partner-item:hover .partner-logo {
  filter: grayscale(0%);
  opacity: 1;
}

.empty-state {
  margin-top: 50px;
  color: #6c757d;
  font-style: italic;
}

@media (max-width: 768px) {
  .section-heading {
    font-size: 2rem;
  }
  .partners-list {
    gap: 15px;
  }
  .partner-item {
    width: 120px;
    height: 80px;
  }
}

@media (max-width: 480px) {
  .section-heading {
    font-size: 1.5rem;
  }
  .partners-list {
    gap: 10px;
  }
  .partner-item {
    width: 90px;
    height: 60px;
  }
}
</style>