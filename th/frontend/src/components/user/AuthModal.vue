<script setup>
import Swal from 'sweetalert2';
import axios from 'axios';
import { ref, reactive, watch } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:show']);

const currentView = ref('login'); // Có thể là 'login', 'register', 'forgot-password', 'verify-otp', 'reset-password'

const loginForm = reactive({
    email: '',
    password: ''
});
const loginError = ref(''); // Biến lỗi tổng hợp cho form đăng nhập

const registerForm = reactive({
    email: '',
    password: '',
    confirmPassword: ''
});
const registerError = ref(''); // Biến lỗi tổng hợp cho form đăng ký

const forgotPasswordForm = reactive({
    email: ''
});
const forgotPasswordError = ref(''); // Biến lỗi tổng hợp cho form quên mật khẩu

const otpForm = reactive({
    email: '', // Sẽ được điền từ forgotPasswordForm.email
    code: ''
});
const otpError = ref(''); // Biến lỗi tổng hợp cho form OTP

const resetPasswordForm = reactive({
    email: '', // Sẽ được điền từ forgotPasswordForm.email
    new_password: '',
    new_password_confirmation: ''
});
const resetPasswordError = ref(''); // Biến lỗi tổng hợp cho form đặt lại mật khẩu

const isSubmitting = ref(false);

watch(() => props.show, (newVal) => {
    if (!newVal) {
        // Khi modal đóng, reset tất cả form và về view login
        resetAllFormsAndState(); // Đổi tên hàm để rõ ràng hơn
    }
});

// Hàm mới: reset tất cả form và trạng thái, đưa về view mặc định
const resetAllFormsAndState = () => {
    loginForm.email = '';
    loginForm.password = '';
    loginError.value = '';

    registerForm.email = '';
    registerForm.password = '';
    registerForm.confirmPassword = '';
    registerError.value = '';

    forgotPasswordForm.email = '';
    forgotPasswordError.value = '';

    otpForm.email = '';
    otpForm.code = '';
    otpError.value = '';

    resetPasswordForm.email = '';
    resetPasswordForm.new_password = '';
    resetPasswordForm.new_password_confirmation = '';
    resetPasswordError.value = '';

    currentView.value = 'login'; // Đảm bảo view trở về login khi đóng hoặc reset
};

const changeView = (viewName) => {
    // Luôn reset các lỗi và form của view hiện tại trước khi chuyển,
    // nhưng không reset toàn bộ modal trừ khi chuyển về login/register từ ngoài
    switch (currentView.value) {
        case 'login':
            loginError.value = '';
            break;
        case 'register':
            registerError.value = '';
            break;
        case 'forgot-password':
            forgotPasswordError.value = '';
            break;
        case 'verify-otp':
            otpError.value = '';
            otpForm.code = ''; // Reset mã OTP khi rời view xác nhận
            break;
        case 'reset-password':
            resetPasswordError.value = '';
            resetPasswordForm.new_password = ''; // Reset mật khẩu mới khi rời view đặt lại
            resetPasswordForm.new_password_confirmation = '';
            break;
    }

    // Thiết lập email cho các bước quên mật khẩu nếu chuyển từ forgot-password
    if (viewName === 'verify-otp' && forgotPasswordForm.email) {
        otpForm.email = forgotPasswordForm.email;
        resetPasswordForm.email = forgotPasswordForm.email; // Đảm bảo email được truyền qua
    } else if (viewName === 'reset-password' && forgotPasswordForm.email) {
        // Nếu đến thẳng reset-password mà không qua verify-otp (trường hợp không mong muốn)
        // hoặc nếu người dùng refresh trang, email sẽ bị mất. Cần cơ chế lưu email khác.
        // Tạm thời giữ nguyên logic nếu nó đang hoạt động trong luồng bình thường.
        otpForm.email = forgotPasswordForm.email;
        resetPasswordForm.email = forgotPasswordForm.email;
    }


    currentView.value = viewName; // Đặt view mới
};


const closeModal = () => {
    emit('update:show', false);
};

