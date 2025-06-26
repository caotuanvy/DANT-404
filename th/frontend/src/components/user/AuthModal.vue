<script setup>
import Swal from 'sweetalert2';
import axios from 'axios';
import { ref, reactive, watch, onMounted } from 'vue'; // Thêm onMounted
import { useRouter } from 'vue-router';

const router = useRouter();

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:show']);

const currentView = ref('login');

const loginForm = reactive({
    email: '',
    password: ''
});
const loginError = ref('');

const registerForm = reactive({
    email: '',
    password: '',
    confirmPassword: ''
});
const registerError = ref('');

const forgotPasswordForm = reactive({
    email: ''
});
const forgotPasswordError = ref('');

const isSubmitting = ref(false);

watch(() => props.show, (newVal) => {
    if (!newVal) {
        currentView.value = 'login';
        resetForms();
    } else {
        // Nếu modal được mở, kiểm tra và hiển thị Google One Tap
        // displayGoogleOneTap(); // Bỏ bình luận nếu muốn dùng One Tap
    }
});

const resetForms = () => {
    loginForm.email = '';
    loginForm.password = '';
    loginError.value = '';

    registerForm.email = '';
    registerForm.password = '';
    registerForm.confirmPassword = '';
    registerError.value = '';

    forgotPasswordForm.email = '';
    forgotPasswordForm.error = '';
};

const changeView = (viewName) => {
    currentView.value = viewName;
    resetForms();
};

const closeModal = () => {
    emit('update:show', false);
};

const handleLogin = async () => {
    loginError.value = '';
    try {
        const res = await axios.post('/login', {
            email: loginForm.email,
            mat_khau: loginForm.password
        });

        const user = res.data.user;
        const token = res.data.token;

        if (user.is_active !== 1) {
            loginError.value = 'Tài khoản của bạn chưa được kích hoạt.';
            return;
        }
        closeModal();
        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(user));
        localStorage.setItem('vai_tro_id', user.vai_tro_id);
        localStorage.setItem('sdt', user.sdt);
        await Swal.fire({
            icon: 'success',
            title: 'Chào mừng!',
            text: `Xin chào ${user.ho_ten || user.email}, chúc bạn một ngày tuyệt vời!`,
            toast: true,
            position: 'top-end',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    } catch (err) {
        loginError.value = err.response?.data?.message || 'Đăng nhập thất bại. Vui lòng kiểm tra lại email và mật khẩu.';
    }
};

const handleRegister = async () => {
    isSubmitting.value = true;

    if (registerForm.password !== registerForm.confirmPassword) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'Mật khẩu và xác nhận mật khẩu không khớp.',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        isSubmitting.value = false;
        return;
    }

    const atIndex = registerForm.email.indexOf('@');
    if (atIndex === -1 || atIndex === 0 || atIndex === registerForm.email.length - 1) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'Địa chỉ email không hợp lệ.',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        isSubmitting.value = false;
        return;
    }
    registerForm.ho_ten = registerForm.email.substring(0, atIndex);

    let toastLoading = null;

    try {
        toastLoading = Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: 'Đang đăng ký tài khoản...',
            showConfirmButton: false,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        const res = await axios.post('/register', {
            ho_ten: registerForm.ho_ten,
            email: registerForm.email,
            sdt: registerForm.sdt || '',
            mat_khau: registerForm.password,
            mat_khau_confirmation: registerForm.confirmPassword
        });

        if (toastLoading) {
            Swal.close(toastLoading);
        }

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Đăng ký thành công! Vui lòng kiểm tra email để kích hoạt tài khoản.',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        }).then(() => {
            changeView('login');
        });

    } catch (err) {
        console.error('Đăng ký thất bại:', err);

        if (toastLoading) {
            Swal.close(toastLoading);
        }

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

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: errorMessage,
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true
        });
    } finally {
        isSubmitting.value = false;
    }
};

const handleForgotPassword = async () => {
    forgotPasswordError.value = '';
    try {
        const res = await axios.post('/forgot-password', { email: forgotPasswordForm.email });
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: res.data.message || `Hướng dẫn khôi phục mật khẩu đã được gửi đến ${forgotPasswordForm.email}.`,
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true
        });
        changeView('login');
    } catch (err) {
        console.error('Lỗi khi gửi yêu cầu quên mật khẩu:', err);
        const errorMessage = err.response?.data?.message || 'Không thể gửi yêu cầu. Vui lòng kiểm tra lại email.';
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: errorMessage,
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true
        });
        forgotPasswordError.value = errorMessage;
    }
};

// --- Thêm các hàm xử lý đăng nhập Google/Facebook ---

