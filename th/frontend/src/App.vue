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
                                <i class="fas fa-user"></i>
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

          <div class="cart-info"><i class="fas fa-shopping-cart"></i> Giỏ hàng (0)</div>
          <div class="notification"><i class="fas fa-bell"></i> Thông báo</div>
        </div>
      </div>
    </header>

    <nav class="main-nav" v-if="!isAdminRoute">
      <div class="container">
        <ul>
          <li>
            <a href="#"><i class="fas fa-bars"></i> Danh mục sản phẩm</a>
          </li>
          <li><a href="#">Trang Chủ</a></li>
          <li><a href="#">Giới Thiệu</a></li>
          <li><a href="#">Tin Tức</a></li>
          <li><a href="#">Liên Hệ</a></li>
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
import { ref, computed, onMounted, watch, onUnmounted } from "vue"; // Đảm bảo onUnmounted đã được import
import { useRouter } from "vue-router";
import AuthModal from "./components/user/AuthModal.vue";
import Swal from "sweetalert2";

const router = useRouter();

// --- Trạng thái hiển thị Modal đăng nhập ---
const showLoginModal = ref(false);

// --- Trạng thái người dùng và menu thả xuống ---
const isLoggedIn = ref(false);
const userName = ref("");
const userRoleId = ref(null);
const showUserMenu = ref(false);

// --- Computed property để ẩn/hiện header/footer/nav trên trang admin ---
const isAdminRoute = computed(() => {
  return router.currentRoute.value.path.startsWith("/admin");
});

// --- Hàm kiểm tra trạng thái đăng nhập từ localStorage ---
const checkLoginStatus = () => {
  const user = localStorage.getItem("user");
  const roleId = localStorage.getItem("vai_tro_id");

  if (user && roleId) {
    try {
      const parsedUser = JSON.parse(user);
      if (parsedUser && parsedUser.ho_ten) {
        isLoggedIn.value = true;
        userName.value = parsedUser.ho_ten;
        userRoleId.value = parseInt(roleId);
      }
    } catch (e) {
      console.error("Lỗi khi phân tích cú pháp thông tin người dùng từ localStorage:", e);
      localStorage.removeItem("user");
      localStorage.removeItem("vai_tro_id");
      isLoggedIn.value = false;
      userName.value = "";
      userRoleId.value = null;
    }
  } else {
    isLoggedIn.value = false;
    userName.value = "";
    userRoleId.value = null;
  }
};

// --- Hàm xử lý đăng xuất ---
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
      // Xóa dữ liệu đăng nhập
      localStorage.removeItem("token");
      localStorage.removeItem("user");
      localStorage.removeItem("vai_tro_id");

      // Cập nhật trạng thái đăng nhập
      isLoggedIn.value = false;
      userName.value = "";
      userRoleId.value = null;
      showUserMenu.value = false;

      // Điều hướng về trang chủ
      router.push("/");

      // Thông báo đã đăng xuất
      Swal.fire({
        toast: true,
        position: "top-end", // Góc trên bên phải
        icon: "success",
        title: "Đã đăng xuất!",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
      });
    }
  });
};

// --- Hàm chuyển hướng đến trang quản lý ---
const navigateToAdmin = () => {
  showUserMenu.value = false; // Đóng menu trước khi chuyển hướng
  router.push("/admin");
};

// --- HÀM MỚI: Chuyển hướng đến trang thông tin người dùng ---
const navigateToUserInfo = () => {
  showUserMenu.value = false; // Đóng menu trước khi chuyển hướng
  router.push("/user"); // Giả định bạn có một route '/user-info' cho trang thông tin
};

// --- Hàm bật/tắt menu thả xuống của người dùng ---
const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value;
};

// --- Đóng menu khi click ra ngoài ---
const closeUserMenuOnClickOutside = (event) => {
  const userMenuWrapper = document.querySelector(".user-menu-wrapper");
  if (userMenuWrapper && !userMenuWrapper.contains(event.target) && showUserMenu.value) {
    showUserMenu.value = false;
  }
};

// --- Lifecycle Hook: Kiểm tra trạng thái đăng nhập khi component được mount ---
onMounted(() => {
  checkLoginStatus();
  // Thêm event listener để đóng menu khi click ra ngoài
  document.addEventListener("click", closeUserMenuOnClickOutside);
});

// --- Lifecycle Hook: Xóa event listener khi component bị unmount ---
onUnmounted(() => {
  document.removeEventListener("click", closeUserMenuOnClickOutside);
});

// --- Watcher: Kiểm tra lại trạng thái đăng nhập khi modal đóng ---
watch(showLoginModal, (newVal) => {
  if (!newVal) {
    // Nếu modal vừa đóng (newVal là false)
    checkLoginStatus(); // Kiểm tra lại trạng thái đăng nhập
  }
});
</script>
<style>
/* Global styles (cơ bản) */
body {
  margin: 0;
  font-family: Arial, sans-serif;
  background-color: #f4f6f8;
  color: #333;
}

