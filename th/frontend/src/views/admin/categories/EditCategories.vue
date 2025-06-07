<template>
    <div>
      <h2>Sửa danh mục</h2>
      <form @submit.prevent="updateCategory">
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
      };
    },
    mounted() {
      this.fetchCategory();
    },
    methods: {
      async fetchCategory() {
        try {
          const token = localStorage.getItem("token");
          const res = await axios.get(
            `http://localhost:8000/api/categories/${this.$route.params.category_id}`,
            {
              headers: {
                Authorization: `Bearer ${token}`,
              },
            }
          );
          this.category_name = res.data.category_name;
          this.description = res.data.description;
        } catch (error) {
          console.error("Lỗi khi lấy danh mục:", error);
          alert("Không thể tải dữ liệu danh mục.");
        }
      },
      async updateCategory() {
        try {
          const token = localStorage.getItem("token");
          const res = await axios.put(
            `http://localhost:8000/api/categories/${this.$route.params.category_id}`,
            {
              category_name: this.category_name,
              description: this.description,
            },
            {
              headers: {
                Authorization: `Bearer ${token}`,
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
  