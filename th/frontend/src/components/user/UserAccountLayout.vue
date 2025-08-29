<template>
  <div class="content-area container">
    <button class="hamburger-btn" @click="toggleSidebar">
      <i class="fas fa-bars"></i>
    </button>

    <aside class="sidebar" :class="{ 'sidebar-open': isSidebarOpen }">
      <div class="sidebar-header">
        <span class="user-name">Xin Chào, <b>{{ userName }}</b></span>
      </div>
      <ul>
        <li
          :class="{ active: $route.path === '/user/profile' || $route.path === '/user' }"
          @click="toggleSidebar"
        >
          <router-link to="/user/profile">
            <i class="fas fa-info-circle"></i> Thông tin và địa chỉ
          </router-link>
        </li>
        <li
          :class="{ active: $route.path === '/user/orders' }"
          @click="toggleSidebar"
        >
          <router-link to="/user/orders">
            <i class="fas fa-clipboard-list"></i> Đơn hàng đã mua
          </router-link>
        </li>
        <li
          :class="{ active: $route.path === '/user/change-password' }"
          @click="toggleSidebar"
        >
          <router-link to="/user/change-password">
            <i class="fas fa-lock"></i> Thay đổi mật khẩu
          </router-link>
        </li>
      </ul>
      <button class="logout-btn" @click="handleLogout">Đăng Xuất</button>
    </aside>

    <main class="main-content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import Swal from "sweetalert2";

const userName = ref("Khách");
const router = useRouter();
const route = useRoute();
const isLoggedIn = ref(true);
const userRoleId = ref(null);
const showUserMenu = ref(false);
const isSidebarOpen = ref(false); // Thêm biến trạng thái cho sidebar

const fetchUserName = () => {
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    try {
      const user = JSON.parse(storedUser);
      if (user && user.ho_ten) {
        userName.value = user.ho_ten;
      }
    } catch (e) {
      console.error("Failed to parse user from localStorage:", e);
      userName.value = "Guest";
    }
  }
};

const handleLogout = () => {
  Swal.fire({
    title: 'Bạn có chắc chắn muốn đăng xuất?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Có, đăng xuất!',
    cancelButtonText: 'Hủy'
  }).then((result) => {
    if (result.isConfirmed) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      localStorage.removeItem('vai_tro_id');
      isLoggedIn.value = false;
      userName.value = '';
      userRoleId.value = null;
      showUserMenu.value = false;
      router.push('/').then(() => {
        window.location.reload();
      });
    }
  });
};

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
};

// Hàm đóng sidebar khi màn hình lớn hơn mobile
const closeSidebarOnResize = () => {
  if (window.innerWidth > 768 && isSidebarOpen.value) {
    isSidebarOpen.value = false;
  }
};

onMounted(() => {
  fetchUserName();
  window.addEventListener('resize', closeSidebarOnResize);
});

onUnmounted(() => {
  window.removeEventListener('resize', closeSidebarOnResize);
});

</script>

<style scoped>
.content-area {
  display: flex;
  gap: 25px;
  padding: 30px 0;
  align-items: flex-start;
  position: relative; /* Quan trọng để nút hamburger định vị tuyệt đối */
}

.container {
  max-width: 1200px;
  margin: 40px auto;
  padding: 0 15px;
}

.sidebar {
  background-color: #f4f6f8;
  padding: 20px;
  border-radius: 8px;
  min-width: 250px;
  color: black;
  transition: transform 0.3s ease-in-out;
}

.sidebar-header {
  display: flex;
  align-items: center;
  padding-bottom: 10px;
  border-bottom: 1px solid var(--border-color);
}

.sidebar-header .user-name {
  font-weight: 500;
  color: var(--dark-text);
}

.sidebar ul {
  list-style: none;
  padding: 0;
  margin-top: 20px;
}

.sidebar ul li {
  margin-bottom: 5px;
}

.sidebar ul li a {
  text-decoration: none;
  color: black;
  font-size: 16px;
  display: flex;
  align-items: center;
  padding: 8px 10px;
  border-radius: 5px;
  transition: background-color 0.2s ease;
}

.sidebar ul li a i {
  margin-right: 10px;
  font-size: 18px;
  color: black;
}

.sidebar ul li a:hover,
.sidebar ul li.active a {
  background-color: #e4e7ed;
  color: black;
}

.sidebar .logout-btn {
  display: block;
  width: 100%;
  padding: 12px 15px;
  background-color: #f8f9fa;
  color: #33ccff;
  border: 1px solid #33ccff;
  border-radius: 5px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  margin-top: 25px;
  transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

.sidebar .logout-btn:hover {
  background-color: #2497d5;
  color: #ffffff;
  border-color: #2497d5;
}

.main-content {
  flex-grow: 1;
  background-color: white;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  min-height: 70vh;
}

/* Nút Hamburger Menu (Ẩn trên desktop) */
.hamburger-btn {
  display: none;
  position: absolute;
  top: 15px;
  right: 15px;
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  z-index: 100; /* Đảm bảo nút ở trên tất cả các lớp khác */
}

/* --- Media Queries cho responsive --- */
@media (max-width: 768px) {
  .content-area {
    flex-direction: column;
    padding: 15px;
    gap: 0;
  }
  
  .container {
    margin: 0 auto;
    padding: 0;
  }

  /* Hiển thị nút hamburger trên mobile */
  .hamburger-btn {
    display: block;
  }

  /* Ẩn sidebar trên mobile */
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    transform: translateX(-100%);
    z-index: 99;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
  }

  /* Hiển thị sidebar khi có class sidebar-open */
  .sidebar.sidebar-open {
    transform: translateX(0);
  }

  .main-content {
    width: 100%;
    padding: 15px;
    margin-top: 50px; /* Tạo khoảng trống để không bị nút hamburger che */
  }
}
</style>
