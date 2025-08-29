<template>
  <div id="app" class="layout-wrapper">
    <header class="top-bar" v-if="!isAdminRoute">
      <div class="container">
        <div class="logo-search">
          <img src="/favicon.png" alt="Logo" class="logo-404" />
          <div class="search-box">
            <input type="text" placeholder="Tìm kiếm sản phẩm..." />
            <button><i class="fas fa-search"></i></button>
          </div>
        </div>
        <div class="header-right">
          <div class="country-selector">
            <img src="/logovn.png" alt="Vietnam Flag" class="flag" />
            <span>Việt Nam</span>
            <i class="fas fa-caret-down"></i>
          </div>

          <template v-if="isLoggedIn">
            <div class="user-menu-wrapper">
              <div class="user-info" @click="toggleUserMenu">
                <img :src="userAvatar" alt="Avatar" class="user-avatar" />
                <span>Xin chào, <strong>{{ userName }}</strong>!</span>
                <i class="fas fa-caret-down"></i>
              </div>
              <div class="user-dropdown-menu" v-if="showUserMenu">
                <ul>
                  <li @click="navigateToUserInfo">Thông tin tài khoản</li>
                  <li v-if="userRoleId === 1" @click="navigateToAdmin">Quản lý</li>
                  <li @click="handleLogout">Đăng xuất</li>
                </ul>
              </div>
            </div>
          </template>
          <template v-else>
            <div class="user-info" @click="showLoginModal = true">
              <i class="fas fa-user"></i> Đăng nhập
            </div>
          </template>

          <div class="cart-info">
            <router-link to="/cart">
              <i class="fas fa-shopping-cart"></i> Giỏ hàng (0)
            </router-link>
          </div>
          <div class="notification"><i class="fas fa-bell"></i> Thông báo</div>
        </div>
      </div>
    </header>

    <nav class="main-nav" v-if="!isAdminRoute">
      <div class="container">
        <div class="nav-mobile-header">
          <i class="fas fa-bars menu-toggle" @click="toggleMobileMenu"></i>
          <router-link to="/">
            <img src="/favicon.png" alt="Logo" class="logo-mobile" />
          </router-link>
        </div>

        <ul :class="{ 'mobile-menu-active': showMobileMenu }">
          <li class="menu-item menu-close">
            <i class="fas fa-times menu-close-icon" @click="toggleMobileMenu"></i>
          </li>
          <li class="menu-item">
            <router-link to="/Danh-muc-san-pham" @click="toggleMobileMenu">Danh Mục Sản Phẩm</router-link>
          </li>
          <li class="menu-item">
            <router-link to="/" @click="toggleMobileMenu">Trang Chủ</router-link>
          </li>
          <li class="menu-item">
            <router-link to="/lien-he" @click="toggleMobileMenu">Liên Hệ</router-link>
          </li>
          <li class="menu-item">
            <router-link to="/tin-tuc" @click="toggleMobileMenu">Tin Tức</router-link>
          </li>
          <li class="menu-item" v-for="page in staticPages.slice(0, 2)" :key="page.id">
            <router-link :to="`/${page.Ten_trang}`" @click="toggleMobileMenu">
              {{ page.Tieu_de_trang }}
            </router-link>
          </li>
        </ul>
        <div class="contact-info">
          <div class="contact-info-chil">
            <i class="fas fa-map-marker-alt"></i>
            <span>Quang Trung, HCM</span>
            <i class="fas fa-caret-down"></i>
          </div>
          <div class="contact-info-chil">
            <i class="fas fa-phone-alt"></i>
            <span>09091005</span>
            <span>Hotline</span>
          </div>
        </div>
      </div>
    </nav>

    <main class="page-content">
      <router-view />
    </main>

    <!-- Logic hiển thị Chat Widget: -->
    <!-- - ChatWidget cho người dùng thông thường: Chỉ hiển thị khi đã đăng nhập, không phải admin và KHÔNG ở trang admin. -->
    <ChatWidget v-if="isLoggedIn && userRoleId !== 1 && !isAdminRoute" />
    
    <!-- - AdminChatWidget cho admin: Chỉ hiển thị khi đã đăng nhập VÀ là admin. -->
    <AdminChatWidget v-if="isLoggedIn && userRoleId === 1" />
    
    <footer class="footer" v-if="!isAdminRoute">
      <div class="container">
        <div class="footer-section footer-about">
          <div class="footer-top">
            <img
              src="/favicon.png"
              alt="FOF Mart Online Supermarket Logo"
              class="footer-logo"
            />
            <div class="footer-info">
              <h3>FOF MART ONLINE Supermarket</h3>
              <p>Copyright 2025</p>
              <p>Chính sách bảo mật</p>
            </div>
          </div>
          <p>FOF Mart Online Supermarket</p>
          <p>
            Địa chỉ: Công viên phần mềm Quang Trung , <br />
            Quận 12, Hồ Chí Minh, Việt Nam
          </p>
          <p>Điện thoại: 00091005</p>
          <p>Fax (028) 199982005</p>
          <p>Mã số doanh nghiệp: 404 NOT FOUND</p>
        </div>
        <div class="footer-section footer-benefits">
          <h3>LỢI ÍCH</h3>
          <ul>
            <li><a href="#">Kênh phân phối chất lượng sản phẩm</a></li>
            <li><a href="#">Giao hàng miễn phí</a></li>
          </ul>
          <p>Hotline: 09091005</p>
          <p>Hộp thư điện tử</p>
          <p>• 404notfound@gmail.com</p>
        </div>
        <div class="footer-section footer-contact">
          <h3>LIÊN HỆ</h3>
          <ul>
            <li>Facebook: <a href="#">anpham</a></li>
            <li>Tik Tok: <a href="#">anpham</a></li>
            <li>Website: <a href="#">anpham</a></li>
          </ul>
        </div>
      </div>
    </footer>

    <AuthModal :show="showLoginModal" @update:show="showLoginModal = $event" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import AuthModal from "./components/user/AuthModal.vue";
