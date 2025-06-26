<template>
  <div>
    <h2>Thêm tin tức mới</h2>
    <form @submit.prevent="addTintuc">
      <div>
        <label for="tieude">Tiêu đề:</label>
        <input
          type="text"
          id="tieude"
          v-model="tieude"
          placeholder="Nhập tiêu đề tin tức"
          required
        />
      </div>
      <div>
        <label for="id_danh_muc_tin_tuc">Danh mục:</label>
        <select v-model="id_danh_muc_tin_tuc" required>
          <option value="">Chọn danh mục</option>
          <option
            v-for="dm in danhMucs"
            :key="dm.id_danh_muc_tin_tuc"
            :value="Number(dm.id_danh_muc_tin_tuc)"
          >
            {{ dm.ten_danh_muc }}
          </option>
        </select>
      </div>
      <div>
        <label for="ngay_dang">Ngày đăng:</label>
        <input
          type="date"
          id="ngay_dang"
          v-model="ngay_dang"
        />
      </div>
      <div>
        <label for="noidung">Nội dung:</label>
        <textarea
          id="noidung"
          v-model="noidung"
          placeholder="Nhập nội dung tin tức"
          required
        ></textarea>
      </div>
      <div>
        <button type="submit">Thêm tin tức</button>
      </div>
    </form>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      tieude: "",
      id_danh_muc_tin_tuc: null,
      ngay_dang: "",
      noidung: "",
      danhMucs: [],
    };
  },
  methods: {
    async getDanhMucs() {
      try {
        const res = await axios.get("http://localhost:8000/api/danh-muc-tin-tuc");
        this.danhMucs = res.data;
        console.log('danhMucs:', this.danhMucs);
      } catch (error) {
        alert("Không thể tải danh mục tin tức!");
      }
    },
    async addTintuc() {
      console.log('tieude:', this.tieude);
      console.log('noidung:', this.noidung);
      console.log('id_danh_muc_tin_tuc:', this.id_danh_muc_tin_tuc);
      try {
        if (!this.tieude || !this.noidung || !this.id_danh_muc_tin_tuc) {
          alert("Vui lòng nhập đầy đủ thông tin!");
          return;
        }
        const token = localStorage.getItem("token");
        if (!token) {
          alert("Bạn cần đăng nhập!");
          return;
        }

        const data = {
          tieude: this.tieude,
          id_danh_muc_tin_tuc: this.id_danh_muc_tin_tuc,
          ngay_dang: this.ngay_dang,
          noidung: this.noidung,
        };

        const res = await axios.post("http://localhost:8000/api/tintuc", data, {
          headers: {
            Authorization: `Bearer ${token}`,
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
  },
  mounted() {
    this.getDanhMucs();
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