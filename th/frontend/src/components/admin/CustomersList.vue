<template>
  <div class="container mt-4">
    <h1 class="h4 fw-bold mb-4">Danh sách người dùng</h1>

    <div class="row mb-3 g-2">
      <div class="col-md-6">
        <div class="search-input-wrapper">
          <input
            type="text"
            class="form-control"
            placeholder="Tìm kiếm theo họ tên, email hoặc SĐT..."
            v-model="searchQuery"
          />
          <button
            v-if="searchQuery"
            class="clear-search-btn"
            @click="clearSearch"
            aria-label="Xóa tìm kiếm"
          >
            &times;
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
    <div v-else-if="filteredAndSearchedUsers.length === 0 && (searchQuery === '' && roleFilter === '' && statusFilter === '')" class="text-muted">
      Không có người dùng nào.
    </div>
    <div v-else-if="filteredAndSearchedUsers.length === 0 && (searchQuery !== '' || roleFilter !== '' || statusFilter !== '')" class="text-muted">
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
        <tr v-for="user in paginatedUsers" :key="user.nguoi_dung_id">
          <td>{{ user.nguoi_dung_id }}</td>
          <td>{{ user.ho_ten }}</td>
          <td>{{ user.email }}</td>
          <td>{{ user.sdt || '(Chưa có SĐT)' }}</td>
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

    <nav v-if="totalPages > 1" aria-label="Page navigation" class="mt-4">
      <ul class="pagination justify-content-center custom-pagination">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <a class="page-link" href="#" @click.prevent="prevPage">Trước</a>
        </li>
        <li class="page-item"
            v-for="page in totalPages"
            :key="page"
            :class="{ active: currentPage === page }"
        >
          <a class="page-link" href="#" @click.prevent="goToPage(page)">{{ page }}</a>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <a class="page-link" href="#" @click.prevent="nextPage">Sau</a>
        </li>
      </ul>
    </nav>

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
      // --- PHÂN TRANG ---
      currentPage: 1, // Trang hiện tại
      itemsPerPage: 10, // Số người dùng hiển thị trên mỗi trang
    };
  },
  computed: {
    filteredAndSearchedUsers() {
      let currentUsers = this.users;

      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        currentUsers = currentUsers.filter(user => {
          const userName = user.ho_ten ? String(user.ho_ten).toLowerCase() : '';
          const userEmail = user.email ? String(user.email).toLowerCase() : '';
          const userSdt = user.sdt ? String(user.sdt).toLowerCase() : '';
          return userName.includes(query) || userEmail.includes(query) || userSdt.includes(query);
        });
      }

      if (this.roleFilter !== '') {
        const roleId = parseInt(this.roleFilter);
        currentUsers = currentUsers.filter(user => user.vai_tro_id === roleId);
      }

      if (this.statusFilter !== '') {
        const statusValue = parseInt(this.statusFilter);
        currentUsers = currentUsers.filter(user => user.trang_thai === statusValue);
      }

      return currentUsers;
    },
    // Tổng số trang cần thiết
    totalPages() {
      return Math.ceil(this.filteredAndSearchedUsers.length / this.itemsPerPage);
    },
    // Người dùng cho trang hiện tại (sau khi lọc, tìm kiếm và phân trang)
    paginatedUsers() {
      const startIndex = (this.currentPage - 1) * this.itemsPerPage;
      const endIndex = startIndex + this.itemsPerPage;
      return this.filteredAndSearchedUsers.slice(startIndex, endIndex);
    }
  },
  watch: {
    // Reset về trang 1 khi thay đổi bộ lọc hoặc tìm kiếm
    searchQuery() {
      this.currentPage = 1;
    },
    roleFilter() {
      this.currentPage = 1;
    },
    statusFilter() {
      this.currentPage = 1;
    },
  },
  mounted() {
    this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('https://api.sieuthi404.io.vn/api/users', {
          headers: {
            Authorization: `Bearer ${token}`
          }
        });

        this.users = response.data.map(user => ({
          ...user,
          trang_thai_checked: user.trang_thai === 1 // Ánh xạ trạng thái số sang boolean cho checkbox
        }));
      } catch (error) {
        console.error('Lỗi khi tải danh sách người dùng:', error);
        // Có thể thêm toast/alert thông báo lỗi cho người dùng
        this.$toast && this.$toast.error('Không thể tải danh sách người dùng.');
      } finally {
        this.loading = false;
      }
    },

    async updateRole(user) {
      try {
        await axios.put(`https://api.sieuthi404.io.vn/api/users/${user.nguoi_dung_id}`, {
          vai_tro_id: user.vai_tro_id,
          trang_thai: user.trang_thai_checked ? 1 : 0 // Gửi trạng thái hiện tại của checkbox
        });
        this.$toast && this.$toast.success('Cập nhật vai trò thành công');
      } catch (error) {
        console.error('Lỗi khi cập nhật vai trò:', error);
        this.$toast && this.$toast.error('Cập nhật vai trò thất bại.');
        this.fetchUsers(); // Tải lại dữ liệu để đồng bộ trạng thái nếu có lỗi
      }
    },

    async toggleUserStatus(user, checked) {
      try {
        user.trang_thai = checked ? 1 : 0; // Cập nhật trạng thái trong model trước khi gửi
        await axios.put(`https://api.sieuthi404.io.vn/api/users/${user.nguoi_dung_id}`, {
          vai_tro_id: user.vai_tro_id, // Gửi cả vai trò hiện tại
          trang_thai: user.trang_thai
        });
        this.$toast && this.$toast.success('Cập nhật trạng thái thành công');
      } catch (error) {
        console.error('Lỗi khi cập nhật trạng thái:', error);
        this.$toast && this.$toast.error('Cập nhật trạng thái thất bại.');
        user.trang_thai_checked = !checked; // Hoàn tác trạng thái checkbox nếu lỗi
        user.trang_thai = !user.trang_thai; // Hoàn tác trạng thái trong model
      }
    },

    showUserDetails(user) {
      this.selectedUser = user;
      const userDetailsModal = new Modal(document.getElementById('userDetailsModal'));
      userDetailsModal.show();
    },

    clearSearch() {
      this.searchQuery = '';
    },
    // Trả về class CSS dựa trên vai trò để tô màu ô select
    getRoleClass(roleId) {
      if (roleId === 1) {
        return 'role-admin-select';
      } else if (roleId === 0) {
        return 'role-user-select';
      }
      return '';
    },
    // --- PHƯƠNG THỨC ĐIỀU KHIỂN PHÂN TRANG ---
    goToPage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
      }
    },
    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
      }
    }
  }
}
</script>

