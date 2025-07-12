<template>
  <div class="add-news-page">
    <h2 class="page-title">Chỉnh sửa tin tức</h2>
    <form @submit.prevent="updateTintuc" class="add-news-form" enctype="multipart/form-data">
      <div class="form-grid">
        <div class="col-main">
          <div class="card custom-card">
            <div class="card-content">
              <h3 class="card-title"><i class="fas fa-bullhorn icon-margin"></i> SEO & AI</h3>
              <button
                type="button"
                class="btn-ai"
                @click="generateSeoContent"
                :disabled="isGeneratingSeo"
                style="margin-bottom: 12px;"
              >
                <span v-if="isGeneratingSeo"><i class="fas fa-spinner fa-spin"></i> Đang tạo nội dung SEO...</span>
                <span v-else><i class="fas fa-magic"></i> Tạo nội dung SEO AI</span>
              </button>
              <div v-if="globalError" class="error-message">{{ globalError }}</div>
              <div v-if="seoTitle || seoDescription || seoKeywords" class="seo-result-box">
                <div class="form-group">
                  <label class="form-label">Tiêu đề SEO</label>
                  <input type="text" class="form-control" v-model="seoTitle" @input="checkSeoCriteria" />
                </div>
                <div class="form-group">
                  <label class="form-label">Mô tả SEO</label>
                  <textarea class="form-control" v-model="seoDescription" rows="2" @input="checkSeoCriteria"></textarea>
                </div>
                <div class="form-group">
                  <label class="form-label">Từ khóa SEO</label>
                  <input type="text" class="form-control" v-model="seoKeywords" @input="checkSeoCriteria" />
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Nội dung *</label>
                <Editor
                  v-model="newsDescriptionLong"
                  :api-key="tinymceApiKey"
                  :init="tinymceConfig"
                  @input="checkSeoCriteria"
                />
              </div>
            </div>
          </div>
          <div class="card custom-card">
            <div class="card-content">
              <h3 class="card-title"><i class="fas fa-info-circle icon-margin"></i> Thông tin chung</h3>
              <div class="form-group">
                <label for="tieude" class="form-label">Tiêu đề *</label>
                <input type="text" id="tieude" v-model="tieude" class="form-control" required @input="checkSeoCriteria" />
              </div>
              <div class="form-group">
                <label for="slug" class="form-label">Đường dẫn (slug)</label>
                <input
                  type="text"
                  id="slug"
                  v-model="slug"
                  class="form-control"
                  placeholder="Tự động tạo từ tiêu đề hoặc tự nhập"
                  @input="userEditedSlug = true; checkSeoCriteria()"
                />
              </div>
              <div class="form-group">
                <label for="id_danh_muc_tin_tuc" class="form-label">Danh mục *</label>
                <select v-model="id_danh_muc_tin_tuc" class="form-control" required @change="checkSeoCriteria">
                  <option value="">Chọn danh mục</option>
                  <option v-for="dm in danhMucs" :key="dm.id_danh_muc_tin_tuc" :value="Number(dm.id_danh_muc_tin_tuc)">
                    {{ dm.ten_danh_muc }}
                  </option>
                </select>
              </div>
              <div class="form-group">
                <label for="ngay_dang" class="form-label">Ngày đăng</label>
                <input type="date" id="ngay_dang" v-model="ngay_dang" class="form-control" @input="checkSeoCriteria" />
              </div>
              <div class="form-group">
                <label class="form-label">Hình ảnh</label>
                <label for="hinh_anh" class="image-upload-label">
                  <i class="fas fa-cloud-upload-alt"></i><span>Chọn hình ảnh</span>
                  <input
                    type="file"
                    id="hinh_anh"
                    @change="onFileChange"
                    accept="image/*"
                    class="hidden-input"
                  />
                </label>
                <div v-if="previewImage" class="image-preview">
                  <img :src="previewImage" alt="Preview" />
                  <button type="button" @click="removeImage" class="remove-image-button">&times;</button>
                </div>
                <div v-else-if="hinh_anh" class="image-preview">
                  <img :src="hinh_anh.startsWith('http') ? hinh_anh : `http://localhost:8000/storage/${hinh_anh}`" alt="Hình ảnh hiện tại" />
                  <button type="button" @click="removeImage" class="remove-image-button">&times;</button>
                </div>
              </div>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-large" :disabled="isSubmitting">
              <span v-if="isSubmitting"><i class="fas fa fa-spinner fa-spin"></i> Đang lưu...</span>
              <span v-else><i class="fas fa-save"></i> Cập nhật tin tức</span>
            </button>
          </div>
        </div>
        <div class="col-seo">
          <div class="seo-criteria-box">
            <h4 class="seo-criteria-title">Đánh giá tiêu chí SEO</h4>
            <ul class="seo-criteria-list">
              <li v-for="(item, idx) in seoCriteria" :key="idx">
                <span :class="{'criteria-passed': item.passed, 'criteria-failed': !item.passed}">
                  <i :class="item.passed ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i>
                  {{ item.label }}
                </span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import axios from "axios";
