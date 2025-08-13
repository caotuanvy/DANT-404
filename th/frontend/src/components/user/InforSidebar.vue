<template>
    <div class="account-layout container">
        <button class="mobile-menu-btn" @click="toggleSidebar">
            <i class="fas fa-bars"></i>
        </button>

        <div class="sidebar-overlay" :class="{ 'is-open': isSidebarOpen }" @click="closeSidebar"></div>

        <aside class="sidebar" :class="{ 'is-open': isSidebarOpen }">
            <button class="close-sidebar-btn" @click="closeSidebar">
                <i class="fas fa-times"></i>
            </button>
            <div class="sidebar-header">
                <span class="user-name">Chào <b>{{ userName }}</b></span>
            </div>
            <ul>
                <li :class="{ 'active': $route.path === '/user/profile' || $route.path === '/user' }">
                    <router-link to="/user/profile" @click="closeSidebar">
                        <i class="fas fa-info-circle"></i> Thông tin và địa chỉ
                    </router-link>
                </li>
                <li :class="{ 'active': $route.path === '/user/orders' }">
                    <router-link to="/user/orders" @click="closeSidebar">
                        <i class="fas fa-clipboard-list"></i> Đơn hàng đã mua
                    </router-link>
                </li>
                <li :class="{ 'active': $route.path === '/user/change-password' }">
                    <router-link to="/user/change-password" @click="closeSidebar">
                        <i class="fas fa-lock"></i> Thay đổi mật khẩu
                    </router-link>
                </li>
            </ul>
            <button class="logout-btn" @click="handleLogoutAndClose">Đăng Xuất</button>
        </aside>

        <main class="main-content">
            <router-view />
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import Swal from 'sweetalert2';

const userName = ref('Khách');
const router = useRouter();
const route = useRoute();
const isSidebarOpen = ref(false);

const fetchUserName = () => {
    const storedUser = localStorage.getItem('user');
    if (storedUser) {
        try {
            const user = JSON.parse(storedUser);
            if (user && user.ho_ten) {
                userName.value = user.ho_ten;
            }
        } catch (e) {
            console.error("Failed to parse user from localStorage:", e);
            userName.value = 'Guest';
        }
    }
};

onMounted(() => {
    fetchUserName();
});

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const closeSidebar = () => {
    isSidebarOpen.value = false;
};

const handleLogoutAndClose = () => {
    closeSidebar();
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
            localStorage.removeItem('user');
            localStorage.removeItem('token');
            router.push('/');
            Swal.fire(
                'Đã đăng xuất!',
                'Bạn đã đăng xuất thành công.',
                'success'
            );
        }
    });
};
</script>

<style scoped>
/* --- GLOBAL CSS VARIABLES --- */
:root {
    --primary-blue: #007bff;
    --dark-blue: #0056b3;
    --light-grey: #f8f9fa;
    --border-color: #dee2e6;
    --text-color: #333;
    --dark-text: #212529;
    --error-color: #dc3545;
    --sidebar-bg: #f4f6f8;
    --main-content-bg: #fff;
}

/* --- BASIC PAGE STYLES --- */
body {
    font-family: 'Roboto', sans-serif;
    line-height: 1.6;
    color: var(--text-color);
    background-color: var(--light-grey);
    margin: 0;
    padding: 0;
}

.account-layout {
    display: flex;
    gap: 25px;
    align-items: flex-start;
}

.container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 15px;
}

/* --- SIDEBAR --- */
.sidebar {
    background-color: var(--sidebar-bg);
    padding: 20px;
    border-radius: 8px;
    min-width: 250px;
    color: black;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease-in-out;
}

.sidebar-header {
    display: flex;
    align-items: center;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 20px;
}

.sidebar-header .user-name {
    font-weight: 500;
    color: var(--dark-text);
    font-size: 18px;
}

.sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar ul li {
    margin-bottom: 8px;
}

.sidebar ul li a {
    text-decoration: none;
    color: black;
    font-size: 16px;
    display: flex;
    align-items: center;
    padding: 12px;
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
}

.sidebar .logout-btn {
    display: block;
    width: 100%;
    padding: 12px 15px;
    background-color: #f8f9fa;
    color: var(--primary-blue);
    border: 1px solid var(--primary-blue);
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    margin-top: 25px;
    transition: all 0.3s ease;
}

.sidebar .logout-btn:hover {
    background-color: var(--primary-blue);
    color: #ffffff;
    border-color: var(--primary-blue);
}

/* --- MAIN CONTENT AREA --- */
.main-content {
    flex-grow: 1;
    background-color: var(--main-content-bg);
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

/* Các nút điều khiển sidebar trên di động */
.mobile-menu-btn, .close-sidebar-btn {
    display: none;
    position: fixed;
    z-index: 101;
    cursor: pointer;
    background-color: var(--primary-blue);
    color: white;
    border: none;
    border-radius: 50%;
    width: 45px;
    height: 45px;
    font-size: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    transition: background-color 0.2s;
}

.mobile-menu-btn {
    top: 20px;
    left: 20px;
}

.close-sidebar-btn {
    top: 15px;
    right: 15px;
    background-color: transparent;
    color: var(--text-color);
    box-shadow: none;
}

.sidebar-overlay {
    display: none;
}

/* --- RESPONSIVE DESIGN --- */
@media (max-width: 992px) {
    .account-layout {
        flex-direction: column;
        gap: 20px;
    }
    
    .sidebar {
        min-width: unset;
        width: 100%;
    }

    .sidebar ul {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
    }

    .sidebar ul li a {
        padding: 8px 15px;
    }

    .sidebar .logout-btn {
        margin-top: 20px;
    }

    .main-content {
        padding: 25px;
    }
}

@media (max-width: 768px) {
    .container {
        margin: 20px auto;
    }

    .account-layout {
        flex-direction: column;
        gap: 20px;
        position: relative;
    }

    /* Hiển thị nút menu trên di động */
    .mobile-menu-btn {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Ẩn sidebar theo mặc định trên di động */
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 280px;
        padding-top: 60px;
        border-radius: 0;
        z-index: 100;
        transform: translateX(-100%);
    }

    /* Hiển thị sidebar khi có class 'is-open' */
    .sidebar.is-open {
        transform: translateX(0);
    }

    .sidebar-header, .sidebar ul, .sidebar .logout-btn {
        padding: 0 20px;
    }

    .sidebar-header {
        margin-bottom: 20px;
    }

    .sidebar ul {
        display: block;
    }

    .sidebar ul li a {
        font-size: 18px;
    }

    .sidebar .logout-btn {
        position: absolute;
        bottom: 20px;
        left: 20px;
        width: calc(100% - 40px);
    }

    /* Hiển thị nút đóng sidebar */
    .close-sidebar-btn {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Hiển thị lớp overlay */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 99;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .sidebar-overlay.is-open {
        display: block;
        opacity: 1;
    }

    .main-content {
        padding: 20px;
    }

    .main-content h1 {
        font-size: 20px;
    }

    .main-content h2 {
        font-size: 16px;
    }
}
</style>