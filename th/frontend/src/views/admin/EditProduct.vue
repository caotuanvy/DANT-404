<template>
  <div class="add-product-page">
    <h2 class="page-title">Chỉnh sửa sản phẩm</h2>
    <p v-if="isLoading" class="loading-text">Đang tải dữ liệu sản phẩm...</p>
    <p v-if="globalError" class="alert danger-alert">{{ globalError }}</p>

    <form v-if="!isLoading && !globalError" @submit.prevent="updateProduct" class="add-product-form">
      <div class="form-grid">
        <div class="col-main">
          <div class="card custom-card">
            <div class="card-content">
              <h3 class="card-title"><i class="fas fa-info-circle icon-margin"></i> Thông tin chung</h3>
              <div class="form-group">
                <label for="ten_san_pham" class="form-label">Tên sản phẩm:*</label>
                <input type="text" id="ten_san_pham" v-model="product.ten_san_pham" class="form-control" />
                <small v-if="errors.ten_san_pham" class="form-error">{{ errors.ten_san_pham[0] }}</small>
              </div>
              <div class="form-group">
                <label for="slug" class="form-label">Đường Dẫn (Slug):*</label>
                <input type="text" id="slug" v-model="product.slug" class="form-control" @input="userEditedSlug = true" />
                <small v-if="errors.slug" class="form-error">{{ errors.slug[0] }}</small>
              </div>
              <div class="form-group">
                <label for="mo_ta" class="form-label">Mô tả chi tiết:</label>
                <editor api-key="tybxi5binushm14hreotxk86vfsdjfaw6qk7vmsj9gv9iw5u" :init="editorConfig" v-model="product.mo_ta" />
                <small v-if="errors.mo_ta" class="form-error">{{ errors.mo_ta[0] }}</small>
              </div>
            </div>
          </div>

          <div class="card custom-card">
            <div class="card-content">
                <h3 class="card-title"><i class="fas fa-swatchbook icon-margin"></i> Các biến thể sản phẩm*</h3>
                <small class="form-text">Thêm hoặc chỉnh sửa các biến thể cho sản phẩm.</small>

                <div v-if="product.variants.length > 0" class="table-container mt-3">
                    <table class="variant-table">
                        <thead>
                            <tr>
                                <th>Tên biến thể</th>
                                <th>Giá</th>
                                <th>Kho</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(variant, index) in product.variants" :key="index">
                                <td>{{variant.ten_bien_the}} ({{variant.mau_sac}} / {{variant.kich_thuoc}})</td>
                                <td>{{variant.gia}}</td>
                                <td>{{variant.so_luong_ton_kho}}</td>
                                <td>
                                    <button type="button" @click="removeVariant(index)" class="btn btn-outline-danger icon-button" title="Xóa biến thể">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <small v-if="errors.variants" class="form-error">{{ errors.variants[0] }}</small>

                <hr />
                <fieldset class="fieldset-style primary-fieldset">
                    <legend class="fieldset-legend">Thêm biến thể mới</legend>
                    <form @submit.prevent="addVariant" class="form-inline-grid">
                        <div>
                            <input v-model="newVariant.ten_bien_the" placeholder="Tên biến thể*" class="form-control"/>
                            <small v-if="newVariantErrors.ten_bien_the" class="form-error">{{ newVariantErrors.ten_bien_the }}</small>
                        </div>
                        <div><input v-model="newVariant.kich_thuoc" placeholder="Kích thước" class="form-control"/></div>
                        <div><input v-model="newVariant.mau_sac" placeholder="Màu sắc" class="form-control"/></div>
                        <div>
                            <input v-model.number="newVariant.so_luong_ton_kho" placeholder="Tồn kho*" type="number" class="form-control"/>
                            <small v-if="newVariantErrors.so_luong_ton_kho" class="form-error">{{ newVariantErrors.so_luong_ton_kho }}</small>
                        </div>
                        <div>
                            <input v-model.number="newVariant.gia" placeholder="Giá*" type="number" class="form-control"/>
                            <small v-if="newVariantErrors.gia" class="form-error">{{ newVariantErrors.gia }}</small>
                        </div>
                        <button type="submit" id="btn-add-varian" class="btn btn-success"><i class="fas fa-plus"></i> Thêm</button>
                    </form>
                </fieldset>
            </div>
        </div>

          <div class="card custom-card">
            <div class="card-content">
              <h3 class="card-title"><i class="fas fa-bullhorn icon-margin"></i> Khuyến mãi & SEO</h3>
              <button type="button" @click="generateSeoContent" :disabled="isGeneratingSeo || !product.ten_san_pham" class="btn-ai" title="Tạo nội dung SEO bằng AI (cần có tên sản phẩm)">
                <span v-if="isGeneratingSeo"><i class="fas fa-spinner fa-spin"></i> Đang tạo...</span>
                <span v-else><i class="fas fa-magic"></i> Tạo AI</span>
              </button>
              <label class="form-label">Khuyến mãi</label>
              <input type="number" v-model.number="product.khuyen_mai" class="form-control mb-2" placeholder="VD: 10 (cho 10%)" />
              <small v-if="errors.khuyen_mai" class="form-error">{{ errors.khuyen_mai[0] }}</small>

              <label class="form-label">Tiêu đề SEO</label>
              <input type="text" v-model="product.Tieu_de_seo" class="form-control mb-2" />
              <small v-if="errors.Tieu_de_seo" class="form-error">{{ errors.Tieu_de_seo[0] }}</small>

              <label class="form-label">Từ khóa SEO</label>
              <input type="text" v-model="product.Tu_khoa" class="form-control mb-2" />
              <small v-if="errors.Tu_khoa" class="form-error">{{ errors.Tu_khoa[0] }}</small>

              <label class="form-label">Nội dung SEO</label>
              <editor api-key="tybxi5binushm14hreotxk86vfsdjfaw6qk7vmsj9gv9iw5u" :init="editorConfig" v-model="product.Mo_ta_seo" />
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
                <label class="switch-label-text" for="trang_thai">Hoạt động (Hiển thị)<span class="switch-button"></span></label>
              </div>
              <div class="form-group toggle-switch">
                <input class="switch-input" type="checkbox" id="noi_bat" v-model="product.noi_bat">
                <label class="switch-label-text" for="noi_bat">Sản phẩm nổi bật<span class="switch-button"></span></label>
              </div>
              <div class="form-group">
                <label for="category" class="form-label">Danh mục:</label>
                <select v-model="product.ten_danh_muc_id" class="form-control">
                  <option v-for="cat in categories" :key="cat.category_id" :value="cat.category_id">{{ cat.ten_danh_muc }}</option>
                </select>
                <small v-if="errors.ten_danh_muc_id" class="form-error">{{ errors.ten_danh_muc_id[0] }}</small>
              </div>
            </div>
          </div>

          <div class="card custom-card">
            <div class="card-content">
              <h3 class="card-title"><i class="fas fa-image icon-margin"></i> Hình ảnh sản phẩm</h3>
              <div v-if="existingImages.length > 0" class="image-preview-container">
                  <div v-for="(image, index) in existingImages" :key="image.id" class="image-preview-item">
                      <img :src="image.url" class="preview-image" />
                      <button type="button" @click="removeExistingImage(image.id, index)" class="remove-image-button">&times;</button>
                  </div>
              </div>
              <label for="images" class="image-upload-label mt-3">
                  <i class="fas fa-cloud-upload-alt"></i><span>Thêm ảnh mới</span>
                  <input type="file" id="images" @change="handleImageUpload" multiple class="hidden-input"/>
              </label>
              <div v-if="newImagePreviews.length > 0" class="image-preview-container">
                  <div v-for="(src, index) in newImagePreviews" :key="index" class="image-preview-item">
                      <img :src="src" class="preview-image" />
                      <button type="button" @click="removeNewImage(index)" class="remove-image-button">&times;</button>
                  </div>
              </div>
              <small v-if="errors.images" class="form-error">{{ errors.images[0] }}</small>
            </div>
          </div>

          <div class="card custom-card mt-4">
            <div class="card-content">
        <h3 class="card-title"><i class="fas fa-chart-line icon-margin"></i> Đánh giá SEO Nội dung</h3>
        <p class="form-text">Phân tích và gợi ý tối ưu hóa SEO cho bài viết của bạn.</p>

        <div class="seo-criteria-list">
            <div class="seo-criteria-item" :class="evaluateFocusKeywordSet.class">
                <span class="criteria-icon"><i :class="evaluateFocusKeywordSet.isGood ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i></span>
                <div class="criteria-details">
                    <span class="criteria-name">Từ khóa tập trung</span>
                    <span class="criteria-suggestion">{{ evaluateFocusKeywordSet.suggestion }}</span>
                </div>
            </div>
            <div class="seo-criteria-item" :class="evaluateSeoTitleKeyword.class">
                <span class="criteria-icon"><i :class="evaluateSeoTitleKeyword.isGood ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i></span>
                <div class="criteria-details">
                    <span class="criteria-name">Từ khóa chính trong Title</span>
                    <span class="criteria-suggestion">{{ evaluateSeoTitleKeyword.suggestion }}</span>
                </div>
            </div>
            <div class="seo-criteria-item" :class="evaluateSeoTitleLength.class">
                <span class="criteria-icon"><i :class="evaluateSeoTitleLength.isGood ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i></span>
                <div class="criteria-details">
                    <span class="criteria-name">Độ dài Title (55-60 ký tự)</span>
                    <span class="criteria-suggestion">{{ evaluateSeoTitleLength.suggestion }}</span>
                </div>
            </div>
            <div class="seo-criteria-item" :class="evaluateSeoDescriptionKeyword.class">
                <span class="criteria-icon"><i :class="evaluateSeoDescriptionKeyword.isGood ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i></span>
                <div class="criteria-details">
                    <span class="criteria-name">Từ khóa chính trong Meta Desc</span>
                    <span class="criteria-suggestion">{{ evaluateSeoDescriptionKeyword.suggestion }}</span>
                </div>
            </div>
            <div class="seo-criteria-item" :class="evaluateSeoDescriptionLength.class">
                <span class="criteria-icon"><i :class="evaluateSeoDescriptionLength.isGood ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i></span>
                <div class="criteria-details">
                    <span class="criteria-name">Độ dài Meta Desc (130-160 ký tự)</span>
                    <span class="criteria-suggestion">{{ evaluateSeoDescriptionLength.suggestion }}</span>
                </div>
            </div>
            <div class="seo-criteria-item" :class="evaluateUrlKeyword.class">
                <span class="criteria-icon"><i :class="evaluateUrlKeyword.isGood ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i></span>
                <div class="criteria-details">
                    <span class="criteria-name">Từ khóa chính trong URL</span>
                    <span class="criteria-suggestion">{{ evaluateUrlKeyword.suggestion }}</span>
                </div>
            </div>
            <div class="seo-criteria-item" :class="evaluateContentLength.class">
                <span class="criteria-icon"><i :class="evaluateContentLength.isGood ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i></span>
                <div class="criteria-details">
                    <span class="criteria-name">Độ dài nội dung (600-2500 từ)</span>
                    <span class="criteria-suggestion">{{ evaluateContentLength.suggestion }}</span>
                </div>
            </div>
            <div class="seo-criteria-item" :class="evaluateKeywordDensity.class">
                <span class="criteria-icon"><i :class="evaluateKeywordDensity.isGood ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i></span>
                <div class="criteria-details">
                    <span class="criteria-name">Mật độ từ khóa chính (1-2%)</span>
                    <span class="criteria-suggestion">{{ evaluateKeywordDensity.suggestion }}</span>
                </div>
            </div>
            <div class="seo-criteria-item" :class="evaluateSubheadingKeyword.class">
                <span class="criteria-icon"><i :class="evaluateSubheadingKeyword.isGood ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i></span>
                <div class="criteria-details">
                    <span class="criteria-name">Từ khóa trong H2, H3, H4</span>
                    <span class="criteria-suggestion">{{ evaluateSubheadingKeyword.suggestion }}</span>
                </div>
            </div>
            <div class="seo-criteria-item" :class="evaluateImageAltText.class">
                <span class="criteria-icon"><i :class="evaluateImageAltText.isGood ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i></span>
                <div class="criteria-details">
                    <span class="criteria-name">Alt text hình ảnh</span>
                    <span class="criteria-suggestion">{{ evaluateImageAltText.suggestion }}</span>
                </div>
            </div>
            <div class="seo-criteria-item" :class="evaluateAddImages.class">
                <span class="criteria-icon"><i :class="evaluateAddImages.isGood ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i></span>
                <div class="criteria-details">
                    <span class="criteria-name">Thêm hình ảnh sản phẩm</span>
                    <span class="criteria-suggestion">{{ evaluateAddImages.suggestion }}</span>
                </div>
            </div>
            <div class="seo-criteria-item" :class="evaluateShortParagraphs.class">
                <span class="criteria-icon"><i :class="evaluateShortParagraphs.isGood ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i></span>
                <div class="criteria-details">
                    <span class="criteria-name">Các đoạn văn ngắn và súc tích</span>
                    <span class="criteria-suggestion">{{ evaluateShortParagraphs.suggestion }}</span>
                </div>
            </div>
            <div class="seo-criteria-item" :class="evaluateInternalLinks.class">
                <span class="criteria-icon"><i :class="evaluateInternalLinks.isGood ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i></span>
                <div class="criteria-details">
                    <span class="criteria-name">Thêm liên kết nội bộ</span>
                    <span class="criteria-suggestion">{{ evaluateInternalLinks.suggestion }}</span>
                </div>
            </div>
            <div class="seo-criteria-item" :class="evaluateKeywordInIntroOutro.class">
                <span class="criteria-icon"><i :class="evaluateKeywordInIntroOutro.isGood ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i></span>
                <div class="criteria-details">
                    <span class="criteria-name">Từ khóa trong mở đầu & kết luận</span>
                    <span class="criteria-suggestion">{{ evaluateKeywordInIntroOutro.suggestion }}</span>
                </div>
            </div>
            <div class="seo-criteria-item" :class="evaluateReadability.class">
                <span class="criteria-icon"><i :class="evaluateReadability.isGood ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i></span>
                <div class="criteria-details">
                    <span class="criteria-name">Ngôn ngữ rõ ràng, dễ hiểu</span>
                    <span class="criteria-suggestion">{{ evaluateReadability.suggestion }}</span>
                </div>
            </div>
            <div class="seo-criteria-item" :class="evaluateUniqueness.class">
                <span class="criteria-icon"><i :class="evaluateUniqueness.isGood ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i></span>
                <div class="criteria-details">
                    <span class="criteria-name">Tính độc đáo nội dung</span>
                    <span class="criteria-suggestion">{{ evaluateUniqueness.suggestion }}</span>
                </div>
            </div>
        </div>
    </div>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" :disabled="isSubmitting" class="btn btn-primary btn-large">
          <span v-if="isSubmitting"><i class="fas fa-spinner fa-spin"></i> Đang cập nhật...</span>
          <span v-else><i class="fas fa-save"></i> Lưu thay đổi</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import Editor from '@tinymce/tinymce-vue';

