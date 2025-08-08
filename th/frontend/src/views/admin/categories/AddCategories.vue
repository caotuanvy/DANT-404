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
        // THAY ĐỔI: Tên biến từ parent_id thành danh_muc_cha_id
        danh_muc_cha_id: null,
        parentCategories: [],
      };
    },
    mounted() {
      this.fetchParentCategories();
    },
    methods: {
      async fetchParentCategories() {
        try {
          const token = localStorage.getItem("token");
          // THAY ĐỔI: API endpoint từ /api/categories thành /api/danh-muc-cha
          const res = await axios.get("http://localhost:8000/api/danh-muc-cha", {
            headers: { Authorization: `Bearer ${token}` },
          });
          // BỎ LỌC: Backend đã trả về đúng danh mục cha, không cần lọc !cat.parent_id nữa
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
      try {
        const token = localStorage.getItem("token");
        if (!token) {
          alert("Vui lòng đăng nhập trước!");
          return;
        }

        const formData = new FormData();
        formData.append("ten_danh_muc", this.category_name);
        formData.append("mo_ta", this.description || "");
        formData.append("danh_muc_cha_id", this.danh_muc_cha_id); // Đã có sẵn danh_muc_cha_id

        if (this.imageFile) {
          formData.append("image", this.imageFile);
          // Backend của bạn trong CategoryController đã tự tạo slug từ tên file ảnh,
          // nên bạn không cần gửi 'slug' riêng ở đây nếu nó chỉ dựa vào tên file.
          // Nếu bạn muốn slug từ tên danh mục khi không có ảnh, hãy xem xét logic đó.
          // formData.append("slug", this.imageFile.name.replace(/\.[^/.]+$/, ""));
        } else {
          // Nếu không có ảnh, bạn có thể tạo slug từ tên danh mục.
          // Đảm bảo backend cũng xử lý trường hợp này hoặc bạn gửi slug rỗng.
          // formData.append("slug", this.category_name.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, ''));
          formData.append("slug", ""); // Hoặc một giá trị slug mặc định nếu backend cần
        }

        const response = await axios.post(
          "http://localhost:8000/api/categories",
          formData,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              "Content-Type": "multipart/form-data",
            },
          }
        );

        if (response.status === 201 || response.status === 200) {
          alert("Danh mục đã được thêm thành công!");
          // Logic chuyển hướng tương tự như khi sửa danh mục con
          if (this.danh_muc_cha_id) {
            // Nếu là danh mục con, chuyển về trang con của danh mục cha vừa chọn
            this.$router.push(`/admin/categories/${this.danh_muc_cha_id}/children`);
          } else {
            // Nếu là danh mục cấp cao nhất (không có cha), chuyển về trang quản lý danh mục chính
            this.$router.push("/admin/categories");
          }
          // Tùy chọn: Reset form sau khi thêm thành công
          this.category_name = "";
          this.description = "";
          this.imageFile = null;
          this.previewImage = null;
          this.danh_muc_cha_id = null;
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
  </style>