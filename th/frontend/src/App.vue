<template>
  <div id="app">
    <header class="top-bar" v-if="!isAdminRoute">
      <div class="container">
        <div class="logo-search">
          <img src="/favicon.png" alt="Logo 404" class="logo-404">
          <div class="search-box">
            <input type="text" placeholder="Tìm kiếm theo tên sản phẩm ...">
            <button type="submit"><i class="fas fa-search"></i></button>
          </div>
        </div>
        <div class="header-right">
          <div class="country-selector">
            <img src="/logovn.png" alt="Vietnam Flag" class="flag">
            <span>Việt Nam</span>
            <i class="fas fa-caret-down"></i>
          </div>
          <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span>Đăng Nhập</span>
            <i class="fas fa-caret-down"></i>
          </div>
          <div class="cart-info">
            <i class="fas fa-shopping-cart"></i>
            <span>Giỏ hàng</span>
          </div>
          <div class="notification">
            <i class="fas fa-bell"></i>
            <span>Thông báo</span>
          </div>
        </div>
      </div>
    </header>

    <nav class="main-nav" v-if="!isAdminRoute">
      <div class="container">
        <ul>
          <li><a href="#"><i class="fas fa-bars"></i> Danh mục sản phẩm</a></li>
          <li><a href="#">Trang Chủ</a></li>
          <li><a href="#">Giới Thiệu</a></li>
          <li><a href="#">Tin Tức</a></li>
          <li><a href="#">Liên Hệ</a></li>
        </ul>
        <div class="contact-info">
          <i class="fas fa-map-marker-alt"></i>
          <span>Quang Trung, HCM</span>
          <i class="fas fa-caret-down"></i>
          <i class="fas fa-phone-alt"></i>
          <span>09091005</span>
          <span>Hotline</span>
        </div>
      </div>
    </nav>

    <router-view />
    <footer class="footer" v-if="!isAdminRoute">
      <div class="container">
        <div class="footer-section footer-about">
          <div class="footer-top">
            <img src="/favicon.png" alt="FOF Mart Online Supermarket Logo" class="footer-logo">
            <div class="footer-info">
              <h3>FOF MART ONLINE Supermarket</h3>
              <p>Copyright 2025</p>
              <p>Chính sách bảo mật</p>
            </div>
          </div>
          <p>FOF Mart Online Supermarket</p>
          <p>Địa chỉ: Công viên phần mềm Quang Trung , <br> Quận 12, Hồ Chí Minh, Việt Nam</p>
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
            <li><a href="#">Facebook:</a></li>
            <li><a href="#">Tik Tok</a></li>
            <li><a href="#">Website</a></li>
          </ul>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const isAdminRoute = computed(() => route.path.startsWith('/admin'))

// FontAwesome CDN link thường được để ở index.html hoặc bạn có thể import thủ công vào đây nếu cần
// import 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css';
</script>

<style>
/* CSS toàn cục cho body, container, header, nav, footer, v.v. */
/* Có thể đặt trong main.css hoặc style.css */
/* Ví dụ: */
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
  margin-right: auto;
  margin-left: auto;
  max-width: 1500px;
}


/* Top Bar */
.top-bar {
  background-color: #f8f9fa;
  padding: 10px 0;
  border-bottom: 1px solid #eee;
}

.top-bar .container {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.top-bar .logo-search {
  display: flex;
  align-items: center;
  gap: 12px;
  max-width: 80%;
}


.top-bar .logo-404 {
  height: 40px;
  width: 60px;
}

.top-bar .search-box {
  display: flex;
  /* border: 1px solid #ddd; */
  border-radius: 5px;
  overflow: hidden;
}

.top-bar .search-box input {
  padding-left: 20px;
  padding-top: 8px;
  padding-bottom: 8px;
  padding-right: 160px;
  text-align: left;
  border: 1px solid #33ccff;
  border-top-left-radius: 20px;
  border-bottom-left-radius: 20px;
}

.top-bar .search-box input:focus {
  border-color: #33ccff;
  outline: none;
}

.search-box input::placeholder {
  text-align: right;
  padding-right: 3800px;
  color: #aaa;
}

.top-bar .search-box button {
  background-color: #33ccff;
  color: white;
  border: none;
  padding: 8px 16px;
  cursor: pointer;
  transition: background-color 0.2s ease;
  border-top-right-radius: 20px;
  border-bottom-right-radius: 20px;
}


.top-bar .search-box button:hover {
  background-color: #00a8e1;
}

.top-bar .header-right {
  display: flex;
  align-items: center;
  gap: 20px;
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
}

.top-bar .country-selector img {
  width: 50px;
}

.top-bar .country-selector img.flag {
  vertical-align: middle;
}

/* Main Nav */
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
  color: #e9ecef;
}

.main-nav .contact-info {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
}

/* Footer */
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

/* .footer-logo {
  width: 50px;
  height: auto;
} */

.footer-info h3,
.footer-info p {
  margin: 0;
  line-height: 1.6;
}

</style>