<style scoped>
/* --- STYLES TỔNG THỂ --- */
.container {
    padding: 2rem;
    max-width: 1200px;
    margin: auto;
}

h1 {
    color: #2c3e50; /* Màu chữ tiêu đề đậm hơn */
    margin-bottom: 1.5rem;
    font-size: 1.75rem; /* Kích thước tiêu đề hợp lý */
}

/* --- KHU VỰC TÌM KIẾM VÀ LỌC --- */
.row.mb-3.g-2 {
    align-items: center;
}

.form-control,
.form-select {
    border-radius: 0.5rem; /* Bo góc nhẹ cho input và select */
    border: 1px solid #ced4da;
    padding: 0.75rem 1rem;
    box-shadow: none; /* Bỏ đổ bóng mặc định */
    transition: all 0.3s ease;
}

.form-control:focus,
.form-select:focus {
    border-color: #33ccff; /* Màu xanh khi focus */
    box-shadow: 0 0 0 0.25rem rgba(51, 204, 255, 0.25); /* Đổ bóng nhẹ khi focus */
    outline: none;
}

/* CSS CHO Ô TÌM KIẾM CÓ NÚT XÓA */
.search-input-wrapper {
    position: relative;
}

.search-input-wrapper .form-control {
    padding-right: 2.8rem; /* Khoảng trống cho nút xóa */
}

