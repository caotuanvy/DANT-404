<template>
  <section class="content">
    <h2>Danh sách sản phẩm</h2>

    <!-- Nút thêm sản phẩm -->
    <router-link to="/admin/products/add" class="btn-add">+ Thêm sản phẩm</router-link>
    <div v-if="loading">Đang tải dữ liệu...</div>

    <table v-if="!loading && products.length > 0">
  <thead>
    <tr>
      <th>#</th>
      <th>Tên sản phẩm</th>
      <th>Hình ảnh</th>
      <th>Nổi Bật</th>
      <th>Mô tả</th>
      <th>Danh mục</th>
      <th>Hành động</th>
    </tr>
  </thead>
  <tbody>
    <tr v-for="(product, index) in products" :key="product.product_id">
      <td>{{ index + 1 }}</td>
      <td>{{ product.product_name }}</td>
      <td>
        <img
      :src="getImageUrl(product.images?.[0])"
      alt="Ảnh sản phẩm"
      style="width: 60px; height: auto; object-fit: cover;"
      >
      </td>

      <td>
        <label class="switch">
          <input 
            type="checkbox" 
            @change="toggleNoiBat(product)" 
            :checked="product.noi_bat == 2"
          />
          <span class="slider"></span>
        </label>
      </td>
      <td>{{ product.description }}</td>
      <td>
        <router-link :to="`/danh-muc/${product.danh_muc?.category_id}`">
          {{ product.danh_muc?.ten_danh_muc || 'Không có' }}
        </router-link>
      </td>
      <td>
      <div class="action-buttons">
        <router-link :to="`/admin/products/${product.product_id}`" class="btn-detail">Xem chi tiết</router-link>
        <br>
        <router-link :to="`/admin/products/${product.product_id}/edit`" class="btn-edit">Sửa</router-link>
        <br>
        <router-link :to="`/admin/products/${product.product_id}/variants`"  class="btn-variants"> Biến thể ({{ product.so_bien_the || 0 }})</router-link>
        <br>
        <button @click="deleteProduct(product.product_id)" class="btn-delete">Xóa</button>
      </div>
    </td>

    </tr>
  </tbody>
</table>


    <p v-if="!loading && products.length === 0">Chưa có sản phẩm nào.</p>
    <p v-if="errorMessage" style="color: red;">{{ errorMessage }}</p>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const products = ref([]);
const errorMessage = ref('');
const loading = ref(false);

const getProducts = async () => {
  loading.value = true;
  errorMessage.value = '';
  try {
    const res = await axios.get('http://localhost:8000/api/products', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    // console.log('Dữ liệu sản phẩm từ API:', res.data); 
    products.value = res.data;
  } catch (error) {
    console.error('Lỗi khi lấy sản phẩm:', error);
    errorMessage.value = 'Lỗi khi lấy sản phẩm: ' + (error.response?.data?.message || error.message);
  } finally {
    loading.value = false;
  }
};
const toggleNoiBat = async (product) => {
  const newStatus = product.noi_bat === 1 ? 2 : 1;
  try {
    await axios.put(`http://localhost:8000/api/products/${product.product_id}/toggle-noi-bat`, {
      noi_bat: newStatus,
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    product.noi_bat = newStatus; 
  } catch (error) {
    console.error('Lỗi khi cập nhật trạng thái nổi bật:', error);
    alert('Cập nhật trạng thái nổi bật thất bại!');
  }
};

const deleteProduct = async (id) => {
  if (!confirm('Bạn có chắc muốn xóa sản phẩm này không?')) return;

  try {
    await axios.delete(`http://localhost:8000/api/products/${id}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    products.value = products.value.filter(p => p.product_id !== id);
  } catch (error) {
    console.error('Lỗi khi xóa sản phẩm:', error);
    alert('Xóa sản phẩm thất bại!');
  }
};
const getImageUrl = (url) => {
  if (!url) return 'https://placehold.co/60x60?text=No+Image';
  return url; 
};



onMounted(() => {
  getProducts();
});
</script>

<style scoped>
.content {
  padding: 20px;
}

.btn-add {
  display: inline-block;
  margin-bottom: 10px;
  padding: 6px 12px;
  background-color: #4FC3F7;
  color: white;
  border-radius: 4px;
  text-decoration: none;
  transition: background-color 0.3s ease, transform 0.3s ease;
  font-weight: bolder;
}
.btn-add:hover {
  background-color: #039BE5;
    transform: scale(1.05);
}
.btn-edit {
  color: #2196f3;
  text-decoration: none;
  margin: 0 5px;
  font-weight: bolder;
}

.btn-delete {
  border: none;
  background: none;
  color: red;
  cursor: pointer;
  padding: 0;
  font-size: 1em;
  font-weight: bolder;
}
button:hover {
  text-decoration: underline;
}
.btn-variants {
  color: #673AB7;
  text-decoration: none;
  margin: 0 5px;
  font-weight: bolder;
}
.btn-variants:hover {
  text-decoration: underline;
}

</style>
