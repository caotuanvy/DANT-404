<template>
  <div class="container mt-4">
    <h1 class="h4 fw-bold mb-4">Danh sách người dùng</h1>

    <!-- KHU VỰC TÌM KIẾM VÀ LỌC -->
    <div class="row mb-3 g-2">
      <div class="col-md-6">
        <!-- Bọc input trong div để thêm nút xóa -->
        <div class="search-input-wrapper">
          <input
            type="text"
            class="form-control"
            placeholder="Tìm kiếm theo họ tên, email hoặc SĐT..."
            v-model="searchQuery"
          />
          <!-- Nút xóa nội dung tìm kiếm -->
          <button
            v-if="searchQuery"
            class="clear-search-btn"
            @click="clearSearch"
            aria-label="Xóa tìm kiếm"
          >
            &times; <!-- Dấu 'x' -->
          </button>
        </div>
      </div>
      <div class="col-md-3">
        <select class="form-select" v-model="roleFilter">
          <option value="">Tất cả vai trò</option>
          <option value="1">Admin</option>
          <option value="0">User</option>
        </select>
      </div>
      <div class="col-md-3">
        <select class="form-select" v-model="statusFilter">
          <option value="">Tất cả trạng thái</option>
          <option value="0">Hoạt động</option>
          <option value="1">Vô hiệu hóa</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="text-muted">Đang tải dữ liệu...</div>
    <div v-else-if="filteredUsers.length === 0 && (searchQuery === '' && roleFilter === '' && statusFilter === '')" class="text-muted">
      Không có người dùng nào.
    </div>
    <div v-else-if="filteredUsers.length === 0 && (searchQuery !== '' || roleFilter !== '' || statusFilter !== '')" class="text-muted">
      Không tìm thấy người dùng nào phù hợp với điều kiện lọc.
    </div>


    <table v-else class="table table-bordered table-hover">
      <thead class="table-light">
        <tr>
          <th>Mã người dùng</th>
          <th>Họ tên</th>
          <th>Email</th>
          <th>SĐT</th>
          <th>Vai trò</th>
          <th>Vô hiệu hóa</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in filteredUsers" :key="user.nguoi_dung_id">
          <td>{{ user.nguoi_dung_id }}</td>
          <td>{{ user.ho_ten }}</td>
          <td>{{ user.email }}</td>
          <td>{{ user.sdt || '(Chưa có SĐT)' }}</td>
          <!-- THAY ĐỔI TẠI ĐÂY: Áp dụng class động trực tiếp vào <select> -->
          <td>
            <select
              v-model="user.vai_tro_id"
              @change="updateRole(user)"
              class="form-select form-select-sm"
              :class="getRoleClass(user.vai_tro_id)"
            >
              <option :value="1">Admin</option>
              <option :value="0">User</option>
            </select>
          </td>
          <td class="text-center">
            <div class="d-flex justify-content-center align-items-center">
              <input type="checkbox" class="custom-switch" v-model="user.trang_thai_checked"
                     @change="toggleUserStatus(user, user.trang_thai_checked)">
            </div>
          </td>
          <td class="d-flex justify-content-center align-items-center h-100">
            <button class="btn btn-sm btn-custom-detail" @click="showUserDetails(user)">Xem chi tiết</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal Xem chi tiết người dùng -->
    <div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content custom-modal-content">
          <div class="modal-header custom-modal-header">
            <h5 class="modal-title" id="userDetailsModalLabel">Thông tin chi tiết người dùng</h5>
          </div>
          <div class="modal-body custom-modal-body" v-if="selectedUser">
            <p><strong>Mã người dùng:</strong> {{ selectedUser.nguoi_dung_id }}</p>
            <p><strong>Họ tên:</strong> {{ selectedUser.ho_ten }}</p>
            <p><strong>Email:</strong> {{ selectedUser.email }}</p>
            <p><strong>SĐT:</strong> {{ selectedUser.sdt || 'Chưa có SĐT' }}</p>
            <p><strong>Vai trò:</strong> {{ selectedUser.vai_tro_id === 1 ? 'Admin' : 'User' }}</p>
            <p><strong>Địa chỉ:</strong> {{ selectedUser.dia_chi || 'Chưa có địa chỉ' }}</p>
            <p><strong>Trạng thái:</strong> {{ selectedUser.trang_thai === 1 ? 'Vô hiệu hóa' : 'Hoạt động' }}</p>
          </div>
          <div class="modal-footer custom-modal-footer">
            <button type="button" class="btn btn-secondary custom-close-btn" data-bs-dismiss="modal">Đóng</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import axios from 'axios';
