<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

// --- BIẾN PHẢN ỨNG CHO THÔNG TIN CÁ NHÂN ---
const userName = ref('Khách');
const userPhone = ref('Chưa có số điện thoại');
const userAvatar = ref('https://placehold.co/40x40/33ccff/FFFFFF?text=AV'); 
const showEditPersonalForm = ref(false); 
const tempUserName = ref(''); 
const tempUserPhone = ref(''); 

// --- BIẾN PHẢN ỨNG CHO ẢNH ĐẠI DIỆN ---
const showEditAvatarForm = ref(false);
const newAvatarFile = ref(null);
const tempAvatarUrl = ref('');

// --- BIẾN PHẢN ỨNG CHO ĐỊA CHỈ ---
const streetAddress = ref('');
const selectedProvinceCode = ref('');
const selectedDistrictCode = ref('');
const selectedWardCode = ref('');
const isDefaultAddress = ref(false);
const currentAddressId = ref(null);

// --- CÁC BIẾN KHÁC ---
const errorMessage = ref('');
const isLoadingAddressData = ref(false);
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);

const router = useRouter();

// --- HÀM HỖ TRỢ VÀ API LOGIC ---
const parseAddress = (fullAddress) => {
    if (!fullAddress) {
        return { street: '', ward: '', district: '', province: '' };
    }
    const parts = fullAddress.split(',').map(part => part.trim());
    let province = '';
    let district = '';
    let ward = '';
    let street = '';
    if (parts.length >= 4) {
        province = parts[0];
        district = parts[1];
        ward = parts[2];
        street = parts.slice(3).join(', ');
    } else if (parts.length === 3) {
        province = parts[0];
        district = parts[1];
        ward = parts[2];
    } else if (parts.length === 2) {
        province = parts[0];
        district = parts[1];
    } else if (parts.length === 1) {
        province = parts[0];
    }
    ward = ward.replace(/^(Phường|Xã)\s/i, '');
    district = district.replace(/^(Quận|Huyện|Thành phố)\s/i, '');
    province = province.replace(/^(Tỉnh|Thành phố)\s/i, '');
    return { street, ward, district, province };
};

async function fetchProvinces() {
    try {
        const cachedProvinces = localStorage.getItem('provinces');
        if (cachedProvinces) {
            provinces.value = JSON.parse(cachedProvinces);
            return;
        }
        const response = await fetch('https://vn-public-apis.fpo.vn/provinces/getAll?limit=-1');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const apiData = await response.json();
        provinces.value = apiData.data.data;
        localStorage.setItem('provinces', JSON.stringify(provinces.value));
    } catch (error) {
        console.error('Lỗi khi tải danh sách Tỉnh/Thành phố:', error.message);
        provinces.value = [];
    }
}