// --- STATE MANAGEMENT ---
const route = useRoute();
const router = useRouter();
const isLoading = ref(true);
const isSubmitting = ref(false);
const isGeneratingSeo = ref(false);
const userEditedSlug = ref(false);

const product = reactive({
    ten_san_pham: '',
    slug: '',
    ten_danh_muc_id: null,
    mo_ta: '',
    noi_bat: false,
    trang_thai: true,
    khuyen_mai: null,
    the: '',
    Tieu_de_seo: '',
    Tu_khoa: '',
    Mo_ta_seo: '',
    variants: [],
});

const newVariant = reactive({
    ten_bien_the: '',
    kich_thuoc: '',
    mau_sac: '',
    gia: null,
    so_luong_ton_kho: null,
});
const newVariantErrors = reactive({});

const categories = ref([]);
const existingImages = ref([]);
const newImageFiles = ref([]); 
const newImagePreviews = ref([]); 

const errors = ref({});
const globalError = ref('');

onMounted(async () => {
    try {
        await getCategories();
        await getProduct();
    } catch (error) {
        console.error("Lỗi khi tải dữ liệu:", error);
        globalError.value = "Không thể tải dữ liệu sản phẩm. Vui lòng thử lại.";
    } finally {
        isLoading.value = false;
    }
});

