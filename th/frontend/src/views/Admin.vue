<template>
  <div class="d-flex flex-column min-vh-100">
  <header class="myheader">
  <div class="myheader-left">
    <img :src =logo alt="Logo" class="myheader-logo" />
    <span class="myheader-greeting">Xin chào: <strong>Administrator</strong></span>
  </div>

  <div class="myheader-center">
    <input type="search" class="app123_searchInput" placeholder="Tìm kiếm..." />
  </div>

  <div class="myheader-right">
    <span class="myheader-version">VERSION 1.0.2</span>
    <i class="myheader-icon bi bi-person-circle"></i>
  </div>
</header>




    <!-- Body -->
    <div class="d-flex flex-grow-1">
      <!-- Sidebar -->
      <aside class="sidebar bg-white border-end px-3 py-4" style="width: 260px;">
        <div class="text-center mb-4">
          <img :src="logo1" alt="Logo" style="max-width: 120px;" />
        </div>

        <div v-for="section in menu" :key="section.label" class="mb-3">
          <div class="section-label mb-2">{{ section.label }}</div>
          <ul class="nav flex-column">
            <li v-for="item in section.items" :key="item.label" class="nav-item">
              <router-link
                :to="item.to"
                class="nav-link d-flex align-items-center px-2 py-2"
              >
                <i :class="[item.icon, 'me-2']"></i> {{ item.label }}
              </router-link>
            </li>
          </ul>
        </div>
      </aside>

      
      <main class="flex-grow-1 p-4 bg-light">
        <br><br><br>
        <div class="d-flex justify-content-end mb-4">
          <button class="btn btn-outline-danger btn-sm" @click="logout">Đăng xuất</button>
        </div>

        <div class="container-table">
          <router-view />
        </div>

      </main>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import logo from '@/assets/images/icon-admin.png';
import logo1 from '@/assets/images/image60.png';
const router = useRouter();

const logout = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('role');
  router.push('/login');
};

const menu = [
  {
    label: 'Quản lý sản phẩm',
    items: [{ to: '/admin/products', label: 'Sản phẩm', icon: 'bi bi-box' }],
  },
  {
    label: 'Quản lý đơn hàng',
    items: [{ to: '/admin/orders', label: 'Đơn hàng', icon: 'bi bi-receipt' }],
  },
  {
    label: 'Quản lý Người dùng',
    items: [{ to: '/admin/customers', label: 'Người Dùng', icon: 'bi bi-user' }],
  },
  {
    label: 'Quản lý thuộc tính',
    items: [{ to: '/admin/attributes', label: 'Thuộc tính', icon: 'bi bi-sliders' }],
  },
  {
    label: 'Quản lý bài viết',
    items: [
      { to: '/admin/dichvu', label: 'Dịch vụ tiện ích', icon: 'bi bi-file-earmark-text' },
      { to: '/admin/album', label: 'Album', icon: 'bi bi-image' },
      { to: '/admin/danhmuctintuc', label: 'Danh Mục Tin tức', icon: 'bi bi-newspaper' },
      { to: '/admin/video', label: 'Video', icon: 'bi bi-camera-video' },
      { to: '/admin/payment', label: 'Hình thức thanh toán', icon: 'bi bi-cash' },
      { to: '/admin/notifications', label: 'Thông báo đẩy', icon: 'bi bi-bell' },
    ],
  },
  {
    label: 'Quản lý trang tĩnh',
    items: [
      { to: '/admin/pages/weekly-program', label: 'Chương trình theo tuần', icon: 'bi bi-calendar-week' },
    ],
  },
];
</script>

<style scoped>

.container {
  width: 100%;
  height: 60px; 
}


.myheader {
  width: 80%;
  margin-left: 19%;
  position: absolute;
  background-color: #ffffff;
  border: 1px solid #d8dce0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 32px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
 
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  z-index: 10;
}

.myheader-left,
.myheader-center,
.myheader-right {
  display: flex;
  align-items: center;
}

.myheader-left {
  flex: 1;
  gap: 10px;
}

.myheader-center {
  flex: 1;
  justify-content: center;
}

.myheader-right {
  flex: 1;
  justify-content: flex-end;
  gap: 10px;
}

.sidebar {
  border-right: 1px solid #dee2e6;
  height: 100%;
  overflow-y: auto;
}

.section-label {
  color: #1266b3;
  font-weight:bolder;
  text-transform: uppercase;
  font-size: 13px;
  margin: 10px;
}

.sidebar .nav-link {
  color: #333;
  font-size: 14px;
  transition: background-color 0.3s ease, color 0.3s ease;
  margin: 10px;
}

.sidebar .nav-link.router-link-exact-active {
  background-color: #f0f0f0;
  border-radius: 5px;
  font-weight: 600;
  margin: 10px;
}

.sidebar .nav-link.router-link-exact-active i {
  color: #1266b3 !important;
}
.app123_searchForm {
  display: inline-block;
}

.app123_searchInput {
  width: 320px;
  padding: 8px 12px;
  font-size: 14px;
  border: 1.5px solid #c2c2c2;
  border-radius: 5px;
  outline: none;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.app123_searchInput:focus {
  border-color: #2563eb;
  box-shadow: 0 0 6px rgba(37, 99, 235, 0.5);
}

.app123_searchInput::placeholder {
  color: #9ca3af;
  font-style: italic;
}
.myheader-logo{
  height: 30px;
}
.container-table{
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 10px;
  background-color: #ffffff;
  border-radius: 8px;
}
</style>
<style>


</style>