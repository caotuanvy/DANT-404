<template>
  <div>
    <h2>Sửa danh mục</h2>
    <div v-if="message" :class="messageClass">
      {{ message }}
    </div>
    <form @submit.prevent="updateCategory" enctype="multipart/form-data">
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
        <label>Hình ảnh hiện tại:</label>
        <div v-if="currentImage">
          <img :src="currentImage" alt="Current" style="width:100px; margin-bottom:10px;" />
        </div>
      </div>
      <div>
        <label for="image">Chọn hình ảnh mới:</label>
        <input type="file" id="image" @change="onFileChange" accept="image/*" />
        <div v-if="previewImage">
          <img :src="previewImage" alt="Preview" style="width:100px; margin-top:10px;" />
        </div>
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
        <button type="submit">Cập nhật danh mục</button>
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
      currentImage: null,
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
    this.fetchCategory();
    this.fetchParentCategories();
  },
  methods: {
    async fetchCategory() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.get(
          `https://api.sieuthi404.io.vn/api/categories/${this.$route.params.id}`,
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );
        this.category_name = res.data.ten_danh_muc;
        this.description = res.data.mo_ta;
        this.currentImage = res.data.image_url;
        this.danh_muc_cha_id = res.data.danh_muc_cha_id || null;
      } catch (error) {
        console.error("Lỗi khi lấy danh mục:", error);
        // Hiển thị thông báo lỗi ngay trên trang
        this.message = "Không thể tải dữ liệu danh mục.";
        this.messageClass = "error-message";
      }
    },
    async fetchParentCategories() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.get(`https://api.sieuthi404.io.vn/api/danh-muc-cha`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
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
    async updateCategory() {
      this.message = ""; // Xóa thông báo cũ trước khi gửi request
      this.messageClass = "";

      try {
        const token = localStorage.getItem("token");
        const formData = new FormData();
        formData.append("ten_danh_muc", this.category_name);
        formData.append("mo_ta", this.description);
        formData.append("danh_muc_cha_id", this.danh_muc_cha_id);
        formData.append("_method", "PUT");

        if (this.imageFile) {
          formData.append("image", this.imageFile);
        }

        const res = await axios.post(
          `https://api.sieuthi404.io.vn/api/categories/${this.$route.params.id}`,
          formData,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              "Content-Type": "multipart/form-data",
            },
          }
        );
        if (res.status === 200) {
          // Gán thông báo thành công và class tương ứng
          this.message = "Cập nhật danh mục thành công! 🎉";
          this.messageClass = "success-message";
          
          // Sử dụng setTimeout để chuyển hướng sau khi thông báo hiện ra
          setTimeout(() => {
            if (this.danh_muc_cha_id) {
              this.$router.push(`/admin/categories/${this.danh_muc_cha_id}/children`);
            } else {
              this.$router.push("/admin/categories");
            }
          }, 1500); // Chờ 1.5 giây để người dùng đọc thông báo
        }
      } catch (error) {
        console.error("Lỗi khi cập nhật danh mục:", error);
        // Gán thông báo lỗi và class tương ứng
        this.message = error.response?.data?.message || "Có lỗi xảy ra khi cập nhật.";
        this.messageClass = "error-message";
      }
    },
  },
};
</script>

<style scoped>
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