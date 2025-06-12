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
          <th>Địa chỉ</th>
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
              <option :value="0">User</option>
            </select>
          </td>
          <td>{{ user.dia_chi || 'Chưa có địa chỉ' }}</td> 
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
        this.users = response.data.map(user => ({
          ...user,
          // `trang_thai` sẽ được trả về từ API. Nếu `trang_thai` là 1, `trang_thai_checked` là true.
          trang_thai_checked: user.trang_thai === 1 
        }));
      } catch (error) {
        console.error('Lỗi khi tải danh sách người dùng:', error);
      } finally {
        this.loading = false;
      }
    },

    async updateRole(user) {
      try {
        await axios.put(`http://localhost:8000/api/users/${user.nguoi_dung_id}`, {
          vai_tro_id: user.vai_tro_id,
          trang_thai: user.trang_thai_checked ? 1 : 0 // Gửi trạng thái hiện tại của checkbox
        });
        // Bạn có thể thêm một thông báo thành công ở đây (ví dụ: dùng this.$toast)
        this.$toast && this.$toast.success('Cập nhật vai trò thành công'); 
      } catch (error) {
        console.error('Lỗi khi cập nhật vai trò:', error);
        alert('Cập nhật vai trò thất bại');
      }
    },

    async toggleUserStatus(user, checked) {
      try {
        await axios.put(`http://localhost:8000/api/users/${user.nguoi_dung_id}`, {
          vai_tro_id: user.vai_tro_id, // Gửi vai trò hiện tại
          trang_thai: checked ? 1 : 0
        });
        user.trang_thai_checked = checked; // Cập nhật trạng thái trong dữ liệu cục bộ nếu thành công
        this.$toast && this.$toast.success('Cập nhật trạng thái thành công');
      } catch (error) {
        console.error('Lỗi khi cập nhật trạng thái:', error);
        alert('Cập nhật trạng thái thất bại');
        // Nếu lỗi thì revert checkbox về trạng thái trước đó
        user.trang_thai_checked = !checked;
      }
    },

    // Hàm deleteUser nếu bạn muốn thêm chức năng xóa người dùng
    async deleteUser(id) {
      if (confirm('Bạn có chắc chắn muốn xóa tài khoản này?')) {
        try {
          await axios.delete(`http://localhost:8000/api/users/${id}`);
          this.users = this.users.filter(user => user.nguoi_dung_id !== id);
          this.$toast && this.$toast.success('Xóa người dùng thành công');
        } catch (error) {
          console.error('Lỗi khi xóa tài khoản:', error);
          alert('Xóa người dùng thất bại');
        }
      }
    }
  }
}
</script>

<style scoped>
.container{
  padding: 20px;
}
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