<template>
  <div class="add-product-container">
    <div v-if="!createdProductId">
      <div class="add-product">
        <h2>Thêm sản phẩm mới</h2>
        <form @submit.prevent="addProduct">
          <div>
            <label for="name">Tên sản phẩm:</label>
            <input
              type="text"
              id="name"
              v-model="product.name"
              placeholder="Nhập tên sản phẩm"
              required
            />
          </div>

          <div>
            <label for="slug">Đường Dẫn</label>
            <input
              type="text"
              id="slug"
              v-model="product.slug"
              placeholder="Đường dẫn sản phẩm (tự động tạo)"
              @input="userEditedSlug = true"
            />
          </div>

          <div>
            <label for="category">Danh mục:</label>
            <select v-model="product.category_id" required>
              <option value="">Chọn danh mục</option>
              <option
                v-for="category in categories"
                :key="category.category_id"
                :value="category.category_id"
              >
                {{ category.ten_danh_muc }}
              </option>
            </select>
          </div>

          <div>
            <label for="description">Mô tả:</label>
            <textarea
              id="description"
              v-model="product.description"
              placeholder="Nhập mô tả sản phẩm"
              rows="4"
              required
            ></textarea>
          </div>

          <div>
            <label for="images">Chọn ảnh sản phẩm:</label>
            <input
              type="file"
              id="images"
              @change="handleImageUpload"
              accept="image/*"
              multiple
              required
            />
            <div v-if="imagePreviews.length > 0" class="image-preview-container">
              <h4>Xem trước ảnh:</h4>
              <div class="previews">
                <img v-for="(src, index) in imagePreviews" :key="index" :src="src" alt="Ảnh xem trước" />
              </div>
            </div>
          </div>

          <div>
            <button type="submit" :disabled="isSubmitting">
              {{ isSubmitting ? 'Đang xử lý...' : 'Thêm sản phẩm & Tiếp tục' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="createdProductId">
      <section class="variant-section">
        <h3>Quản lý Biến thể cho sản phẩm: <strong>{{ product.name }}</strong></h3>

        <div v-if="loadingVariants">Đang tải biến thể...</div>
        <table v-if="!loadingVariants && variants.length > 0" class="variant-table">
          <thead>
            <tr>
              <th>Tên biến thể</th>
              <th>Kích thước</th>
              <th>Màu sắc</th>
              <th>Tồn kho</th>
              <th>Giá</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="variant in variants" :key="variant.bien_the_id">
              <td>{{ variant.ten_bien_the }}</td>
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
        <p v-if="!loadingVariants && variants.length === 0">Sản phẩm này chưa có biến thể nào.</p>

        <hr />

        <div class="form-wrapper">
          <fieldset v-if="!editingVariantId">
            <legend>➕ Thêm biến thể mới</legend>
            <form @submit.prevent="addVariant" class="form-layout">
              <input v-model="newVariant.ten_bien_the" placeholder="Tên biến thể" required />
              <input v-model="newVariant.kich_thuoc" placeholder="Kích thước" />
              <input v-model="newVariant.mau_sac" placeholder="Màu sắc" />
              <input v-model.number="newVariant.so_luong_ton_kho" placeholder="Tồn kho" type="number" required />
              <input v-model.number="newVariant.gia" placeholder="Giá" type="number" required />
              <button type="submit" class="add-btn">Thêm</button>
            </form>
          </fieldset>

          <fieldset v-if="editingVariantId">
            <legend>✏️ Chỉnh sửa biến thể</legend>
            <form @submit.prevent="updateVariant" class="form-layout">
              <input v-model="editVariant.ten_bien_the" placeholder="Tên biến thể" required />
              <input v-model="editVariant.kich_thuoc" placeholder="Kích thước" />
              <input v-model="editVariant.mau_sac" placeholder="Màu sắc" />
              <input v-model.number="editVariant.so_luong_ton_kho" placeholder="Tồn kho" type="number" required />
              <input v-model.number="editVariant.gia" placeholder="Giá" type="number" required />
              <div class="btn-group">
                <button type="submit" class="update-btn">Cập nhật</button>
                <button type="button" @click="cancelEdit" class="cancel-btn">Hủy</button>
              </div>
            </form>
          </fieldset>
        </div>
        <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

        <div class="completion-buttons">
            <button @click="$router.push('/admin/products')" class="finish-btn">Hoàn tất và về danh sách</button>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();

// --- STATE QUẢN LÝ CHUNG ---
const createdProductId = ref(null); // **Chìa khóa**: Lưu ID sản phẩm sau khi tạo thành công.
const isSubmitting = ref(false);
const errorMessage = ref('');

// --- STATE CHO SẢN PHẨM ---
const product = ref({
  name: '',
  category_id: '',
  description: '',
  slug: '',
});
const imageFiles = ref([]);
const imagePreviews = ref([]);
const categories = ref([]);
const userEditedSlug = ref(false);

// --- STATE CHO BIẾN THỂ ---
const variants = ref([]);
const loadingVariants = ref(false);
const editingVariantId = ref(null);
const newVariant = ref({
  ten_bien_the: '',
  kich_thuoc: '',
  mau_sac: '',
  so_luong_ton_kho: '',
  gia: '',
});
const editVariant = ref({});


// --- METHODS LIÊN QUAN ĐẾN SẢN PHẨM ---

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

watch(() => product.value.name, (newVal) => {
  if (!userEditedSlug.value) {
    product.value.slug = generateSlug(newVal);
  }
});

const handleImageUpload = (event) => {
  imageFiles.value = Array.from(event.target.files);
  imagePreviews.value = imageFiles.value.map(file => URL.createObjectURL(file));
};

const getCategories = async () => {
  try {
    const res = await axios.get("http://localhost:8000/api/categories", {
      headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
    });
    categories.value = res.data;
  } catch (error) {
    alert("Không thể tải danh mục!");
  }
};

const addProduct = async () => {
  isSubmitting.value = true;
  errorMessage.value = '';
  try {
    const token = localStorage.getItem("token");
    if (!token) {
      alert("Bạn cần đăng nhập!");
      isSubmitting.value = false;
      return;
    }

    const formData = new FormData();
    formData.append("ten_san_pham", product.value.name);
    formData.append("ten_danh_muc_id", product.value.category_id);
    formData.append("mo_ta", product.value.description);
    formData.append("slug", product.value.slug);
    imageFiles.value.forEach(file => {
      formData.append("images[]", file);
    });

    const res = await axios.post("http://localhost:8000/api/products", formData, {
      headers: { Authorization: `Bearer ${token}` },
    });

    if (res.status === 201) {
      alert("Thêm sản phẩm thành công! Bây giờ hãy thêm biến thể.");
      // **Chìa khóa**: Lưu ID sản phẩm vừa tạo và chuyển sang giao diện biến thể
      // Giả sử API trả về đối tượng sản phẩm có `product_id`
      createdProductId.value = res.data.data.san_pham_id;  
      // Tải các biến thể (ban đầu sẽ rỗng)
      await getVariants();
    }
  } catch (error) {
    alert("Có lỗi xảy ra khi thêm sản phẩm!");
    console.error(error);
  } finally {
    isSubmitting.value = false;
  }
};

// --- METHODS LIÊN QUAN ĐẾN BIẾN THỂ ---

const getAuthHeaders = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
});

