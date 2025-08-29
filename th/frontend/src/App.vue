<template>
  <div id="app" class="layout-wrapper">
    <header class="top-bar" v-if="!isAdminRoute">
      <div class="container">
        <div class="logo-search">
          <img src="/favicon.png" alt="Logo" class="logo-404" />
          <div class="search-box-wrapper">
            <div class="search-box">
              <input
                type="text"
                placeholder="Tìm kiếm sản phẩm..."
                v-model="q"
                @input="search"
                @keyup.enter="handleSearch"
              />
              <button @click="handleSearch"><i class="fas fa-search"></i></button>
            </div>
            
            <ul v-if="results.length" class="suggestions">
              <li
                v-for="p in results"
                :key="p.product_id"
                @mousedown.prevent="go(p.slug)"
              >
                <img v-if="p.images?.length" :src="p.images[0]" />
                <div>
                  <b>{{ p.product_name }}</b>
                  <small>{{ format(p.min_price) }} - {{ format(p.max_price) }} đ</small>
                </div>
              </li>
            </ul>
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
            <a href="#" @click.prevent="goToCart">
              <i class="fas fa-shopping-cart"></i> Giỏ hàng (0)
            </a>
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

    <ChatWidget v-if="isLoggedIn && userRoleId !== 1 && !isAdminRoute" />
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
            <li>Facebook: <a href="#">FoFMarket</a></li>
            <li>Tik Tok: <a href="#">FoFMarket</a></li>
            <li>Website: <a href="#">FoFMarket</a></li>
          </ul>
        </div>
      </div>
    </footer>
    <AuthModal :show="showLoginModal" @update:show="showLoginModal = $event" />
  </div>
</template>
<script setup>
import { ref, computed, onMounted, watch, onUnmounted } from "vue";
import { useRouter, useRoute } from "vue-router"; // Import useRoute
import AuthModal from "./components/user/AuthModal.vue";
import Swal from "sweetalert2";
import axios from "axios";
import ChatWidget from './components/user/ChatWidget.vue';
import AdminChatWidget from './components/user/AdminChatWidget.vue';

const router = useRouter();
const route = useRoute(); // Sử dụng useRoute để lấy thông tin route hiện tại

const showLoginModal = ref(false);
const isLoggedIn = ref(false);
const userName = ref("");
const userAvatar = ref("");
const userRoleId = ref(null);
const showUserMenu = ref(false);
const staticPages = ref([]);
const showMobileMenu = ref(false);

const q = ref(""); // query
const results = ref([]); // kết quả gợi ý
const showSuggestions = ref(false); // Thêm biến này để kiểm soát hiển thị gợi ý
let timer = null;

const isAdminRoute = computed(() => {
  return router.currentRoute.value.path.startsWith("/admin");
});

const toggleMobileMenu = () => {
  showMobileMenu.value = !showMobileMenu.value;
};

