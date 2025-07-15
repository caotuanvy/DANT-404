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
          <div class="search-container"> <input 
              type="search" 
              class="search-input" 
              placeholder="Tìm kiếm chức năng..." 
              v-model="searchTerm" 
              @input="handleSearchInput"
              @focus="showSearchResults = true"
              @blur="hideSearchResultsDelayed"
              @keydown.enter="goToFirstSearchResult"
              @keydown.up.prevent="navigateSearchResults('up')"
              @keydown.down.prevent="navigateSearchResults('down')"
            />
            <div class="search-results-dropdown" v-if="showSearchResults && filteredMenuItems.length">
              <ul class="search-results-list">
                <li 
                  v-for="(item, index) in filteredMenuItems" 
                  :key="item.to" 
                  :class="{ 'active': index === selectedSearchResultIndex }"
                  @mousedown.prevent="selectSearchResult(item.to)" >
                  <i :class="[item.icon, 'search-result-icon']"></i>
                  <span>{{ item.label }}</span>
                </li>
              </ul>
            </div>
            <div class="search-results-dropdown no-results" v-if="showSearchResults && searchTerm && !filteredMenuItems.length">
                Không tìm thấy kết quả phù hợp.
            </div>
          </div>
        </div>

        <div class="header-right">
          <span class="version-text">VERSION 1.5.4</span>

          <div class="notification-badge">
            <i class="bi bi-bell"></i>
            <span class="badge">3</span>
          </div>

          <div class="user-menu" @click="openAdminInfoModal">
            <img :src="logo" alt="Admin" class="admin-avatar" />
            <div class="user-info">
              <span class="admin-name">{{ adminInfo ? adminInfo.name : 'Administrator' }}</span>
              <span class="admin-role">{{ adminInfo ? adminInfo.role : 'Super Admin' }}</span>
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

      <div class="modal-overlay" v-if="showAdminInfoModal" @click.self="closeAdminInfoModal">
        <div class="admin-info-modal">
          <div class="modal-header">
            <h3>Thông tin tài khoản</h3>
            <button class="close-button" @click="closeAdminInfoModal">&times;</button>
          </div>
          <div class="modal-body">
            <div v-if="adminInfo">
              <div class="info-item">
                <strong>ID người dùng:</strong> <span>{{ adminInfo.id }}</span>
              </div>
              <div class="info-item">
                <strong>Tên đăng nhập:</strong> <span>{{ adminInfo.name }}</span>
              </div>
              <div class="info-item">
                <strong>Email:</strong> <span>{{ adminInfo.email }}</span>
              </div>
              <div class="info-item">
                <strong>Số điện thoại:</strong> <span>{{ adminInfo.phone }}</span>
              </div>
              <div class="info-item">
                <strong>Vai trò:</strong> <span>{{ adminInfo.role }}</span>
              </div>
              </div>
            <div v-else>
              <p>Đang tải thông tin hoặc không có thông tin người dùng.</p>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-primary" @click="goToProfile">Xem hồ sơ</button>
            <button class="btn btn-danger" @click="logoutAndCloseModal">Đăng xuất</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'; // Import computed
import { useRouter } from 'vue-router';
import logo from '@/assets/images/icon-admin.png';
import logo1 from '@/assets/images/image60.png';

const showAdminInfoModal = ref(false);
const adminInfo = ref(null);

const router = useRouter();

const openSection = ref('Quản lý sản phẩm');

const toggleSection = (sectionLabel) => {
  if (openSection.value === sectionLabel) {
    openSection.value = null;
  } else {
    openSection.value = sectionLabel;
  }
};

const loadLoggedInAdminInfo = () => {
  const storedUser = localStorage.getItem('user');
  if (storedUser) {
    try {
      const user = JSON.parse(storedUser);
      adminInfo.value = {
        id: user.nguoi_dung_id,
        name: user.ho_ten || 'Administrator',
        email: user.email || 'admin@example.com',
        phone: user.sdt || 'Chưa có số điện thoại',
        role: user.vai_tro || 'Super Admin',
      };
    } catch (e) {
      console.error('Lỗi khi phân tích JSON từ localStorage:', e);
      adminInfo.value = null;
      localStorage.removeItem('user');
    }
  } else {
    console.warn('Không tìm thấy thông tin người dùng trong localStorage.');
    adminInfo.value = null;
  }
};