import { Modal } from 'bootstrap/dist/js/bootstrap.bundle.min.js';

export default {
  name: 'CustomersList',
  data() {
    return {
      users: [],
      loading: true,
      selectedUser: null,
      searchQuery: '',
      roleFilter: '',
      statusFilter: '',
    };
  },
  computed: {
    filteredUsers() {
      let currentUsers = this.users;

      // 1. Lọc theo họ tên, email HOẶC SĐT (searchQuery)
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        currentUsers = currentUsers.filter(user => {
          const userName = user.ho_ten ? String(user.ho_ten).toLowerCase() : '';
          const userEmail = user.email ? String(user.email).toLowerCase() : '';
          const userSdt = user.sdt ? String(user.sdt).toLowerCase() : '';
          return userName.includes(query) || userEmail.includes(query) || userSdt.includes(query);
        });
      }

      // 2. Lọc theo vai trò (roleFilter)
      if (this.roleFilter !== '') {
        const roleId = parseInt(this.roleFilter);
        currentUsers = currentUsers.filter(user => user.vai_tro_id === roleId);
      }

      // 3. Lọc theo trạng thái (statusFilter)
      if (this.statusFilter !== '') {
        const statusValue = parseInt(this.statusFilter);
        currentUsers = currentUsers.filter(user => user.trang_thai === statusValue);
      }

      return currentUsers;
    }
  },
  mounted() {
    this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('http://localhost:8000/api/users', {
          headers: {
            Authorization: `Bearer ${token}`
          }
        });

        this.users = response.data.map(user => ({
          ...user,
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
          trang_thai: user.trang_thai_checked ? 1 : 0
        });
        this.$toast && this.$toast.success('Cập nhật vai trò thành công');
      } catch (error) {
        console.error('Lỗi khi cập nhật vai trò:', error);
        alert('Cập nhật vai trò thất bại');
      }
    },

    async toggleUserStatus(user, checked) {
      try {
        await axios.put(`http://localhost:8000/api/users/${user.nguoi_dung_id}`, {
          vai_tro_id: user.vai_tro_id,
          trang_thai: checked ? 1 : 0
        });
        user.trang_thai_checked = checked;
        this.$toast && this.$toast.success('Cập nhật trạng thái thành công');
      } catch (error) {
        console.error('Lỗi khi cập nhật trạng thái:', error);
        alert('Cập nhật trạng thái thất bại');
        user.trang_thai_checked = !checked;
      }
    },

    showUserDetails(user) {
      this.selectedUser = user;
      const userDetailsModal = new Modal(document.getElementById('userDetailsModal'));
      userDetailsModal.show();
    },

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
    },
    clearSearch() {
      this.searchQuery = '';
    },
    getRoleClass(roleId) {
      if (roleId === 1) {
        return 'role-admin-select'; // Class mới cho select Admin
      } else if (roleId === 0) {
        return 'role-user-select'; // Class mới cho select User
      }
      return '';
    }
  }
}
</script>

<style scoped>
.container {
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

/* CSS TÙY CHỈNH CHO MODAL */
.custom-modal-content {
  border-radius: 15px; /* Bo tròn góc */
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); /* Đổ bóng */
  overflow: hidden; /* Đảm bảo nội dung không tràn ra ngoài góc bo tròn */
}