const getVariants = async () => {
  if (!createdProductId.value) return;
  loadingVariants.value = true;
  errorMessage.value = '';
  try {
  
    const res = await axios.get(`http://localhost:8000/api/products/${createdProductId.value}`, {
      headers: getAuthHeaders(),
    });
    variants.value = res.data.variants || []; 

  } catch (error) {
    console.error('ERROR when calling getVariants:', error.response || error);
    errorMessage.value = 'Không thể tải danh sách biến thể.';
  } finally {
    loadingVariants.value = false;
  }
};

const addVariant = async () => {
  errorMessage.value = '';
  try {
    await axios.post(`http://localhost:8000/api/products/${createdProductId.value}/variants`, newVariant.value, {
      headers: getAuthHeaders(),
    });
    newVariant.value = { ten_bien_the: '', kich_thuoc: '', mau_sac: '', so_luong_ton_kho: '', gia: '' };
    await getVariants(); // Tải lại danh sách
  } catch (error) {
    console.error('Lỗi khi thêm biến thể:', error);
    errorMessage.value = 'Thêm biến thể thất bại. Vui lòng kiểm tra lại thông tin.';
  }
};

const startEditVariant = (variant) => {
  editingVariantId.value = variant.bien_the_id;
  editVariant.value = { ...variant };
};