// Google Login
const handleGoogleLogin = () => {
    // Để tích hợp Google Login, bạn cần dùng Google Identity Services SDK
    // Tham khảo: https://developers.google.com/identity/gsi/web/guides/overview
    // Cách 1: Sử dụng popup hoặc redirect (khuyến nghị cho button)
    const googleClientId = 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com'; // Thay thế bằng Client ID của bạn

    window.google.accounts.id.initialize({
        client_id: googleClientId,
        callback: handleGoogleCredentialResponse, // Hàm callback sau khi nhận được thông tin
        ux_mode: 'popup' // hoặc 'redirect'
    });

    // Mở popup đăng nhập Google
    window.google.accounts.id.prompt(); // Hoặc bạn có thể đính kèm vào nút của mình
                                       // window.google.accounts.id.renderButton(
                                       //   document.getElementById("google-login-button"),
                                       //   { theme: "outline", size: "large" }  // tùy chỉnh nút
                                       // );
};

const handleGoogleCredentialResponse = async (response) => {
    // 'response' chứa id_token từ Google
    if (response.credential) {
        try {
            // Gửi id_token này về backend của bạn để xác minh
            // Backend sẽ dùng Google API để xác minh token và lấy thông tin người dùng
            const res = await axios.post('/api/auth/google', {
                id_token: response.credential
            });

            // Xử lý phản hồi từ backend tương tự như handleLogin
            const user = res.data.user;
            const token = res.data.token;

            closeModal();
            localStorage.setItem('token', token);
            localStorage.setItem('user', JSON.stringify(user));
            localStorage.setItem('vai_tro_id', user.vai_tro_id);
            localStorage.setItem('sdt', user.sdt);

            await Swal.fire({
                icon: 'success',
                title: 'Chào mừng!',
                text: `Xin chào ${user.ho_ten || user.email}, chúc bạn một ngày tuyệt vời!`,
                toast: true,
                position: 'top-end',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        } catch (error) {
            console.error('Đăng nhập Google thất bại:', error);
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: error.response?.data?.message || 'Đăng nhập Google thất bại. Vui lòng thử lại.',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true
            });
        }
    }
};

// Facebook Login
const handleFacebookLogin = () => {
    // Đảm bảo Facebook SDK đã được tải
    if (window.FB) {
        window.FB.login(async function(response) {
            if (response.authResponse) {
                // Người dùng đã đăng nhập và cấp quyền
                const accessToken = response.authResponse.accessToken;
                console.log('Access Token Facebook:', accessToken);

                try {
                    // Gửi accessToken này về backend của bạn để xác minh
                    // Backend sẽ dùng accessToken để lấy thông tin người dùng từ Facebook API
                    const res = await axios.post('/api/auth/facebook', {
                        access_token: accessToken
                    });

                    // Xử lý phản hồi từ backend tương tự như handleLogin
                    const user = res.data.user;
                    const token = res.data.token;

                    closeModal();
                    localStorage.setItem('token', token);
                    localStorage.setItem('user', JSON.stringify(user));
                    localStorage.setItem('vai_tro_id', user.vai_tro_id);
                    localStorage.setItem('sdt', user.sdt);

                    await Swal.fire({
                        icon: 'success',
                        title: 'Chào mừng!',
                        text: `Xin chào ${user.ho_ten || user.email}, chúc bạn một ngày tuyệt vời!`,
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });

                } catch (error) {
                    console.error('Đăng nhập Facebook thất bại:', error);
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: error.response?.data?.message || 'Đăng nhập Facebook thất bại. Vui lòng thử lại.',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true
                    });
                }
            } else {
                console.log('Người dùng hủy đăng nhập Facebook hoặc không cấp quyền.');
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'warning',
                    title: 'Đăng nhập Facebook bị hủy.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            }
        }, { scope: 'email,public_profile' }); // Yêu cầu quyền truy cập email và public profile
    } else {
        console.error('Facebook SDK chưa được tải.');
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'Không thể kết nối Facebook. Vui lòng thử lại sau.',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }
};

// Gán các hàm xử lý vào nút
// onMounted(() => {
//     // Nếu bạn muốn hiển thị Google One Tap khi modal mở, có thể gọi ở đây
//     // Nhưng thường thì chỉ cần gắn vào button
// });

</script>

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
                        <button class="social-btn google-btn" @click="handleGoogleLogin">
                            <img src="https://img.icons8.com/color/16/000000/google-logo.png" alt="Google icon">
                            Continue with Google
                        </button>
                        <button class="social-btn facebook-btn" @click="handleFacebookLogin">
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
                    <button type="submit" class="login-btn" :disabled="isSubmitting">
                        <span v-if="isSubmitting">Đang xử lý...</span>
                        <span v-else>Đăng ký</span>
                    </button>
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

<style scoped>
/* Giữ nguyên các style CSS hiện có */
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
    border-radius: 4px;
    box-sizing: border-box;
    transition: none;
    background-color: transparent;
    box-shadow: none;
}

.form-group input:focus {
    outline: none;
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

/* THÊM CÁC QUY TẮC CSS NÀY VÀO ĐÂY */
.login-btn:disabled {
    background-color: #cccccc; /* Màu nền xám hơn */
    cursor: not-allowed; /* Con trỏ chuột thành biểu tượng "không được phép" */
    opacity: 0.7; /* Giảm độ mờ */
    pointer-events: none; /* Vô hiệu hóa tất cả sự kiện chuột, bao gồm hover */
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