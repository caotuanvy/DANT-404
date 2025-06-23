<template>
  <div>
    <h2>Thêm danh mục tin tức</h2>
    <form @submit.prevent="addCategory" enctype="multipart/form-data">
      <div>
        <label for="ten_danh_muc">Tên danh mục:</label>
        <input
          type="text"
          id="ten_danh_muc"
          v-model="ten_danh_muc"
          placeholder="Nhập tên danh mục"
          required
        />
      </div>
      <div>
        <label for="mo_ta">Mô tả:</label>
        <textarea
          id="mo_ta"
          v-model="mo_ta"
          placeholder="Nhập mô tả danh mục"
        ></textarea>
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
      ten_danh_muc: "",
      mo_ta: "",
      file: null,
      previewImage: "",
    };
  },
  methods: {
    onFileChange(e) {
      const file = e.target.files[0];
      if (file) {
        this.file = file;
        this.previewImage = URL.createObjectURL(file);
      }
    },
    async addCategory() {
      try {
        const token = localStorage.getItem("token");
        if (!token) {
          alert("Vui lòng đăng nhập trước!");
          return;
        }

        const formData = new FormData();
        formData.append("ten_danh_muc", this.ten_danh_muc);
        formData.append("mo_ta", this.mo_ta);
        if (this.file) {
          formData.append("hinh_anh", this.file);
        }

        // Không cần gửi ngày tạo, backend sẽ tự động thêm ngày tạo
        const response = await axios.post(
          "http://localhost:8000/api/danh-muc-tin-tuc",
          formData,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              "Content-Type": "multipart/form-data",
            },
          }
        );

        if (response.status === 201 || response.status === 200) {
          alert("Danh mục tin tức đã được thêm thành công!");
          this.$router.push("/admin/danhmuctintuc");
        }
      } catch (error) {
        console.error("Lỗi khi thêm danh mục:", error);
        if (error.response) {
          alert(`Lỗi: ${error.response.data.message || "Có lỗi xảy ra!"}`);
        } else {
          alert("Có lỗi xảy ra, vui lòng thử lại!");
        }
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

h2 {
  text-align: center;
}

button {
  padding: 0.5rem 1rem;
  background-color: #007bff;
  color: white;
  border: none;
  cursor: pointer;
}
</style>