async function fetchDistricts(provinceCode) {
    if (!provinceCode) {
        districts.value = [];
        wards.value = [];
        return;
    }
    try {
        const cacheKey = `districts_${provinceCode}`;
        const cachedDistricts = localStorage.getItem(cacheKey);
        if (cachedDistricts) {
            districts.value = JSON.parse(cachedDistricts);
            return;
        }
        const response = await fetch(`https://vn-public-apis.fpo.vn/districts/getByProvince?provinceCode=${provinceCode}&limit=-1`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const apiData = await response.json();
        districts.value = apiData.data.data;
        localStorage.setItem(cacheKey, JSON.stringify(districts.value));
    } catch (error) {
        console.error('Lỗi khi tải danh sách Quận/Huyện:', error.message);
        districts.value = [];
        wards.value = [];
    }
}

async function fetchWards(districtCode) {
    if (!districtCode) {
        wards.value = [];
        return;
    }
    try {
        const cacheKey = `wards_${districtCode}`;
        const cachedWards = localStorage.getItem(cacheKey);
        if (cachedWards) {
            wards.value = JSON.parse(cachedWards);
            return;
        }
        const response = await fetch(`https://vn-public-apis.fpo.vn/wards/getByDistrict?districtCode=${districtCode}&limit=-1`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const apiData = await response.json();
        wards.value = apiData.data.data;
        localStorage.setItem(cacheKey, JSON.stringify(wards.value));
    } catch (error) {
        console.error('Lỗi khi tải danh sách Phường/Xã:', error.message);
        wards.value = [];
    }
}

// --- HÀM XỬ LÝ SỰ KIỆN ---
const loadUserProfile = () => {
  const storedUser = localStorage.getItem('user');
  if (!storedUser) return;

  const user = JSON.parse(storedUser);

  // ✅ Lấy ký tự đầu tiên của họ tên
  const firstLetter = user.ho_ten
    ? user.ho_ten.trim().charAt(0).toUpperCase()
    : 'K'; 

  // Gán dữ liệu
  userName.value = user.ho_ten || 'Khách';
  userPhone.value = user.sdt || 'Chưa có số điện thoại';
  userAvatar.value =
    user.anh_dai_dien ||
    `https://placehold.co/40x40/33ccff/FFFFFF?text=${firstLetter}`;
  tempUserName.value = user.ho_ten || '';
  tempUserPhone.value = user.sdt || '';
};


const enableEditPersonal = () => {
    tempUserName.value = userName.value;
    tempUserPhone.value = userPhone.value;
    showEditPersonalForm.value = true;
    errorMessage.value = '';
};

const cancelEditPersonal = () => {
    showEditPersonalForm.value = false;
    errorMessage.value = '';
};

const handleUpdatePersonal = async () => {
    errorMessage.value = '';
    if (!tempUserName.value.trim()) {
        errorMessage.value = 'Họ tên không được để trống.';
        return;
    }
    if (tempUserName.value.trim().length > 20) {
        errorMessage.value = 'Họ tên không được vượt quá 20 ký tự.';
        return;
    }
    if (!tempUserPhone.value.trim()) {
        errorMessage.value = 'Số điện thoại không được để trống.';
        return;
    }
    const phoneRegex = /^0[0-9]{9,10}$/; 
    if (!phoneRegex.test(tempUserPhone.value.trim())) {
        errorMessage.value = 'Số điện thoại không hợp lệ. Vui lòng nhập 10-11 chữ số và bắt đầu bằng 0.';
        return;
    }
    const storedUser = localStorage.getItem('user');
    if (!storedUser) {
        errorMessage.value = 'Vui lòng đăng nhập để cập nhật thông tin.';
        router.push('/');
        return;
    }
    const user = JSON.parse(storedUser);
    const userId = user.nguoi_dung_id;
    if (!userId) {
        errorMessage.value = 'Không thể xác định ID người dùng. Vui lòng đăng nhập lại.';
        router.push('/');
        return;
    }
    try {
        const response = await axios.put(`http://localhost:8000/api/users/${userId}`, {
            ho_ten: tempUserName.value.trim(),
            sdt: tempUserPhone.value.trim()
        });
        userName.value = tempUserName.value;
        userPhone.value = tempUserPhone.value;
        const updatedUserFromApi = response.data.user;
        let userToStore = user;
        if (updatedUserFromApi) {
            userToStore = updatedUserFromApi;
        } else {
            userToStore.ho_ten = userName.value;
            userToStore.sdt = userPhone.value;
        }
        localStorage.setItem('user', JSON.stringify(userToStore));
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Cập nhật thông tin cá nhân thành công!',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didClose: () => {
                window.location.reload();
            }
        });
        showEditPersonalForm.value = false;
    } catch (error) {
        console.error('Lỗi khi cập nhật thông tin cá nhân:', error.response?.data || error.message);
        let currentErrorMessage = 'Đã xảy ra lỗi không xác định. Vui lòng thử lại.';
        if (error.response?.data?.message) {
            currentErrorMessage = error.response.data.message;
        } else if (error.response?.data?.errors) {
            let detailedErrors = [];
            for (const key in error.response.data.errors) {
                detailedErrors = detailedErrors.concat(error.response.data.errors[key]);
            }
            currentErrorMessage = detailedErrors.join('; ');
        }
        errorMessage.value = currentErrorMessage;
    }
};

const enableEditAvatar = () => {
    tempAvatarUrl.value = userAvatar.value;
    newAvatarFile.value = null;
    showEditAvatarForm.value = true;
    errorMessage.value = '';
};