import Swal from "sweetalert2";
import axios from "axios";
import ChatWidget from './components/user/ChatWidget.vue';
import AdminChatWidget from './components/user/AdminChatWidget.vue'; // Import component chat của admin

const router = useRouter();

const showLoginModal = ref(false);
const isLoggedIn = ref(false);
const userName = ref("");
const userAvatar = ref("");
const userRoleId = ref(null);
const showUserMenu = ref(false);
const staticPages = ref([]);
const showMobileMenu = ref(false);

const isAdminRoute = computed(() => {
  return router.currentRoute.value.path.startsWith("/admin");
});

const toggleMobileMenu = () => {
  showMobileMenu.value = !showMobileMenu.value;
};

// Hàm mới để đóng menu khi click ra ngoài
const closeMobileMenuOnClickOutside = (event) => {
  if (showMobileMenu.value) {
    const mobileMenuElement = document.querySelector('.main-nav ul');
    const menuToggleButton = document.querySelector('.menu-toggle');

    // Kiểm tra xem vị trí click có nằm ngoài menu và nút mở menu không
    if (
      mobileMenuElement &&
      !mobileMenuElement.contains(event.target) &&
      menuToggleButton &&
      !menuToggleButton.contains(event.target)
    ) {
      showMobileMenu.value = false;
    }
  }
};

const checkLoginStatus = () => {
  const user = localStorage.getItem("user");
  const roleId = localStorage.getItem("vai_tro_id");

  if (user && roleId) {
    try {
      const parsedUser = JSON.parse(user);
      if (parsedUser && parsedUser.ho_ten) {
        isLoggedIn.value = true;
        userName.value = parsedUser.ho_ten;

        let defaultAvatarUrl = '';
        if (parsedUser.ho_ten) {
            const firstLetter = parsedUser.ho_ten.charAt(0).toUpperCase();
            defaultAvatarUrl = `https://placehold.co/40x40/33ccff/FFFFFF?text=${firstLetter}`;
        } else {
            defaultAvatarUrl = `https://placehold.co/40x40/33ccff/FFFFFF?text=AV`;
        }

        userAvatar.value = parsedUser.anh_dai_dien || defaultAvatarUrl;
        userRoleId.value = parseInt(roleId);
      }
    } catch (e) {
      console.error("Lỗi khi phân tích cú pháp thông tin người dùng từ localStorage:", e);
      localStorage.removeItem("user");
      localStorage.removeItem("vai_tro_id");
      isLoggedIn.value = false;
      userName.value = "";
      userAvatar.value = '';
      userRoleId.value = null;
    }
  } else {
    isLoggedIn.value = false;
    userName.value = "";
    userAvatar.value = '';
    userRoleId.value = null;
  }
};

