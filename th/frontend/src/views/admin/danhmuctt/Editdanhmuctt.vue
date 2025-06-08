<template>
  <div>
    <h2>Sửa danh mục tin tức</h2>
    <form @submit.prevent="updateCategory">
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
      ten_danh_muc: "",
      mo_ta: "",
      hinh_anh: "",
      previewImage: "",
      file: null,
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
          `http://localhost:8000/api/danh-muc-tin-tuc/${this.$route.params.id}`,
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );
        this.ten_danh_muc = res.data.ten_danh_muc;
        this.mo_ta = res.data.mo_ta;
        this.hinh_anh = res.data.hinh_anh;
        if (this.hinh_anh) {
          this.previewImage = this.hinh_anh.startsWith('http')
            ? this.hinh_anh
            : `http://localhost:8000/storage/${this.hinh_anh}`;
        }
      } catch (error) {
        console.error("Lỗi khi lấy danh mục:", error);
        alert("Không thể tải dữ liệu danh mục.");
      }
    },
    onFileChange(e) {
      const file = e.target.files[0];
      if (file) {
        this.file = file;
        this.hinh_anh = file.name; // chỉ lưu tên file, nếu backend chỉ nhận tên file
        this.previewImage = URL.createObjectURL(file);
      }
    },
    async updateCategory() {
      try {
        const token = localStorage.getItem("token");
        // Nếu chỉ lưu tên file:
        const payload = {
          ten_danh_muc: this.ten_danh_muc,
          mo_ta: this.mo_ta,
          hinh_anh: this.hinh_anh,
        };
        const res = await axios.put(
          `http://localhost:8000/api/danh-muc-tin-tuc/${this.$route.params.id}`,
          payload,
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );

        if (res.status === 200) {
          alert("Cập nhật danh mục thành công!");
          this.$router.push("/admin/danhmuctintuc");
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