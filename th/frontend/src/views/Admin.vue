<template>
  <div class="d-flex min-vh-100">
    <!-- Sidebar -->
    <aside class="sidebar bg-white border-end px-3 py-4" style="width: 260px;">
      <div class="text-center mb-4">
        <img src="" alt="Logo" style="max-width: 120px;" />
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

    <!-- Main Content -->
    <main class="flex-grow-1 p-4 bg-light">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0">🎯 Bảng điều khiển</h5>
        <button class="btn btn-outline-danger btn-sm" @click="logout">Đăng xuất</button>
      </div>

      <router-view />
    </main>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
const router = useRouter()

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('role')
  router.push('/login')
}

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
      { to: '/admin/news', label: 'Tin tức', icon: 'bi bi-newspaper' },
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
]
</script>

<style scoped>
.sidebar {
  border-right: 1px solid #dee2e6;
  height: 100%;
  overflow-y: auto;
}

.section-label {
  color: #1266b3;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 13px;
}

/* Mặc định */
.sidebar .nav-link {
  color: #333;
  font-size: 14px;
  transition: background-color 0.3s ease, color 0.3s ease;
}
/* Khi router-link active */
.sidebar .nav-link.router-link-exact-active {
  background-color: #f0f0f0;
  border-radius: 5px;
  font-weight: 600;
  color: #1266b3 !important;
}

.sidebar .nav-link.router-link-exact-active i {
  color: #1266b3 !important;
}
</style>