const cancelEdit = () => {
    editingVariantId.value = null;
    editVariant.value = {};
}

const updateVariant = async () => {
  errorMessage.value = '';
  try {
    await axios.put(`http://localhost:8000/api/variants/${editingVariantId.value}`, editVariant.value, {
       headers: getAuthHeaders(),
    });
    cancelEdit();
    await getVariants();
  } catch (error) {
    console.error('Lỗi khi cập nhật biến thể:', error);
    errorMessage.value = 'Cập nhật biến thể thất bại.';
  }
};

const deleteVariant = async (variantId) => {
  if (!confirm('Bạn có chắc muốn xóa biến thể này không?')) return;
  errorMessage.value = '';
  try {
    await axios.delete(`http://localhost:8000/api/variants/${variantId}`, {
      headers: getAuthHeaders(),
    });
    await getVariants(); // Tải lại danh sách
  } catch (error) {
    console.error('Lỗi khi xóa biến thể:', error);
    errorMessage.value = 'Xóa biến thể thất bại.';
  }
};

const formatPrice = (price) => {
  if (price === null || price === undefined) return '0 đ';
  return Number(price).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
};

// --- LIFECYCLE HOOK ---
onMounted(() => {
  getCategories();
});

</script>

<style scoped>
/* --- General Styles --- */
.add-product-container {
  max-width: 900px;
  margin: 20px auto;
  font-family: sans-serif;
}

.error {
  color: #e53935;
  margin-top: 15px;
  text-align: center;
}

hr {
  border: 0;
  border-top: 1px solid #eee;
  margin: 2rem 0;
}

button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

/* --- Add Product Form Styles --- */
.add-product {
  background: #fff;
  padding: 25px;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.add-product h2 {
    margin-top: 0;
    text-align: center;
    color: #333;
}
.add-product form div {
  margin-bottom: 1.2rem;
}
.add-product label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #555;
}
.add-product input[type="text"],
.add-product input[type="file"],
.add-product select,
.add-product textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box; /* Important */
}
.add-product button {
  padding: 12px 20px;
  background-color: #28a745;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  width: 100%;
  font-size: 16px;
  font-weight: bold;
}
.add-product button:hover {
    background-color: #218838;
}

.image-preview-container {
    margin-top: 15px;
}
.previews {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}
.previews img {
  width: 120px;
  height: 120px;
  object-fit: cover;
  border: 1px solid #ddd;
  border-radius: 5px;
}

/* --- Variant Section Styles --- */
.variant-section {
  margin-top: 30px;
  padding: 25px;
  background: #f9f9f9;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.variant-section h3 {
    text-align: center;
    color: #333;
    margin-top: 0;
}
.variant-section strong {
    color: #1266b3;
}

.variant-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}
.variant-table th, .variant-table td {
  border: 1px solid #ccc;
  padding: 12px;
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
  transition: background-color 0.2s;
}

.edit-btn { background-color: #1266b3; }
.edit-btn:hover { background-color: #0d4a81; }
.delete-btn { background-color: #e53935; }
.delete-btn:hover { background-color: #c62828; }
.add-btn { background-color: #28a745; }
.update-btn { background-color: #f9a825; }
.cancel-btn { background-color: #757575; }
.finish-btn {
    background-color: #17a2b8;
    padding: 10px 20px;
    font-size: 16px;
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
  flex: 1 1 150px;
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
  margin-left: auto;
}

.completion-buttons {
    text-align: center;
    margin-top: 30px;
}
</style>