<template>
    <div>
      <h2>Thêm danh mục mới</h2>
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
      };
    },
    methods: {
      async addCategory() {
        try {
          const token = localStorage.getItem("token");
          if (!token) {
            alert("Vui lòng đăng nhập trước!");
            return;
          }
  
          const response = await axios.post(
            "http://localhost:8000/api/categories",
            {
              category_name: this.category_name,
              description: this.description || "", 
            },
            {
              headers: {
                Authorization: `Bearer ${token}`,
              },
            }
          );
  
          if (response.status === 201 || response.status === 200) {
            alert("Danh mục đã được thêm thành công!");
            this.$router.push("/admin/category");
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
  
  button {
    padding: 0.5rem 1rem;
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
  }
  </style>
  