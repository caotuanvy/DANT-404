<template>
  <div>
    <h2>Thêm tin tức mới</h2>
    <form @submit.prevent="addTintuc" enctype="multipart/form-data">
      <div>
        <label for="tieude">Tiêu đề:</label>
        <input type="text" id="tieude" v-model="tieude" required />
      </div>
      <div>
        <label for="slug">Đường dẫn (slug):</label>
        <input
          type="text"
          id="slug"
          v-model="slug"
          placeholder="Tự động tạo từ tiêu đề hoặc tự nhập"
          @input="userEditedSlug = true"
        />
      </div>
      <div>
        <label for="id_danh_muc_tin_tuc">Danh mục:</label>
        <select v-model="id_danh_muc_tin_tuc" required>
          <option value="">Chọn danh mục</option>
          <option v-for="dm in danhMucs" :key="dm.id_danh_muc_tin_tuc" :value="Number(dm.id_danh_muc_tin_tuc)">
            {{ dm.ten_danh_muc }}
          </option>
        </select>
      </div>
      <div>
        <label for="ngay_dang">Ngày đăng:</label>
        <input type="date" id="ngay_dang" v-model="ngay_dang" />
      </div>
      <div>
        <label for="noidung">Nội dung:</label>
        <div id="editorjs" class="editor-box"></div>
      </div>
      <div>
        <label for="hinh_anh">Hình ảnh:</label>
        <input
          type="file"
          id="hinh_anh"
          @change="onFileChange"
          accept="image/*"
        />
        <div v-if="previewImage" style="margin-top: 10px;">
          <img :src="previewImage" alt="Preview" style="max-width: 120px; max-height: 80px;" />
        </div>
      </div>
      <div>
        <button type="submit">Thêm tin tức</button>
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
      noi_bat: 0,
      duyet_tin_tuc: 0,
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

        // Lấy nội dung từ EditorJS
        const output = await this.editor.save();
        const noidung = JSON.stringify(output);

        const formData = new FormData();
        formData.append("tieude", this.tieude);
        formData.append("slug", this.slug);
        formData.append("id_danh_muc_tin_tuc", Number(this.id_danh_muc_tin_tuc));
        formData.append("ngay_dang", this.ngay_dang);
        formData.append("noidung", noidung);
        formData.append("noi_bat", 0);
        formData.append("duyet_tin_tuc", 0);
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
.editor-box {
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  min-height: 120px;
  padding: 8px 12px;
  background: #fafbfc;
  margin-bottom: 12px;
}
</style>