.clear-search-btn {
    position: absolute;
    right: 0.75rem; /* Cách lề phải */
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #a0a0a0; /* Màu xám nhẹ hơn */
    font-size: 1.6rem; /* Kích thước dấu 'x' */
    cursor: pointer;
    padding: 0;
    line-height: 1;
    z-index: 2;
    transition: color 0.2s ease;
}

.clear-search-btn:hover {
    color: #666;
}

/* --- TRẠNG THÁI TẢI & KHÔNG CÓ DỮ LIỆU --- */
.text-muted {
    text-align: center;
    padding: 2rem 0;
    font-size: 1.1rem;
    color: #6c757d !important;
}

/* --- BẢNG DỮ LIỆU --- */
.table {
    margin-top: 1.5rem;
    border-collapse: separate; /* Để border-radius hoạt động */
    border-spacing: 0; /* Loại bỏ khoảng cách giữa các ô */
    border-radius: 0.75rem; /* Bo góc bảng */
    overflow: hidden; /* Đảm bảo nội dung không tràn ra */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); /* Đổ bóng nhẹ cho bảng */
}

.table thead th {
    background-color: #e9ecef; /* Nền header nhẹ hơn */
    color: #495057; /* Màu chữ header */
    font-weight: 600;
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 2px solid #dee2e6; /* Đường kẻ dưới header */
    text-align: center; /* Căn giữa nội dung header */
}

.table tbody tr {
    transition: background-color 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f8f9fa; /* Nền nhẹ khi hover */
}

.table tbody td {
    padding: 1rem;
    vertical-align: middle;
    border-top: 1px solid #e9ecef; /* Đường kẻ giữa các hàng */
    color: #343a40;
    text-align: center; /* Căn giữa nội dung các ô */
}

.table tbody td:first-child {
    font-weight: bold; /* ID người dùng nổi bật hơn */
    color: #007bff;
}

.table tbody td:nth-child(2), /* Họ tên */
.table tbody td:nth-child(3) /* Email */ {
    text-align: left; /* Căn trái cho họ tên và email để dễ đọc */
}


/* CSS MỚI CHO Ô SELECT VAI TRÒ */
.form-select-sm {
    padding: 0.4rem 1rem; /* Điều chỉnh padding cho select nhỏ */
    font-size: 0.9rem;
    border-radius: 0.3rem;
    appearance: none; /* Bỏ mũi tên mặc định */
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e"); /* Mũi tên tùy chỉnh */
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 16px 12px;
}

.role-admin-select {
    background-color: #e0f7fa !important; /* Màu xanh ngọc nhạt */
    color: #007bb6 !important; /* Màu chữ xanh đậm */
    border-color: #00bcd4 !important;
}

.role-user-select {
    background-color: #e6f7e6 !important; /* Màu xanh lá cây nhạt */
    color: #28a745 !important; /* Màu chữ xanh lá cây đậm */
    border-color: #28a745 !important;
}