import Editor from '@tinymce/tinymce-vue';

export default {
  components: {
    Editor,
  },
  data() {
    return {
      tieude: "",
      slug: "",
      userEditedSlug: false,
      id_danh_muc_tin_tuc: null,
      ngay_dang: "",
      danhMucs: [],
      file: null,
      previewImage: "",
      hinh_anh: "",
      originalHinhAnh: "",
      isSubmitting: false,
      isGeneratingSeo: false,
      globalError: "",
      seoTitle: "",
      seoDescription: "",
      seoKeywords: "",
      newsDescriptionLong: "",
      tinymceApiKey: '41eu6h6iewknwxlxtm1mh0dge0z3tg5ubvt2clbc0dq85wgo', // <<< THAY THẾ BẰNG API KEY CỦA BẠN TẠI TINYMCE.COM
      tinymceConfig: {
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
        images_upload_url: '/api/tinymce/upload-image', // URL này thường chỉ là đường dẫn tương đối
        images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
          const formData = new FormData();
          formData.append('file', blobInfo.blob(), blobInfo.filename());

          const token = localStorage.getItem("token");
          const headers = {
            'Content-Type': 'multipart/form-data',
          };
          if (token) {
            headers['Authorization'] = `Bearer ${token}`;
          }

          // Đảm bảo URL đầy đủ khi gọi Axios
          axios.post('http://localhost:8000/api/tinymce/upload-image', formData, { // Thêm /api vào đây
          headers: headers,
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
        },
        convert_urls: false,
        relative_urls: false,
        remove_script_host: false,
        document_base_url: 'http://localhost:8000/'
      },
      seoCriteria: [
        { label: "Tiêu đề chứa từ khóa chính", passed: false },
        { label: "Tiêu đề dưới 60 ký tự", passed: false },
        { label: "Mô tả meta chứa từ khóa", passed: false },
        { label: "Mô tả meta 140-155 ký tự", passed: false },
        { label: "Có từ khóa SEO", passed: false },
        { label: "Từ khóa không trùng lặp", passed: false },
        { label: "Nội dung trên 300 từ", passed: false },
        { label: "Nội dung có tiêu đề phụ (H2, H3)", passed: false },
        { label: "Có hình ảnh minh họa", passed: false },
        { label: "Có liên kết nội bộ", passed: false },
        { label: "Có liên kết ngoài", passed: false },
        { label: "Không lạm dụng từ khóa", passed: false },
        { label: "Không có lỗi chính tả", passed: false },
        { label: "URL thân thiện SEO", passed: false },
        { label: "Slug không quá dài", passed: false },
        { label: "Có mô tả ảnh (alt)", passed: false },
      ],
    };
  },
  created() {
    this.getDanhMucs();
    this.getTintuc();
  },
  watch: {
    tieude(newVal) {
      if (!this.userEditedSlug) {
        this.slug = this.generateSlug(newVal);
      }
      this.checkSeoCriteria();
    },
    seoTitle() {
      this.checkSeoCriteria();
    },
    seoDescription() {
      this.checkSeoCriteria();
    },
    seoKeywords() {
      this.checkSeoCriteria();
    },
    newsDescriptionLong() {
      this.checkSeoCriteria();
    },
    slug() {
      this.checkSeoCriteria();
    },
  },
  methods: {
    async getDanhMucs() {
      try {
        const res = await axios.get("http://localhost:8000/api/danh-muc-tin-tuc");
        this.danhMucs = res.data;
      } catch (error) {
        alert("Không thể tải danh mục tin tức!");
        console.error("Lỗi tải danh mục:", error);
      }
    },
    async getTintuc() {
      try {
        const token = localStorage.getItem("token");
        if (!token) {
          alert("Bạn cần đăng nhập để xem tin tức!");
          this.$router.push("/login");
          return;
        }

        const res = await axios.get(`http://localhost:8000/api/tintuc/${this.$route.params.id}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        const data = res.data;

        this.tieude = data.tieude;
        this.slug = data.slug;
        this.id_danh_muc_tin_tuc = Number(data.id_danh_muc_tin_tuc);
        this.ngay_dang = data.ngay_dang ? data.ngay_dang.substring(0, 10) : "";
        this.seoTitle = data.tieu_de_seo || "";
        this.seoDescription = data.mo_ta_seo || "";
        this.seoKeywords = data.tu_khoa_seo || "";

        let content = data.noidung || "";
        content = content
          .replace(/<\s*html[^>]*>/gi, '')
          .replace(/<\s*\/\s*html\s*>/gi, '')
          .replace(/<\s*head[^>]*>[\s\S]*?<\s*\/\s*head\s*>/gi, '')
          .replace(/<\s*body[^>]*>/gi, '')
          .replace(/<\s*\/\s*body\s*>/gi, '');
        this.newsDescriptionLong = content.trim();

        this.hinh_anh = data.hinh_anh || "";
        this.originalHinhAnh = data.hinh_anh || "";
        this.checkSeoCriteria();
      } catch (error) {
        alert("Không thể tải dữ liệu tin tức hoặc không có quyền!");
        console.error("Lỗi tải tin tức:", error);
      }
    },
    onFileChange(e) {
      const file = e.target.files[0];
      if (file) {
        this.file = file;
        this.previewImage = URL.createObjectURL(file);
        this.hinh_anh = "";
        this.checkSeoCriteria();
      }
    },
    removeImage() {
      this.file = null;
      this.previewImage = "";
      this.hinh_anh = "";
      const input = document.getElementById('hinh_anh');
      if (input) input.value = "";
      this.checkSeoCriteria();
    },
    async updateTintuc() {
      this.isSubmitting = true;
      try {
        if (!this.tieude || !this.id_danh_muc_tin_tuc) {
          alert("Vui lòng nhập đầy đủ thông tin bắt buộc (Tiêu đề, Danh mục)!");
          this.isSubmitting = false;
          return;
        }
        const token = localStorage.getItem("token");
        if (!token) {
          alert("Bạn cần đăng nhập!");
          this.$router.push("/login");
          this.isSubmitting = false;
          return;
        }

        const formData = new FormData();
        formData.append("tieude", this.tieude);
        formData.append("slug", this.slug);
        formData.append("id_danh_muc_tin_tuc", Number(this.id_danh_muc_tin_tuc));
        formData.append("ngay_dang", this.ngay_dang);
        formData.append("tieu_de_seo", this.seoTitle || "");
        formData.append("mo_ta_seo", this.seoDescription || "");
        formData.append("tu_khoa_seo", this.seoKeywords || "");
        formData.append("noidung", this.newsDescriptionLong || "");

        if (this.file) {
          formData.append("hinh_anh", this.file);
        } else if (this.originalHinhAnh && this.hinh_anh === "") {
          formData.append("remove_hinh_anh", "true");
        }

        const res = await axios.post(
          `http://localhost:8000/api/tintuc/${this.$route.params.id}?_method=PUT`,
          formData,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              "Content-Type": "multipart/form-data",
            },
          }
        );

        if (res.status === 200 || res.status === 201) {
          alert("Cập nhật tin tức thành công!");
          this.$router.push("/admin/tintuc");
        }
      } catch (error) {
        console.error("Lỗi khi cập nhật tin tức:", error.response ? error.response.data : error);
        alert("Có lỗi xảy ra khi cập nhật tin tức! Vui lòng kiểm tra lại thông tin.");
      } finally {
        this.isSubmitting = false;
      }
    },
    async generateSeoContent() {
      if (!this.tieude) {
        alert('Vui lòng nhập Tiêu đề trước khi tạo nội dung SEO.');
        return;
      }
      this.isGeneratingSeo = true;
      this.globalError = "";
      try {
        const token = localStorage.getItem("token");
        const response = await axios.post("http://localhost:8000/api/tintuc/generate-seo", {
          tieude: this.tieude,
          noidung: this.newsDescriptionLong || this.tieude,
        }, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        });

        if (response.data.seo_title) {
          this.seoTitle = response.data.seo_title;
        }
        if (response.data.seo_description) {
          this.seoDescription = response.data.seo_description;
        }
        if (response.data.seo_keywords) {
          this.seoKeywords = response.data.seo_keywords;
        }
        if (response.data.news_description_long) {
          let content = response.data.news_description_long;
          content = content
            .replace(/<\s*html[^>]*>/gi, '')
            .replace(/<\s*\/\s*html\s*>/gi, '')
            .replace(/<\s*head[^>]*>[\s\S]*?<\s*\/\s*head\s*>/gi, '')
            .replace(/<\s*body[^>]*>/gi, '')
            .replace(/<\s*\/\s*body\s*>/gi, '');
          this.newsDescriptionLong = content.trim();
        }

        this.checkSeoCriteria();
        alert('Tạo nội dung AI và SEO thành công!');
      } catch (error) {
        console.error("Lỗi khi tạo nội dung AI:", error.response ? error.response.data : error);
        this.globalError = "Tạo SEO thất bại! Vui lòng thử lại.";
      } finally {
        this.isGeneratingSeo = false;
      }
    },
    generateSlug(text) {
      if (!text) return '';
      return text
        .toString()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .trim()
        .replace(/\s+/g, "-")
        .replace(/[^\w-]+/g, "")
        .replace(/--+/g, "-");
    },
    checkSeoCriteria() {
      const keyword = this.seoKeywords?.split(',')[0]?.trim() || '';

      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = this.newsDescriptionLong;
      const newsContentWithoutHtml = tempDiv.textContent || tempDiv.innerText || '';


      this.seoCriteria[0].passed = keyword && this.tieude.toLowerCase().includes(keyword.toLowerCase());
      this.seoCriteria[1].passed = this.tieude.length > 0 && this.tieude.length <= 60;
      this.seoCriteria[2].passed = keyword && this.seoDescription.toLowerCase().includes(keyword.toLowerCase());
      this.seoCriteria[3].passed = this.seoDescription.length >= 140 && this.seoDescription.length <= 155;
      this.seoCriteria[4].passed = !!this.seoKeywords && this.seoKeywords.length > 0;

      if (this.seoKeywords) {
        const arr = this.seoKeywords.split(',').map(s => s.trim().toLowerCase()).filter(s => s.length > 0);
        this.seoCriteria[5].passed = arr.length === new Set(arr).size;
      } else {
        this.seoCriteria[5].passed = false;
      }

      this.seoCriteria[6].passed = newsContentWithoutHtml.trim().split(/\s+/).filter(word => word.length > 0).length >= 300;
      this.seoCriteria[7].passed = /<h2[\s>]/i.test(this.newsDescriptionLong) || /<h3[\s>]/i.test(this.newsDescriptionLong);
      this.seoCriteria[8].passed = !!this.file || (this.previewImage && this.previewImage.length > 0) || (this.hinh_anh && this.hinh_anh.length > 0) || /<img[\s>]/i.test(this.newsDescriptionLong);

      const currentDomain = window.location.hostname;
      this.seoCriteria[9].passed = new RegExp(`<a[^>]+href=["'](?:https?:\/\/)?(?:www\\.)?${currentDomain}[^"']*["']`, 'i').test(this.newsDescriptionLong) || /<a[^>]+href=["']\/(?!\/)[^"']*["']/i.test(this.newsDescriptionLong);
      this.seoCriteria[10].passed = /<a[^>]+href=["']https?:\/\/(?!www\.)(?!${currentDomain})[^"']+["']/i.test(this.newsDescriptionLong);

      if (newsContentWithoutHtml && keyword) {
        const totalWords = newsContentWithoutHtml.trim().split(/\s+/).filter(word => word.length > 0).length;
        const keywordCount = (newsContentWithoutHtml.match(new RegExp(`\\b${keyword}\\b`, 'gi')) || []).length;
        this.seoCriteria[11].passed = totalWords > 0 ? (keywordCount / totalWords) < 0.05 : true;
      } else {
        this.seoCriteria[11].passed = true;
      }

      this.seoCriteria[12].passed = true;
      this.seoCriteria[13].passed = /^[a-z0-9-]+$/.test(this.slug);
      this.seoCriteria[14].passed = this.slug.length > 0 && this.slug.length <= 80;
      this.seoCriteria[15].passed = (!!this.file || (this.previewImage && this.previewImage.length > 0) || (this.hinh_anh && this.hinh_anh.length > 0)) && /<img[^>]+alt=["'][^"']+["']/i.test(this.newsDescriptionLong);
    },
  },
};
</script>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
.add-news-page {
  padding: 20px;
}
.page-title {
  color: #333;
  margin-bottom: 25px;
  font-size: 1.8em;
  font-weight: 600;
}
.add-news-form {
  max-width: 1400px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  padding: 40px;
  margin: 0 auto;
}
.form-grid {
  display: grid;
  grid-template-columns: 2.8fr 1.5fr;
  gap: 50px;
}
.col-main {
  grid-column: 1;
}
.col-seo {
  grid-column: 2;
}
.card.custom-card {
  width: 100%;
  min-width: 0;
}
.seo-criteria-box {
  width: 100%;
  min-width: 0;
}
@media (max-width: 1400px) {
  .add-news-form {
    max-width: 100vw;
    padding: 20px;
  }
  .form-grid {
    gap: 20px;
  }
}
@media (max-width: 992px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  .col-seo {
    grid-column: 1;
    margin-top: 20px;
  }
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
  color: #007bff;
}
.form-group {
  margin-bottom: 18px;
  display: flex;
  flex-direction: column;
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
.image-preview {
  margin-top: 10px;
  position: relative;
}
.image-preview img {
  max-width: 120px;
  max-height: 80px;
  border-radius: 6px;
  border: 1px solid #e0e0e0;
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
.form-actions {
  margin-top: 24px;
  display: flex;
  justify-content: flex-end;
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
.btn-sm.btn-success {
  background: #28a745;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 8px 14px;
  font-size: 0.95rem;
  cursor: pointer;
  margin-left: 4px;
  transition: background 0.2s;
}
.btn-sm.btn-success:hover {
  background: #218838;
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
.seo-criteria-box {
  background: #f4f8ff;
  border-radius: 8px;
  padding: 14px 14px 10px 14px;
  margin-bottom: 12px;
  border: 1px solid #d0e3ff;
  position: sticky;
  top: 30px;
}
.seo-criteria-title {
  font-size: 1.08em;
  font-weight: 600;
  color: #1a237e;
  margin-bottom: 8px;
}
.seo-criteria-list {
  list-style: none;
  padding: 0;
  margin: 0;
}
.seo-criteria-list li {
  margin-bottom: 4px;
  font-size: 0.98em;
}
.criteria-passed {
  color: #388e3c;
}
.criteria-failed {
  color: #d32f2f;
}
.seo-criteria-list i {
  margin-right: 6px;
}
.error-message {
  color: #dc3545;
  margin-top: 8px;
  font-size: 0.98rem;
}
.seo-result-box {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 18px 14px;
  margin-bottom: 10px;
  margin-top: 10px;
  border: 1px solid #e0e0e0;
}
@media (max-width: 992px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  .col-seo {
    grid-column: 1;
    margin-top: 20px;
  }
}
</style>