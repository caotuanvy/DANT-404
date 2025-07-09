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
                <editor
                  api-key="tybxi5binushm14hreotxk86vfsdjfaw6qk7vmsj9gv9iw5u"
                  :init="editorConfig"
                  v-model="product.mo_ta"
                />
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
                      <div>
                          <input v-model="newVariant.ten_bien_the" placeholder="Tên biến thể*" class="form-control"/>
                          <small v-if="newVariantErrors.ten_bien_the" class="form-error">{{ newVariantErrors.ten_bien_the }}</small>
                      </div>
                      <div>
                          <input v-model="newVariant.kich_thuoc" placeholder="Kích thước" class="form-control"/>
                      </div>
                      <div>
                          <input v-model="newVariant.mau_sac" placeholder="Màu sắc" class="form-control"/>
                      </div>
                      <div>
                          <input v-model.number="newVariant.so_luong_ton_kho" placeholder="Tồn kho*" type="number" class="form-control"/>
                          <small v-if="newVariantErrors.so_luong_ton_kho" class="form-error">{{ newVariantErrors.so_luong_ton_kho }}</small>
                      </div>
                      <div>
                          <input v-model.number="newVariant.gia" placeholder="Giá*" type="number" class="form-control"/>
                          <small v-if="newVariantErrors.gia" class="form-error">{{ newVariantErrors.gia }}</small>
                      </div>
                      <div>
                          <input v-model.number="newVariant.chieu_dai" placeholder="Dài (cm)" type="number" class="form-control"/>
                          <small v-if="newVariantErrors.chieu_dai" class="form-error">{{ newVariantErrors.chieu_dai }}</small>
                      </div>
                      <div>
                          <input v-model.number="newVariant.chieu_rong" placeholder="Rộng (cm)" type="number" class="form-control"/>
                      </div>
                      <div>
                          <input v-model.number="newVariant.chieu_cao" placeholder="Cao (cm)" type="number" class="form-control"/>
                      </div>
                      <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i> Thêm</button>
                  </form>
              </fieldset>
            </div>
          </div>
          
          <div class="card custom-card">
            <div class="card-content">
              <h3 class="card-title"><i class="fas fa-bullhorn icon-margin"></i> Khuyến mãi & SEO</h3>
              <button
                  type="button"
                  @click="openAiModal"
                  :disabled="isGeneratingSeo"
                  class="btn-ai"
                  title="Mở công cụ AI để tạo nội dung">
                  <span v-if="isGeneratingSeo"><i class="fas fa-spinner fa-spin"></i> Đang tạo...</span>
                  <span v-else><i class="fas fa-magic"></i> Tự Tạo Bài Viết</span>
              </button>
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
                <editor
                  api-key="i8jfbctjyqwxr70lr3tabny38dnxu76jsa0w2suliq0hdyf1"
                  :init="editorConfig"
                  v-model="product.Mo_ta_seo"
                />
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
          <label class="switch-label-text" for="trang_thai">
            Hoạt động (Hiển thị)
            <span class="switch-button"></span> </label>
                </div>
                <div class="form-group toggle-switch">
                  <input class="switch-input" type="checkbox" id="noi_bat" v-model="product.noi_bat">
                  <label class="switch-label-text" for="noi_bat">
                    Ghim trang chủ
                    <span class="switch-button"></span> </label>
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
          <span v-if="isSubmitting"><i class="fas fa fa-spinner fa-spin"></i> Đang lưu...</span>
          <span v-else><i class="fas fa-save"></i> Lưu sản phẩm</span>
        </button>
      </div>
        <p v-if="globalError" class="alert danger-alert">{{ globalError }}</p>
    </form>
    <div v-if="showAiModal" class="modal-overlay" @click.self="closeAiModal">
    <div class="modal-content-ai">
        <div class="modal-header">
            <h3>Tạo nội dung bằng AI</h3>
            <button type="button" class="close-button" @click="closeAiModal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="aiProductName" class="form-label">Tên sản phẩm chính:</label>
                <input type="text" id="aiProductName" v-model="aiInput.product_name" class="form-control" />
                <small class="form-text">Đây là tên chính AI sẽ dùng để tạo nội dung.</small>
            </div>
            <div class="form-group">
                <label for="aiKeywords" class="form-label">Các từ khóa liên quan (phân cách bởi dấu phẩy):</label>
                <input type="text" id="aiKeywords" v-model="aiInput.keywords" class="form-control" placeholder="VD: bánh ngọt, bánh kem, sinh nhật" />
                <small class="form-text">Các từ khóa giúp AI hiểu rõ hơn ngữ cảnh.</small>
            </div>
            <div class="form-group">
                <label for="aiDetailedContent" class="form-label">Nội dung chi tiết bổ sung (tùy chọn):</label>
                <textarea id="aiDetailedContent" v-model="aiInput.detailed_content" class="form-control" rows="5" placeholder="VD: Sản phẩm có hương vị vani, được làm thủ công, không chứa chất bảo quản."></textarea>
                <small class="form-text">Cung cấp thêm thông tin cụ thể về sản phẩm.</small>
            </div>

            <div class="form-group">
                <label class="form-label">Tùy chọn nội dung chi tiết AI tạo:</label>
                <div class="form-check">
                    <input type="checkbox" id="aiSpecs" v-model="aiInput.is_product_specifications" class="form-check-input">
                    <label class="form-check-label" for="aiSpecs">Bao gồm thông số kỹ thuật/đặc điểm sản phẩm</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" id="aiManufacture" v-model="aiInput.is_manufacture_info" class="form-check-input">
                    <label class="form-check-label" for="aiManufacture">Bao gồm thông tin sản xuất</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" id="aiExport" v-model="aiInput.is_export_info" class="form-check-input">
                    <label class="form-check-label" for="aiExport">Bao gồm thông tin xuất khẩu</label>
                </div>
            </div>

            <p v-if="aiGenerationError" class="form-error mt-2">{{ aiGenerationError }}</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeAiModal">Hủy</button>
            <button type="button" class="btn btn-primary" @click="generateSeoContent" :disabled="isGeneratingAiContent || !aiInput.product_name">
                <span v-if="isGeneratingAiContent"><i class="fas fa-spinner fa-spin"></i> Đang tạo...</span>
                <span v-else><i class="fas fa-magic"></i> Tạo Nội Dung</span>
            </button>
        </div>
    </div>
