<template>
  <div class="add-product-page">
    <h2 class="page-title">Thêm sản phẩm mới</h2>

    <form @submit.prevent="handleSubmit" class="add-product-form">
      <div class="form-grid">
        <div class="col-main">
          <div class="card custom-card">
            <div class="card-content">
              <h3 class="card-title"><i class="fas fa-info-circle icon-margin"></i> Thông tin chung</h3>
              <div class="form-group">
                <label for="ten_san_pham" class="form-label">Tên sản phẩm:*</label>
                <input type="text" id="ten_san_pham" v-model="product.ten_san_pham" class="form-control"/>
                <small v-if="errors.ten_san_pham" class="form-error">{{ errors.ten_san_pham[0] }}</small>
              </div>
              <div class="form-group">
                <label for="slug" class="form-label">Đường Dẫn (Slug):*</label>
                <input type="text" id="slug" v-model="product.slug" class="form-control"/>
                <small v-if="errors.slug" class="form-error">{{ errors.slug[0] }}</small>
              </div>
              <div class="form-group">
                <label for="mo_ta" class="form-label">Mô tả chi tiết:</label>
                <textarea id="mo_ta" v-model="product.mo_ta" rows="4" class="form-control"></textarea>
                <small v-if="errors.mo_ta" class="form-error">{{ errors.mo_ta[0] }}</small>
              </div>
            </div>
          </div>

          <div class="card custom-card">
            <div class="card-content">
              <h3 class="card-title"><i class="fas fa-swatchbook icon-margin"></i> Các biến thể sản phẩm*</h3>
              <small class="form-text">Thêm ít nhất một biến thể cho sản phẩm.</small>

              <div v-if="product.variants.length > 0" class="table-container mt-3">
                  <table class="variant-table">
                      <thead><tr><th>Tên biến thể</th><th>Giá</th><th>Kho</th><th>Hành động</th></tr></thead>
                      <tbody>
                          <tr v-for="(variant, index) in product.variants" :key="index">
                              <td>{{variant.ten_bien_the}} ({{variant.mau_sac}} / {{variant.kich_thuoc}})</td>
                              <td>{{variant.gia}}</td>
                              <td>{{variant.so_luong_ton_kho}}</td>
                              <td><button type="button" @click="removeVariant(index)" class="btn btn-outline-danger icon-button"><i class="fas fa-trash-alt"></i></button></td>
                          </tr>
                      </tbody>
                  </table>
              </div>
              <small v-if="errors.variants" class="form-error">{{ errors.variants[0] }}</small>
              
              <div v-for="(variant, index) in product.variants" :key="index">
                <small v-if="errors[`variants.${index}.gia`]" class="form-error">
                  Lỗi ở biến thể #{{ index + 1 }}: {{ errors[`variants.${index}.gia`][0] }}
                </small>
              </div>

              <hr />
              <fieldset class="fieldset-style primary-fieldset">
                  <legend class="fieldset-legend">Thêm biến thể mới</legend>
                  <form @submit.prevent="addVariant" class="form-inline-grid">
                      <input v-model="newVariant.ten_bien_the" placeholder="Tên biến thể*" class="form-control"/>
                      <input v-model="newVariant.kich_thuoc" placeholder="Kích thước" class="form-control"/>
                      <input v-model="newVariant.mau_sac" placeholder="Màu sắc" class="form-control"/>
                      <input v-model.number="newVariant.so_luong_ton_kho" placeholder="Tồn kho*" type="number" class="form-control"/>
                      <input v-model.number="newVariant.gia" placeholder="Giá*" type="number" class="form-control"/>
                      <input v-model.number="newVariant.chieu_dai" placeholder="Dài (cm)" type="number" class="form-control"/>
                      <input v-model.number="newVariant.chieu_rong" placeholder="Rộng (cm)" type="number" class="form-control"/>
                      <input v-model.number="newVariant.chieu_cao" placeholder="Cao (cm)" type="number" class="form-control"/>
                      <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i> Thêm</button>
                  </form>
              </fieldset>
            </div>
          </div>
          
          <div class="card custom-card">
            <div class="card-content">
              <h3 class="card-title"><i class="fas fa-bullhorn icon-margin"></i> Khuyến mãi & SEO</h3>
                <label class="form-label">Khuyến mãi</label>
                <input type="number" v-model.number="product.khuyen_mai" class="form-control mb-2" placeholder="VD: 10 (cho 10%)"/>
                <small v-if="errors.khuyen_mai" class="form-error">{{ errors.khuyen_mai[0] }}</small>

                <label class="form-label">Tiêu đề seo</label>
                <input type="text" v-model="product.Tieu_de_seo" class="form-control mb-2"/>
                <small v-if="errors.Tieu_de_seo" class="form-error">{{ errors.Tieu_de_seo[0] }}</small>

                <label class="form-label">Từ khóa seo</label>
                <input type="text" v-model="product.Tu_khoa" class="form-control mb-2"/>
                <small v-if="errors.Tu_khoa" class="form-error">{{ errors.Tu_khoa[0] }}</small>

                <label class="form-label">Nội dung seo</label>
                <textarea v-model="product.Mo_ta_seo" rows="3" class="form-control"></textarea>
                <small v-if="errors.Mo_ta_seo" class="form-error">{{ errors.Mo_ta_seo[0] }}</small>
            </div>
          </div>
        </div>

        <div class="col-sidebar">
          <div class="card custom-card">
            <div class="card-content">
              <h3 class="card-title"><i class="fas fa-cogs icon-margin"></i> Tùy chọn</h3>
              <div class="form-group toggle-switch">
                  <input class="switch-input" type="checkbox" id="trang_thai" v-model="product.trang_thai">
                  <label class="switch-label" for="trang_thai">Hoạt động (Hiển thị)</label>
              </div>
              <div class="form-group toggle-switch">
                  <input class="switch-input" type="checkbox" id="noi_bat" v-model="product.noi_bat">
                  <label class="switch-label" for="noi_bat">Sản phẩm nổi bật</label>
              </div>
              <div class="form-group">
                  <label for="category" class="form-label">Danh mục:</label>
                  <select v-model="product.ten_danh_muc_id" class="form-control">
                      <option :value="null">-- Chọn --</option>
                      <option v-for="cat in categories" :key="cat.category_id" :value="cat.category_id">{{ cat.ten_danh_muc }}</option>
                  </select>
                  <small v-if="errors.ten_danh_muc_id" class="form-error">{{ errors.ten_danh_muc_id[0] }}</small>
              </div>
              <div class="form-group">
                <label for="gia" class="form-label">Giá gốc (tùy chọn):</label>
                <input type="number" id="gia" v-model.number="product.gia" placeholder="Giá chung nếu cần" class="form-control"/>
                 <small v-if="errors.gia" class="form-error">{{ errors.gia[0] }}</small>
              </div>
              <div class="form-group">
                <label for="the" class="form-label">Tags:</label>
                <input type="text" id="the" v-model="product.the" class="form-control"/>
                 <small v-if="errors.the" class="form-error">{{ errors.the[0] }}</small>
              </div>
            </div>
          </div>

          <div class="card custom-card">
              <div class="card-content">
                <h3 class="card-title"><i class="fas fa-image icon-margin"></i> Hình ảnh sản phẩm</h3>
                <label for="images" class="image-upload-label">
                  <i class="fas fa-cloud-upload-alt"></i><span>Hình Ảnh</span>
                  <input type="file" id="images" @change="handleImageUpload" multiple class="hidden-input"/>
                </label>
                <div v-if="imagePreviews.length > 0" class="image-preview-container">
                  <div v-for="(src, index) in imagePreviews" :key="index" class="image-preview-item">
                    <img :src="src" class="preview-image" />
                    <button type="button" @click="removeImage(index)" class="remove-image-button">&times;</button>
                  </div>
                </div>
                  <small v-if="errors.images" class="form-error">{{ errors.images[0] }}</small>
              </div>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" :disabled="isSubmitting" class="btn btn-primary btn-large">
          <span v-if="isSubmitting"><i class="fas fa-spinner fa-spin"></i> Đang lưu...</span>
          <span v-else><i class="fas fa-save"></i> Lưu sản phẩm</span>
        </button>
      </div>
        <p v-if="globalError" class="alert danger-alert">{{ globalError }}</p>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import axios from 'axios';

