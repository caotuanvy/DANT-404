<template>
  <section class="variant-section">
    <h3>Biến thể sản phẩm</h3>

    <div v-if="loading">Đang tải biến thể...</div>

    <table v-if="!loading && variants.length > 0" class="variant-table">
      <thead>
        <tr>
          <th>Kích thước</th>
          <th>Màu sắc</th>
          <th>Tồn kho</th>
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
            <button class="edit-btn" @click="startEditVariant(variant)">Sửa</button>
            <button class="delete-btn" @click="deleteVariant(variant.bien_the_id)">Xóa</button>
          </td>
        </tr>
      </tbody>
    </table>
    <p v-if="!loading && variants.length === 0">Chưa có biến thể nào.</p>

    <hr />

    <div class="form-wrapper">
      <fieldset>
        <legend>➕ Thêm biến thể mới</legend>
        <form @submit.prevent="addVariant" class="form-layout">
          <input v-model="newVariant.kich_thuoc" placeholder="Kích thước" required />
          <input v-model="newVariant.mau_sac" placeholder="Màu sắc" required />
          <input v-model.number="newVariant.so_luong_ton_kho" placeholder="Tồn kho" type="number" required />
          <input v-model.number="newVariant.gia" placeholder="Giá" type="number" required />
          <button type="submit" class="add-btn">Thêm</button>
        </form>
      </fieldset>

      <fieldset v-if="editingVariantId">
        <legend>✏️ Chỉnh sửa biến thể</legend>
        <form @submit.prevent="updateVariant" class="form-layout">
          <input v-model="editVariant.kich_thuoc" placeholder="Kích thước" required />
          <input v-model="editVariant.mau_sac" placeholder="Màu sắc" required />
          <input v-model.number="editVariant.so_luong_ton_kho" placeholder="Tồn kho" type="number" required />
          <input v-model.number="editVariant.gia" placeholder="Giá" type="number" required />
          <div class="btn-group">
            <button type="submit" class="update-btn">Cập nhật</button>
            <button type="button" @click="editingVariantId = null" class="cancel-btn">Hủy</button>
          </div>
        </form>
      </fieldset>
    </div>

    <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
  </section>
</template>


<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useRoute } from 'vue-router';

const route = useRoute();
const productId = route.params.id;
const editingVariantId = ref(null);
const editVariant = ref({
  kich_thuoc: '',
  mau_sac: '',
  so_luong_ton_kho: 0,
  gia: 0,
});

const variants = ref([]);
const loading = ref(false);
const errorMessage = ref('');

const newVariant = ref({
  kich_thuoc: '',
  mau_sac: '',
  so_luong_ton_kho: '',
  gia: '',
});
const startEditVariant = (variant) => {
  editingVariantId.value = variant.bien_the_id;
  editVariant.value = { ...variant }; 
};

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
const updateVariant = async () => {
  try {
    await axios.put(`http://localhost:8000/api/variants/${editingVariantId.value}`, editVariant.value, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    editingVariantId.value = null;
    editVariant.value = { kich_thuoc: '', mau_sac: '', so_luong_ton_kho: 0, gia: 0 };
    getVariants();
  } catch (error) {
    console.error('Lỗi khi cập nhật biến thể:', error);
    errorMessage.value = 'Cập nhật biến thể thất bại.';
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
  padding: 20px;
  background: #f9f9f9;
  border-radius: 8px;
}

.variant-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

.variant-table th,
.variant-table td {
  border: 1px solid #ccc;
  padding: 10px;
  text-align: center;
}

.variant-table th {
  background-color: #e0e0e0;
}

button {
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  color: white;
  margin: 2px;
  cursor: pointer;
}

.edit-btn {
  background-color: #1266b3;
}

.edit-btn:hover {
  background-color: #1266b3;
}

.delete-btn {
  background-color: #e53935;
}

.delete-btn:hover {
  background-color: #c62828;
}

.add-btn {
  background-color: #1266b3;
}

.update-btn {
  background-color: #f9a825;
}

.cancel-btn {
  background-color: #9e9e9e;
}

.form-wrapper {
  display: flex;
  flex-direction: column;
  gap: 30px;
  margin-top: 20px;
}

form.form-layout {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: center;
}

input {
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
  flex: 1 1 200px;
}

fieldset {
  border: 1px solid #ccc;
  border-radius: 6px;
  padding: 15px;
  background-color: #fff;
}

legend {
  font-weight: bold;
  padding: 0 8px;
}

.btn-group {
  display: flex;
  gap: 10px;
}

.error {
  color: red;
  margin-top: 15px;
}
</style>