const cancelEditAvatar = () => {
    showEditAvatarForm.value = false;
    newAvatarFile.value = null;
    errorMessage.value = '';
};

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        if (!file.type.startsWith('image/')) {
            errorMessage.value = 'Vui lòng chọn một file ảnh hợp lệ.';
            newAvatarFile.value = null;
            tempAvatarUrl.value = userAvatar.value;
            return;
        }
        newAvatarFile.value = file;
        tempAvatarUrl.value = URL.createObjectURL(file);
        errorMessage.value = '';
    }
};

const handleUpdateAvatar = async () => {
    errorMessage.value = '';
    if (!newAvatarFile.value) {
        errorMessage.value = 'Vui lòng chọn một file ảnh mới.';
        return;
    }

    const storedUser = localStorage.getItem('user');
    if (!storedUser) {
        errorMessage.value = 'Vui lòng đăng nhập để cập nhật ảnh đại diện.';
        router.push('/');
        return;
    }
    const user = JSON.parse(storedUser);
    const userId = user.nguoi_dung_id;

    const formData = new FormData();
    formData.append('anh_dai_dien', newAvatarFile.value);

    try {
        const response = await axios.post(`http://localhost:8000/api/users/${userId}/avatar`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        
        userAvatar.value = response.data.anh_dai_dien;

        const updatedUser = { ...user, anh_dai_dien: userAvatar.value };
        localStorage.setItem('user', JSON.stringify(updatedUser));

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Cập nhật ảnh đại diện thành công!',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        cancelEditAvatar();
        window.location.reload();
        
    } catch (error) {
        console.error('Lỗi khi cập nhật ảnh đại diện:', error.response?.data || error.message);
        errorMessage.value = error.response?.data?.message || 'Cập nhật ảnh đại diện thất bại.';
    }
};

const handleUpdateAddress = async () => {
    errorMessage.value = '';
    if (!selectedProvinceCode.value || !selectedDistrictCode.value || !selectedWardCode.value || !streetAddress.value.trim()) {
        errorMessage.value = 'Vui lòng điền đầy đủ thông tin địa chỉ.';
        return;
    }
    const foundDistrictInSelectedProvince = districts.value.some(d => d.code === selectedDistrictCode.value);
    if (!foundDistrictInSelectedProvince) {
        errorMessage.value = 'Huyện/Quận đã chọn không hợp lệ.';
        return;
    }
    const foundWardInSelectedDistrict = wards.value.some(w => w.code === selectedWardCode.value);
    if (!foundWardInSelectedDistrict) {
        errorMessage.value = 'Phường/Xã đã chọn không hợp lệ.';
        return;
    }
    const provinceName = provinces.value.find(p => p.code === selectedProvinceCode.value)?.name_with_type || '';
    const districtName = districts.value.find(d => d.code === selectedDistrictCode.value)?.name_with_type || '';
    const wardName = wards.value.find(w => w.code === selectedWardCode.value)?.name_with_type || '';
    const fullAddress = [provinceName, districtName, wardName, streetAddress.value.trim()].filter(part => part).join(', ');
    const storedUser = localStorage.getItem('user');
    if (!storedUser) {
        errorMessage.value = 'Vui lòng đăng nhập để cập nhật địa chỉ.';
        router.push('/');
        return;
    }
    const user = JSON.parse(storedUser);
    const userId = user.nguoi_dung_id;
    try {
        isLoadingAddressData.value = true;
        if (currentAddressId.value) {
            await axios.put(`/dia_chi/${currentAddressId.value}`, {
                nguoi_dung_id: userId,
                dia_chi: fullAddress,
                mac_dinh: isDefaultAddress.value
            });
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Cập nhật địa chỉ thành công!',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        } else {
            const response = await axios.post('/dia_chi', {
                nguoi_dung_id: userId,
                dia_chi: fullAddress,
                mac_dinh: isDefaultAddress.value
            });
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Thêm địa chỉ mới thành công!',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            if (response.data && response.data.id_dia_chi) {
                currentAddressId.value = response.data.id_dia_chi;
            }
        }
    } catch (error) {
        console.error('Lỗi khi cập nhật/thêm địa chỉ:', error.response?.data || error.message);
        errorMessage.value = 'Cập nhật địa chỉ thất bại. Vui lòng thử lại.';
    } finally {
        isLoadingAddressData.value = false;
    }
};