const handleLogout = () => {
  Swal.fire({
    title: "Bạn có chắc chắn muốn đăng xuất?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Có, đăng xuất!",
    cancelButtonText: "Hủy",
  }).then((result) => {
    if (result.isConfirmed) {
      localStorage.removeItem("token");
      localStorage.removeItem("user");
      localStorage.removeItem("vai_tro_id");

      isLoggedIn.value = false;
      userName.value = "";
      userAvatar.value = '';
      userRoleId.value = null;
      showUserMenu.value = false;

      router.push("/");

      Swal.fire({
        toast: true,
        position: "top-end",
        icon: "success",
        title: "Đã đăng xuất!",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
      });
    }
  });
};

const navigateToAdmin = () => {
  showUserMenu.value = false;
  router.push("/admin");
};

const navigateToUserInfo = () => {
  showUserMenu.value = false;
  router.push("/user");
};

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value;
};

const closeUserMenuOnClickOutside = (event) => {
  const userMenuWrapper = document.querySelector(".user-menu-wrapper");
  if (userMenuWrapper && !userMenuWrapper.contains(event.target) && showUserMenu.value) {
    showUserMenu.value = false;
  }
};

const fetchStaticPages = async () => {
  try {
    const response = await axios.get("http://localhost:8000/api/static-pages");
    staticPages.value = response.data;
  } catch (error) {
    console.error("Lỗi khi tải static pages:", error);
  }
};

onMounted(() => {
  checkLoginStatus();
  fetchStaticPages();
  document.addEventListener("click", closeUserMenuOnClickOutside);
  // Thêm event listener cho menu di động
  document.addEventListener('click', closeMobileMenuOnClickOutside);
});

onUnmounted(() => {
  document.removeEventListener("click", closeUserMenuOnClickOutside);
  // Gỡ bỏ event listener khi component bị hủy
  document.removeEventListener('click', closeMobileMenuOnClickOutside);
});

watch(showLoginModal, (newVal) => {
  if (!newVal) {
    checkLoginStatus();
  }
});
</script>
<style>
/* Global styles (cơ bản) */
body {
  margin: 0;
  font-family: "Lora", serif;
  background-color: #ffffff;
  color: #333;
}

.container {
  width: 100%;
  max-width: 1200px;
  padding-right: 12px;
  padding-left: 12px;
  margin: 0 auto;
}

/* --- Top Bar (Header chính) --- */
.top-bar {
  background-color: white;
  padding: 10px 0;
  border-bottom: 1px solid white;
}

.top-bar .container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
}

.top-bar .logo-search {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-grow: 1;
  margin-right: 20px;
}

.logo-search img {
  box-shadow: none;
}

.top-bar .logo-404 {
  height: 50px;
  width: auto;
  flex-shrink: 0;
}

.top-bar .search-box {
  display: flex;
  width: 100%;
  max-width: 600px;
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid #007bff;
}

.top-bar .search-box input {
  padding: 8px 20px;
  width: 100%;
  border: none;
  outline: none;
  font-size: 14px;
}

.search-box input::placeholder {
  color: #aaa;
}

.top-bar .search-box button {
  background-color: #007bff;
  color: white;
  border: none;
  padding: 8px 16px;
  cursor: pointer;
  transition: background-color 0.2s ease;
  font-size: 16px;
}

.top-bar .search-box button:hover {
  background-color: #036cdc;
}

