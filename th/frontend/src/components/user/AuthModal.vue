<template>
    <div v-if="show" class="login-modal-overlay" @click.self="closeModal">
        <div class="login-modal-content">
            <span class="close-button" @click="closeModal">&times;</span>
            <div class="login-left">
                <img src="/imglogin.png" alt="Login Illustration">
            </div>
            <div class="login-right">
                <div class="modal-header">
                    <h2 v-if="currentView === 'login'">Đăng nhập</h2>
                    <h2 v-else-if="currentView === 'register'">Đăng ký tài khoản</h2>
                    <h2 v-else>Quên mật khẩu?</h2>
                    <p class="modal-description" v-if="currentView === 'login'">Đăng nhập để mở khóa nhiều chức
                        năng<br>Dễ dàng tìm kiếm nhạc cụ mong muốn</p>
                    <p class="modal-description" v-else-if="currentView === 'register'">Đăng ký để khám phá thêm và nhận
                        thông báo mới nhất</p>
                    <p class="modal-description" v-else>Nhập email của bạn để nhận hướng dẫn khôi phục mật khẩu.</p>
                </div>

                <template v-if="currentView !== 'forgot-password'">
                    <div class="social-login-buttons">
                        <button class="social-btn google-btn">
                            <img src="https://img.icons8.com/color/16/000000/google-logo.png" alt="Google icon">
                            Continue with Google
                        </button>
                        <button class="social-btn facebook-btn">
                            <i class="fab fa-facebook-f"></i>
                            Continue with Facebook
                        </button>
                    </div>
                    <div class="divider">
                        <span v-if="currentView === 'login'">Hoặc đăng nhập với</span>
                        <span v-else-if="currentView === 'register'">Hoặc đăng ký với</span>
                    </div>
                </template>
                <template v-else>
                    <div class="divider">
                        <span>Nhập email của bạn</span>
                    </div>
                </template>

                <form @submit.prevent="handleLogin" v-if="currentView === 'login'">
                    <div class="form-group input-with-icon">
                        <i class="far fa-envelope"></i>
                        <input type="email" id="email" placeholder="Nhập email của bạn" required
                            v-model="loginForm.email">
                    </div>
                    <div class="form-group input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" placeholder="Nhập mật khẩu của bạn" required
                            v-model="loginForm.password">
                    </div>
                    <button type="submit" class="login-btn">Đăng nhập</button>
                    <p style="color: red" v-if="loginError">{{ loginError }}</p>
                    <p class="form-footer">
                        Bạn chưa có tài khoản? <a href="#" @click.prevent="changeView('register')">Đăng ký</a> <span
                            class="footer-separator">|</span> <a href="#"
                            @click.prevent="changeView('forgot-password')">Quên mật
                            khẩu?</a>
                    </p>
                </form>

                <form @submit.prevent="handleRegister" v-else-if="currentView === 'register'">
                    <div class="form-group input-with-icon">
                        <i class="far fa-envelope"></i>
                        <input type="email" id="registerEmail" placeholder="Nhập email của bạn" required
                            v-model="registerForm.email">
                    </div>
                    <div class="form-group input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="registerPassword" placeholder="Tạo mật khẩu" required
                            v-model="registerForm.password">
                    </div>
                    <div class="form-group input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirmPassword" placeholder="Xác nhận mật khẩu" required
                            v-model="registerForm.confirmPassword">
                    </div>
                    <button type="submit" class="login-btn">Đăng ký</button>
                    <p style="color: red" v-if="registerError">{{ registerError }}</p>
                    <p class="form-footer">
                        Bạn đã có tài khoản? <a href="#" @click.prevent="changeView('login')">Đăng nhập</a>
                    </p>
                </form>

                <form @submit.prevent="handleForgotPassword" v-else>
                    <div class="form-group input-with-icon">
                        <i class="far fa-envelope"></i>
                        <input type="email" id="forgotPasswordEmail" placeholder="Nhập email của bạn" required
                            v-model="forgotPasswordForm.email">
                    </div>
                    <button type="submit" class="login-btn">Gửi yêu cầu</button>
                    <p style="color: red" v-if="forgotPasswordError">{{ forgotPasswordError }}</p>
                    <p class="form-footer">
                        <a href="#" @click.prevent="changeView('login')">Quay lại Đăng nhập</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
import { ref, reactive, watch } from 'vue'; // Import watch
import { useRouter } from 'vue-router';

const router = useRouter();

// Props để kiểm soát việc hiển thị modal từ component cha (ví dụ: App.vue)
const props = defineProps({
    show: {
        type: Boolean,
        default: false
    }
});

// Emit sự kiện để thông báo cho component cha khi modal muốn đóng
const emit = defineEmits(['update:show']);

// Trạng thái hiện tại của form: 'login', 'register' hoặc 'forgot-password'
const currentView = ref('login');

// Dữ liệu cho form Đăng nhập
const loginForm = reactive({
    email: '',
    password: '' // Đổi từ mat_khau thành password để nhất quán
});
const loginError = ref('');