// --- WATCHERS & HOOKS ---
watch(selectedProvinceCode, async (newCode, oldCode) => {
    if (newCode !== oldCode) {
        isLoadingAddressData.value = true;
        errorMessage.value = '';
        selectedDistrictCode.value = '';
        selectedWardCode.value = '';
        await fetchDistricts(newCode);
        isLoadingAddressData.value = false;
    }
});

watch(selectedDistrictCode, async (newCode, oldCode) => {
    if (newCode !== oldCode) {
        isLoadingAddressData.value = true;
        errorMessage.value = '';
        selectedWardCode.value = '';
        await fetchWards(newCode);
        isLoadingAddressData.value = false;
    }
});

watch(selectedWardCode, () => {
    errorMessage.value = '';
});

watch(streetAddress, () => {
    errorMessage.value = '';
});

onMounted(async () => {
    loadUserProfile();
    const storedUser = localStorage.getItem('user');
    if (!storedUser) {
        router.push('/');
        return;
    }
    const user = JSON.parse(storedUser);
    isLoadingAddressData.value = true;
    try {
        await fetchProvinces();
        const response = await axios.get(`/dia_chi/nguoi_dung/${user.nguoi_dung_id}`);
        if (response.data && response.data.length > 0) {
            const addressData = response.data[0];
            currentAddressId.value = addressData.id_dia_chi;
            const userAddress = addressData.dia_chi;
            const { street, ward, district, province } = parseAddress(userAddress);
            streetAddress.value = street;
            const foundProvince = provinces.value.find(p => p.name_with_type.toLowerCase().includes(province.toLowerCase()) || p.name.toLowerCase().includes(province.toLowerCase()));
            if (foundProvince) {
                selectedProvinceCode.value = foundProvince.code;
                await Promise.all([
                    (async () => {
                        await fetchDistricts(selectedProvinceCode.value);
                        const foundDistrict = districts.value.find(d => d.name_with_type.toLowerCase().includes(district.toLowerCase()) || d.name.toLowerCase().includes(district.toLowerCase()));
                        if (foundDistrict) {
                            selectedDistrictCode.value = foundDistrict.code;
                            await Promise.all([
                                (async () => {
                                    await fetchWards(selectedDistrictCode.value);
                                    const foundWard = wards.value.find(w => w.name_with_type.toLowerCase().includes(ward.toLowerCase()) || w.name.toLowerCase().includes(ward.toLowerCase()));
                                    if (foundWard) {
                                        selectedWardCode.value = foundWard.code;
                                    } else {
                                        console.warn('Không tìm thấy phường/xã khớp:', ward);
                                    }
                                })()
                            ]);
                        } else {
                            console.warn('Không tìm thấy quận/huyện khớp:', district);
                        }
                    })()
                ]);
            } else {
                console.warn('Không tìm thấy tỉnh/thành phố khớp:', province);
            }
        } else {
            currentAddressId.value = null;
        }
    } catch (error) {
        console.error('LỖI KHI TẢI ĐỊA CHỈ NGƯỜI DÙNG:', error.response?.data || error.message);
        currentAddressId.value = null;
    } finally {
        isLoadingAddressData.value = false;
    }
});

const handleLogout = () => {
    localStorage.removeItem('user');
    router.push('/');
};
</script>