// Hàm hỗ trợ để lấy thông báo lỗi từ đối tượng lỗi của backend
const getValidationErrorMessage = (errors) => {
    let messages = [];
    for (const key in errors) {
        if (Object.hasOwnProperty.call(errors, key)) {
            // Lấy thông báo đầu tiên của mỗi trường lỗi
            messages.push(errors[key][0]);
        }
    }
    // Ghép các thông báo thành một chuỗi, mỗi thông báo trên một dòng
    return messages.join('<br>');
};

const handleLogin = async () => {
    loginError.value = '';
    isSubmitting.value = true;

    try {
        const res = await axios.post('/login', {
            email: loginForm.email,
            mat_khau: loginForm.password
        });

        const user = res.data.user;
        const token = res.data.token;

        // --- BẮT ĐẦU PHẦN THÊM MỚI/CẬP NHẬT ---
        // ✅ KIỂM TRA TRẠNG THÁI TÀI KHOẢN SAU KHI ĐĂNG NHẬP THÀNH CÔNG
        // Giả sử backend trả về trường `trang_thai` trong đối tượng user.
        // Quy ước: 0 = hoạt động, 1 = vô hiệu hóa.
        if (user.trang_thai === 1) { // Hoặc bất kỳ giá trị nào bạn quy định cho trạng thái vô hiệu hóa
            loginError.value = 'Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ bộ phận hỗ trợ.';
            isSubmitting.value = false; // Bật lại nút submit
            
            await Swal.fire({
                icon: 'error',
                title: 'Tài khoản bị vô hiệu hóa',
                text: loginError.value,
                confirmButtonText: 'Đã hiểu',
                timer: 5000,
                timerProgressBar: true
            });
            
            // Tùy chọn: Chuyển hướng đến một trang riêng để giải thích về tài khoản vô hiệu hóa
            // router.push('/tai-khoan-vo-hieu-hoa'); 
            return; // Dừng hàm, không tiến hành lưu token và chuyển hướng
        }
        // --- KẾT THÚC PHẦN THÊM MỚI/CẬP NHẬT ---


        // ✅ Nếu thành công và tài khoản không bị vô hiệu hóa, lưu token và chuyển tiếp
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
            showConfirmButton: false
        });
    } catch (err) {
        isSubmitting.value = false;

        const res = err.response;

        // ✅ Kiểm tra lỗi kích hoạt từ backend (giữ nguyên logic cũ)
        if (res?.status === 403 && res.data?.require_activation) {
            loginError.value = 'Tài khoản chưa được kích hoạt.';

            const result = await Swal.fire({
                icon: 'error',
                title: 'Tài khoản chưa kích hoạt',
                html: 'Vui lòng kiểm tra email để kích hoạt tài khoản.<br>Bạn muốn gửi lại email kích hoạt?',
                showCancelButton: true,
                confirmButtonText: 'Gửi lại',
                cancelButtonText: 'Để sau',
                timer: 5000,
                timerProgressBar: true
            });

            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Đang gửi email kích hoạt...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    await axios.post('/resend-activation', { email: loginForm.email });

                    Swal.close();

                    await Swal.fire({
                        icon: 'success',
                        title: 'Đã gửi lại email kích hoạt!',
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false
                    });
                } catch (err) {
                    Swal.close();

                    await Swal.fire({
                        icon: 'error',
                        title: 'Gửi lại thất bại!',
                        text: err.response?.data?.message || 'Vui lòng thử lại sau.',
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            }

            return; // ❌ Không tiếp tục xử lý nữa
        }

        // Các lỗi khác (giữ nguyên logic cũ)
        if (res?.data?.errors) {
            loginError.value = getValidationErrorMessage(res.data.errors);
        } else {
            loginError.value = res?.data?.message || 'Đăng nhập thất bại. Vui lòng kiểm tra lại.';
        }
    }
};