</div>
  </div>
  
</template>

<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue';
import axios from 'axios';
import Editor from '@tinymce/tinymce-vue';
const showAiModal = ref(false);
 
const aiInput = reactive({    
    product_name: '',
    keywords: '',
    detailed_content: '', 
    is_product_specifications: false, 
    is_manufacture_info: false,      
    is_export_info: false,           
});
const aiGenerationError = ref('');
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
const isGeneratingSeo = ref(false);
const isGeneratingAiContent = ref(false);
const newVariant = reactive({
  ten_bien_the: '',
  kich_thuoc: '',
  mau_sac: '',
  gia: null,
  so_luong_ton_kho: null,
  chieu_dai: null,
  chieu_rong: null,
  chieu_cao: null,
});
const newVariantErrors = reactive({});

const categories = ref([]);
const imageFiles = ref([]);
const imagePreviews = ref([]);
const isSubmitting = ref(false);
const errors = ref({});
const globalError = ref('');

onMounted(async () => {
  try {
    const response = await axios.get('categories');
    categories.value = response.data;
  } catch (error) {
    console.error("Không thể tải danh mục:", error);
  }
});

watch(() => product.ten_san_pham, (newVal) => {
  if (newVal) {
    product.slug = newVal.toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim().replace(/đ/g, 'd').replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-');
  }
});

