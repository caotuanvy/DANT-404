<template>
    <div class="content-area container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <span class="user-name">Anh <b>{{ userName }}</b></span>
            </div>
            <ul>
                <li :class="{ 'active': $route.path === '/user/profile' || $route.path === '/user' }">
                    <router-link to="/user/profile">
                        <i class="fas fa-info-circle"></i> Thông tin và địa chỉ
                    </router-link>
                </li>
                <li :class="{ 'active': $route.path === '/user/orders' }">
                    <router-link to="/user/orders">
                        <i class="fas fa-clipboard-list"></i> Đơn hàng đã mua
                    </router-link>
                </li>
                <li :class="{ 'active': $route.path === '/user/change-password' }">
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
import { ref, onMounted } from 'vue'; // Import onMounted
import { useRouter, useRoute } from 'vue-router'; // Import useRoute
import Swal from 'sweetalert2'; // Nếu bạn muốn dùng SweetAlert2 cho logout

const userName = ref('Khách');
const router = useRouter();
const route = useRoute(); // Để kiểm tra đường dẫn hiện tại cho class 'active'

// Hàm để lấy tên người dùng từ localStorage
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
            userName.value = 'Guest'; // Fallback
        }
    }
};

// Gọi khi component được mount để hiển thị tên người dùng
onMounted(() => {
    fetchUserName();
});


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
            localStorage.removeItem('user');
            localStorage.removeItem('token'); // Đảm bảo xóa cả token
            router.push('/'); // Chuyển hướng về trang chủ hoặc trang đăng nhập
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
/* Bạn hãy đặt các CSS styles cho sidebar và layout tại đây */
/* Ví dụ từ các đoạn mã trước đó của bạn */
.content-area {
    display: flex;
    gap: 25px;
    padding: 30px 0;
    align-items: flex-start;
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
    color: #007bff;
    border: 1px solid #007bff;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    margin-top: 25px;
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

.sidebar .logout-btn:hover {
    background-color: #007bff;
    color: #ffffff;
    border-color: #007bff;
}

.sidebar .logout-btn:active {
    background-color: #0056b3;
    border-color: #0056b3;
    color: #ffffff;
}

.main-content {
    flex-grow: 1;
    background-color: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

/* Các biến CSS toàn cục nếu bạn đã định nghĩa ở đâu đó */
/* :root {
    --primary-blue: #007bff;
    --dark-blue: #0056b3;
    --light-grey: #f8f9fa;
    --border-color: #dee2e6;
    --text-color: #333;
    --dark-text: #212529;
} */
</style>