const handleRegister = async () => {
    registerError.value = ''; // Reset lỗi
    isSubmitting.value = true;

    if (registerForm.password !== registerForm.confirmPassword) {
        registerError.value = 'Mật khẩu và xác nhận mật khẩu không khớp.';
        isSubmitting.value = false;
        return;
    }
    if (registerForm.password.length < 8) {
        registerError.value = 'Mật khẩu phải có ít nhất 8 ký tự.';
        isSubmitting.value = false;
        return;
    }

    const atIndex = registerForm.email.indexOf('@');
    if (atIndex === -1 || atIndex === 0 || atIndex === registerForm.email.length - 1) {
        registerError.value = 'Địa chỉ email không hợp lệ.';
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

        if (err.response && err.response.data) {
            if (err.response.data.errors) {
                const backendErrors = err.response.data.errors;
                // Kiểm tra lỗi email cụ thể từ backend validation errors
                if (backendErrors.email && backendErrors.email.includes('The email has already been taken.')) {
                    registerError.value = 'Email này đã được sử dụng. Vui lòng chọn một email khác.';
                } else {
                    registerError.value = getValidationErrorMessage(backendErrors);
                }
            } else {
                // Kiểm tra lỗi chung từ backend message (ít phổ biến hơn cho validation nhưng vẫn có thể xảy ra)
                if (err.response.data.message === 'The email has already been taken.') {
                    registerError.value = 'Email này đã được sử dụng. Vui lòng chọn một email khác.';
                } else {
                    registerError.value = err.response.data.message || 'Đăng ký thất bại. Vui lòng thử lại.';
                }
            }
        } else {
            registerError.value = 'Đăng ký thất bại. Lỗi kết nối mạng hoặc server.';
        }
    } finally {
        isSubmitting.value = false;
    }
};

const handleForgotPassword = async () => {
    forgotPasswordError.value = ''; // Reset lỗi
    isSubmitting.value = true;
    try {
        const res = await axios.post('/send-reset-code', { email: forgotPasswordForm.email });

        otpForm.email = forgotPasswordForm.email;
        resetPasswordForm.email = forgotPasswordForm.email;

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: res.data.message || `Mã xác nhận đã được gửi đến ${forgotPasswordForm.email}.`,
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true
        }).then(() => {
            changeView('verify-otp');
        });
    } catch (err) {
        console.error('Lỗi khi gửi yêu cầu quên mật khẩu:', err);
        if (err.response && err.response.data) {
            if (err.response.data.errors) {
                forgotPasswordError.value = getValidationErrorMessage(err.response.data.errors);
            } else {
                forgotPasswordError.value = err.response.data.message || 'Không thể gửi yêu cầu. Vui lòng kiểm tra lại email.';
            }
        } else {
            forgotPasswordError.value = 'Lỗi kết nối mạng hoặc server. Không thể gửi yêu cầu.';
        }
    } finally {
        isSubmitting.value = false;
    }
};

const handleVerifyOtp = async () => {
    otpError.value = ''; // Reset lỗi
    isSubmitting.value = true;
    try {
        const res = await axios.post('/verify-reset-code', {
            email: otpForm.email,
            code: otpForm.code
        });

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: res.data.message || 'Mã xác nhận đúng. Bạn có thể đặt lại mật khẩu.',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        }).then(() => {
            changeView('reset-password');
        });
    } catch (err) {
        console.error('Lỗi khi xác nhận OTP:', err);
        if (err.response && err.response.data) {
            if (err.response.data.errors) {
                otpError.value = getValidationErrorMessage(err.response.data.errors);
            } else {
                otpError.value = err.response.data.message || 'Mã xác nhận không đúng hoặc đã hết hạn.';
            }
        } else {
            otpError.value = 'Lỗi kết nối mạng hoặc server. Không thể xác nhận mã.';
        }
    } finally {
        isSubmitting.value = false;
    }
};