const openAdminInfoModal = () => {
  showAdminInfoModal.value = true;
  loadLoggedInAdminInfo();
};

const closeAdminInfoModal = () => {
  showAdminInfoModal.value = false;
};

const logout = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  console.log('Người dùng đã đăng xuất');
  alert('Bạn đã đăng xuất thành công!');
  router.push('/login');
};

const logoutAndCloseModal = () => {
  logout();
  closeAdminInfoModal();
};

const goToProfile = () => {
  router.push('/admin/profile');
  closeAdminInfoModal();
};

const goBack = () => {
  router.back();
};

// --- LOGIC TÌM KIẾM CHỨC NĂNG MỚI ---
const searchTerm = ref(''); // Biến lưu trữ giá trị tìm kiếm
const showSearchResults = ref(false); // Biến kiểm soát hiển thị kết quả
const selectedSearchResultIndex = ref(-1); // Index của kết quả được chọn (dùng cho phím mũi tên)

// Danh sách menu (giữ nguyên, nhưng tôi sẽ đưa ra ngoài để tiện sử dụng cho tìm kiếm)
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
      { to: '/admin/binhluan', label: 'Bình Luận', icon: 'bi bi-file-earmark-text' },
      { to: '/admin/video', label: 'Video', icon: 'bi bi-camera-video' },
      { to: '/admin/payment', label: 'Hình thức thanh toán', icon: 'bi bi-cash' },
      { to: '/admin/send-push', label: 'Thông báo đẩy', icon: 'bi bi-bell' },
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

// Computed property để làm phẳng danh sách menu và lọc kết quả
const allMenuItems = computed(() => {
  const items = [];
  menu.forEach(section => {
    section.items.forEach(item => {
      // Thêm label của section vào để tìm kiếm cả theo tên danh mục lớn
      items.push({
        ...item,
        searchableLabel: `${section.label} > ${item.label}` // VD: "Quản lý sản phẩm > Sản phẩm"
      });
    });
  });
  return items;
});

const filteredMenuItems = computed(() => {
  if (!searchTerm.value) {
    return []; // Không có từ khóa tìm kiếm thì không hiển thị gì
  }
  const lowerCaseSearchTerm = searchTerm.value.toLowerCase();
  return allMenuItems.value.filter(item =>
    item.searchableLabel.toLowerCase().includes(lowerCaseSearchTerm)
  );
});

// Xử lý khi input thay đổi
const handleSearchInput = () => {
  showSearchResults.value = true;
  selectedSearchResultIndex.value = -1; // Reset selection
};

// Ẩn kết quả tìm kiếm sau một khoảng trễ (để click vào kết quả không bị mất)
let blurTimeout;
const hideSearchResultsDelayed = () => {
  blurTimeout = setTimeout(() => {
    showSearchResults.value = false;
    searchTerm.value = ''; // Xóa nội dung tìm kiếm khi ẩn
    selectedSearchResultIndex.value = -1;
  }, 150); // Khoảng thời gian đủ để người dùng click
};

// Chọn kết quả tìm kiếm và điều hướng
const selectSearchResult = (to) => {
  clearTimeout(blurTimeout); // Hủy bỏ việc ẩn nếu người dùng click
  router.push(to);
  showSearchResults.value = false;
  searchTerm.value = ''; // Xóa nội dung tìm kiếm sau khi điều hướng
  selectedSearchResultIndex.value = -1;
};

// Điều hướng bằng phím Enter (khi có kết quả được chọn)
const goToFirstSearchResult = () => {
  if (selectedSearchResultIndex.value !== -1 && filteredMenuItems.value[selectedSearchResultIndex.value]) {
    selectSearchResult(filteredMenuItems.value[selectedSearchResultIndex.value].to);
  } else if (filteredMenuItems.value.length > 0) { // Nếu không có cái nào được chọn, chọn cái đầu tiên
    selectSearchResult(filteredMenuItems.value[0].to);
  }
};