const closeMobileMenuOnClickOutside = (event) => {
  if (showMobileMenu.value) {
    const mobileMenuElement = document.querySelector('.main-nav ul');
    const menuToggleButton = document.querySelector('.menu-toggle');
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

const search = () => {
  clearTimeout(timer);
  if (q.value.length < 2) {
    results.value = [];
    showSuggestions.value = false;
    return;
  }
  timer = setTimeout(async () => {
    try {
      const res = await axios.get(`http://localhost:8000/api/products?search=${q.value}`);
      results.value = res.data;
      showSuggestions.value = true;
    } catch (err) {
      console.error("Search error:", err);
      results.value = [];
      showSuggestions.value = false;
    }
  }, 300);
};

const go = (slug) => {
  // Lấy slug của sản phẩm hiện tại
  const currentSlug = route.params.slug;
  const newPath = `/san-pham/${slug}`;

  // Kiểm tra nếu đường dẫn mới giống đường dẫn cũ, buộc tải lại trang
  if (route.path === newPath || (route.name === 'ProductDetail' && currentSlug === slug)) {
    window.location.reload();
  } else {
    router.push(newPath);
  }

  results.value = [];
  q.value = "";
  showSuggestions.value = false;
};

const handleSearch = () => {
  if (q.value.trim() !== '') {
    showSuggestions.value = false;
    router.push({ path: '/tim-kiem', query: { q: q.value } });
  } else {
    Swal.fire({
      icon: 'warning',
      title: 'Thông báo',
      text: 'Vui lòng nhập từ khóa để tìm kiếm sản phẩm!',
      confirmButtonColor: '#33ccff'
    });
  }
};

const format = (n) => new Intl.NumberFormat("vi-VN").format(n || 0);

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
  const searchBoxWrapper = document.querySelector(".search-box-wrapper");
  
  if (userMenuWrapper && !userMenuWrapper.contains(event.target) && showUserMenu.value) {
    showUserMenu.value = false;
  }

  if (searchBoxWrapper && !searchBoxWrapper.contains(event.target) && showSuggestions.value) {
    showSuggestions.value = false;
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

const goToCart = () => {
  if (!isLoggedIn.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Bạn chưa đăng nhập',
      text: 'Vui lòng đăng nhập để xem giỏ hàng!',
      confirmButtonColor: '#03A2DC'
    });
    return;
  }
  router.push("/cart");
};

onMounted(() => {
  checkLoginStatus();
  fetchStaticPages();
  document.addEventListener("click", closeUserMenuOnClickOutside);
  document.addEventListener('click', closeMobileMenuOnClickOutside);
});

onUnmounted(() => {
  document.removeEventListener("click", closeUserMenuOnClickOutside);
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

/* --- Search Box và Gợi ý --- */
.search-box-wrapper {
  position: relative;
  flex-grow: 1;
  max-width: 600px;
}

.search-box {
  display: flex;
  width: 100%;
  border-radius: 20px;
  border: 1px solid #33ccff;
  overflow: hidden;
}

.search-box input {
  padding: 8px 20px;
  width: 100%;
  border: none;
  outline: none;
  font-size: 14px;
}

.search-box input::placeholder {
  color: #aaa;
}

.search-box button {
  background-color: #33ccff;
  color: white;
  border: none;
  padding: 8px 16px;
  cursor: pointer;
  transition: background-color 0.2s ease;
  font-size: 16px;
}

.search-box button:hover {
  background-color: #33ccff;
}

/* Suggestions dropdown */
.suggestions {
  position: absolute;
  top: 100%;
  left: 0;
  width: 100%;
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  margin-top: 4px;
  list-style: none;
  padding: 4px 0;
  max-height: 300px;
  overflow-y: auto;
  z-index: 1000;
}

.suggestions li {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  cursor: pointer;
  transition: background 0.2s ease;
}

.suggestions li:hover {
  background: #f0f8ff;
}

.suggestions img {
  width: 40px;
  height: 40px;
  border-radius: 6px;
  object-fit: cover;
  flex-shrink: 0;
  border: 1px solid #eee;
}

.suggestion-info {
  display: flex;
  flex-direction: column;
  font-size: 14px;
  line-height: 1.3;
}

.suggestion-info strong {
  font-weight: 600;
  color: #333;
  margin-bottom: 2px;
}

.suggestion-info span {
  font-size: 13px;
  color: #888;
}


/* --- Phần CSS cũ đã được gộp lại --- */
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
  color: #33ccff;
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
  color: #33ccff;
}

.top-bar .country-selector img.flag {
  width: 30px;
  height: 20px;
  object-fit: cover;
  border-radius: 3px;
  vertical-align: middle;
}

.main-nav {
  background-color: #33ccff;
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
  color: white;
}

.main-nav ul li a.router-link-active,
.main-nav ul li a.router-link-exact-active {
  color: white;
  font-weight: bold;
  cursor: default;
}

.main-nav ul li a.router-link-active:hover,
.main-nav ul li a.router-link-exact-active:hover {
  color: white;
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
    background-color: #33ccff;
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

  .menu-close {
    display: none;
  }
}

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
  color: #33ccff;
}

.user-info strong {
  color: #33ccff;
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
  color: #33ccff;
}

.user-info .fa-caret-down {
  margin-left: 5px;
  transition: transform 0.2s ease;
}

.user-info.active .fa-caret-down {
  transform: rotate(180deg);
}

.header-right .user-info span strong {
  color: #33ccff;
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