.top-bar .header-right {
  display: flex;
  align-items: center;
  gap: 20px;
  flex-shrink: 0;
}

.top-bar .country-selector,
.top-bar .user-info,
.top-bar .cart-info,
.top-bar .notification {
  display: flex;
  align-items: center;
  gap: 5px;
  color: #555;
  font-size: 14px;
  cursor: pointer;
  white-space: nowrap;
  transition: color 0.2s ease;
}

.top-bar .user-info:hover,
.top-bar .cart-info:hover,
.top-bar .notification:hover,
.top-bar .country-selector:hover {
  color: #046fe1;
}

.top-bar .cart-info {
  display: flex;
  align-items: center;
  gap: 5px;
  color: #555;
  font-size: 14px;
  cursor: pointer;
  white-space: nowrap;
  transition: color 0.2s ease;
}

.top-bar .cart-info a {
  text-decoration: none;
  color: inherit;
  display: flex;
  align-items: center;
  gap: 5px;
}

.top-bar .cart-info:hover {
  color: #036bda;
}

.top-bar .country-selector img.flag {
  width: 30px;
  height: 20px;
  object-fit: cover;
  border-radius: 3px;
  vertical-align: middle;
}

/* --- Main Navigation (Menu chính) --- */
.main-nav {
  background-color: #007bff;
  padding: 10px 0;
  color: white;
}

.main-nav .container {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.main-nav ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  gap: 20px;
}

/* Hiệu ứng hover cho các menu item */
.main-nav ul li a {
  color: white;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s ease;
  display: flex;
  align-items: center;
  gap: 5px;
}

.main-nav ul li a:hover {
  color: white; /* Màu xanh lá cây khi hover */
}

/* Các quy tắc mới để vô hiệu hóa hover cho trang hiện tại */
.main-nav ul li a.router-link-active,
.main-nav ul li a.router-link-exact-active {
  color: white; /* Luôn hiển thị màu xanh lá cây cho trang hiện tại */
  font-weight: bold; /* Thêm hiệu ứng nổi bật */
  cursor: default; /* Thay đổi con trỏ chuột để người dùng biết không thể nhấp */
}

/* Vô hiệu hóa hiệu ứng hover trên liên kết của trang hiện tại */
.main-nav ul li a.router-link-active:hover,
.main-nav ul li a.router-link-exact-active:hover {
  color: white; /* Giữ nguyên màu, không thay đổi khi hover */
}

.main-nav .contact-info {
  display: flex;
  align-items: center;
  gap: 30px;
  font-size: 14px;
}
.main-nav .contact-info-chil {
  display: flex;
  gap: 5px;
  font-size: 14px;
}
.main-nav .contact-info-chil i {
  margin: auto;
}

/* --- Footer --- */
.footer {
  background-color: #343a40;
  color: #f8f9fa;
  padding: 40px 0;
  margin-top: 40px;
}

.footer .container {
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 30px;
}

.footer-section {
  flex: 1 1 250px;
  min-width: 250px;
}

.footer-section h3 {
  color: #ffffff;
  margin-bottom: 20px;
  font-size: 16px;
}

.footer-section p,
.footer-section li {
  font-size: 14px;
  line-height: 1.8;
}

.footer-section ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-section ul li a {
  color: #adb5bd;
  text-decoration: none;
  transition: color 0.2s ease;
}

.footer-section ul li a:hover {
  color: #ffffff;
}

.footer-logo {
  margin-bottom: 15px;
}

.footer-top {
  display: flex;
  align-items: center;
  gap: 16px;
}

.footer-top {
  box-shadow: none;
}

.footer-info h3,
.footer-info p {
  margin: 0;
  line-height: 1.6;
}