.custom-modal-header {
  background-color: #33ccff; /* Màu nền xanh dương */
  color: white; /* Chữ trắng */
  border-bottom: none; /* Bỏ đường viền dưới */
  padding: 1.5rem; /* Tăng padding */
  border-top-left-radius: 15px; /* Bo tròn góc trên bên trái */
  border-top-right-radius: 15px; /* Bo tròn góc trên bên phải */
}

.custom-modal-header .btn-close {
  filter: invert(1) grayscale(100%) brightness(200%); /* Biến icon close thành màu trắng */
}

.custom-modal-body {
  padding: 2rem; /* Tăng padding */
  line-height: 1.8; /* Tăng khoảng cách dòng */
  font-size: 1.05rem; /* Tăng kích thước chữ một chút */
}

.custom-modal-body p {
  margin-bottom: 0.8rem; /* Khoảng cách giữa các dòng thông tin */
}

.custom-modal-body strong {
  color: #333; /* Màu đậm hơn cho label */
}

.custom-modal-footer {
  background-color: #f8f9fa; /* Màu nền xám nhạt */
  border-top: none; /* Bỏ đường viền trên */
  padding: 1.2rem 2rem; /* Padding đồng bộ */
  border-bottom-left-radius: 15px; /* Bo tròn góc dưới bên trái */
  border-bottom-right-radius: 15px; /* Bo tròn góc dưới bên phải */
}

.custom-close-btn {
  background-color: #6c757d; /* Màu xám đậm hơn cho nút đóng */
  border-color: #6c757d;
  color: white;
  padding: 0.5rem 1.5rem; /* Tăng kích thước nút */
  border-radius: 8px; /* Bo tròn nút */
  transition: background-color 0.2s ease-in-out, transform 0.1s ease-in-out;
}

.custom-close-btn:hover {
  background-color: #5a6268;
  border-color: #545b62;
  transform: translateY(-1px); /* Hiệu ứng nhấc nhẹ khi hover */
}

/* CSS TÙY CHỈNH CHO NÚT XEM CHI TIẾT */
.btn-custom-detail {
  background-color: #33ccff; /* Màu nền tùy chỉnh */
  border-color: #33ccff; /* Màu viền tùy chỉnh */
  color: white; /* Màu chữ */
  transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out, transform 0.1s ease-in-out;
}

.btn-custom-detail:hover {
  background-color: #00b3e6; /* Màu đậm hơn khi hover */
  border-color: #00b3e6;
  transform: translateY(-1px); /* Hiệu ứng nhấc nhẹ khi hover */
}

/* CSS CHO Ô TÌM KIẾM CÓ NÚT XÓA */
.search-input-wrapper {
  position: relative;
  width: 100%;
}

.search-input-wrapper .form-control {
  padding-right: 2.5rem; /* Tạo khoảng trống cho nút xóa */
}

.clear-search-btn {
  position: absolute;
  right: 0.5rem; /* Cách lề phải */
  top: 50%;
  transform: translateY(-50%); /* Căn giữa theo chiều dọc */
  background: none;
  border: none;
  color: #999; /* Màu xám nhạt */
  font-size: 1.5rem; /* Kích thước dấu 'x' */
  cursor: pointer;
  padding: 0;
  line-height: 1; /* Đảm bảo dấu 'x' không bị lệch */
  z-index: 2; /* Đảm bảo nút nằm trên input */
}

.clear-search-btn:hover {
  color: #666; /* Màu đậm hơn khi hover */
}

/* CSS MỚI CHO Ô SELECT VAI TRÒ */
.role-admin-select {
  background-color: #00ffff !important; /* Màu vàng Gold */
  color: #333 !important; /* Đảm bảo chữ dễ đọc */
  border-color: #00ffff !important; /* Đồng bộ màu viền */
}

.role-user-select {
  background-color: #f8f9fa !important; /* Màu xám nhạt của Bootstrap */
  color: #333 !important;
  border-color: #f8f9fa !important;
}
</style>