// Dữ liệu cho form Đăng ký
const registerForm = reactive({

    email: '',
    //   sdt: '',
    password: '', // Đổi từ mat_khau thành password
    confirmPassword: '' // Đổi từ mat_khau_confirmation thành confirmPassword
});
const registerError = ref('');

// Dữ liệu cho form Quên mật khẩu
const forgotPasswordForm = reactive({
    email: ''
});
const forgotPasswordError = ref('');


// Watcher để reset form và view khi modal được đóng từ bên ngoài
watch(() => props.show, (newVal) => {
    if (!newVal) { // Nếu modal vừa đóng
        currentView.value = 'login'; // Reset về view đăng nhập
        resetForms(); // Reset dữ liệu form
    }
});

const resetForms = () => {
    loginForm.email = '';
    loginForm.password = '';
    loginError.value = '';

    registerForm.username = '';
    registerForm.email = '';
    registerForm.sdt = '';
    registerForm.password = '';
    registerForm.confirmPassword = '';
    registerError.value = '';

    forgotPasswordForm.email = '';
    forgotPasswordError.value = '';
};

// Hàm để thay đổi giữa các view (login, register, forgot-password)
const changeView = (viewName) => {
    currentView.value = viewName;
    resetForms(); // Reset form mỗi khi chuyển view
};

// Hàm đóng modal
const closeModal = () => {
    emit('update:show', false); // Thông báo cho component cha đóng modal
};

// --- Xử lý API cho Login, Register, Forgot Password ---

const handleLogin = async () => {
    loginError.value = ''; // Reset lỗi trước mỗi lần submit
    try {
        const res = await axios.post('/login', { // Đảm bảo tên trường khớp với API
            email: loginForm.email,
            mat_khau: loginForm.password // Gửi theo tên trường API yêu cầu (mat_khau)
        });

        const user = res.data.user;
        const token = res.data.token;

        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(user));
        localStorage.setItem('vai_tro_id', user.vai_tro_id);

        // Đóng modal sau khi đăng nhập thành công
        closeModal();

        if (user.vai_tro_id === 1) {
            router.push('/');
        } else {
            router.push('/');
        }
    } catch (err) {
        loginError.value = err.response?.data?.message || 'Đăng nhập thất bại. Vui lòng kiểm tra lại email và mật khẩu.';
    }
};

const handleRegister = async () => {
    registerError.value = ''; // Đặt lại lỗi trước mỗi lần gửi

    // Kiểm tra mật khẩu và xác nhận mật khẩu
    if (registerForm.password !== registerForm.confirmPassword) {
        registerError.value = 'Mật khẩu và xác nhận mật khẩu không khớp.';
        return;
    }

    // --- Bắt đầu: Logic để lấy tên người dùng từ email ---
    const atIndex = registerForm.email.indexOf('@');
    if (atIndex > -1) {
        // Lấy phần trước '@' làm ho_ten
        registerForm.ho_ten = registerForm.email.substring(0, atIndex);
    } else {
        // Xử lý trường hợp email không hợp lệ (không có '@')
        // Bạn có thể hiển thị lỗi hoặc đặt một giá trị mặc định
        registerError.value = 'Địa chỉ email không hợp lệ.';
        return;
    }
    // --- Kết thúc: Logic để lấy tên người dùng từ email ---

    try {
        const res = await axios.post('/register', {
            ho_ten: registerForm.ho_ten, // Bây giờ sẽ lấy từ email
            email: registerForm.email,
            sdt: registerForm.sdt || '', // Đặt giá trị mặc định cho sdt nếu không có trường nhập
            mat_khau: registerForm.password,
            mat_khau_confirmation: registerForm.confirmPassword
        });

        localStorage.setItem('token', res.data.token);
        alert('Đăng ký thành công! Vui lòng đăng nhập.');
        changeView('login'); // Chuyển về form đăng nhập sau khi đăng ký thành công

    } catch (err) {
        console.error('Đăng ký thất bại:', err); // Luôn ghi lỗi vào console để gỡ lỗi

        let errorMessage = 'Đăng ký thất bại. Vui lòng thử lại.';

        if (err.response && err.response.data && err.response.data.errors) {
            const errors = err.response.data.errors;

            if (errors.email) {
                if (errors.email.includes('The email has already been taken.')) {
                    errorMessage = 'Email này đã được sử dụng. Vui lòng chọn email khác.';
                } else {
                    errorMessage = 'Email không hợp lệ: ' + errors.email[0];
                }
            } else if (errors.mat_khau) {
                if (errors.mat_khau.includes('The mat khau field must be at least 8 characters.')) {
                    errorMessage = 'Mật khẩu phải có ít nhất 8 ký tự.';
                } else if (errors.mat_khau.includes('The password confirmation does not match.')) {
                    errorMessage = 'Mật khẩu xác nhận không khớp.';
                } else {
                    errorMessage = 'Mật khẩu không hợp lệ: ' + errors.mat_khau[0];
                }
            } else if (errors.sdt) {
                errorMessage = 'Số điện thoại không hợp lệ: ' + errors.sdt[0];
            } else if (errors.ho_ten) {
                errorMessage = 'Họ tên không hợp lệ: ' + errors.ho_ten[0];
            } else {
                const firstErrorKey = Object.keys(errors)[0];
                if (firstErrorKey) {
                    errorMessage = errors[firstErrorKey][0];
                }
            }

        } else if (err.response && err.response.data && err.response.data.message) {
            errorMessage = err.response.data.message;
        }

        registerError.value = errorMessage;
    }
};

