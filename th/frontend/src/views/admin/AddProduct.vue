<template>
  <div class="add-product">
    <h2>Thêm sản phẩm mới</h2>
    <form @submit.prevent="addProduct">
      <div>
        <label for="name">Tên sản phẩm:</label>
        <input
          type="text"
          id="name"
          v-model="name"
          placeholder="Nhập tên sản phẩm"
          required
        />
      </div>

      <div>
        <label for="price">Giá sản phẩm:</label>
        <input
          type="number"
          id="price"
          v-model="price"
          placeholder="Nhập giá sản phẩm"
          required
        />
      </div>

      <div>
        <label for="category">Danh mục:</label>
       <select v-model="category_id" required>
  <option value="">Chọn danh mục</option>
  <option
    v-for="category in categories"
    :key="category.category_id"
    :value="category.category_id"
  >
    {{ category.ten_danh_muc }}
  </option>
</select>

      </div>

      <div>
        <label for="description">Mô tả:</label>
        <textarea
          id="description"
          v-model="description"
          placeholder="Nhập mô tả sản phẩm"
          required
        ></textarea>
      </div>

      <div>
        <label for="images">Chọn ảnh sản phẩm:</label>
        <input
          type="file"
          id="images"
          @change="handleImageUpload"
          accept="image/*"
          multiple
          required
        />
        <div v-if="imagePreviews.length > 0">
          <h4>Xem trước ảnh:</h4>
          <div v-for="(src, index) in imagePreviews" :key="index">
            <img :src="src" alt="Ảnh sản phẩm" style="width: 150px; height: auto; margin-right:10px;" />
          </div>
        </div>
      </div>

      <div>
        <button type="submit">Thêm sản phẩm</button>
      </div>
    </form>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      name: "",
      price: "",
      category_id: "",
      description: "",
      imageFiles: [],
      imagePreviews: [],
      categories: [],
    };
  },
  methods: {
    handleImageUpload(event) {
      this.imageFiles = Array.from(event.target.files);
      this.imagePreviews = this.imageFiles.map(file => URL.createObjectURL(file));
    },
    async getCategories() {
      try {
        const res = await axios.get("http://localhost:8000/api/categories", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("token")}`,
          },
        });
        this.categories = res.data;
      } catch (error) {
        alert("Không thể tải danh mục!");
      }
    },
    async addProduct() {
      try {
        const token = localStorage.getItem("token");
        if (!token) {
          alert("Bạn cần đăng nhập!");
          return;
        }

        const formData = new FormData();
        formData.append("ten_san_pham", this.name);
        formData.append("gia", this.price);
        formData.append("ten_danh_muc_id", this.category_id);
        formData.append("mo_ta", this.description);

        this.imageFiles.forEach(file => {
          formData.append("images[]", file);
        });

        const res = await axios.post("http://localhost:8000/api/products", formData, {
          headers: {
            Authorization: `Bearer ${token}`,
            // Không set Content-Type, axios tự set với FormData
          },
        });

        if (res.status === 201) {
          alert("Thêm sản phẩm thành công!");
          this.$router.push("/admin/products");
        }
      } catch (error) {
        alert("Có lỗi xảy ra khi thêm sản phẩm!");
      }
    },
  },
  mounted() {
    this.getCategories();
  },
};
</script>

<style scoped>
.add-product {
  background: #fff;
  padding: 20px;
  border-radius: 10px;
  max-width: 600px;
  margin: auto;
}
.add-product form div {
  margin-bottom: 1rem;
}
.add-product button {
  padding: 0.5rem 1rem;
  background-color: #28a745;
  color: white;
  border: none;
  cursor: pointer;
}
.add-product img {
  margin-top: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  margin-right: 10px;
}
</style>