// --- STATE MANAGEMENT ---
const product = reactive({
  ten_san_pham: '',
  slug: '',
  ten_danh_muc_id: null,
  mo_ta: '',
  gia: null,
  noi_bat: false,
  trang_thai: true,
  khuyen_mai: null,
  the: '',
  Tieu_de_seo: '',
  Tu_khoa: '',
  Mo_ta_seo: '',
  ngay_bat_dau_giam_gia: null,
  ngay_ket_thuc_giam_gia: null,
  variants: [],
});

const newVariant = reactive({
    ten_bien_the: '',
    kich_thuoc: '',
    mau_sac: '',
    gia: null,
    so_luong_ton_kho: null,
});

const categories = ref([]);
const imageFiles = ref([]);
const imagePreviews = ref([]);
const isSubmitting = ref(false);
const errors = ref({});
const globalError = ref('');

// --- LIFECYCLE & DATA FETCHING ---
onMounted(async () => {
    try {
        const response = await axios.get('categories');
        categories.value = response.data;
    } catch (error) {
        console.error("Không thể tải danh mục:", error);
    }
});

// Tự động tạo slug
watch(() => product.ten_san_pham, (newVal) => {
    if (newVal) {
        product.slug = newVal.toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim().replace(/đ/g, 'd').replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-');
    }
});