const handleForgotPassword = async () => {
    forgotPasswordError.value = ''; // Reset lỗi trước mỗi lần submit
    try {
        // Giả sử API quên mật khẩu nhận email và trả về thông báo
        const res = await axios.post('/forgot-password', { email: forgotPasswordForm.email });
        alert(res.data.message || `Hướng dẫn khôi phục mật khẩu đã được gửi đến ${forgotPasswordForm.email}.`);
        changeView('login'); // Quay lại form đăng nhập
    } catch (err) {
        forgotPasswordError.value = err.response?.data?.message || 'Không thể gửi yêu cầu. Vui lòng kiểm tra lại email.';
    }
};
</script>

<style scoped>
/* Toàn bộ CSS đã được điều chỉnh cho modal, bạn sẽ dán vào đây */
/* Global styles */
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
    flex-grow: 1;
    margin-right: 20px;
}

.top-bar .logo-404 {
    height: 40px;
    width: auto;
    flex-shrink: 0;
}

.top-bar .search-box {
    display: flex;
    width: 100%;
    max-width: 600px;
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid #33ccff;
}

.top-bar .search-box input {
    padding: 8px 20px;
    width: 100%;
    border: none;
    outline: none;
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
}

.top-bar .search-box button:hover {
    background-color: #00a8e1;
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
}

.top-bar .country-selector img.flag {
    width: 30px;
    height: 20px;
    object-fit: cover;
    border-radius: 3px;
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

.footer-info h3,
.footer-info p {
    margin: 0;
    line-height: 1.6;
}

/* --- Start: Login Modal Styles --- */
.login-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    transition: opacity 0.3s ease;
}

.login-modal-content {
    width: 800px;
    max-width: 90%;
    height: 600px;
    max-height: 90%;
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    position: relative;
    display: flex;
    overflow: hidden;
}

.login-left {
    flex: 0.45;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #f0f2f5;
    /* padding: 20px; */
}

.login-left img {
    max-width: 100%;
    height: auto;
    display: block;
    border-top-left-radius: 8px;
    border-bottom-left-radius: 8px;
}

.login-right {
    flex: 0.55;
    padding: 25px 30px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 15px;
    overflow-y: auto;
}

.login-right form {
    padding: 0;
    /* Đặt padding về 0 */
    background-color: transparent;
    /* Loại bỏ background */
    border: none;
    /* Loại bỏ border */
    box-shadow: none;
    /* Loại bỏ box-shadow */
    width: 100%;
    /* Giữ nguyên chiều dài 100% của phần tử cha */
}

.close-button {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 28px;
    font-weight: bold;
    color: #666;
    cursor: pointer;
    transition: color 0.2s ease;
}

.close-button:hover {
    color: #333;
}

.modal-header {
    margin-bottom: 5px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    /* Căn trái các phần tử con */
}

.modal-header h2 {
    font-size: 24px;
    color: #333;
    margin-bottom: 8px;
}

.modal-description {
    font-size: 13px;
    color: #777;
    line-height: 1.4;
    margin-bottom: 0;
}

.social-login-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 100%;
}

.social-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 10px 15px;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s ease, border-color 0.2s ease;
    border: 1px solid #ddd;
    background-color: white;
    color: #333;
}

.social-btn img,
.social-btn i {
    margin-right: 8px;
}

.social-btn:hover {
    background-color: #f0f0f0;
}

.google-btn {
    /* Keep existing styles */
}

.facebook-btn {
    /* Keep existing styles */
}

.divider {
    display: flex;
    align-items: center;
    text-align: center;
    color: #aaa;
    font-size: 12px;
    margin: 8px 0;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #eee;
}

.divider:not(:empty)::before {
    margin-right: .25em;
}

.divider:not(:empty)::after {
    margin-left: .25em;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #555;
}

.form-group input {
    width: 100%;
    padding: 10px;
    /* border: none;  */
    border-radius: 4px;
    box-sizing: border-box;
    transition: none;
    background-color: transparent;
    box-shadow: none;
}

.form-group input:focus {
    outline: none;
    /* border-color: transparent; */
    box-shadow: none;
}

.form-group.input-with-icon {
    position: relative;
}

.form-group.input-with-icon i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 15px;
}

.form-group.input-with-icon input {
    padding-left: 40px;
    border-radius: 6px;
}

.login-btn {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    font-weight: bold;
    background-color: #33ccff;
    border-radius: 6px;
    border: none;
    color: white;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.login-btn:hover {
    background-color: #00a8e1;
}

.form-footer {
    text-align: center;
    margin-top: 20px;
    font-size: 13px;
    color: #666;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
}

.form-footer a {
    color: #33ccff;
    text-decoration: none;
    font-weight: 600;
}

.form-footer a:hover {
    text-decoration: underline;
}
</style>