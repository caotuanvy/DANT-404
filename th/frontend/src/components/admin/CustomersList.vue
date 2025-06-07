<template>
  <div class="container mt-4">
    <h1 class="h4 fw-bold mb-4">Danh sách người dùng</h1>

    <div v-if="loading" class="text-muted">Đang tải dữ liệu...</div>
    <div v-else-if="users.length === 0" class="text-muted">Không có người dùng nào.</div>

    <table v-else class="table table-bordered table-hover">
      <thead class="table-light">
        <tr>
          <th>Mã người dùng</th>
          <th>Họ tên</th>
          <th>Email</th>
          <th>SĐT</th>
          <th>Vai trò</th>
          <th>Vô hiệu hóa</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.nguoi_dung_id">
          <td>{{ user.nguoi_dung_id }}</td>
          <td>{{ user.ho_ten }}</td>
          <td>{{ user.email }}</td>
          <td>{{ user.sdt }}</td>
          <td>
            <select v-model="user.vai_tro_id" @change="updateRole(user)" class="form-select form-select-sm">
              <option :value="1">Admin</option>
              <option :value="2">User</option>
            </select>
          </td>
          <td class="text-center">
            <div class="d-flex justify-content-center align-items-center">
              <input type="checkbox" class="custom-switch" v-model="user.trang_thai_checked"
                @change="toggleUserStatus(user, user.trang_thai_checked)">
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
<!-- <p>an đẹp trai</p> -->


<script>
import axios from 'axios';

export default {
  name: 'CustomersList',
  data() {
    return {
      users: [],
      loading: true,
    };
  },
  mounted() {
    this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      try {
        const response = await axios.get('http://localhost:8000/api/users');
        this.users = response.data;
      } catch (error) {
        console.error('Lỗi khi tải danh sách người dùng:', error);
      } finally {
        this.loading = false;
      }
    },
    async deleteUser(id) {
      if (confirm('Bạn có chắc chắn muốn xóa tài khoản này?')) {
        try {
          await axios.delete(`http://localhost:8000/api/users/${id}`);
          this.users = this.users.filter(user => user.nguoi_dung_id !== id);
        } catch (error) {
          console.error('Lỗi khi xóa tài khoản:', error);
        }
      }
    }
  }
}
</script>

<style scoped>
.custom-switch {
  width: 50px;
  height: 25px;
  background-color: #ccc;
  border-radius: 25px;
  position: relative;
  appearance: none;
  -webkit-appearance: none;
  outline: none;
  cursor: pointer;
  transition: background-color 0.2s ease-in-out;
}

.custom-switch::before {
  content: "";
  position: absolute;
  top: 2px;
  left: 2px;
  width: 21px;
  height: 21px;
  background-color: white;
  border-radius: 50%;
  transition: transform 0.2s ease-in-out;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.custom-switch:checked {
  background-color: green;
}

.custom-switch:checked::before {
  transform: translateX(25px);
}
</style>