const addVariant = () => {
  Object.keys(newVariantErrors).forEach(key => newVariantErrors[key] = '');

  let isValid = true;

  if (!newVariant.ten_bien_the) {
    newVariantErrors.ten_bien_the = 'Tên biến thể là bắt buộc.';
    isValid = false;
  }
  if (!newVariant.gia || newVariant.gia <= 0) {
    newVariantErrors.gia = 'Giá phải là số dương.';
    isValid = false;
  }
  if (newVariant.so_luong_ton_kho === null || newVariant.so_luong_ton_kho < 0) {
    newVariantErrors.so_luong_ton_kho = 'Số lượng tồn kho không hợp lệ.';
    isValid = false;
  }
  if (newVariant.chieu_dai !== null && newVariant.chieu_dai < 0) {
    newVariantErrors.chieu_dai = 'Chiều dài không hợp lệ.';
    isValid = false;
  }

  if (!isValid) {
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
  URL.revokeObjectURL(imagePreviews.value[index]);
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

const generateSeoContent = async () => {
    if (!aiInput.product_name) {
        aiGenerationError.value = 'Tên sản phẩm là bắt buộc để tạo nội dung AI.';
        return;
    }

    isGeneratingAiContent.value = true; 
    aiGenerationError.value = '';

    try {
        const payload = {
            product_name: aiInput.product_name,
            keywords: aiInput.keywords,
            detailed_content: aiInput.detailed_content,
            generate_specs: aiInput.is_product_specifications, 
            generate_manufacture: aiInput.is_manufacture_info, 
            generate_export: aiInput.is_export_info,           
           
        };

        const response = await axios.post('products/generate-seo', payload, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('token')}`
            }
        });
        if (response.data.seo_title) {
            product.Tieu_de_seo = response.data.seo_title;
        }
        if (response.data.seo_description) {
            product.Mo_ta_seo = response.data.seo_description;
        }
        if (response.data.seo_keywords) {
            product.Tu_khoa = response.data.seo_keywords;
        }
        if (response.data.product_description_long) {
            product.mo_ta = response.data.product_description_long;
        }

        alert('Tạo nội dung AI thành công!');
        closeAiModal(); 

    } catch (error) {
        console.error("Lỗi khi tạo nội dung AI:", error);
        if (error.response && error.response.status === 401) {
            aiGenerationError.value = "Tạo nội dung AI thất bại: Phiên đăng nhập đã hết hạn hoặc không hợp lệ. Vui lòng đăng nhập lại.";
        } else {
            aiGenerationError.value = "Tạo nội dung AI thất bại: " + (error.response?.data?.error || "Lỗi không xác định.");
        }
    } finally {
        isGeneratingAiContent.value = false; 
    }
};

// --- TINYMCE EDITOR
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

// --- COMPUTED PROPERTIES CHO ĐÁNH GIÁ SEO NỘI DUNG ---
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
const openAiModal = () => {
    aiInput.product_name = product.ten_san_pham; 
    aiInput.keywords = product.Tu_khoa;
    aiInput.detailed_content = ''; 
    aiInput.is_product_specifications = false;
    aiInput.is_manufacture_info = false;
    aiInput.is_export_info = false;
    aiGenerationError.value = '';
    showAiModal.value = true;
};

const closeAiModal = () => {
    showAiModal.value = false;
    aiGenerationError.value = ''; 
};
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
    if (imageFiles.value.length > 0) return createSeoResult(true, `Đã có ${imageFiles.value.length} hình ảnh được thêm.`);
    return createSeoResult(false, 'Thêm một vài hình ảnh để làm cho nội dung của bạn hấp dẫn.');
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
.container-table{
  max-width: 1350px !important;
  padding: 10px !important;
  
}
.seo-criteria-list {
    display: flex;
    flex-direction: column;
    
}
.seo-criteria-item {
    display: flex;
    align-items: flex-start;
    gap: 5px;
    height: 55px;
    
}
form div {
  margin-bottom: 0px !important;
}
.criteria-details {
    display: flex;
    flex-direction: column;
}
.criteria-name {
    font-weight: 500;
    font-size: 0.85em;
    
}
.criteria-suggestion {
    font-size: 0.65em;
    color: #6c757d;
    
}
.status-good { color: #007bff; }
.status-bad { color: #6c757d; }

.add-product-page {
  padding: 20px;
}

.page-title {
  color: #333;
  margin-bottom: 25px;
  font-size: 1.8em;
  font-weight: 600;
}

.add-product-form {
  max-width: 1200px !important;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  padding: 25px;
}

.form-grid {
  display: grid;
  grid-template-columns: 4fr 2fr;
  gap: 30px;
}

.card {
  background-color: #fff;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
  margin-bottom: 25px;
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
  color: #007bff; /* Primary color for icons */
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
  transition: border-color 0.2s ease-in-out;
}

.form-control:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

textarea.form-control {
  resize: vertical;
  min-height: 100px;
}

.form-error {
  color: #dc3545;
  font-size: 0.85em;
  margin-top: 5px;
  display: block;
}

.form-text {
    font-size: 0.85em;
    color: #6c757d;
    margin-bottom: 15px;
}

/* Toggle Switch */
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
    color: #333; 
    font-weight: 500;
}

.switch-button {
    display: block;
    width: 50px; 
    min-width: 50px;
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
    border-radius: 90px;
    transition: 0.3s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2); 
}


.switch-input:checked + .switch-label-text .switch-button {
    background: #007bff; 
}

.switch-input:checked + .switch-label-text .switch-button:after {
    left: calc(100% - 2.5px);
    transform: translateX(-100%);
}



/* Variant Table */
.variant-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}

.variant-table th,
.variant-table td {
  border: 1px solid #e0e0e0;
  padding: 10px 12px;
  text-align: left;
}

.variant-table th {
  background-color: #f8f8f8;
  font-weight: bold;
  color: #333;
}

.variant-table .icon-button {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  font-size: 1.1em;
  color: #dc3545; /* Red for delete */
}
.variant-table .icon-button:hover {
    color: #c82333;
}
.fieldset-style {
    border: 1px solid #007bff; 
    padding: 20px;
    margin-top: 20px;
    border-radius: 8px;
    background-color: #e7f3ff; 
}

.fieldset-legend {
    font-size: 1.1em;
    font-weight: bold;
    color: #007bff;
    padding: 0 10px;
    margin-left: 10px;
    border-radius: 5px;
}

.form-inline-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    align-items: end; /* Align items to the bottom */
}

.form-inline-grid .form-control {
    margin-bottom: 0; /* Remove bottom margin from form-control */
}

.form-inline-grid .btn {
    align-self: center; /* Center the button vertically */
    padding: 10px 15px;
    font-size: 1em;
}

/* Image Upload */
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
  font-size: 1.1em;
  transition: all 0.2s ease-in-out;
  min-height: 120px;
  text-align: center;
}

.image-upload-label:hover {
  background-color: #e7f3ff;
  border-color: #0056b3;
}

.image-upload-label i {
  font-size: 2.5em;
  margin-bottom: 10px;
}

.hidden-input {
  display: none;
}

.image-preview-container {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 20px;
}

.image-preview-item {
  position: relative;
  width: 100px;
  height: 100px;
  border: 1px solid #ddd;
  border-radius: 5px;
  overflow: hidden;
}

.preview-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
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
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2em;
  cursor: pointer;
  padding: 0;
  line-height: 1;
  transition: background-color 0.2s;
}

.remove-image-button:hover {
  background-color: rgba(255, 0, 0, 0.9);
}

/* Form Actions */
.form-actions {
  margin-top: 30px;
  text-align: right;
}

.btn {
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1em;
  font-weight: 500;
  transition: background-color 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-primary {
  background-color: #007bff;
  color: white;
  border: 1px solid #007bff;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0056b3;
  border-color: #0056b3;
}

.btn-success {
    background-color: #28a745;
    color: white;
    border: 1px solid #28a745;
}

.btn-success:hover:not(:disabled) {
    background-color: #218838;
    border-color: #1e7e34;
}

.btn-outline-danger {
    background-color: transparent;
    color: #dc3545;
    border: 1px solid #dc3545;
}

.btn-outline-danger:hover:not(:disabled) {
    background-color: #dc3545;
    color: white;
}

.btn-large {
  padding: 12px 25px;
  font-size: 1.1em;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn i {
  margin-right: 8px;
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

.alert {
  padding: 10px 15px;
  border-radius: 5px;
  margin-top: 20px;
  font-size: 0.95em;
}

.danger-alert {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

/* SEO Analysis Table */
.seo-analysis-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
  font-size: 0.9em;
}

.seo-analysis-table th,
.seo-analysis-table td {
  border: 1px solid #e0e0e0;
  padding: 10px 12px;
  text-align: left;
  vertical-align: top;
}

.seo-analysis-table th {
  background-color: #f8f8f8;
  font-weight: bold;
  color: #333;
}

.seo-analysis-table .status-good {
  color: #28a745; /* Green */
  font-weight: bold;
}

.seo-analysis-table .status-bad {
  color: #dc3545; /* Red */
  font-weight: bold;
}

.seo-analysis-table .status-neutral {
  color: #ffc107; /* Orange/Yellow for neutral/warning */
  font-weight: bold;
}
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000; /* Đảm bảo modal hiển thị trên cùng */
}

.modal-content-ai {
    background-color: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    width: 90%;
    max-width: 600px; /* Chiều rộng tối đa của modal */
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    max-height: 90vh; /* Giới hạn chiều cao để có thể scroll */
    overflow-y: auto; /* Thêm scroll nếu nội dung dài */
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #eee;
    padding-bottom: 15px;
    margin-bottom: 20px;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.5rem;
    color: #333;
}

.modal-body {
    flex-grow: 1; /* Cho phép body mở rộng */
    padding-bottom: 20px;
}

.modal-footer {
    border-top: 1px solid #eee;
    padding-top: 15px;
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px; /* Khoảng cách giữa các nút */
}

.close-button {
    background: none;
    border: none;
    font-size: 1.8rem;
    cursor: pointer;
    color: #888;
    transition: color 0.2s;
}

.close-button:hover {
    color: #333;
}
@media (max-width: 992px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .form-inline-grid {
    grid-template-columns: 1fr;
  }
  .form-inline-grid .btn {
      width: 100%;
  }
}
</style>