const addVariant = () => {
    if (!newVariant.ten_bien_the || !newVariant.gia || !newVariant.so_luong_ton_kho) {
        alert('Vui lòng điền Tên, Giá và Kho cho biến thể.');
        return;
    }
    product.variants.push({ ...newVariant });
    Object.assign(newVariant, { ten_bien_the: '', kich_thuoc: '', mau_sac: '', gia: null, so_luong_ton_kho: null, chieu_dai: null, chieu_rong: null, chieu_cao: null });
};

const removeVariant = (index) => {
    product.variants.splice(index, 1);
};

const handleImageUpload = (event) => {
    const files = Array.from(event.target.files);
    files.forEach(file => {
        imageFiles.value.push(file);
        imagePreviews.value.push(URL.createObjectURL(file));
    });
};

const removeImage = (index) => {
    imageFiles.value.splice(index, 1);
    imagePreviews.value.splice(index, 1);
};

const handleSubmit = async () => {
    isSubmitting.value = true;
    errors.value = {};
    globalError.value = '';

    const formData = new FormData();
    for (const key in product) {
      let value = product[key]; 
      if (typeof value === 'boolean') {
          value = value ? '1' : '0';
      }
      if (key === 'variants') {
          formData.append('variants', JSON.stringify(product.variants));
      } else {
          if (value !== null && value !== undefined) {
              formData.append(key, value);
          }
      }
    }

    imageFiles.value.forEach(file => {
        formData.append('images[]', file);
    });

    try {
        const response = await axios.post('products', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        alert('Thêm sản phẩm thành công!');
        window.location.href = '/admin/products';

    } catch (error) {
        if (error.response && error.response.status === 422) {
            errors.value = error.response.data.errors;
            globalError.value = error.response.data.message; 
        } else {
            globalError.value = error.response?.data?.message || "Đã có lỗi xảy ra từ máy chủ.";
        }
        console.error("Lỗi khi thêm sản phẩm:", error);
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

.add-product-page {
  padding: 30px;
  min-height: 100vh;
  color: #333;
  box-sizing: border-box;
}

.page-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #2c3e50;
  text-align: center;
  margin-bottom: 40px;
  position: relative;
  padding-bottom: 15px;
}

.page-title::after {
  content: '';
  position: absolute;
  left: 50%;
  bottom: 0;
  transform: translateX(-50%);
  width: 80px;
  height: 4px;
  background-color: #007bff;
  border-radius: 2px;
}

.add-product-form {
  padding: 35px;
  border-radius: 15px;
  box-shadow: none !important;
  max-width: 1200px;
  margin: 0 auto;
}

.form-grid {
  display: grid;
  grid-template-columns: 3fr 1fr; 
  gap: 35px;
  align-items: flex-start;
}

.card.custom-card {
  border: 1px solid #e0e6ed;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
  margin-bottom: 25px;
  overflow: hidden;
}

.card-content {
  padding: 25px;
}

.card-title {
  font-size: 1.45rem;
  font-weight: 600;
  color: #34495e;
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 2px solid #f0f2f5;
  display: flex;
  align-items: center;
}

.card-title i {
  color: #007bff;
  font-size: 1.6rem;
  margin-right: 12px;
}

.form-group {
  margin-bottom: 22px;
}

.form-label {
  font-weight: 600;
  display: block;
  margin-bottom: 10px;
  color: #555;
  font-size: 1rem;
}

.form-control {
  box-sizing: border-box;
  width: 100%;
  border-radius: 8px;
  padding: 12px 15px;
  font-size: 1rem;
  border: 1px solid #dcdfe6;
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.form-control:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25), inset 0 1px 3px rgba(0, 0, 0, 0.05);
  background-color: #fcfdff;
  outline: none;
}

textarea.form-control {
  min-height: 80px;
  resize: vertical;
}

.form-text {
  font-size: 0.875rem;
  color: #777;
  margin-top: 6px;
}

.form-error {
    color: #dc3545;
    font-size: 0.875em;
    margin-top: 0.25rem;
    display: block;
}

/* --- Image Upload --- */
.image-upload-label {
  border: 2px dashed #a8d4ff;
  background-color: #eaf6ff; 
  border-radius: 12px;
  min-height: 140px;
  font-size: 1.15rem;
  font-weight: 500;
  color: #007bff;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: all 0.3s ease;
  padding: 20px; 
}

.image-upload-label:hover {
  border-color: #0056b3;
  background-color: #d6eaff; 
  color: #0056b3;
}

.image-upload-label i {
  font-size: 3rem; 
  margin-bottom: 8px; 
  animation: bounceIn 0.6s cubic-bezier(0.68, -0.55, 0.27, 1.55);
}

@keyframes bounceIn {
  0% { transform: scale(0.3); opacity: 0; }
  50% { transform: scale(1.05); opacity: 1; }
  70% { transform: scale(0.9); }
  100% { transform: scale(1); }
}

.hidden-input { 
  display: none;
}

.image-preview-container {
  display: flex;
  flex-wrap: wrap;
  gap: 15px; 
  margin-top: 20px;
  justify-content: center; 
}

.image-preview-item {
  width: 120px; 
  height: 120px;
  border: 1px solid #ced4da;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  position: relative;
  transition: transform 0.2s ease;
}

.image-preview-item:hover {
  transform: translateY(-3px);
}

.preview-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block; 
}

