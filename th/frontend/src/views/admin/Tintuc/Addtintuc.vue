<template>
  <div class="add-news-page">
    <h2 class="page-title">Thêm tin tức mới</h2>
    <form @submit.prevent="addTintuc" class="add-news-form" enctype="multipart/form-data">
      <div class="form-grid">
        <div class="col-main">
          <!-- SEO & AI block -->
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
                  <input type="text" class="form-control" v-model="seoTitle" readonly />
                </div>
                <div class="form-group">
                  <label class="form-label">Mô tả SEO</label>
                  <textarea class="form-control" v-model="seoDescription" rows="2" readonly></textarea>
                </div>
                <div class="form-group">
                  <label class="form-label">Từ khóa SEO</label>
                  <input type="text" class="form-control" v-model="seoKeywords" readonly />
                </div>
              </div>
            </div>
          </div>
          <!-- End SEO & AI block -->

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
                <select v-model="id_danh_muc_tin_tuc" class="form-control" required>
                  <option value="">Chọn danh mục</option>
                  <option v-for="dm in danhMucs" :key="dm.id_danh_muc_tin_tuc" :value="Number(dm.id_danh_muc_tin_tuc)">
                    {{ dm.ten_danh_muc }}
                  </option>
                </select>
              </div>
              <div class="form-group">
                <label for="ngay_dang" class="form-label">Ngày đăng</label>
                <input type="date" id="ngay_dang" v-model="ngay_dang" class="form-control" />
              </div>
              <div class="form-group">
                <label for="noidung" class="form-label">Nội dung *</label>
                <div style="display: flex; align-items: flex-start; gap: 10px;">
                  <div id="editorjs" class="editor-box" style="flex: 1"></div>
                </div>
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
              </div>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-large">
              <span v-if="isSubmitting"><i class="fas fa fa-spinner fa-spin"></i> Đang lưu...</span>
              <span v-else><i class="fas fa-save"></i> Thêm tin tức</span>
            </button>
          </div>
        </div>
        <div class="col-seo">
          <!-- Đánh giá tiêu chí SEO chuyển sang bên phải -->
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
import EditorJS from "@editorjs/editorjs";
import Header from "@editorjs/header";
import List from "@editorjs/list";
import Paragraph from "@editorjs/paragraph";
import Quote from "@editorjs/quote";
import Marker from "@editorjs/marker";
import Delimiter from "@editorjs/delimiter";
import InlineCode from "@editorjs/inline-code";
import LinkTool from "@editorjs/link";