// Điều hướng kết quả bằng phím mũi tên
const navigateSearchResults = (direction) => {
  if (!filteredMenuItems.value.length) return;

  if (direction === 'down') {
    selectedSearchResultIndex.value = (selectedSearchResultIndex.value + 1) % filteredMenuItems.value.length;
  } else if (direction === 'up') {
    selectedSearchResultIndex.value = (selectedSearchResultIndex.value - 1 + filteredMenuItems.value.length) % filteredMenuItems.value.length;
  }

  // Cuộn đến phần tử được chọn (nếu cần) - cần DOM manipulation
  const activeItem = document.querySelector('.search-results-list li.active');
  if (activeItem) {
    activeItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
};

onMounted(() => {
  loadLoggedInAdminInfo(); // Tải thông tin admin khi component được mount
});
</script>
<style scoped>

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
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5); /* Nền tối mờ */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 10000; /* Đảm bảo modal nằm trên tất cả các phần tử khác */
}

.admin-info-modal {
  background-color: #ffffff;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  width: 90%; /* Chiều rộng tương đối */
  max-width: 400px; /* Chiều rộng tối đa */
  overflow: hidden; /* Đảm bảo nội dung không tràn ra ngoài */
  animation: fadeInScale 0.3s ease-out; /* Hiệu ứng mở */
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #eee;
  background-color: #f7f7f7;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.2rem;
  color: #333;
}

.close-button {
  background: none;
  border: none;
  font-size: 1.8rem;
  color: #888;
  cursor: pointer;
  padding: 0 5px;
  line-height: 1;
}

.close-button:hover {
  color: #333;
}

.modal-body {
  padding: 20px;
  font-size: 14px;
  line-height: 1.6;
  color: #555;
}

.info-item {
  margin-bottom: 10px;
}

.info-item strong {
  color: #333;
  display: inline-block;
  min-width: 120px; /* Căn chỉnh cho đẹp */
}

.modal-footer {
  padding: 15px 20px;
  border-top: 1px solid #eee;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

/* Nút Bootstrap (nếu bạn đang dùng Bootstrap CSS) */
.btn {
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
}

.btn-primary {
  background-color: #0d6efd;
  color: white;
  border: 1px solid #0d6efd;
}

.btn-primary:hover {
  background-color: #0b5ed7;
  border-color: #0a58ca;
}

.btn-danger {
  background-color: #dc3545;
  color: white;
  border: 1px solid #dc3545;
}

.btn-danger:hover {
  background-color: #bb2d3b;
  border-color: #b02a37;
}


@keyframes fadeInScale {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
.search-container {
  position: relative;
  width: 300px; /* Điều chỉnh chiều rộng theo ý muốn */
  margin-left: 20px; /* Khoảng cách từ icon menu-toggle */
}

.search-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.search-input:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.search-results-dropdown {
  position: absolute;
  top: 100%; /* Đặt ngay bên dưới input */
  left: 0;
  width: 100%;
  background-color: #fff;
  border: 1px solid #ddd;
  border-radius: 5px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  z-index: 100; /* Đảm bảo nó hiển thị trên các phần khác */
  max-height: 300px; /* Giới hạn chiều cao và thêm cuộn */
  overflow-y: auto;
  margin-top: 5px; /* Khoảng cách giữa input và dropdown */
}

.search-results-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.search-results-list li {
  padding: 10px 15px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 10px; /* Khoảng cách giữa icon và text */
  transition: background-color 0.2s ease;
  color: #333;
}

.search-results-list li:hover,
.search-results-list li.active { /* Style cho item được chọn bằng phím mũi tên */
  background-color: #f0f0f0;
  color: #007bff;
}

.search-results-list li.active {
    font-weight: bold;
}

.search-results-list li span {
  flex-grow: 1; 
}

.search-results-list .search-result-icon {
  font-size: 16px;
  color: #666;
}

.search-results-list li.active .search-result-icon {
    color: #007bff;
}

.search-results-dropdown.no-results {
  padding: 10px 15px;
  color: #888;
  font-style: italic;
  text-align: center;
}
</style>