<template>
    <h1>Thông tin tài khoản</h1>
    <hr>
    <div class="account-details-section">
        <h2>THÔNG TIN CÁ NHÂN</h2>
        <div class="user-avatar-section">
            <img :src="userAvatar" alt="Ảnh đại diện" class="avatar-display">
            <a href="#" class="edit-link" @click.prevent="enableEditAvatar">
                <i class="fas fa-camera"></i>Sửa ảnh đại diện
            </a>
        </div>

        <div v-if="showEditAvatarForm" class="avatar-edit-form">
            <div class="form-group">
                <label for="avatarInput">Chọn ảnh mới</label>
                <input type="file" id="avatarInput" @change="handleFileChange" accept="image/*">
                <div v-if="tempAvatarUrl" class="avatar-preview">
                    <img :src="tempAvatarUrl" alt="Ảnh xem trước" class="preview-image">
                </div>
            </div>
            <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
            <div class="form-actions">
                <button class="save-btn" @click="handleUpdateAvatar">LƯU</button>
                <button class="cancel-btn" @click="cancelEditAvatar">HỦY</button>
            </div>
        </div>
        
        <div v-else-if="!showEditPersonalForm">
            <p>
                {{ userName }} - {{ userPhone }}
                <a href="#" class="edit-link" @click.prevent="enableEditPersonal">
                    <i class="fas fa-edit"></i>Sửa
                </a>
            </p>
        </div>
        <div v-else class="personal-edit-form">
            <div class="form-group">
                <label for="editName">Họ và tên</label>
                <input type="text" id="editName" v-model="tempUserName">
            </div>
            <div class="form-group">
                <label for="editPhone">Số điện thoại</label>
                <input type="text" id="editPhone" v-model="tempUserPhone">
            </div>
            <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
            <div class="form-actions">
                <button class="save-btn" @click="handleUpdatePersonal">LƯU</button>
                <button class="cancel-btn" @click="cancelEditPersonal">HỦY</button>
            </div>
        </div>
    </div>
    <hr>
    <div class="shipping-address-section">
        <h2>ĐỊA CHỈ NHẬN HÀNG</h2>
        <div class="form-row">
            <div class="form-group">
                <label for="province">Tỉnh/Thành phố</label>
                <select id="province" v-model="selectedProvinceCode">
                    <option value="">Chọn Tỉnh/Thành phố</option>
                    <option v-for="province in provinces" :key="province.code" :value="province.code">
                        {{ province.name_with_type || province.name }}
                    </option>
                </select>
            </div>
            <div class="form-group">
                <label for="district">Quận/Huyện</label>
                <select id="district" v-model="selectedDistrictCode" :disabled="!selectedProvinceCode || isLoadingAddressData">
                    <option value="">Chọn Quận/Huyện</option>
                    <option v-for="district in districts" :key="district.code" :value="district.code">
                        {{ district.name_with_type || district.name }}
                    </option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="ward">Phường/Xã</label>
                <select id="ward" v-model="selectedWardCode" :disabled="!selectedDistrictCode || isLoadingAddressData">
                    <option value="">Chọn Phường/Xã</option>
                    <option v-for="ward in wards" :key="ward.code" :value="ward.code">
                        {{ ward.name_with_type || ward.name }}
                    </option>
                </select>
            </div>
            <div class="form-group">
                <label for="address">Số Nhà, Tên Đường*</label>
                <input type="text" id="address" v-model="streetAddress" :disabled="isLoadingAddressData">
            </div>
        </div>
        <p v-if="errorMessage && !showEditPersonalForm" class="error-message">{{ errorMessage }}</p>
        <button class="update-btn" @click="handleUpdateAddress" :disabled="isLoadingAddressData">CẬP NHẬT</button>
    </div>
</template>

<style scoped>
/* Thêm style cho thông báo lỗi */
.error-message {
    color: red !important;
    font-size: 14px;
    margin-top: 10px;
    margin-bottom: 5px;
    text-align: left;
    font-weight: 500;
}

.sidebar {
    background-color: #f4f6f8;
    padding: 20px;
    border-radius: 8px;
    min-width: 250px;
    color: black;
}

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