/* --- CUSTOM SWITCH (VÔ HIỆU HÓA) --- */
.custom-switch {
    width: 48px; /* Giảm chiều rộng một chút */
    height: 24px; /* Giảm chiều cao */
    background-color: #ccc;
    border-radius: 24px;
    position: relative;
    appearance: none;
    -webkit-appearance: none;
    outline: none;
    cursor: pointer;
    transition: background-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.custom-switch::before {
    content: "";
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px; /* Kích thước nút kéo */
    height: 20px;
    background-color: white;
    border-radius: 50%;
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.custom-switch:checked {
    background-color: #16a34a; /* Màu đỏ cho trạng thái Vô hiệu hóa */
}

.custom-switch:checked::before {
    transform: translateX(24px); /* Dịch chuyển nút kéo */
}

/* --- NÚT XEM CHI TIẾT --- */
.btn-custom-detail {
    background-color: #33ccff; /* Màu xanh dương sáng */
    border-color: #33ccff;
    color: white;
    padding: 0.5rem 1.2rem;
    border-radius: 0.5rem;
    font-size: 0.9rem;
    transition: all 0.2s ease-in-out;
    display: inline-flex; /* Để căn giữa icon nếu có */
    align-items: center;
    justify-content: center;
}

.btn-custom-detail:hover {
    background-color: #00b3e6;
    border-color: #00b3e6;
    transform: translateY(-2px); /* Nhấc nhẹ lên */
    box-shadow: 0 4px 8px rgba(51, 204, 255, 0.3);
}

/* --- MODAL XEM CHI TIẾT --- */
.modal-dialog.modal-dialog-centered {
    display: flex;
    align-items: center;
    min-height: calc(100% - 1rem); /* Đảm bảo đủ không gian nếu màn hình nhỏ */
}

/* Kiểu dáng cho khung nội dung modal */
.custom-modal-content {
    border-radius: 12px; /* Bo góc mềm mại hơn */
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); /* Đổ bóng nhẹ nhàng, hiện đại */
    overflow: hidden; /* Đảm bảo không tràn ra ngoài góc bo */
    background-color: #ffffff; /* Nền trắng sạch */
}

/* Phần header của modal */
.custom-modal-header {
    background-color: #33ccff; /* Màu xanh dương chủ đạo */
    color: white; /* Chữ trắng */
    border-bottom: none; /* Bỏ đường viền dưới */
    padding: 1.5rem 2rem; /* Tăng padding để thoáng hơn */
    font-size: 1.35rem; /* Kích thước tiêu đề lớn hơn */
    font-weight: 700; /* Tiêu đề đậm */
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
    display: flex; /* Dùng flexbox để căn chỉnh tốt hơn */
    justify-content: space-between; /* Đẩy tiêu đề và nút đóng ra hai bên */
    align-items: center; /* Căn giữa theo chiều dọc */
}

/* Nút đóng (X) trên header */
.custom-modal-header .btn-close {
    background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23fff'%3e%3cpath d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707A1 1 0 0 1 .293.293z'/%3e%3c/svg%3e") center/1em auto no-repeat;
    opacity: 1; /* Đảm bảo hiển thị rõ ràng */
    padding: 0.5rem;
    margin: -0.5rem -0.5rem -0.5rem auto; /* Điều chỉnh vị trí nút */
    transition: transform 0.2s ease-in-out;
}

.custom-modal-header .btn-close:hover {
    transform: rotate(90deg); /* Hiệu ứng xoay nhẹ khi hover */
}

/* Phần body của modal */
.custom-modal-body {
    padding: 2rem; /* Padding đồng đều */
    line-height: 1.6; /* Khoảng cách dòng thoáng đãng */
    font-size: 1rem; /* Kích thước chữ tiêu chuẩn */
    color: #34495e; /* Màu chữ xám đậm dễ đọc */
}

.custom-modal-body p {
    margin-bottom: 0.9rem; /* Khoảng cách giữa các dòng thông tin */
    display: flex; /* Dùng flexbox để căn chỉnh label và giá trị */
    align-items: flex-start; /* Căn trên đầu nếu nội dung dài */
}

.custom-modal-body strong {
    color: #2c3e50; /* Màu đậm hơn cho label */
    font-weight: 600; /* Làm đậm label */
    min-width: 130px; /* Đảm bảo căn chỉnh đều các label */
    display: inline-block; /* Để min-width hoạt động */
    margin-right: 10px; /* Khoảng cách giữa label và giá trị */
}

/* Phần footer của modal */
.custom-modal-footer {
    background-color: #f8f9fa; /* Nền xám nhạt */
    border-top: 1px solid #e9ecef; /* Đường viền trên nhẹ */
    padding: 1.2rem 2rem;
    border-bottom-left-radius: 12px;
    border-bottom-right-radius: 12px;
    display: flex;
    justify-content: flex-end; /* Đẩy nút đóng sang phải */
}

/* Nút Đóng trong footer */
.custom-close-btn {
    background-color: #6c757d; /* Màu xám đậm */
    border-color: #6c757d;
    color: white;
    padding: 0.7rem 1.8rem; /* Kích thước nút lớn hơn */
    border-radius: 8px; /* Bo góc nút */
    font-size: 0.95rem;
    transition: all 0.2s ease-in-out; /* Hiệu ứng chuyển động mượt mà */
}

.custom-close-btn:hover {
    background-color: #5a6268; /* Màu đậm hơn khi hover */
    border-color: #545b62;
    transform: translateY(-2px); /* Nhấc nhẹ lên */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15); /* Đổ bóng khi hover */
}

