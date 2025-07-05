<template>
  <div class="admin-layout">
    <aside class="sidebar">
      <div class="sidebar-header">
        <img :src="logo1" alt="Logo" class="sidebar-logo" />
      </div>

      <nav class="sidebar-nav">
        <div v-for="section in menu" :key="section.label" class="menu-section">
          <div class="section-header" @click="toggleSection(section.label)">
            <span>{{ section.label }}</span>
            <i class="bi bi-chevron-down chevron-icon" :class="{ 'is-open': openSection === section.label }"></i>
          </div>

          <ul class="nav-items" v-if="openSection === section.label">
            <li v-for="item in section.items" :key="item.label">
              <router-link :to="item.to" class="nav-link">
                <i :class="[item.icon, 'nav-icon']"></i>
                <span>{{ item.label }}</span>
              </router-link>
            </li>
          </ul>
        </div>
      </nav>
    </aside>

    <div class="main-content">
     <header class="app-header">
    <div class="header-left">
        <i class="bi bi-list menu-toggle-icon"></i> 
        <input type="search" class="search-input" placeholder="Tìm kiếm..." />
    </div>

    <div class="header-right">
        <span class="version-text">VERSION 1.5.4</span>

        <div class="notification-badge">
            <i class="bi bi-bell"></i>
            <span class="badge">3</span>
        </div>

        <div class="user-menu">
            <img :src="logo" alt="Admin" class="admin-avatar" />
            <div class="user-info">
                <span class="admin-name">Administrator</span>
                <span class="admin-role">Super Admin</span>
            </div>
            <i class="bi bi-chevron-down dropdown-icon"></i>
        </div>
    </div>
</header>

      <main class="page-content">
        

        <div class="container-table">
          <button class="btn btn-outline-secondary btn-sm" @click="goBack" style="position: absolute; right: 115px;">
            <i class="bi bi-arrow-left me-1"></i> Quay Lại
          </button>
          <router-view />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import logo from '@/assets/images/icon-admin.png';
import logo1 from '@/assets/images/image60.png';

const router = useRouter();

// State to control which menu section is open
const openSection = ref('Quản lý sản phẩm'); // Mặc định mở mục đầu tiên

const toggleSection = (sectionLabel) => {
  if (openSection.value === sectionLabel) {
    openSection.value = null; // Close if clicking the same one
  } else {
    openSection.value = sectionLabel; // Open the new one
  }
};

const goBack = () => {
  router.back();
}

const menu = [
  {
    label: 'Quản lý sản phẩm',
    items: [
        { to: '/admin/products', label: 'Sản phẩm', icon: 'bi bi-box' },
        { to: '/admin/Category', label: 'Danh mục sản phẩm', icon: 'bi bi-newspaper' },
    ]
  },
  {
    label: 'Quản lý đơn hàng',
    items: [{ to: '/admin/orders', label: 'Đơn hàng', icon: 'bi bi-receipt' }],
  },
  {
    label: 'Quản lý Người dùng',
    items: [{ to: '/admin/customers', label: 'Người Dùng', icon: 'bi bi-person' }],
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
      { to: '/admin/tintuc', label: 'Tin tức', icon: 'bi bi-newspaper' },
      { to: '/admin/video', label: 'Video', icon: 'bi bi-camera-video' },
      { to: '/admin/payment', label: 'Hình thức thanh toán', icon: 'bi bi-cash' },
      { to: '/admin/notifications', label: 'Thông báo đẩy', icon: 'bi bi-bell' },
    ],
  },
  {
    label: 'Quản lý trang tĩnh',
    items: [
      { to: '/admin/slide', label: 'Slide Show', icon: 'bi bi-calendar-week' },
      { to: '/admin/introduce',label : 'Trang Tĩnh', icon: 'bi bi-info-circle' },
    ],
  },
];
</script>

<style scoped>
/* Main Layout */
.admin-layout {
  display: flex;
  min-height: 100vh;
  background-color: #f8f9fa;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Sidebar */
.sidebar {
  width: 260px;
  background-color: #ffffff;
  border-right: 1px solid #dee2e6;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
}
.sidebar-header {
  padding: 1.5rem 1rem;
  text-align: center;
  border-bottom: 1px solid #dee2e6;
}
.sidebar-logo {
  max-width: 120px;
}
.sidebar-nav {
  overflow-y: auto;
  flex-grow: 1;
}

/* Menu Section */
.menu-section {
  border-bottom: 1px solid #f0f0f0;
}
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 20px;
  cursor: pointer;
  user-select: none;
  font-weight: 600;
  font-size: 14px;
  color: #343a40;
  transition: background-color 0.2s ease;
}
.section-header:hover {
  background-color: #f8f9fa;
}
.chevron-icon {
  font-size: 12px;
  transition: transform 0.3s ease;
}
.chevron-icon.is-open {
  transform: rotate(180deg);
}

/* Nav Items */
.nav-items {
  list-style: none;
  padding: 0;
  margin: 0;
  background-color: #fdfdfd;
}
.nav-link {
  display: flex;
  align-items: center;
  padding: 12px 20px 12px 30px;
  color: #495057;
  text-decoration: none;
  font-size: 14px;
  transition: background-color 0.2s ease, color 0.2s ease;
}
.nav-icon {
  margin-right: 12px;
  width: 20px;
  text-align: center;
  font-size: 16px;
  
}
.nav-link:hover {
  background-color: #e9ecef;
  color: #000;
  list-style-type: none;
  text-decoration: none;
}
.nav-link.router-link-exact-active {
  background-color: #e7f1ff;
  color: #0d6efd;
  font-weight: 600;
  border-right: 3px solid #0d6efd;
}

/* Main Content Area */
.main-content {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

/* App Header */
.app-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 24px;
  height: 64px;
  background-color: #ffffff;
  border-bottom: 1px solid #dee2e6;
  flex-shrink: 0;
  width: 96%;
  margin-left: 25px;
  margin-top: -20px;
}
.header-left, .header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}
.header-right {
    
    gap: 24px; /* Tăng khoảng cách giữa các mục */
}

.notification-badge {
    position: relative;
    cursor: pointer;
    font-size: 20px;
    color: #555;
}

.notification-badge .badge {
    position: absolute;
    top: -5px;
    right: -8px;
    background-color: #dc3545;
    color: white;
    font-size: 10px;
    font-weight: 600;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
}

.user-menu {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    padding: 6px 12px;
    border-radius: 8px;
    transition: background-color 0.2s ease;
}

.user-menu:hover {
    background-color: #f8f9fa;
}

.user-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    line-height: 1.2;
}

.admin-name {
    font-weight: 600;
    font-size: 14px;
}

.admin-role {
    font-size: 12px;
    color: #6c757d;
}

.dropdown-icon {
    font-size: 14px;
    color: #6c757d;
}
.search-input {
  padding: 8px 12px;
  font-size: 14px;
  border: 1.5px solid #ced4da;
  border-radius: 6px;
  outline: none;
  width: 320px;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}
.search-input:focus {
  border-color: #80bdff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
.version-text {
  font-size: 12px;
  color: #6c757d;
  font-weight: 500;
}
.admin-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
}
.admin-name {
  font-weight: 600;
}


/* Page Content */
.page-content {
  padding: 24px;
  overflow-y: auto;
  flex-grow: 1;
}
.container-table {
  background-color: #ffffff;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}
.sidebar-logo {
  box-shadow: none !important;
}
</style>