.container {
  width: 100%;
  padding-right: 12px;
  padding-left: 12px;
  margin-right: 100px;
  /* margin-left: 100px; */
  /* max-width: 1500px;  */
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
  /* Khoảng cách giữa các phần tử chính trong top-bar */
}

.top-bar .logo-search {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-grow: 1;
  /* Cho phép nó mở rộng để chiếm không gian */
  margin-right: 20px;
}

.logo-search img {
  box-shadow: none;
}

.top-bar .logo-404 {
  height: 50px;
  /* Kích thước logo */
  width: auto;
  flex-shrink: 0;
  /* Ngăn logo bị co lại */
}

.top-bar .search-box {
  display: flex;
  width: 100%;
  max-width: 600px;
  /* Chiều rộng tối đa cho thanh tìm kiếm */
  border-radius: 20px;
  /* Bo tròn góc */
  overflow: hidden;
  border: 1px solid #33ccff;
  /* Viền xanh */
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
  background-color: #33ccff;
  color: white;
  border: none;
  padding: 8px 16px;
  cursor: pointer;
  transition: background-color 0.2s ease;
  font-size: 16px;
}

.top-bar .search-box button:hover {
  background-color: #00a8e1;
}

.top-bar .header-right {
  display: flex;
  align-items: center;
  gap: 20px;
  /* Khoảng cách giữa các icon/thông tin người dùng */
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
  /* Ngăn chữ bị xuống dòng */
  transition: color 0.2s ease;
}

.top-bar .user-info:hover,
.top-bar .cart-info:hover,
.top-bar .notification:hover,
.top-bar .country-selector:hover {
  color: #33ccff;
  /* Đổi màu khi hover */
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
  /* Khoảng cách giữa các mục menu */
}

.main-nav ul li a {
  color: white;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s ease;
  display: flex;
  align-items: center;
  gap: 5px;
  /* Khoảng cách giữa icon và chữ */
}

.main-nav ul li a:hover {
  color: #e9ecef;
  /* Đổi màu khi hover */
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

/* --- Footer (CSS ví dụ cho footer, bạn có thể điều chỉnh) --- */
.footer {
    background-color: #343a40;
    color: #f8f9fa;
    padding: 40px 0;
    
}

.footer .container {
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
  /* Cho phép các phần tử xuống dòng trên màn hình nhỏ */
  gap: 30px;
}

.footer-section {
  flex: 1 1 250px;
  /* Mỗi phần chiếm ít nhất 250px, có thể co giãn */
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

/* --- Responsive adjustments for Header/Nav (ví dụ) --- */
@media (max-width: 992px) {
  .top-bar .logo-search {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
    margin-right: 10px;
  }

  .top-bar .search-box {
    max-width: none;
    /* Cho phép tìm kiếm chiếm toàn bộ chiều rộng có sẵn */
  }

  .main-nav .container {
    flex-direction: column;
    gap: 15px;
  }

  .main-nav ul {
    flex-wrap: wrap;
    justify-content: center;
  }

  .main-nav .contact-info {
    display: none;
    /* Có thể ẩn thông tin liên hệ trên màn hình nhỏ */
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
    justify-content: center;
    gap: 15px;
  }

  .top-bar .country-selector {
    display: none;
  }
}

.header-right .user-info {
  /* Để giữ khoảng cách và căn chỉnh */
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
  color: #007bff;
  /* Màu sắc nổi bật cho tên người dùng */
  font-weight: bold;
}

.user-menu-wrapper {
  position: relative;
  /* Quan trọng để menu con định vị tương đối */
  display: flex;
  align-items: center;
  /* Giữ các phần tử của wrapper trên cùng một hàng */
}

.user-dropdown-menu {
  position: absolute;
  top: 100%;
  /* Đặt menu ngay bên dưới user-info */
  right: 0;
  /* Căn phải với user-info */
  background-color: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  min-width: 150px;
  z-index: 100;
  /* Đảm bảo menu hiển thị trên các nội dung khác */
  overflow: hidden;
  /* Để bo góc của ul */
  padding: 8px 0;
  /* Đệm trên dưới cho các item menu */
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
  /* Ngăn chữ bị xuống dòng */
}

.user-dropdown-menu ul li:hover {
  background-color: #f0f0f0;
  color: #33ccff;
}

/* Điều chỉnh lại icon mũi tên trong user-info để nó xoay khi menu mở */
.user-info .fa-caret-down {
  margin-left: 5px;
  transition: transform 0.2s ease;
}

.user-info.active .fa-caret-down {
  transform: rotate(180deg);
  /* Xoay mũi tên khi menu mở */
}

/* Các điều chỉnh khác cho user-info khi đăng nhập */
.header-right .user-info span strong {
  color: #007bff;
  /* Màu sắc nổi bật cho tên người dùng */
  font-weight: bold;
}
.layout-wrapper {
  display: flex;
  flex-direction: column;
  min-height: 150vh;
}

.page-content {
  flex: 1;
}
</style>
