<template>
  <div class="add-news-page">
    <h2 class="page-title">Thêm tin tức mới</h2>
    <form @submit.prevent="addTintuc" class="add-news-form" enctype="multipart/form-data">
      <div class="form-card">
        <div class="form-group">
          <label for="tieude" class="form-label">Tiêu đề *</label>
          <input type="text" id="tieude" v-model="tieude" class="form-control" required />
        </div>
        <div class="form-group">
          <label for="slug" class="form-label">Đường dẫn (slug)</label>
          <input
            type="text"
            id="slug"
            v-model="slug"
            class="form-control"
            placeholder="Tự động tạo từ tiêu đề hoặc tự nhập"
            @input="userEditedSlug = true"
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
          <div id="editorjs" class="editor-box"></div>
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
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Thêm tin tức</button>
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
    };
  },
  watch: {
    tieude(newVal) {
      if (!this.userEditedSlug) {
        this.slug = this.generateSlug(newVal);
      }
    },
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
      }
    },
    removeImage() {
      this.file = null;
      this.previewImage = "";
      // Optional: clear the input value
      const input = document.getElementById('hinh_anh');
      if (input) input.value = "";
    },
    async addTintuc() {
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
        if (error.response && error.response.data && error.response.data.errors) {
          alert(Object.values(error.response.data.errors).join('\n'));
        } else {
          alert("Có lỗi xảy ra khi thêm tin tức!");
        }
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
        i18n: {
          messages: {
            ui: {
              blockTunes: {
                toggler: {
                  'Click to tune': 'Nhấn để chỉnh',
                  'Click to untune': 'Nhấn để bỏ chỉnh'
                }
              },
              inlineToolbar: {
                converter: 'Chuyển định dạng'
              }
            },
            toolNames: {
              Text: 'Văn bản',
              Heading: 'Tiêu đề',
              List: 'Danh sách',
              Quote: 'Trích dẫn',
              Marker: 'Đánh dấu',
              Delimiter: 'Dấu phân cách',
              InlineCode: 'Mã nội tuyến',
              Link: 'Liên kết',
              Color: 'Màu'
            },
            tools: {
              link: {
                'Add a link': 'Thêm liên kết'
              }
            }
          }
        }
      });
      await this.editor.isReady;
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
  padding: 32px 0;
  max-width: 600px;
  margin: 0 auto;
}
.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: #2c3e50;
  text-align: center;
  margin-bottom: 32px;
}
.add-news-form {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.07);
  padding: 32px 24px;
}
.form-card {
  display: flex;
  flex-direction: column;
  gap: 22px;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.form-label {
  font-weight: 600;
  color: #444;
}
.form-control {
  border: 1px solid #dcdfe6;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 1rem;
  transition: border-color 0.2s;
}
.form-control:focus {
  border-color: #007bff;
  outline: none;
}
.editor-box {
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  min-height: 120px;
  padding: 8px 12px;
  background: #fafbfc;
}
.image-upload-label {
  border: 2px dashed #a8d4ff;
  background-color: #eaf6ff;
  border-radius: 12px;
  min-height: 80px;
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
  font-size: 2rem;
  margin-bottom: 8px;
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
.btn.btn-primary {
  background: #007bff;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 12px 28px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.2s;
}
.btn.btn-primary:hover {
  background: #0056b3;
}
</style>