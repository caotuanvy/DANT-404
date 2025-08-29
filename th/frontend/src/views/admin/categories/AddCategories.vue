<template>
  <div>
    <h2>Thêm danh mục mới</h2>
    <div v-if="message" :class="messageClass">
      {{ message }}
    </div>
    <form @submit.prevent="addCategory">
      <div>
        <label for="name">Tên danh mục:</label>
        <input
          type="text"
          id="name"
          v-model="category_name"
          placeholder="Nhập tên danh mục"
          required
        />
      </div>
      <div>
        <label for="description">Mô tả:</label>
        <textarea
          id="description"
          v-model="description"
          placeholder="Nhập mô tả danh mục"
          required
        ></textarea>
      </div>
      <div>
        <label for="parent">Danh mục cha:</label>
        <select v-model="danh_muc_cha_id" id="parent">
          <option :value="null">-- Không có --</option>
          <option v-for="cat in parentCategories" :key="cat.id" :value="cat.id">
            {{ cat.ten_danh_muc }}
          </option>
        </select>
      </div>
      <div>
        <label for="image">Hình ảnh:</label>
        <input type="file" id="image" @change="onFileChange" accept="image/*" />
        <div v-if="previewImage">
          <img :src="previewImage" alt="Preview" style="width: 100px; margin-top: 10px;" />
        </div>
      </div>
      <div>
        <button type="submit">Thêm danh mục</button>
      </div>
    </form>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      category_name: "",
      description: "",
      imageFile: null,
      previewImage: null,
      danh_muc_cha_id: null,
      parentCategories: [],
      // Thêm các biến mới để quản lý thông báo
      message: "",
      messageClass: "",
    };
  },
  mounted() {
    this.fetchParentCategories();
  },
  methods: {
    async fetchParentCategories() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.get("https://api.sieuthi404.io.vn/api/danh-muc-cha", {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.parentCategories = res.data;
      } catch (error) {
        console.error("Lỗi khi lấy danh mục cha:", error);
      }
    },
    onFileChange(e) {
      const file = e.target.files[0];
      if (file) {
        this.imageFile = file;
        this.previewImage = URL.createObjectURL(file);
      }
    },
    async addCategory() {
      this.message = ""; // Xóa thông báo cũ
      this.messageClass = "";

      try {
        const token = localStorage.getItem("token");
        if (!token) {
          this.message = "Vui lòng đăng nhập trước!";
          this.messageClass = "error-message";
          return;
        }

        const formData = new FormData();
        formData.append("ten_danh_muc", this.category_name);
        formData.append("mo_ta", this.description || "");
        formData.append("danh_muc_cha_id", this.danh_muc_cha_id);

        if (this.imageFile) {
          formData.append("image", this.imageFile);
          formData.append("slug", this.category_name.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, ''));
        } else {
           formData.append("slug", this.category_name.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, ''));
        }

        const response = await axios.post(
          "https://api.sieuthi404.io.vn/api/categories",
          formData,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              "Content-Type": "multipart/form-data",
            },
          }
        );

        if (response.status === 201 || response.status === 200) {
          this.message = "Danh mục đã được thêm thành công! 🎉";
          this.messageClass = "success-message";
          
          setTimeout(() => {
            if (this.danh_muc_cha_id) {
              this.$router.push(`/admin/categories/${this.danh_muc_cha_id}/children`);
            } else {
              this.$router.push("/admin/categories");
            }
            // Tùy chọn: Reset form sau khi chuyển hướng
            this.category_name = "";
            this.description = "";
            this.imageFile = null;
            this.previewImage = null;
            this.danh_muc_cha_id = null;
          }, 1500); // Chờ 1.5 giây
        }
      } catch (error) {
        console.error("Lỗi khi thêm danh mục:", error);
        this.message = error.response?.data?.message || "Có lỗi xảy ra, vui lòng thử lại!";
        this.messageClass = "error-message";
      }
    },
  },
};
</script>

<style scoped>
/* Giữ nguyên style */
form {
  max-width: 500px;
  margin: 0 auto;
}

div {
  margin-bottom: 1rem;
}

button {
  padding: 0.5rem 1rem;
  background-color: #007bff;
  color: white;
  border: none;
  cursor: pointer;
}

/* Thêm CSS cho thông báo */
.success-message {
  padding: 1rem;
  margin-bottom: 1rem;
  border-radius: 5px;
  background-color: #d4edda; /* Màu nền xanh lá nhạt */
  color: #155724; /* Màu chữ xanh lá đậm */
  border: 1px solid #c3e6cb;
  font-weight: bold;
  text-align: center;
}

.error-message {
  padding: 1rem;
  margin-bottom: 1rem;
  border-radius: 5px;
  background-color: #f8d7da; /* Màu nền đỏ nhạt */
  color: #721c24; /* Màu chữ đỏ đậm */
  border: 1px solid #f5c6cb;
  font-weight: bold;
  text-align: center;
}
</style>