<template>
  <div class="edit-product">
    <h2>Chỉnh sửa sản phẩm</h2>
    <form @submit.prevent="updateProduct">
      <div>
        <label>Tên sản phẩm:</label>
        <input v-model="product.product_name" required />
      </div>
      <div>
        <label>Giá:</label>
        <input v-model.number="product.price" type="number" required />
      </div>
      <div>
        <label for="category">Danh mục:</label>
        <select v-model="product.category_id" id="category" required>
          <option
            v-for="category in categories"
            :key="category.category_id"
            :value="category.category_id"
          >
            {{ category.category_name }}
          </option>
        </select>
      </div>

      <div v-if="product.images && product.images.length > 0" style="margin-top: 1rem;">
        <div
          v-for="(image, index) in product.images"
          :key="image.image_id"
          style="display: flex; align-items: center; margin-bottom: 10px;"
        >
          <img
            :src="getFullImageUrl(image.image_url)"
            alt="Ảnh sản phẩm"
            style="width: 150px; margin-right: 10px;"
          />
          <button type="button" @click.prevent="removeImage(image.image_id)">
            Xóa ảnh
          </button>
        </div>
      </div>

      <div style="margin-top: 1rem;">
        <label>Thêm ảnh mới:</label>
        <input type="file" @change="handleImageUpload" accept="image/*" />
      </div>

      <div style="margin-top: 1rem;">
        <label>Mô tả:</label>
        <textarea v-model="product.description"></textarea>
      </div>

      <button type="submit" style="margin-top: 1rem;">Cập nhật</button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const product = ref({
  product_name: '',
  price: '',
  category_id: '',
  description: '',
  images: [],
});

const categories = ref([]);

const route = useRoute();
const router = useRouter();

const getProduct = async () => {
  try {
    const res = await axios.get(`http://localhost:8000/api/products/${route.params.id}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    product.value = res.data;
  } catch (err) {
    console.error('Không thể lấy sản phẩm', err);
  }
};

const getCategories = async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/categories', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    categories.value = res.data;
  } catch (err) {
    console.error('Không thể lấy danh mục', err);
  }
};

const getFullImageUrl = (path) => {
  if (!path) return '';
  return path.startsWith('http') ? path : `http://localhost:8000${path}`;
};

const removeImage = async (imageId) => {
  if (!confirm('Bạn có chắc muốn xóa ảnh này?')) return;
  try {
    await axios.delete(`http://localhost:8000/api/products/${route.params.id}/images/${imageId}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    // Xóa ảnh trong local state để không phải reload
    product.value.images = product.value.images.filter(img => img.image_id !== imageId);
    alert('Xóa ảnh thành công!');
  } catch (err) {
    console.error('Xóa ảnh thất bại', err.response ? err.response.data : err.message);
    alert('Xóa ảnh thất bại!');
  }
};

const handleImageUpload = async (e) => {
  const file = e.target.files[0];
  if (!file) return;

  const maxSizeInMB = 2;
  if (file.size > maxSizeInMB * 1024 * 1024) {
    alert('Kích thước tệp hình ảnh không được lớn hơn 2MB.');
    return;
  }

  const formData = new FormData();
  formData.append('image', file);

  try {
    const res = await axios.post(`http://localhost:8000/api/products/${route.params.id}/images`, formData, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'multipart/form-data',
      },
    });

    // Thêm ảnh mới vào product.images để cập nhật UI
    if (res.data && res.data.image) {
      product.value.images.push(res.data.image);
    }

    alert('Tải ảnh lên thành công');
    e.target.value = ''; 
  } catch (err) {
    console.error('Tải ảnh lên thất bại', err.response ? err.response.data : err.message);
    alert('Tải ảnh lên thất bại');
  }
};

const updateProduct = async () => {
  try {
    const payload = {
      product_name: product.value.product_name,
      price: product.value.price,
      category_id: product.value.category_id,
      description: product.value.description,
    };

    await axios.put(`http://localhost:8000/api/products/${route.params.id}`, payload, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });

    alert('Cập nhật thành công!');
    router.push('/admin/products');
  } catch (err) {
    console.error('Cập nhật thất bại', err.response ? err.response.data : err.message);
    alert('Cập nhật thất bại!');
  }
};

onMounted(() => {
  getProduct();
  getCategories();
});
</script>

<style scoped>
.edit-product {
  background: #fff;
  padding: 20px;
  border-radius: 10px;
  max-width: 600px;
  margin: auto;
}
.edit-product form div {
  margin-bottom: 1rem;
}
</style>