.sidebar-header {
    display: flex;
    align-items: center;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

.sidebar-header .avatar {
    border-radius: 50%;
    margin-right: 10px;
    border: 2px solid var(--primary-blue);
}

.sidebar-header .user-name {
    font-weight: 500;
    color: var(--dark-text);
}

.sidebar ul li a {
    text-decoration: none;
    color: black;
    font-size: 16px;
    display: flex;
    align-items: center;
    padding: 8px 10px;
    border-radius: 5px;
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

/* Main Content */
.main-content {
    flex-grow: 1;
    background-color: white;
    padding: 30px;
    border-radius: 8px;
    /* min-height: 500px; */
}

.main-content h1 {
    font-size: 23px;
    color: black;
    font-weight: bold;
    border-bottom: 1px solid var(--border-color);
}

.main-content hr {
    margin-top: 20px;
    margin-bottom: 20px;
    color: black;
}

.main-content h2 {
    font-size: 18px;
    color: var(--dark-text);
    margin-bottom: 15px;
}

.account-details-section,
.shipping-address-section {
    margin-bottom: 0;
    padding-bottom: 0;
}

.account-details-section p {
    font-size: 16px;
    color: var(--text-color);
}

.account-details-section .edit-link {
    color: blue;
    text-decoration: none;
    margin-left: 10px;
    font-size: 14px;
}

.account-details-section .edit-link i {
    margin-right: 5px;
    color: blue;
}

/* --- STYLE MỚI CHO FORM CHỈNH SỬA THÔNG TIN CÁ NHÂN --- */
.personal-edit-form .form-group {
    margin-bottom: 15px;
}

.personal-edit-form .form-group label {
    font-size: 14px;
    color: #555;
    margin-bottom: 5px;
    display: block;
}

.personal-edit-form .form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid black;
    border-radius: 5px;
    font-size: 15px;
}

.personal-edit-form .form-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    justify-content: flex-start; /* Căn nút về bên trái */
}

.form-actions .save-btn,
.form-actions .cancel-btn {
    padding: 10px 25px;
    border-radius: 5px;
    font-size: 15px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-weight: bold;
    margin: 0;
}

.form-actions .save-btn {
    background-color: #28a745; /* Màu xanh lá cho nút lưu */
    color: white;
    border: none;
}

.form-actions .save-btn:hover {
    background-color: #218838;
}

.form-actions .cancel-btn {
    background-color: #ff1f1f; 
    color: white;
    border: none;
}

.form-actions .cancel-btn:hover {
    background-color: #ad0000;
}

/* Kế thừa style error-message đã có */
.form-actions .error-message {
    margin-top: 10px;
    margin-bottom: 0;
}

.shipping-address-section .form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
}

.shipping-address-section .form-group {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.shipping-address-section .form-group label {
    font-size: 14px;
    color: #555;
    margin-bottom: 5px;
}

.shipping-address-section .form-group select,
.shipping-address-section .form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid black;
    border-radius: 5px;
    font-size: 15px;
    background-color: white;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http://www.w3.org/2000/svg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23000%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13.2-6.5h-258.4c-5.4%200-10.7%202.7-13.2%206.5-2.5%203.8-2.5%208.5%200%2012.3l129.2%20129.2c2.5%202.5%205.8%203.8%209.2%203.8s6.7-1.3%209.2-3.8l129.2-129.2c2.5-3.8%202.5-8.5%200-12.3z%22/%3E%3C/svg%3E');
    background-repeat: no-repeat;
    background-position: right 10px top 50%;
    background-size: 12px;
    padding-right: 30px;
}

/* Thêm style cho disabled state */
.shipping-address-section .form-group select:disabled,
.shipping-address-section .form-group input:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
    opacity: 0.7;
}

.shipping-address-section .update-btn {
    display: block;
    margin: 40px auto 0 auto;
    padding: 12px 80px;
    background-color: #4a90e2;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-weight: bold;
}

.shipping-address-section .update-btn:hover {
    background-color: #145eb1;
}

/* Style cho nút khi disabled */
.shipping-address-section .update-btn:disabled {
    background-color: #cccccc;
    cursor: not-allowed;
}


/* Global CSS Variables (cần được đặt ở App.vue hoặc file CSS global nếu muốn dùng chung) */
:root {
    --primary-blue: #007bff;
    --dark-blue: #0056b3;
    --light-grey: #f8f9fa;
    --border-color: #dee2e6;
    --text-color: #333;
    --dark-text: #212529;
    --header-bg: #e0e0e0;
    --nav-bg: #0099ff;
    --sidebar-bg: #fff;
    --footer-bg: #343a40;
    --footer-text: #adb5bd;
}

