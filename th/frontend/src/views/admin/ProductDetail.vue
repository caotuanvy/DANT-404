<template>
  <div class="product-detail">
    <h2>Chi tiết sản phẩm</h2>

    <div v-if="product">
      <p><strong>Tên:</strong> {{ product.ten_san_pham }}</p>

      <p><strong>Giá:</strong>
        <span v-if="product.gia">
          {{ product.gia.toLocaleString() }}đ
        </span>
        <span v-else>
          Đang cập nhật
        </span>
      </p>

      <p><strong>Loại:</strong>
        {{ product.danh_muc?.ten_danh_muc || 'Không có danh mục' }}
      </p>

      <p><strong>Mô tả:</strong> {{ product.mo_ta }}</p>

      <div v-if="product.hinh_anh_san_pham && product.hinh_anh_san_pham.length > 0">
        <p><strong>Hình ảnh:</strong></p>
        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
          <img
            v-for="(image, index) in product.hinh_anh_san_pham"
            :key="index"
            :src="getFullImageUrl(image.image_url)"
            alt="Ảnh sản phẩm"
            style="width: 100px; height: 100px; object-fit: cover;"
          />
        </div>
      </div>
    </div>
    <div v-else>
      <p>Đang tải sản phẩm...</p>
    </div>

    <button @click="goBack" style="margin-top: 1rem;">Quay lại</button>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const product = ref(null);
const route = useRoute();
const router = useRouter();

const getProductDetail = async () => {
  try {
    const res = await axios.get(`http://localhost:8000/api/products/${route.params.id}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    product.value = res.data;
  } catch (err) {
    console.error('Không thể lấy chi tiết sản phẩm', err);
  }
};

const getFullImageUrl = (url) => {
  if (!url) return '';
  return url.startsWith('http') ? url : `http://localhost:8000${url}`;
};

const goBack = () => {
  router.push('/admin/products');
};

onMounted(() => {
  getProductDetail();
});
</script>

<style scoped>
.product-detail {
  background: #fff;
  padding: 20px;
  border-radius: 10px;
  max-width: 700px;
  margin: auto;
}
</style>