const getProduct = async () => {
    const productId = route.params.id;
    const response = await axios.get(`products/${productId}`);
    const data = response.data; 
    product.ten_san_pham = data.product_name;
    product.slug = data.slug;
    product.ten_danh_muc_id = data.danh_muc?.category_id; 
    product.mo_ta = data.description || '';
    product.noi_bat = !!data.noi_bat;
    product.trang_thai = !!data.trang_thai;
    product.khuyen_mai = data.khuyen_mai;
    product.the = data.the || '';
    product.Tieu_de_seo = data.Tieu_de_seo || '';
    product.Tu_khoa = data.Tu_khoa || '';
    product.Mo_ta_seo = data.Mo_ta_seo || '';
    product.variants = data.variants || [];
    existingImages.value = data.images || [];
    console.log("Dữ liệu API trả về:", response.data); 
};

const getCategories = async () => {
    const response = await axios.get('categories');
    categories.value = response.data;
};
watch(() => product.ten_san_pham, (newVal) => {
    if (newVal && !userEditedSlug.value) {
        product.slug = newVal.toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim().replace(/\s+/g, '-');
    }
});

const addVariant = () => {
    let isValid = true;
    if (!newVariant.ten_bien_the) { newVariantErrors.ten_bien_the = 'Tên là bắt buộc.'; isValid = false; }
    if (!newVariant.gia || newVariant.gia <= 0) { newVariantErrors.gia = 'Giá phải dương.'; isValid = false; }
    if (newVariant.so_luong_ton_kho === null || newVariant.so_luong_ton_kho < 0) { newVariantErrors.so_luong_ton_kho = 'Số lượng không hợp lệ.'; isValid = false; }
    
    if (!isValid) return;

    product.variants.push({ ...newVariant });
    Object.assign(newVariant, { ten_bien_the: '', kich_thuoc: '', mau_sac: '', gia: null, so_luong_ton_kho: null });
};

