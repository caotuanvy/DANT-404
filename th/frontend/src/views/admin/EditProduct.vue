<template>
  <div class="edit-product">
    <h2>🛠️ Chỉnh sửa sản phẩm</h2>
    <form @submit.prevent="updateProduct" class="form-grid">

      <div class="form-group">
        <label>Tên sản phẩm:</label>
        <input v-model="product.product_name" required />
      </div>

      <div class="form-group">
        <label>Danh mục:</label>
        <select v-model="product.ten_danh_muc_id" required>
          <option
            v-for="category in categories"
            :key="category.category_id"
            :value="category.category_id"
          >
            {{ category.ten_danh_muc }}
          </option>
        </select>
      </div>

      <div class="form-group">
        <label>Khuyến mãi (%):</label>
        <input type="number" v-model="product.khuyen_mai" required />
      </div>
      <div>
        <div>
        <label>Đường dẫn:</label>
        <input
          type="text"
          v-model="product.slug"
          @input="userEditedSlug.value = true"
          placeholder="Đường dẫn sản phẩm (tự động tạo)"
        />
        <span v-if="errors.slug" style="color: red">{{ errors.slug[0] }}</span>
      </div>
      </div>

      <div class="form-group full">
        <label>Mô tả:</label>
        <textarea v-model="product.description"></textarea>
      </div>

      <!-- Danh sách ảnh -->
      <div v-if="product.images.length" class="image-grid full">
        <div
          v-for="(image, index) in product.images"
          :key="image.id"
          class="image-item"
        >
          <img :src="image.url" alt="Ảnh sản phẩm" class="preview-img" />
          <button
            type="button"
            class="delete-btn"
            @click="removeImageById(image.id, index)"
            title="Xóa ảnh"
          >
            🗑️
          </button>
        </div>
      </div>

      <!-- Upload ảnh -->
      <div class="form-group full">
        <label>Thêm ảnh mới:</label>
        <input type="file" multiple @change="handleImageUpload" />
      </div>

      <div class="form-group full">
        <button type="submit" class="update-btn">💾 Cập nhật</button>
      </div>
    </form>
  </div>
</template>

<script setup>

import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
const errors = ref({});
const product = ref({
  ten_san_pham: '',
  ten_danh_muc_id: '',
  mo_ta: '',
  khuyen_mai: '', 
  images: [],
  slug: '',
});

const categories = ref([]);
const route = useRoute();
const router = useRouter();
const userEditedSlug = ref(false);

const getProduct = async () => {
  try {
    const res = await axios.get(`http://localhost:8000/api/products/${route.params.id}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });

    const p = res.data;
    product.value.product_name = p.product_name;
    product.value.ten_danh_muc_id = p.danh_muc?.category_id || '';
    product.value.slug = p.slug || '';
    product.value.description = p.description;
    product.value.khuyen_mai = p.khuyen_mai || 0; 
    product.value.images = (p.images || []).map(img => ({
      id: img.id,
      image_path: img.image_path,
      url: img.url || `http://localhost:8000/storage/${img.image_path}`,
    }));
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

const removeImageById = async (imageId, index) => {
  if (!confirm('Bạn có chắc muốn xóa ảnh này?')) return;

  try {
    await axios.delete(`http://localhost:8000/api/product-images/${imageId}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });

    product.value.images.splice(index, 1);
    alert('Xóa ảnh thành công!');
  } catch (err) {
    console.error('Xóa ảnh thất bại', err.response ? err.response.data : err.message);
    alert('Xóa ảnh thất bại!');
  }
};

const handleImageUpload = async (e) => {
  const files = e.target.files;
  if (!files.length) return;

  const maxSizeInMB = 2;
  for (let i = 0; i < files.length; i++) {
    if (files[i].size > maxSizeInMB * 1024 * 1024) {
      alert(`Ảnh thứ ${i + 1} vượt quá 2MB, vui lòng chọn ảnh nhỏ hơn.`);
      return;
    }
  }

  const formData = new FormData();
  for (let i = 0; i < files.length; i++) {
    formData.append('images[]', files[i]);
  }

  try {
    const res = await axios.post(
      `http://localhost:8000/api/products/${route.params.id}/images`,
      formData,
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'multipart/form-data',
        },
      }
    );

    if (res.data && res.data.uploaded_images) {
      const newImages = res.data.uploaded_images.map(img => ({
        id: img.id,
        image_path: img.image_path,
        url: `http://localhost:8000/storage/${img.image_path}`,
      }));
      product.value.images.push(...newImages);
    }

    alert('Tải ảnh lên thành công');
    e.target.value = '';
  } catch (err) {
    console.error('Tải ảnh lên thất bại', err.response ? err.response.data : err.message);
    alert('Tải ảnh lên thất bại');
  }
};
const generateSlug = (text) => {
  return text
    .toString()
    .normalize('NFD')                      
    .replace(/[\u0300-\u036f]/g, '')      
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9\s-]/g, '')         
    .replace(/\s+/g, '-')               
    .replace(/-+/g, '-');                 
};
const updateProduct = async () => {
   errors.value = {};
  try {
    const payload = {
      ten_san_pham: product.value.product_name,
      category_id: product.value.ten_danh_muc_id,
      slug: product.value.slug,
      mo_ta: product.value.description,
      khuyen_mai: product.value.khuyen_mai,
      
    };

    await axios.put(`http://localhost:8000/api/products/${route.params.id}`, payload, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });

    alert('Cập nhật thành công!');
    router.push('/admin/products');
  } catch (err) {
  if (err.response && err.response.data && err.response.data.errors) {
    errors.value = err.response.data.errors;
  }
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
  background: #fdfdfd;
  padding: 32px;
  border-radius: 16px;
  max-width: 900px;
  margin: auto;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

h2 {
  text-align: center;
  margin-bottom: 24px;
  font-size: 28px;
  color: #2c3e50;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group.full {
  grid-column: span 2;
}

label {
  font-weight: 600;
  margin-bottom: 6px;
  color: #34495e;
}

input,
select,
textarea {
  padding: 10px 14px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 16px;
  background: #fff;
  transition: border 0.2s ease;
}

input:focus,
select:focus,
textarea:focus {
  border-color: #4caf50;
  outline: none;
}

textarea {
  resize: vertical;
  min-height: 100px;
}
.update-btn {
  padding: 12px 20px;
  background-color: #3b82f6;
  color: white;
  font-weight: 600;
  font-size: 16px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s ease;
}

.update-btn:hover {
  background-color: #316dcf;
}


.image-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 16px;
}

.image-item {
  position: relative;
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
  background: #fff;
}

.preview-img {
  width: 100%;
  height: 120px;
  object-fit: cover;
  display: block;
}

.delete-btn {
  position: absolute;
  top: 8px;
  right: 8px;
  background: rgba(255, 77, 79, 0.9);
  color: white;
  border: none;
  border-radius: 50%;
  padding: 6px 8px;
  cursor: pointer;
  font-size: 14px;
}

.delete-btn:hover {
  background: rgba(217, 54, 62, 0.95);
}
</style>