.remove-image-button {
  background-color: rgba(220, 53, 69, 0.9); 
  color: white;
  border: none;
  width: 32px;
  height: 32px;
  font-size: 1.2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  padding: 0;
  line-height: 1;
  border-radius: 50%;
  position: absolute;
  top: 5px;
  right: 5px;
  z-index: 10;
  transition: background-color 0.2s ease, transform 0.2s ease;
}
.remove-image-button:hover {
  background-color: #c82333;
  transform: scale(1.1);
}

/* --- Variants Section (Table and Forms) --- */
.table-container {
    overflow-x: auto; 
    border: 1px solid #e9ecef; 
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.03);
}

.variant-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  margin-top: 15px;
  min-width: 600px;
}

.variant-table thead th {
  background-color: #f5f8fb; 
  font-weight: 700;
  color: #444;
  padding: 15px 10px;
  border-bottom: 2px solid #e9ecef;
  border-top: none;
  text-align: center;
  font-size: 0.95rem;
}

.variant-table tbody td {
  padding: 12px 10px;
  vertical-align: middle;
  border-bottom: 1px solid #f0f2f5;
  text-align: center;
  font-size: 0.9rem;
}

.variant-table tbody tr:last-child td {
  border-bottom: none; 
}

.variant-table tbody tr:hover {
  background-color: #fdfdfd;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
  transform: translateY(-1px);
  transition: all 0.2s ease;
}