const removeVariant = (index) => {
    if (confirm('Bạn có chắc muốn xóa biến thể này?')) {
        product.variants.splice(index, 1);
    }
};

const handleImageUpload = (event) => {
    const files = Array.from(event.target.files);
    files.forEach(file => {
        newImageFiles.value.push(file);
        newImagePreviews.value.push(URL.createObjectURL(file));
    });
};

const removeNewImage = (index) => {
    URL.revokeObjectURL(newImagePreviews.value[index]);
    newImageFiles.value.splice(index, 1);
    newImagePreviews.value.splice(index, 1);
};

const removeExistingImage = async (imageId, index) => {
    if (!confirm('Ảnh này sẽ bị xóa vĩnh viễn. Bạn có chắc?')) return;
    try {
        await axios.delete(`product-images/${imageId}`);
        existingImages.value.splice(index, 1);
        alert('Xóa ảnh thành công!');
    } catch (error) {
        console.error("Lỗi khi xóa ảnh:", error);
        alert('Xóa ảnh thất bại.');
    }
};

const updateProduct = async () => {
    isSubmitting.value = true;
    errors.value = {};
    globalError.value = '';

    const formData = new FormData();
    // Thêm _method: 'PUT' để Laravel hiểu đây là request PUT
    formData.append('_method', 'PUT');

    for (const key in product) {
      let value = product[key]; 
      if (typeof value === 'boolean') {
        value = value ? '1' : '0';
      }
      if (key === 'variants') {
        formData.append('variants', JSON.stringify(product.variants));
      } else if (value !== null && value !== undefined) {
        formData.append(key, value);
      }
    }

    newImageFiles.value.forEach(file => {
        formData.append('images[]', file);
    });

    try {
        await axios.post(`products/${route.params.id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
             
        });
        alert('Cập nhật sản phẩm thành công!');
        router.push('/admin/products');
    } catch (error) {
        if (error.response && error.response.status === 422) {
            errors.value = error.response.data.errors;
            globalError.value = "Vui lòng kiểm tra lại các lỗi trong form.";
        } else {
            globalError.value = error.response?.data?.message || "Đã có lỗi xảy ra từ máy chủ.";
        }
    } finally {
        isSubmitting.value = false;
    }
};

const generateSeoContent = async () => {
    if (!product.ten_san_pham) {
        alert('Vui lòng nhập Tên sản phẩm trước.');
        return;
    }
    isGeneratingSeo.value = true;
    globalError.value = ''; 

    try {
        const token = localStorage.getItem('token');
        if (!token) {
            globalError.value = "Không tìm thấy token. Vui lòng đăng nhập lại.";
            isGeneratingSeo.value = false;
            return;
        }
        const response = await axios.post('products/generate-seo', {
            product_name: product.ten_san_pham,
        }, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        if (response.data.seo_title) product.Tieu_de_seo = response.data.seo_title;
        if (response.data.seo_description) product.Mo_ta_seo = response.data.seo_description;
        if (response.data.seo_keywords) product.Tu_khoa = response.data.seo_keywords;
        if (response.data.product_description_long) product.mo_ta = response.data.product_description_long;
        
        alert('Tạo nội dung AI thành công!');

    } catch (error) {
        if (error.response && error.response.status === 401) {
             globalError.value = "Xác thực thất bại (Unauthenticated). Token có thể đã hết hạn.";
        } else {
             globalError.value = "Tạo SEO thất bại: " + (error.response?.data?.error || "Lỗi không xác định.");
        }
        console.error("Lỗi khi tạo SEO:", error);
    } finally {
        isGeneratingSeo.value = false;
    }
};

// --- TINYMCE EDITOR CONFIG ---
const editorConfig = {
 height: 500,
 menubar: false,
 branding: false,
 plugins: [
  'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor',
  'searchreplace', 'visualblocks', 'code', 'fullscreen',
  'insertdatetime', 'media', 'table', 'help', 'wordcount',
  'emoticons', 'codesample', 'directionality', 'quickbars', 'charmap' 
 ],
 toolbar:
  'code | newdocument | cut copy | undo redo | searchreplace | ' +
  'bold italic underline strikethrough | superscript subscript | removeformat | ' +
  'alignleft aligncenter alignright alignjustify | ' +
  'bullist numlist outdent indent | ' +
  'blockquote | link unlink anchor | ' + 
  'image media table | emoticons codesample | fullscreen preview | help', 
 images_upload_url: '/tinymce/upload-image',
 images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
  const formData = new FormData();
  formData.append('file', blobInfo.blob(), blobInfo.filename());

  axios.post('/tinymce/upload-image', formData, { 
   headers: {
    'Content-Type': 'multipart/form-data',
    
   },
   onUploadProgress: (event) => {
    progress(event.loaded / event.total * 100);
   }
  })
  .then(response => {
   resolve(response.data.location);
  })
  .catch(error => {
   console.error('TinyMCE Image Upload Error:', error);
   reject('Upload ảnh thất bại: ' + (error.response?.data?.message || error.message));
  });
 }),
 content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
 style_formats: [
  { title: 'Headings', items: [
   { title: 'Heading 1', format: 'h1' },
   { title: 'Heading 2', format: 'h2' },
   { title: 'Heading 3', format: 'h3' },
   { title: 'Heading 4', format: 'h4' },
   { title: 'Heading 5', format: 'h5' },
   { title: 'Heading 6', format: 'h6' }
  ]},
  { title: 'Inline', items: [
   { title: 'Bold', icon: 'bold', format: 'bold' },
   { title: 'Italic', icon: 'italic', format: 'italic' },
   { title: 'Underline', icon: 'underline', format: 'underline' },
   { title: 'Strikethrough', icon: 'strikethrough', format: 'strikethrough' },
   { title: 'Superscript', icon: 'superscript', format: 'superscript' },
   { title: 'Subscript', icon: 'subscript', format: 'subscript' },
   { title: 'Code', icon: 'code', format: 'code' }
  ]},
  { title: 'Blocks', items: [
   { title: 'Paragraph', format: 'p' },
   { title: 'Blockquote', format: 'blockquote' },
   { title: 'Div', format: 'div' },
   { title: 'Pre', format: 'pre' }
  ]},
  { title: 'Alignment', items: [
   { title: 'Left', icon: 'alignleft', format: 'alignleft' },
   { title: 'Center', icon: 'aligncenter', format: 'aligncenter' },
   { title: 'Right', icon: 'alignright', format: 'alignright' },
   { title: 'Justify', icon: 'alignjustify', format: 'alignjustify' }
  ]}
 ],
 language: 'vi',
 skin: 'oxide', 
 content_css: 'default', 
 image_description: false, 
 image_title: true, 
 browser_spellcheck: true,
 contextmenu: 'link image table',
 quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote',
 quickbars_image_toolbar: 'alignleft aligncenter alignright | imageoptions',
 quickbars_insert_toolbar: 'quicktable quickimage quicklink',
 setup: (editor) => {
  editor.on('init', () => {});
 }
};

// --- COMPUTED PROPERTIES FOR SEO ANALYSIS ---
// ... Copy toàn bộ các computed properties (evaluate...) từ component AddProduct ...
const normalizeText = (text) => text ? text.toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim() : '';
const stripHtml = (html) => {
  if (!html) return "";
  const doc = new DOMParser().parseFromString(html, 'text/html');
  return doc.body.textContent || "";
};
const parseHtmlContent = (htmlString) => {
    const parser = new DOMParser();
    return parser.parseFromString(htmlString, 'text/html');
};
const createSeoResult = (isGood, suggestion) => ({ isGood, class: isGood ? 'status-good' : 'status-bad', suggestion });

// Biến dùng chung
const mainKeyword = computed(() => normalizeText(product.ten_san_pham));
const descriptionHtml = computed(() => product.mo_ta || '');
const descriptionText = computed(() => normalizeText(stripHtml(product.mo_ta || '')));
const wordCount = computed(() => descriptionText.value.split(/\s+/).filter(Boolean).length);


// 1. Từ khóa tập trung
const evaluateFocusKeywordSet = computed(() => {
    if (mainKeyword.value) return createSeoResult(true, 'Từ khóa tập trung đã được xác định.');
    return createSeoResult(false, 'Đặt một Từ khóa tập trung (Tên sản phẩm) cho nội dung này.');
});

// 2. Từ khóa chính trong Title
const evaluateSeoTitleKeyword = computed(() => {
    const seoTitle = normalizeText(product.Tieu_de_seo || '');
    if (!mainKeyword.value) return createSeoResult(false, 'Cần Tên sản phẩm để xác định từ khóa chính.');
    if (seoTitle.includes(mainKeyword.value)) return createSeoResult(true, 'Từ khóa chính đã có trong tiêu đề SEO.');
    return createSeoResult(false, 'Thêm Từ khóa chính vào tiêu đề SEO của bạn.');
});

// 3. Độ dài Title
const evaluateSeoTitleLength = computed(() => {
    const len = (product.Tieu_de_seo || '').length;
    if (len === 0) return createSeoResult(false, 'Tiêu đề đang trống. Cố gắng có được 55-60 ký tự.');
    if (len >= 55 && len <= 60) return createSeoResult(true, `Độ dài lý tưởng: ${len} ký tự.`);
    return createSeoResult(false, `Độ dài ${len} ký tự. Nên trong khoảng 55-60 ký tự.`);
});

// 4. Từ khóa chính trong Meta Desc
const evaluateSeoDescriptionKeyword = computed(() => {
    const seoDescription = normalizeText(product.Mo_ta_seo || '');
    if (!mainKeyword.value) return createSeoResult(false, 'Cần Tên sản phẩm để xác định từ khóa chính.');
    if (seoDescription.includes(mainKeyword.value)) return createSeoResult(true, 'Từ khóa chính đã có trong mô tả meta.');
    return createSeoResult(false, 'Thêm Từ khóa chính vào Mô tả meta SEO của bạn.');
});

// 5. Độ dài Meta Desc
const evaluateSeoDescriptionLength = computed(() => {
    const len = (product.Mo_ta_seo || '').length;
    if (len === 0) return createSeoResult(false, 'Mô tả meta SEO đang trống. Cố gắng có được 130-160 ký tự.');
    if (len >= 130 && len <= 160) return createSeoResult(true, `Độ dài lý tưởng: ${len} ký tự.`);
    return createSeoResult(false, `Độ dài ${len} ký tự. Nên trong khoảng 130-160 ký tự.`);
});

// 6. Từ khóa chính trong URL
const evaluateUrlKeyword = computed(() => {
    const slug = normalizeText(product.slug || '');
    if (!mainKeyword.value) return createSeoResult(false, 'Cần Tên sản phẩm để xác định từ khóa chính.');
    const comparableSlug = slug.replace(/-/g, ' ');
    if (comparableSlug.includes(mainKeyword.value)) return createSeoResult(true, 'Từ khóa chính đã có trong URL.');
    return createSeoResult(false, 'Sử dụng từ khóa chính trong URL của bạn.');
});

// 7. Độ dài nội dung
const evaluateContentLength = computed(() => {
    if (wordCount.value === 0) return createSeoResult(false, 'Nội dung đang trống. Nội dung phải dài 600-2500 từ.');
    if (wordCount.value >= 600 && wordCount.value <= 2500) return createSeoResult(true, `Nội dung có ${wordCount.value} từ (độ dài lý tưởng).`);
    return createSeoResult(false, `Nội dung có ${wordCount.value} từ (ngắn). Nội dung phải dài 600-2500 từ.`);
});

// 8. Mật độ từ khóa chính
const evaluateKeywordDensity = computed(() => {
    if (!mainKeyword.value || wordCount.value === 0) return createSeoResult(false, 'Cần Tên sản phẩm và Mô tả chi tiết để tính mật độ.');
    const occurrences = (descriptionText.value.match(new RegExp(mainKeyword.value.replace(/ /g, '\\s*'), 'g')) || []).length;
    const density = (occurrences / wordCount.value) * 100;
    if (density >= 1 && density <= 2) return createSeoResult(true, `Mật độ: ${density.toFixed(2)}%.`);
    return createSeoResult(false, `Mật độ: ${density.toFixed(2)}%. Nhắm đến khoảng 1-2% Mật độ từ khóa.`);
});

// 9. Từ khóa trong H2, H3, H4
const evaluateSubheadingKeyword = computed(() => {
    if (!mainKeyword.value || !descriptionHtml.value) return createSeoResult(false, 'Nội dung trống hoặc không có từ khóa chính để phân tích tiêu đề phụ.');
    const doc = parseHtmlContent(descriptionHtml.value);
    const headings = doc.querySelectorAll('h2, h3, h4');
    if (headings.length === 0) return createSeoResult(false, 'Nội dung chưa có tiêu đề phụ (H2, H3, H4).');
    for (let h of headings) {
        if (normalizeText(h.textContent).includes(mainKeyword.value)) return createSeoResult(true, 'Từ khóa chính đã có trong các tiêu đề phụ.');
    }
    return createSeoResult(false, 'Sử dụng từ khóa chính trong các tiêu đề phụ (H2, H3, v.v.).');
});

// 10. Alt text hình ảnh
const evaluateImageAltText = computed(() => {
    if (!descriptionHtml.value) return createSeoResult(true, 'Không có nội dung để kiểm tra hình ảnh.');
    const doc = parseHtmlContent(descriptionHtml.value);
    const images = doc.querySelectorAll('img');
    if (images.length === 0) return createSeoResult(true, 'Không có hình ảnh nào trong mô tả chi tiết.');
    for (let img of images) {
        if (!img.alt || img.alt.trim() === '') return createSeoResult(false, 'Một số hình ảnh trong mô tả chưa có thuộc tính alt.');
    }
    return createSeoResult(true, 'Tất cả hình ảnh trong mô tả đã có thuộc tính alt.');
});

// 11. Thêm hình ảnh cho nội dung
const evaluateAddImages = computed(() => {
    
  const totalImages = existingImages.value.length + newImageFiles.value.length;
  if (totalImages > 0) {
        return createSeoResult(true, `Đã có ${totalImages} hình ảnh sản phẩm.`);
    }
  return createSeoResult(false, 'Thêm một vài hình ảnh để làm cho sản phẩm hấp dẫn hơn.');
});

// 12. Các đoạn văn ngắn và súc tích
const evaluateShortParagraphs = computed(() => {
    if (!descriptionHtml.value) return createSeoResult(false, 'Viết mô tả chi tiết để đánh giá đoạn văn ngắn.');
    const doc = parseHtmlContent(descriptionHtml.value);
    const paragraphs = doc.querySelectorAll('p');
    if (paragraphs.length === 0) return createSeoResult(false, 'Nội dung chưa được chia thành các đoạn văn.');
    for (let p of paragraphs) {
        const pWordCount = p.textContent.split(/\s+/).filter(Boolean).length;
        if (pWordCount > 150) return createSeoResult(false, 'Một số đoạn văn quá dài (hơn 150 từ).');
    }
    return createSeoResult(true, 'Các đoạn văn có độ dài hợp lý.');
});

// 13. Thêm liên kết nội bộ
const evaluateInternalLinks = computed(() => {
    if (!descriptionHtml.value) return createSeoResult(false, 'Nội dung trống.');
    const doc = parseHtmlContent(descriptionHtml.value);
    if (doc.querySelectorAll('a').length > 0) return createSeoResult(true, 'Đã tìm thấy liên kết trong nội dung.');
    return createSeoResult(false, 'Thêm liên kết nội bộ vào nội dung của bạn.');
});

// 14. Từ khóa trong mở đầu & kết luận
const evaluateKeywordInIntroOutro = computed(() => {
    if (!mainKeyword.value || wordCount.value < 20) return createSeoResult(false, 'Cần Tên sản phẩm và Mô tả chi tiết để đánh giá.');
    const introText = descriptionText.value.substring(0, 200);
    const introFound = introText.includes(mainKeyword.value);
    if (introFound) return createSeoResult(true, 'Từ khóa chính xuất hiện trong đoạn mở đầu.');
    return createSeoResult(false, 'Từ khóa chính nên có trong đoạn mở đầu của mô tả chi tiết.');
});

// 15. Ngôn ngữ rõ ràng, dễ hiểu
const evaluateReadability = computed(() => {
    if (wordCount.value < 50) return createSeoResult(false, 'Hãy viết mô tả chi tiết sản phẩm để kiểm tra khả năng đọc.');
    return createSeoResult(true, 'Sử dụng câu ngắn và từ ngữ đơn giản để dễ đọc hơn.');
});

// 16. Tính độc đáo nội dung
const evaluateUniqueness = computed(() => {
    if (wordCount.value > 50) return createSeoResult(true, 'Nội dung có độ dài hợp lý. Cần kiểm tra trùng lặp thủ công.');
    return createSeoResult(false, 'Đảm bảo nội dung độc quyền, không trùng lặp (cần kiểm tra thủ công).');
});


</script>

<style scoped>


.add-product-page {
  padding: 20px;
}
.page-title {
  color: #333;
  margin-bottom: -30px;
  margin-top: -30px;
  font-size: 1.8em;
  font-weight: 600;
  
}
.add-product-form {
  max-width: 1300px !important;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: none !important;
  padding: 25px;
}
.form-grid {
  display: grid;
  grid-template-columns: 4fr 2fr;
  gap: 30px;
}
.col-main, .col-sidebar {
    display: flex;
    flex-direction: column;
    gap: 25px;
}
.card {
  background-color: #fff;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
}
.card-content {
  padding: 20px;
}
.card-title {
  font-size: 1.2em;
  color: #333;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  font-weight: 600;
}
.icon-margin {
  margin-right: 8px;
  color: #007bff;
}
.form-group {
  margin-bottom: 18px;
}
.form-label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #555;
}
.form-control {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 1em;
}
.form-error {
  color: #dc3545;
  font-size: 0.85em;
  margin-top: 5px;
}
#btn-add-varian{
    background-color: #007bff !important;
}
.toggle-switch {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}
.switch-input {
    height: 0;
    width: 0;
    visibility: hidden;
    position: absolute;
}
.switch-label-text {
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    width: 100%;
    padding: 5px 0;
    font-weight: 500;
}
.switch-button {
    display: block;
    width: 50px;
    height: 25px;
    background: #ccc;
    border-radius: 25px;
    position: relative;
    transition: background-color 0.3s;
}
.switch-button:after {
    content: '';
    position: absolute;
    top: 2.5px;
    left: 2.5px;
    width: 20px;
    height: 20px;
    background: #fff;
    border-radius: 50%;
    transition: 0.3s;
}
.switch-input:checked + .switch-label-text .switch-button {
    background: #007bff;
}
.switch-input:checked + .switch-label-text .switch-button:after {
    left: calc(100% - 2.5px);
    transform: translateX(-100%);
}
.image-upload-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border: 2px dashed #007bff;
    border-radius: 8px;
    padding: 20px;
    cursor: pointer;
    color: #007bff;
}
.hidden-input {
    display: none;
}
.image-preview-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 15px;
}
.image-preview-item {
    position: relative;
    width: 100px;
    height: 100px;
}
.preview-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 5px;
}
.remove-image-button {
    position: absolute;
    top: 5px;
    right: 5px;
    background-color: rgba(255, 0, 0, 0.7);
    color: white;
    border: none;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    cursor: pointer;
}
.form-actions {
    margin-top: 30px;
    text-align: right;
}
.btn-primary {
    background-color: #007bff;
    color: white;
    border: 1px solid #007bff;
}
.btn-large {
    padding: 12px 25px;
    font-size: 1.1em;
}
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.btn-ai {
  font-family: 'Segoe UI', sans-serif;
  font-weight: 600;
  font-size: 16px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: white;
  background: linear-gradient(135deg, #8A2BE2 0%, #4B0082 100%);
  border: none;
  padding: 6px 18px;
  border-radius: 5px;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
}

.btn-ai:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(75, 0, 130, 0.4);
  background: linear-gradient(135deg, #9932CC 0%, #5D0096 100%);
}


.btn-ai:active {
  transform: translateY(0);
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}
.alert.danger-alert {
    background-color: #f8d7da;
    color: #721c24;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 20px;
}
.seo-criteria-list {
    display: flex;
    flex-direction: column;
    font-size: 14px;
   
}
.seo-criteria-item {
    display: flex;
    align-items: flex-start;
    gap: 5px;
    height: 55px;
}
.criteria-details {
    display: flex;
    flex-direction: column;
}
.criteria-name {
    font-weight: 500;
}
.criteria-suggestion {
    font-size: 0.85em;
    color: #6c757d;
}
.status-good { color: #007bff; }
.status-bad { color: #6c757d; }

</style>