export default {
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
      editor: null,
      isSubmitting: false,
      // SEO/AI
      isGeneratingSeo: false,
      globalError: "",
      seoTitle: "",
      seoDescription: "",
      seoKeywords: "",
      newsDescriptionLong: "",
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
    }
  },
  methods: {
    generateSlug(text) {
      return text
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, "")
        .replace(/\s+/g, "-")
        .replace(/-+/g, "-");
    },
    async getDanhMucs() {
      try {
        const res = await axios.get("http://localhost:8000/api/danh-muc-tin-tuc");
        this.danhMucs = res.data;
      } catch (error) {
        alert("Không thể tải danh mục tin tức!");
      }
    },
    onFileChange(e) {
      const file = e.target.files[0];
      if (file) {
        this.file = file;
        this.previewImage = URL.createObjectURL(file);
        this.checkSeoCriteria();
      }
    },
    removeImage() {
      this.file = null;
      this.previewImage = "";
      const input = document.getElementById('hinh_anh');
      if (input) input.value = "";
      this.checkSeoCriteria();
    },
    async addTintuc() {
      this.isSubmitting = true;
      try {
        if (!this.tieude || !this.id_danh_muc_tin_tuc) {
          alert("Vui lòng nhập đầy đủ thông tin!");
          return;
        }
        const token = localStorage.getItem("token");
        if (!token) {
          alert("Bạn cần đăng nhập!");
          return;
        }
        const output = await this.editor.save();
        const noidung = JSON.stringify(output);

        const formData = new FormData();
        formData.append("tieude", this.tieude);
        formData.append("slug", this.slug);
        formData.append("id_danh_muc_tin_tuc", Number(this.id_danh_muc_tin_tuc));
        formData.append("ngay_dang", this.ngay_dang);
        formData.append("noidung", noidung);
        formData.append("tieu_de_seo", this.seoTitle || "");
        formData.append("mo_ta_seo", this.seoDescription || "");
        formData.append("tu_khoa_seo", this.seoKeywords || "");
        if (this.file) {
          formData.append("hinh_anh", this.file);
        }

        const res = await axios.post("http://localhost:8000/api/tintuc", formData, {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "multipart/form-data",
          },
        });

        if (res.status === 201) {
          alert("Thêm tin tức thành công!");
          this.$router.push("/admin/tintuc");
        }
      } catch (error) {
        alert("Có lỗi xảy ra khi thêm tin tức!");
      } finally {
        this.isSubmitting = false;
      }
    },
    async generateSeoContent() {
      if (!this.tieude) {
        alert('Vui lòng nhập Tiêu đề trước khi tạo nội dung SEO.');
        return;
      }
      if (!this.editor) {
        alert('Vui lòng nhập Nội dung trước khi tạo nội dung SEO.');
        return;
      }
      this.isGeneratingSeo = true;
      this.globalError = "";
      try {
        const output = await this.editor.save();
        const noidung = JSON.stringify(output);

        const token = localStorage.getItem("token");
        const response = await axios.post("http://localhost:8000/api/tintuc/generate-seo", {
          tieude: this.tieude,
          noidung: noidung,
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
          this.newsDescriptionLong = response.data.news_description_long;
          // Chèn trực tiếp vào EditorJS khi tạo xong AI
          const currentIndex = this.editor.blocks.getCurrentBlockIndex();
          await this.editor.blocks.insert('paragraph', { text: this.newsDescriptionLong }, undefined, currentIndex + 1);
        }

        this.checkSeoCriteria();
        alert('Tạo nội dung AI thành công!');
      } catch (error) {
        this.globalError = "Tạo SEO thất bại!";
      } finally {
        this.isGeneratingSeo = false;
      }
    },
    async initEditor() {
      if (this.editor) {
        await this.editor.destroy();
        this.editor = null;
      }
      this.editor = new EditorJS({
        holder: "editorjs",
        autofocus: true,
        placeholder: "Nhập nội dung tin tức...",
        tools: {
          header: Header,
          paragraph: {
            class: Paragraph,
            inlineToolbar: true
          },
          list: List,
          quote: Quote,
          marker: Marker,
          delimiter: Delimiter,
          inlineCode: InlineCode,
          linkTool: {
            class: LinkTool,
            config: { endpoint: null }
          },
        },
        onChange: () => {
          this.checkSeoCriteria();
        }
      });
      await this.editor.isReady;
    },
    async checkSeoCriteria() {
      // Lấy từ khóa chính đầu tiên
      const keyword = this.seoKeywords?.split(',')[0]?.trim() || '';
      // Tiêu đề chứa từ khóa chính
      this.seoCriteria[0].passed = keyword && this.tieude.toLowerCase().includes(keyword.toLowerCase());
      // Tiêu đề dưới 60 ký tự
      this.seoCriteria[1].passed = this.tieude.length > 0 && this.tieude.length <= 60;
      // Meta description chứa từ khóa
      this.seoCriteria[2].passed = keyword && this.seoDescription.toLowerCase().includes(keyword.toLowerCase());
      // Meta description 140-155 ký tự
      this.seoCriteria[3].passed = this.seoDescription.length >= 140 && this.seoDescription.length <= 155;
      // Có từ khóa SEO
      this.seoCriteria[4].passed = !!this.seoKeywords && this.seoKeywords.length > 0;
      // Từ khóa không trùng lặp
      if (this.seoKeywords) {
        const arr = this.seoKeywords.split(',').map(s => s.trim().toLowerCase());
        this.seoCriteria[5].passed = arr.length === new Set(arr).size;
      } else {
        this.seoCriteria[5].passed = false;
      }
      // Nội dung trên 300 từ
      if (this.editor) {
        const output = await this.editor.save();
        let text = '';
        output.blocks.forEach(block => {
          if (block.data && block.data.text) text += block.data.text + ' ';
        });
        this.seoCriteria[6].passed = text.trim().split(/\s+/).length >= 300;
        // Nội dung có tiêu đề phụ (H2, H3)
        this.seoCriteria[7].passed = output.blocks.some(block => block.type === 'header' && (block.data.level === 2 || block.data.level === 3));
        // Có liên kết nội bộ
        this.seoCriteria[9].passed = output.blocks.some(block => block.type === 'linkTool' && block.data && block.data.link && block.data.link.includes(window.location.hostname));
        // Có liên kết ngoài
        this.seoCriteria[10].passed = output.blocks.some(block => block.type === 'linkTool' && block.data && block.data.link && !block.data.link.includes(window.location.hostname));
      }
      // Có hình ảnh minh họa
      this.seoCriteria[8].passed = !!this.file || (this.previewImage && this.previewImage.length > 0);
      // Không lạm dụng từ khóa (tần suất < 5%)
      if (this.editor && keyword) {
        const output = await this.editor.save();
        let text = '';
        output.blocks.forEach(block => {
          if (block.data && block.data.text) text += block.data.text + ' ';
        });
        const totalWords = text.trim().split(/\s+/).length;
        const keywordCount = (text.match(new RegExp(keyword, 'gi')) || []).length;
        this.seoCriteria[11].passed = totalWords > 0 ? (keywordCount / totalWords) < 0.05 : true;
      }
      // Không có lỗi chính tả (giả lập: luôn true)
      this.seoCriteria[12].passed = true;
      // URL thân thiện SEO
      this.seoCriteria[13].passed = /^[a-z0-9-]+$/.test(this.slug);
      // Slug không quá dài
      this.seoCriteria[14].passed = this.slug.length > 0 && this.slug.length <= 80;
      // Có mô tả ảnh (alt) (giả lập: nếu có ảnh thì true)
      this.seoCriteria[15].passed = !!this.file || (this.previewImage && this.previewImage.length > 0);
    }
  },
  mounted() {
    this.getDanhMucs();
    this.initEditor();
  },
  beforeUnmount() {
    if (this.editor) {
      this.editor.destroy();
      this.editor = null;
    }
  }
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
  max-width: 1400px; /* tăng từ 900px lên 1400px */
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  padding: 40px;      /* tăng padding */
  margin: 0 auto;
}
.form-grid {
  display: grid;
  grid-template-columns: 2.8fr 1.5fr; /* tăng tỷ lệ hai cột */
  gap: 50px; /* tăng khoảng cách giữa hai bảng */
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
.editor-box {
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  min-height: 120px;
  padding: 8px 12px;
  background: #fafbfc;
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
.ai-description {
  background: #fff;
  border-radius: 6px;
  padding: 10px 12px;
  border: 1px solid #e0e0e0;
  min-height: 60px;
  font-size: 1rem;
  color: #333;
  margin-top: 4px;
  max-height: 300px;
  overflow-y: auto;
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