const handleResetPassword = async () => {
    resetPasswordError.value = ''; // Reset lỗi
    isSubmitting.value = true;

    if (resetPasswordForm.new_password.length < 8) {
        resetPasswordError.value = 'Mật khẩu mới phải có ít nhất 8 ký tự.';
        isSubmitting.value = false;
        return;
    }
    if (resetPasswordForm.new_password !== resetPasswordForm.new_password_confirmation) {
        resetPasswordError.value = 'Mật khẩu mới và xác nhận mật khẩu không khớp.';
        isSubmitting.value = false;
        return;
    }

    try {
        const res = await axios.post('/reset-password', {
            email: resetPasswordForm.email,
            new_password: resetPasswordForm.new_password,
            new_password_confirmation: resetPasswordForm.new_password_confirmation
        });

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: res.data.message || 'Mật khẩu của bạn đã được đặt lại thành công.',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        }).then(() => {
            changeView('login');
        });
    } catch (err) {
        console.error('Lỗi khi đặt lại mật khẩu:', err);
        if (err.response && err.response.data) {
            if (err.response.data.errors) {
                resetPasswordError.value = getValidationErrorMessage(err.response.data.errors);
            } else {
                resetPasswordError.value = err.response.data.message || 'Không thể đặt lại mật khẩu. Vui lòng thử lại.';
            }
        } else {
            resetPasswordError.value = 'Lỗi kết nối mạng hoặc server. Không thể đặt lại mật khẩu.';
        }
    } finally {
        isSubmitting.value = false;
    }
};


// --- Thêm các hàm xử lý đăng nhập Google/Facebook ---
// (Giữ nguyên các hàm này)
// Google Login
const handleGoogleLogin = () => {
    if (typeof window.google === 'undefined' || !window.google.accounts?.id) {
        console.error('Google One Tap SDK chưa được tải.');
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'Không thể kết nối Google. Vui lòng thử lại sau.',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        return;
    }

    window.google.accounts.id.initialize({
        client_id: '710318641931-iqof2cq4qcg68fnccru81o31n7gbdqc0.apps.googleusercontent.com', // <-- Đã thay thế client_id của bạn vào đây
        callback: handleGoogleCredentialResponse,
        ux_mode: 'popup'
    });

    window.google.accounts.id.prompt();
};


