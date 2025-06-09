<template>
  <section class="variant-section">
    <h3>Biến thể sản phẩm</h3>

    <div v-if="loading">Đang tải biến thể...</div>
    <table v-if="!loading && variants.length > 0">
      <thead>
        <tr>
          <th>Kích thước</th>
          <th>Màu sắc</th>
          <th>Số lượng tồn kho</th>
          <th>Giá</th>
          <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="variant in variants" :key="variant.bien_the_id">
          <td>{{ variant.kich_thuoc }}</td>
          <td>{{ variant.mau_sac }}</td>
          <td>{{ variant.so_luong_ton_kho }}</td>
          <td>{{ formatPrice(variant.gia) }}</td>
          <td>
            <button @click="deleteVariant(variant.bien_the_id)">Xóa</button>
          </td>
        </tr>
      </tbody>
    </table>
    <p v-if="!loading && variants.length === 0">Chưa có biến thể nào.</p>

    <hr />

    <h4>Thêm biến thể mới</h4>
    <form @submit.prevent="addVariant">
      <input v-model="newVariant.kich_thuoc" placeholder="Kích thước" required />
      <input v-model="newVariant.mau_sac" placeholder="Màu sắc" required />
      <input v-model.number="newVariant.so_luong_ton_kho" placeholder="Tồn kho" type="number" required />
      <input v-model.number="newVariant.gia" placeholder="Giá" type="number" required />
      <button type="submit">Thêm</button>
    </form>

    <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
  </section>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useRoute } from 'vue-router';

const route = useRoute();
const productId = route.params.id;

const variants = ref([]);
const loading = ref(false);
const errorMessage = ref('');

const newVariant = ref({
  kich_thuoc: '',
  mau_sac: '',
  so_luong_ton_kho: '',
  gia: '',
});

const getVariants = async () => {
  loading.value = true;
  try {
    const res = await axios.get(`http://localhost:8000/api/products/${productId}/variants`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    variants.value = res.data;
  } catch (error) {
    console.error('Lỗi khi lấy biến thể:', error);
    errorMessage.value = 'Không thể tải biến thể.';
  } finally {
    loading.value = false;
  }
};

const addVariant = async () => {
  try {
    await axios.post(`http://localhost:8000/api/products/${productId}/variants`, newVariant.value, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    newVariant.value = { kich_thuoc: '', mau_sac: '', so_luong_ton_kho: 0, gia: 0 };
    getVariants();
  } catch (error) {
    console.error('Lỗi khi thêm biến thể:', error);
    errorMessage.value = 'Thêm biến thể thất bại.';
  }
};

const deleteVariant = async (variantId) => {
  if (!confirm('Bạn có chắc muốn xóa biến thể này không?')) return;

  try {
    await axios.delete(`http://localhost:8000/api/variants/${variantId}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    variants.value = variants.value.filter(v => v.bien_the_id !== variantId);
  } catch (error) {
    console.error('Lỗi khi xóa biến thể:', error);
    errorMessage.value = 'Xóa biến thể thất bại.';
  }
};

const formatPrice = (price) => {
 return Number(price).toLocaleString('vi-VN', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' đ';

};

onMounted(() => {
  getVariants();
});
</script>

<style scoped>
.variant-section {
  margin-top: 30px;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}
th, td {
  border: 1px solid #ccc;
  padding: 6px 10px;
  text-align: left;
}
form {
  margin-top: 20px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}
input {
  padding: 6px;
  border: 1px solid #ccc;
  border-radius: 4px;
}
button {
  padding: 6px 12px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
}
button:hover {
  background-color: #388E3C;
  cursor: pointer;
}
.error {
  color: red;
  margin-top: 10px;
}
</style>