/* --- Responsive adjustments --- */
@media (max-width: 992px) {
  .top-bar .logo-search {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
    margin-right: 10px;
  }

  .top-bar .search-box {
    max-width: none;
  }

  .main-nav .container {
    flex-direction: column;
    align-items: flex-start;
    gap: 0;
  }

  .main-nav ul {
    flex-direction: column;
    width: 250px;
    max-height: auto;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    z-index: 1000;
    background-color: #0173ed;
    padding: 60px 20px 20px;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
    transform: translateX(-100%);
    transition: transform 0.3s ease-in-out;
  }

  .main-nav ul.mobile-menu-active {
    transform: translateX(0);
  }
  
  .main-nav ul li a {
    padding: 10px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    width: 100%;
    display: block;
  }

  .main-nav ul li:last-child a {
    border-bottom: none;
  }

  .main-nav .menu-toggle {
    display: block;
    color: white;
    font-size: 24px;
    cursor: pointer;
  }

  .main-nav .nav-mobile-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    padding: 5px 0;
  }

  .main-nav .logo-mobile {
    display: block;
    height: 40px;
    margin-left: 10px;
  }

  .main-nav .contact-info {
    display: none;
  }
  
  /* Style cho nút đóng menu */
  .menu-close {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 20px;
    position: absolute;
    top: 15px;
    right: 15px;
  }
  
  .menu-close-icon {
    font-size: 24px;
    color: white;
    cursor: pointer;
    padding: 5px;
  }
}

@media (min-width: 993px) {
  .main-nav .menu-toggle {
    display: none;
  }

  .main-nav .logo-mobile {
    display: none;
  }
  
  .main-nav .nav-mobile-header {
    display: none;
  }

  /* Ẩn nút đóng trên màn hình desktop */
  .menu-close {
    display: none;
  }
}

/* Các thay đổi để khắc phục lỗi tràn trên màn hình nhỏ */
@media (max-width: 768px) {
  .top-bar .container {
    flex-wrap: wrap;
    justify-content: center;
    gap: 15px;
  }

  .top-bar .logo-search {
    width: 100%;
    margin-right: 0;
    justify-content: center;
    align-items: center;
  }

  .top-bar .header-right {
    width: 100%;
    justify-content: space-around;
    align-items: center;
    gap: 10px;
  }

  .top-bar .cart-info span,
  .top-bar .notification span {
    display: none;
  }
  
  .top-bar .cart-info a,
  .top-bar .notification {
    display: flex;
    align-items: center;
    gap: 5px;
  }

  .top-bar .user-info {
    font-size: 12px;
    flex-shrink: 0;
    white-space: nowrap;
    display: flex;
    align-items: center;
    padding: 0 5px;
  }

  .user-info strong {
    display: none;
  }

  .header-right .user-avatar {
    width: 30px;
    height: 30px;
    margin-right: 5px;
  }

  .top-bar .country-selector {
    display: none;
  }

  .top-bar .search-box {
    margin-top: 10px;
  }
}

.header-right .user-info {
  display: flex;
  align-items: center;
  gap: 5px;
  color: #555;
  font-size: 14px;
  cursor: pointer;
  white-space: nowrap;
  transition: color 0.2s ease;
}

.header-right .user-info:hover {
  color: #046ee0;
}

.user-info strong {
  color: #007bff;
  font-weight: bold;
}

.user-menu-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.user-dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  background-color: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  min-width: 150px;
  z-index: 100;
  overflow: hidden;
}

.user-dropdown-menu ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.user-dropdown-menu ul li {
  padding: 10px 15px;
  cursor: pointer;
  color: #333;
  font-size: 14px;
  transition: background-color 0.2s ease, color 0.2s ease;
  white-space: nowrap;
}

.user-dropdown-menu ul li:hover {
  background-color: #f0f0f0;
  color: #0471e5;
}

.user-info .fa-caret-down {
  margin-left: 5px;
  transition: transform 0.2s ease;
}

.user-info.active .fa-caret-down {
  transform: rotate(180deg);
}

.header-right .user-info span strong {
  color: #007bff;
  font-weight: bold;
}

.layout-wrapper {
  display: flex;
  flex-direction: column;
  min-height: 150vh;
  font-family: "Lora", serif !important;
}

.page-content {
  flex: 1;
}

.header-right .user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  margin-right: 5px;
  border: 2px solid #ddd;
}
</style>