body {
    font-family: 'Roboto', sans-serif;
    line-height: 1.6;
    color: var(--text-color);
    background-color: var(--light-grey);
}
.error-message {
    color: red !important;
    font-size: 14px;
    margin-top: 10px;
    margin-bottom: 5px;
    text-align: left;
    font-weight: 500;
}

.sidebar {
    background-color: #f4f6f8;
    padding: 20px;
    border-radius: 8px;
    min-width: 250px;
    color: black;
}

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

.sidebar-header {
    display: flex;
    align-items: center;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

.sidebar-header .avatar {
    border-radius: 50%;
    margin-right: 10px;
    border: 2px solid var(--primary-blue);
}

.sidebar-header .user-name {
    font-weight: 500;
    color: var(--dark-text);
}

.sidebar ul li a {
    text-decoration: none;
    color: black;
    font-size: 16px;
    display: flex;
    align-items: center;
    padding: 8px 10px;
    border-radius: 5px;
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

/* Main Content */
.main-content {
    flex-grow: 1;
    background-color: white;
    padding: 30px;
    border-radius: 8px;
}

.main-content h1 {
    font-size: 23px;
    color: black;
    font-weight: bold;
    border-bottom: 1px solid var(--border-color);
}

.main-content hr {
    margin-top: 20px;
    margin-bottom: 20px;
    color: black;
}

.main-content h2 {
    font-size: 18px;
    color: var(--dark-text);
    margin-bottom: 15px;
}

.account-details-section,
.shipping-address-section {
    margin-bottom: 0;
    padding-bottom: 0;
}

.account-details-section p {
    font-size: 16px;
    color: var(--text-color);
}

.account-details-section .edit-link {
    color: blue;
    text-decoration: none;
    margin-left: 10px;
    font-size: 14px;
}

.account-details-section .edit-link i {
    margin-right: 5px;
    color: blue;
}

/* --- STYLE MỚI CHO FORM CHỈNH SỬA THÔNG TIN CÁ NHÂN --- */
.personal-edit-form .form-group {
    margin-bottom: 15px;
}

.personal-edit-form .form-group label {
    font-size: 14px;
    color: #555;
    margin-bottom: 5px;
    display: block;
}

.personal-edit-form .form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid black;
    border-radius: 5px;
    font-size: 15px;
}

.personal-edit-form .form-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    justify-content: flex-start;
}

.personal-edit-form .save-btn,
.personal-edit-form .cancel-btn {
    padding: 10px 25px;
    border-radius: 5px;
    font-size: 15px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-weight: bold;
    margin: 0;
}

.personal-edit-form .save-btn {
    background-color: #28a745;
    color: white;
    border: none;
}

.personal-edit-form .save-btn:hover {
    background-color: #218838;
}

.personal-edit-form .cancel-btn {
    background-color: #ff1f1f;
    color: white;
    border: none;
}

.personal-edit-form .cancel-btn:hover {
    background-color: #ad0000;
}

/* Kế thừa style error-message đã có */
.personal-edit-form .error-message {
    margin-top: 10px;
    margin-bottom: 0;
}

/* --- STYLE MỚI CHO FORM CHỈNH SỬA ẢNH ĐẠI DIỆN --- */
.user-avatar-section {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.user-avatar-section .avatar-display {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 15px;
    border: 2px solid #ddd;
}

.user-avatar-section .edit-link {
    font-size: 14px;
    color: #007bff;
    text-decoration: none;
    display: flex;
    align-items: center;
}

.user-avatar-section .edit-link i {
    margin-right: 5px;
}

.avatar-edit-form {
    background-color: #f9f9f9;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-top: 15px;
    margin-bottom: 20px;
}

.avatar-edit-form .form-group label {
    font-weight: bold;
    color: #333;
}

.avatar-edit-form .form-group input[type="file"] {
    margin-top: 10px;
}

.avatar-edit-form .avatar-preview {
    margin-top: 15px;
    text-align: center;
}

.avatar-edit-form .preview-image {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #007bff;
}

.avatar-edit-form .form-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    justify-content: flex-start;
}
</style>