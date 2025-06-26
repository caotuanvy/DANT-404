<script setup>
import { ref } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const oldPassword = ref('');
const newPassword = ref('');
const confirmNewPassword = ref('');
const errorMessage = ref('');
const successMessage = ref('');
const isLoading = ref(false);

const handleChangePassword = async () => {
    errorMessage.value = '';
    successMessage.value = '';

    if (!oldPassword.value || !newPassword.value || !confirmNewPassword.value) {
        errorMessage.value = 'Vui lòng điền đầy đủ tất cả các trường mật khẩu.';
        return;
    }

    if (newPassword.value !== confirmNewPassword.value) {
        errorMessage.value = 'Mật khẩu mới và xác nhận mật khẩu mới không khớp.';
        return;
    }

    if (newPassword.value.length < 8) {
        errorMessage.value = 'Mật khẩu mới phải có ít nhất 8 ký tự.';
        return;
    }

    if (newPassword.value === oldPassword.value) {
        errorMessage.value = 'Mật khẩu mới không được trùng với mật khẩu cũ.';
        return;
    }

    const storedUser = localStorage.getItem('user');
    if (!storedUser) {
        errorMessage.value = 'Bạn chưa đăng nhập. Vui lòng đăng nhập lại.';
        return;
    }

    const user = JSON.parse(storedUser);
    const userId = user.nguoi_dung_id;
    const token = localStorage.getItem('token');

    if (!userId || !token) {
        errorMessage.value = 'Không thể xác định thông tin người dùng. Vui lòng đăng nhập lại.';
        return;
    }

    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

    isLoading.value = true;

    try {
        const response = await axios.put(`http://localhost:8000/api/users/${userId}/change-password`, {
            old_password: oldPassword.value,
            new_password: newPassword.value,
            new_password_confirmation: confirmNewPassword.value,
        });

        if (response.data.success) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Đổi mật khẩu thành công!',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didClose: () => {
                    oldPassword.value = '';
                    newPassword.value = '';
                    confirmNewPassword.value = '';
                }
            });
            // successMessage.value = 'Đổi mật khẩu thành công!';
        } else {
            errorMessage.value = response.data.message || 'Đổi mật khẩu thất bại.';
        }
    } catch (error) {
        // console.error('Lỗi khi đổi mật khẩu:', error.response?.data || error.message);
        let currentErrorMessage = 'Đã xảy ra lỗi không xác định. Vui lòng thử lại.';

        if (error.response) {
            if (error.response.status === 401) {
                currentErrorMessage = error.response.data.message || 'Bạn chưa đăng nhập hoặc token không hợp lệ.';
            } else if (error.response.status === 422 && error.response.data?.errors) {
                let detailedErrors = [];
                for (const key in error.response.data.errors) {
                    detailedErrors.push(error.response.data.errors[key][0]);
                }
                currentErrorMessage = detailedErrors.join('; ');
            } else if (error.response.data?.message) {
                currentErrorMessage = error.response.data.message;
            }
        } else {
            currentErrorMessage = 'Không thể kết nối đến máy chủ. Vui lòng kiểm tra internet.';
        }

        errorMessage.value = currentErrorMessage;
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <h1>Đổi mật khẩu</h1>
    <hr />
    <div class="change-password-section">
        <h2>THAY ĐỔI MẬT KHẨU</h2>
        <div class="form-group">
            <label for="oldPassword">Mật khẩu cũ</label>
            <input type="password" id="oldPassword" v-model="oldPassword" placeholder="Nhập mật khẩu cũ" />
        </div>
        <div class="form-group">
            <label for="newPassword">Mật khẩu mới</label>
            <input type="password" id="newPassword" v-model="newPassword" placeholder="Nhập mật khẩu mới" />
        </div>
        <div class="form-group">
            <label for="confirmNewPassword">Xác nhận mật khẩu mới</label>
            <input type="password" id="confirmNewPassword" v-model="confirmNewPassword" placeholder="Xác nhận mật khẩu mới" />
        </div>

        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
        <p v-if="successMessage" class="success-message">{{ successMessage }}</p>

        <button class="change-password-btn" @click="handleChangePassword" :disabled="isLoading">
            <span v-if="isLoading">Đang xử lý...</span>
            <span v-else>ĐỔI MẬT KHẨU</span>
        </button>
    </div>
</template>

<style scoped>
.error-message {
    color: red;
    font-size: 14px;
    margin-top: 10px;
    font-weight: 500;
    text-align: left;
}

.success-message {
    color: green;
    font-size: 14px;
    margin-top: 10px;
    font-weight: 500;
    text-align: left;
}

h1 {
    font-size: 23px;
    font-weight: bold;
    color: black;
    margin-bottom: 20px;
}

h2 {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 20px;
}

.change-password-section .form-group {
    margin-bottom: 20px;
}

.change-password-section label {
    font-size: 15px;
    font-weight: 500;
    color: #555;
    display: block;
    margin-bottom: 8px;
}

.change-password-section input[type="password"] {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 16px;
    box-sizing: border-box;
}

.change-password-section input[type="password"]:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.change-password-btn {
    padding: 12px 30px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 17px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-top: 15px;
}

.change-password-btn:hover {
    background-color: #0056b3;
}

.change-password-btn:disabled {
    background-color: #a0cbed;
    cursor: not-allowed;
    opacity: 0.7;
}

@media (max-width: 768px) {
    .change-password-btn {
        width: 100%;
        padding: 10px;
        font-size: 16px;
    }
}
</style>