.icon-button { 
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}
.icon-button i {
    font-size: 0.9em;
}

.fieldset-style { 
  border: 1px solid #d1ecf1 !important; 
  border-radius: 8px !important;
  padding: 20px !important;
  background-color: #e9f5f8; 
  margin-top: 25px;
}

.fieldset-legend {
  font-size: 1.2rem;
  font-weight: 700;
  width: auto;
  padding: 0 15px;
  margin-bottom: 0;
  color: #007bff;
  border-bottom: none;
  background-color: #e9f5f8; 
}

.form-inline-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); 
    gap: 10px; 
    align-items: flex-end;
}

/* --- Toggle Switch (Trạng thái) --- */
.toggle-switch {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f0f2f5;
}
.toggle-switch:last-child {
    border-bottom: none;
}
.toggle-switch .switch-input {
    appearance: none;
    width: 48px;
    height: 28px;
    background-color: #e0e0e0;
    border-radius: 14px;
    position: relative;
    cursor: pointer;
    transition: background-color 0.3s ease;
    outline: none;
}
.toggle-switch .switch-input:checked {
    background-color: #28a745; 
}
.toggle-switch .switch-input::before {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 24px;
    height: 24px;
    background-color: #fff;
    border-radius: 50%;
    transition: transform 0.3s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.toggle-switch .switch-input:checked::before {
    transform: translateX(20px);
}
.toggle-switch .switch-label {
    margin-left: 10px; 
    cursor: pointer;
    font-weight: 500;
    color: #495057;
    flex-grow: 1; 
}

/* --- Form Actions and Alerts --- */
.form-actions {
  display: flex;
  justify-content: flex-end;
  padding-top: 30px;
  border-top: 1px solid #eee;
  margin-top: 30px;
}

.btn {
  font-weight: 600;
  padding: 12px 25px;
  border-radius: 8px;
  transition: all 0.25s ease-in-out;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  border: 1px solid transparent;
}
.btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

.btn i {
  font-size: 1.1rem;
}

.btn-primary {
  background-color: #007bff;
  border-color: #007bff;
  color: white;
  box-shadow: 0 4px 10px rgba(0, 123, 255, 0.2);
}
.btn-primary:hover:not(:disabled) {
  background-color: #0056b3;
  border-color: #004085;
  box-shadow: 0 6px 15px rgba(0, 123, 255, 0.3);
  transform: translateY(-2px);
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}
.btn-success:hover:not(:disabled) {
    background-color: #218838;
    border-color: #1e7e34;
}

.btn-outline-danger {
  color: #dc3545;
  border-color: #dc3545;
  background-color: transparent;
}
.btn-outline-danger:hover:not(:disabled) {
  color: #fff;
  background-color: #dc3545;
}

.btn-large { 
    padding: 14px 30px;
    font-size: 1.1rem;
}

.alert {
  padding: 15px 25px;
  border-radius: 10px;
  font-weight: 500;
  font-size: 1rem;
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 25px;
  text-align: center;
  justify-content: center;
}
.danger-alert {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

</style>