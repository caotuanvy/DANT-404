<template>
  <div class="page-wrapper">
   

    <div class="content-card">
      <div class="table-container">
        <div class="danhmuc-grid">
          <div v-if="loading" class="text-center py-8 full-width">Đang tải danh mục...</div>
          <div v-else-if="error" class="text-center py-8 full-width error-message">{{ error }}</div>
          <div v-else-if="limitedCategories.length === 0" class="text-center py-8 full-width no-data-message">
            Không có danh mục cấp cha nào để hiển thị.
          </div>
          
          <router-link
            v-else
            v-for="cat in limitedCategories"
            :key="cat.id"
            :to="{ name: 'CategoryProductsUser', params: { id: cat.id, slug: toSlug(cat.ten_danh_muc) } }"
            class="danhmuc-item-link"
            :class="{ 'is-inactive': cat.trang_thai == 0 }"
          >
            <img
              :src="cat.image_url"
              :alt="cat.ten_danh_muc"
              class="danhmuc-thumbnail"
            />
            <div class="overlay"></div>
            <span class="danhmuc-title">{{ cat.ten_danh_muc }}</span>
          </router-link>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const categories = ref([]);
const loading = ref(true);
const error = ref(null);
const router = useRouter();
const API_BASE_URL = 'http://localhost:8000/api';

const getCategories = async () => {
  loading.value = true;
  error.value = null;
  try {
    const res = await axios.get(`${API_BASE_URL}/danh-muc-cha`);
    categories.value = res.data;
  } catch (err) {
    console.error('Lỗi khi lấy danh mục cấp cha:', err);
    error.value = 'Không thể tải danh mục cấp cha. Vui lòng thử lại sau.';
  } finally {
    loading.value = false;
  }
};

const limitedCategories = computed(() => {
  return categories.value.slice(0, 8);
});

// Hàm để tạo slug từ một chuỗi
const toSlug = (str) => {
    if (!str) return '';
    str = str.toLowerCase();
    str = str.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ặ|ẵ|â|ấ|ầ|ẩ|ậ|ẫ/g, "a");
    str = str.replace(/é|è|ẻ|ẹ|ẽ|ê|ế|ề|ể|ệ|ễ/g, "e");
    str = str.replace(/i|í|ì|ỉ|ị|ĩ/g, "i");
    str = str.replace(/ó|ò|ỏ|ọ|õ|ô|ố|ồ|ổ|ộ|ỗ|ơ|ớ|ờ|ở|ợ|ỡ/g, "o");
    str = str.replace(/ú|ù|ủ|ụ|ũ|ư|ứ|ừ|ử|ự|ữ/g, "u");
    str = str.replace(/ý|ỳ|ỷ|ỵ|ỹ/g, "y");
    str = str.replace(/đ/g, "d");
    str = str.replace(/[^a-z0-9 ]/g, "");
    str = str.replace(/\s+/g, '-');
    str = str.replace(/^-+|-+$/g, "");
    return str;
};

onMounted(getCategories);
</script>

<style scoped>
/* CSS đã được tối ưu và chỉnh sửa */
.page-wrapper {
  
  padding: 2rem;
  min-height: auto;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}
.page-header {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 2rem;
}
.page-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: #111827;
}
.content-card {
  background-color: #fff;
  border-radius: 8px;

  padding: 1.5rem;
}

.table-container {
  overflow-x: auto;
}

.danhmuc-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 15px;
  padding: 15px;
  width: 100%;
}

.danhmuc-item-link {
  position: relative;
  display: block;
  width: 100%;
  height: 150px; /* Chiều cao cố định cho mỗi ô */
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  text-decoration: none;
  color: #fff;
  cursor: pointer;
}

.danhmuc-item-link:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.danhmuc-thumbnail {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.3s ease;
}

.danhmuc-item-link:hover .danhmuc-thumbnail {
  transform: scale(1.1);
}

/* Hiệu ứng mờ overlay để chữ dễ đọc hơn */
.overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  transition: background 0.3s ease;
}

.danhmuc-item-link:hover .overlay {
  background: rgba(0, 0, 0, 0.7);
}

.danhmuc-title {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-weight: 700;
  font-size: 1.2em;
  text-align: center;
  z-index: 10;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
  word-break: break-word;
  padding: 0 10px;
}

.is-inactive {
  opacity: 0.6;
}
.is-inactive .overlay {
  background: rgba(0, 0, 0, 0.8);
}
.is-inactive .danhmuc-title {
  color: #c9c9c9 !important;
}

.text-center { text-align: center; }
.py-8 { padding-top: 2rem; padding-bottom: 2rem; }
.full-width { grid-column: 1 / -1; }
</style>