const handleGoogleCredentialResponse = async (response) => {
    if (response.credential) {
        try {
            const res = await axios.post('/auth/google', {
                id_token: response.credential
            });

            const user = res.data.user;
            const token = res.data.token;

            // --- BẮT ĐẦU PHẦN THÊM MỚI/CẬP NHẬT CHO ĐĂNG NHẬP GOOGLE ---
            // ✅ KIỂM TRA TRẠNG THÁI TÀI KHOẢN SAU KHI ĐĂNG NHẬP GOOGLE THÀNH CÔNG
            if (user.trang_thai === 1) { 
                Swal.fire({
                    icon: 'error',
                    title: 'Tài khoản bị vô hiệu hóa',
                    text: 'Tài khoản Google của bạn đã bị vô hiệu hóa bởi quản trị viên. Vui lòng liên hệ bộ phận hỗ trợ.',
                    confirmButtonText: 'Đã hiểu',
                    timer: 5000,
                    timerProgressBar: true
                });
                router.push('/tai-khoan-vo-hieu-hoa'); 
                return; // Dừng hàm, không tiến hành lưu token và chuyển hướng
            }
            // --- KẾT THÚC PHẦN THÊM MỚI/CẬP NHẬT ---

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
            // Có thể thêm kiểm tra error.response?.data?.status === 'disabled' ở đây
            // nếu backend cũng trả về lỗi 403 cho Google login
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
    if (window.FB) {
        window.FB.login(async function(response) {
            if (response.authResponse) {
                const accessToken = response.authResponse.accessToken;
                console.log('Access Token Facebook:', accessToken);

                try {
                    const res = await axios.post('/api/auth/facebook', {
                        access_token: accessToken
                    });

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
        }, { scope: 'email,public_profile' });
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
                    <h2 v-else-if="currentView === 'forgot-password'">Quên mật khẩu?</h2>
                    <h2 v-else-if="currentView === 'verify-otp'">Xác nhận mã bảo mật</h2>
                    <h2 v-else-if="currentView === 'reset-password'">Đặt lại mật khẩu</h2>
                    
                    <p class="modal-description" v-if="currentView === 'login'">Đăng nhập để mở khóa nhiều chức năng<br>Dễ dàng tìm kiếm nhạc cụ mong muốn</p>
                    <p class="modal-description" v-else-if="currentView === 'register'">Đăng ký để khám phá thêm và nhận thông báo mới nhất</p>
                    <p class="modal-description" v-else-if="currentView === 'forgot-password'">Nhập email của bạn để nhận mã xác nhận khôi phục mật khẩu.</p>
                    <p class="modal-description" v-else-if="currentView === 'verify-otp'">Chúng tôi đã gửi một mã xác nhận 6 chữ số đến email <span class="email-display">{{ otpForm.email }}</span>. Vui lòng kiểm tra hộp thư đến và nhập mã tại đây.</p>
                    <p class="modal-description" v-else-if="currentView === 'reset-password'">Tạo mật khẩu mới cho tài khoản <span class="email-display">{{ resetPasswordForm.email }}</span> của bạn.</p>
                </div>

                <template v-if="currentView === 'login' || currentView === 'register'">
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
                <template v-else-if="currentView === 'forgot-password'">
                    <div class="divider">
                        <span>Nhập email của bạn</span>
                    </div>
                </template>
                   <template v-else-if="currentView === 'verify-otp'">
                    <div class="divider">
                        <span>Nhập mã xác nhận</span>
                    </div>
                </template>
                   <template v-else-if="currentView === 'reset-password'">
                    <div class="divider">
                        <span>Tạo mật khẩu mới</span>
                    </div>
                </template>

                <form @submit.prevent="handleLogin" v-if="currentView === 'login'">
                    <div class="form-group input-with-icon">
                        <i class="far fa-envelope"></i>
                        <input type="email" id="email" placeholder="Nhập email của bạn" required
                            v-model="loginForm.email"
                            :class="{ 'is-invalid': loginError }">
                    </div>
                    <div class="form-group input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" placeholder="Nhập mật khẩu của bạn" required
                            v-model="loginForm.password"
                            :class="{ 'is-invalid': loginError }">
                    </div>
                    
                    <button type="submit" class="login-btn" :disabled="isSubmitting">
                        <span v-if="isSubmitting">Đang xử lý...</span>
                        <span v-else>Đăng nhập</span>
                    </button>
                    <p class="error-message" v-if="loginError" v-html="loginError"></p>
                    <p class="form-footer">
                        Bạn chưa có tài khoản? <a href="#" @click.prevent="changeView('register')">Đăng ký</a> <span
                            class="footer-separator">|</span> <a href="#"
                            @click.prevent="changeView('forgot-password')">Quên mật khẩu?</a>
                    </p>
                </form>

                <form @submit.prevent="handleRegister" v-else-if="currentView === 'register'">
                    <div class="form-group input-with-icon">
                        <i class="far fa-envelope"></i>
                        <input type="email" id="registerEmail" placeholder="Nhập email của bạn" required
                            v-model="registerForm.email"
                            :class="{ 'is-invalid': registerError }">
                    </div>
                    <div class="form-group input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="registerPassword" placeholder="Tạo mật khẩu" required
                            v-model="registerForm.password"
                            :class="{ 'is-invalid': registerError }">
                    </div>
                    <div class="form-group input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirmPassword" placeholder="Xác nhận mật khẩu" required
                            v-model="registerForm.confirmPassword"
                            :class="{ 'is-invalid': registerError }">
                    </div>
                    
                    <button type="submit" class="login-btn" :disabled="isSubmitting">
                        <span v-if="isSubmitting">Đang xử lý...</span>
                        <span v-else>Đăng ký</span>
                    </button>
                    <p class="error-message" v-if="registerError" v-html="registerError"></p>
                    <p class="form-footer">
                        Bạn đã có tài khoản? <a href="#" @click.prevent="changeView('login')">Đăng nhập</a>
                    </p>
                </form>

                <form @submit.prevent="handleForgotPassword" v-else-if="currentView === 'forgot-password'">
                    <div class="form-group input-with-icon">
                        <i class="far fa-envelope"></i>
                        <input type="email" id="forgotPasswordEmail" placeholder="Nhập email của bạn" required
                            v-model="forgotPasswordForm.email"
                            :class="{ 'is-invalid': forgotPasswordError }">
                    </div>
                    
                    <button type="submit" class="login-btn" :disabled="isSubmitting">
                        <span v-if="isSubmitting">Đang gửi...</span>
                        <span v-else>Gửi mã xác nhận</span>
                    </button>
                    <p class="error-message" v-if="forgotPasswordError" v-html="forgotPasswordError"></p>
                    <p class="form-footer">
                        <a href="#" @click.prevent="changeView('login')">Quay lại Đăng nhập</a>
                    </p>
                </form>

                <form @submit.prevent="handleVerifyOtp" v-else-if="currentView === 'verify-otp'">
                    <div class="form-group input-with-icon">
                        <i class="fas fa-key"></i>
                        <input type="text" id="otpCode" placeholder="Nhập mã xác nhận (6 chữ số)" required
                            v-model="otpForm.code" maxlength="6"
                            :class="{ 'is-invalid': otpError }">
                    </div>
                    
                    <button type="submit" class="login-btn" :disabled="isSubmitting">
                        <span v-if="isSubmitting">Đang xác nhận...</span>
                        <span v-else>Xác nhận mã</span>
                    </button>
                    <p class="error-message" v-if="otpError" v-html="otpError"></p>
                    <p class="form-footer">
                        <a href="#" @click.prevent="handleForgotPassword">Gửi lại mã</a> <span class="footer-separator">|</span> 
                        <a href="#" @click.prevent="changeView('login')">Quay lại Đăng nhập</a>
                    </p>
                </form>

                <form @submit.prevent="handleResetPassword" v-else-if="currentView === 'reset-password'">
                    <div class="form-group input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="newPassword" placeholder="Nhập mật khẩu mới" required
                            v-model="resetPasswordForm.new_password"
                            :class="{ 'is-invalid': resetPasswordError }">
                    </div>
                    <div class="form-group input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirmNewPassword" placeholder="Xác nhận mật khẩu mới" required
                            v-model="resetPasswordForm.new_password_confirmation"
                            :class="{ 'is-invalid': resetPasswordError }">
                    </div>
                    
                    <button type="submit" class="login-btn" :disabled="isSubmitting">
                        <span v-if="isSubmitting">Đang đặt lại...</span>
                        <span v-else>Đặt lại mật khẩu</span>
                    </button>
                    <p class="error-message" v-if="resetPasswordError" v-html="resetPasswordError"></p>
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
    /* Điều chỉnh chiều rộng và chiều cao tối đa của modal */
    max-width: 800px; /* Có thể điều chỉnh tùy ý */
    max-height: 600px; /* Điều chỉnh chiều cao tối đa để tránh scroll */
    width: 90%; /* Tương thích trên các màn hình khác nhau */
    height: 90%; /* Tương thích trên các màn hình khác nhau */
    display: flex;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden; /* Quan trọng: Đảm bảo không có scrollbar không mong muốn trên modal chính */
}


.login-left {
    flex: 0.45;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #ffffff;
    /* THÊM border-top-left-radius và border-bottom-left-radius cho .login-left */
    /* Đồng thời đảm bảo phần bên phải của nó không bo góc */
    border-top-left-radius: 10px; /* Khớp với border-radius của modal */
    border-bottom-left-radius: 10px; /* Khớp với border-radius của modal */
    /* Đảm bảo không có border-radius ở bên phải của .login-left */
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    /* Đặt overflow: hidden cho .login-left để cắt ảnh bên trong nếu cần */
    overflow: hidden;
    box-shadow: 1px 0 0 #f0f2f5;
}

.login-left img {
    max-width: 100%;
    height: auto; 
    object-fit: cover; 
    display: block;
    border-radius: 0;
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
    background-color: transparent;
    border: none;
    box-shadow: none;
    width: 100%;
    margin: 0;
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
/* Trong phần <style scoped> của bạn */
.error-message {
    color: #dc3545; /* Màu đỏ cho lỗi */
    font-size: 0.85em;
    margin-top: 5px;
    margin-bottom: 10px; /* Tạo khoảng cách với element tiếp theo */
    text-align: left; /* Căn lề trái */
    padding-left: 5px; /* Thụt vào một chút để khớp với input */
}

/* Tùy chọn: Thêm viền đỏ cho input khi có lỗi */
.form-group input.is-invalid {
    border-color: #dc3545 !important;
}
</style>