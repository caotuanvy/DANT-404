<template>
  <div>
    <h2>Sửa danh mục</h2>
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
      // THAY ĐỔI: Tên biến từ parent_id thành danh_muc_cha_id
      danh_muc_cha_id: null,
      parentCategories: [],
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
          `http://localhost:8000/api/categories/${this.$route.params.id}`,
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );
        this.category_name = res.data.ten_danh_muc;
        this.description = res.data.mo_ta;
        this.currentImage = res.data.image_url;
        // THAY ĐỔI: Lấy giá trị từ danh_muc_cha_id thay vì parent_id
        this.danh_muc_cha_id = res.data.danh_muc_cha_id || null;
      } catch (error) {
        console.error("Lỗi khi lấy danh mục:", error);
        alert("Không thể tải dữ liệu danh mục.");
      }
    },
    async fetchParentCategories() {
      try {
        const token = localStorage.getItem("token");
        // THAY ĐỔI: API endpoint từ /api/categories thành /api/danh-muc-cha
        const res = await axios.get(`http://localhost:8000/api/danh-muc-cha`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        this.parentCategories = res.data; // Không cần lọc nếu backend trả về đúng DanhMucCha
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
      try {
        const token = localStorage.getItem("token");
        const formData = new FormData();
        formData.append("ten_danh_muc", this.category_name);
        formData.append("mo_ta", this.description);
        // THAY ĐỔI: Tên trường gửi đi từ parent_id thành danh_muc_cha_id
        formData.append("danh_muc_cha_id", this.danh_muc_cha_id);

        // RẤT QUAN TRỌNG CHO PHƯƠNG THỨC PUT VỚI FORM-DATA
        // Thêm trường _method=PUT để Laravel nhận diện đây là PUT request khi dùng FormData
        formData.append("_method", "PUT");

        if (this.imageFile) {
          formData.append("image", this.imageFile);
        }

        const res = await axios.post( // Vẫn dùng axios.post và thêm _method=PUT
          `http://localhost:8000/api/categories/${this.$route.params.id}`, // Bỏ `?_method=PUT` ở đây, vì đã thêm vào formData
          formData,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              "Content-Type": "multipart/form-data",
            },
          }
        );
        if (res.status === 200) {
          alert("Cập nhật danh mục thành công!");
          this.$router.push("/admin/category");
        }
      } catch (error) {
        console.error("Lỗi khi cập nhật danh mục:", error);
        alert("Có lỗi xảy ra khi cập nhật.");
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
</style>