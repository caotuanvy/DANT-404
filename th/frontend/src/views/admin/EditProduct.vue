<template>
  <div class="edit-product">
    <h2>Chỉnh sửa sản phẩm</h2>
    <form @submit.prevent="updateProduct">
      <div>
        <label>Tên sản phẩm:</label>
        <input v-model="product.ten_san_pham" required />
      </div>

      <div>
        <label for="category">Danh mục:</label>
        <select v-model="product.ten_danh_muc_id" id="category" required>
          <option
            v-for="category in categories"
            :key="category.category_id"
            :value="category.category_id"
          >
            {{ category.ten_danh_muc }}
          </option>
        </select>
      </div>

      <!-- Danh sách ảnh -->
      <div v-if="product.images.length" style="margin-top: 1rem;">
        <div
          v-for="(image, index) in product.images"
          :key="image.id"
          style="display: flex; align-items: center; margin-bottom: 10px;"
        >
          <img
            :src="image.url"
            alt="Ảnh sản phẩm"
            style="width: 150px; margin-right: 10px;"
          />
          <button type="button" @click.prevent="removeImageById(image.id, index)">
            Xóa ảnh
          </button>
        </div>
      </div>

      <div style="margin-top: 1rem;">
        <label>Thêm ảnh mới:</label>
        <input type="file" multiple @change="handleImageUpload" />
      </div>

      <div style="margin-top: 1rem;">
        <label>Mô tả:</label>
        <textarea v-model="product.mo_ta"></textarea>
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
  ten_san_pham: '',
  ten_danh_muc_id: '',
  mo_ta: '',
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

    const p = res.data;

    product.value = {
      ten_san_pham: p.ten_san_pham,
      ten_danh_muc_id: p.danh_muc?.category_id || '',
      mo_ta: p.mo_ta,
      images: (p.images || []).map(img => ({
        id: img.id,
        image_path: img.image_path,
        url: img.url || `http://localhost:8000/storage/${img.image_path}`,
      })),
    };
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
    const res = await axios.post(`http://localhost:8000/api/products/${route.params.id}/images`, formData, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'multipart/form-data',
      },
    });

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

const updateProduct = async () => {
  try {
    const payload = {
      ten_san_pham: product.value.ten_san_pham,
      ten_danh_muc_id: product.value.ten_danh_muc_id,
      mo_ta: product.value.mo_ta,
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