/* --- PHẦN PHÂN TRANG MỚI --- */
.custom-pagination .page-item .page-link {
    border-radius: 0.3rem; /* Bo góc nhẹ cho các nút phân trang */
    margin: 0 0.25rem; /* Khoảng cách giữa các nút */
    border: 1px solid #dee2e6;
    color: #007bff; /* Màu xanh mặc định */
    transition: all 0.2s ease-in-out;
    padding: 0.6rem 1rem;
}

.custom-pagination .page-item .page-link:hover {
    background-color: #e9ecef;
    border-color: #ced4da;
    color: #0056b3;
}

.custom-pagination .page-item.active .page-link {
    background-color: #33ccff; /* Màu xanh chủ đạo khi active */
    border-color: #33ccff;
    color: white; /* Chữ trắng */
    box-shadow: 0 2px 5px rgba(51, 204, 255, 0.3); /* Đổ bóng nhẹ */
}

.custom-pagination .page-item.disabled .page-link {
    color: #6c757d; /* Màu xám cho nút bị vô hiệu hóa */
    pointer-events: none; /* Không thể nhấp */
    background-color: #f8f9fa;
    border-color: #dee2e6;
    opacity: 0.7;
}
/* --- RESPONSIVE MOBILE --- */
@media (max-width: 767.98px) {
  /* Input và Select xếp dọc */
  .row.mb-3.g-2 {
    flex-direction: column;
  }
  .row.mb-3.g-2 > div {
    width: 100%;
  }

  /* Bảng có scroll ngang */
  .table {
    display: block;
    width: 100%;
    overflow-x: auto;
    white-space: nowrap;
  }

  /* Thu gọn padding bảng */
  .table tbody td,
  .table thead th {
    padding: 0.6rem;
    font-size: 0.85rem;
  }

  /* Ẩn bớt cột trên mobile (Email, Vai trò) */
  .table thead th:nth-child(3),
  .table thead th:nth-child(5),
  .table tbody td:nth-child(3),
  .table tbody td:nth-child(5) {
    display: none;
  }

  /* Nút xem chi tiết nhỏ gọn hơn */
  .btn-custom-detail {
    padding: 0.35rem 0.7rem;
    font-size: 0.75rem;
  }

  /* Modal body text nhỏ hơn */
  .custom-modal-body {
    font-size: 0.9rem;
  }

  /* Phân trang nhỏ gọn */
  .custom-pagination .page-item .page-link {
    padding: 0.4rem 0.7rem;
    font-size: 0.85rem;
  }
}
/* Căn giữa mũi tên dropdown trong tất cả select */
.form-select,
.form-select-sm {
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;

  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 0.75rem center; /* Căn giữa mũi tên */
  background-size: 16px 12px;

  padding-right: 2rem; /* Chừa khoảng cho mũi tên */
  line-height: 1.5;    /* Đảm bảo căn giữa theo chiều dọc */
}

.form-select-sm {
  padding: 0.45rem 2rem 0.45rem 0.75rem; /* fix padding nhỏ hơn nhưng vẫn đủ chỗ arrow */
  font-size: 0.9rem;
}

</style>