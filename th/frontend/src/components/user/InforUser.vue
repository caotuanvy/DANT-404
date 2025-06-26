<template>
    <div class="content-area container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <span class="user-name">Anh <b>{{ userName }}</b></span>
            </div>
            <ul>
                <li class="active"><a href="#"><i class="fas fa-info-circle"></i> Thông tin và địa chỉ</a></li>
                <li><a href="#"><i class="fas fa-clipboard-list"></i> Đơn hàng đã mua</a></li>
                <li><a href="#"><i class="fas fa-lock"></i> Thay đổi mật khẩu</a></li>
            </ul>
            <button class="logout-btn" @click="handleLogout">Đăng Xuất</button>
        </aside>

        <main class="main-content">
            <h1>Thông tin tài khoản</h1>
            <hr>
            <div class="account-details-section">
                <h2>THÔNG TIN CÁ NHÂN</h2>
                <p>{{ userName }} - {{ userPhone }} <a href="#" class="edit-link"><i class="fas fa-edit"></i>Sửa</a></p>
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
                        <select id="district" v-model="selectedDistrictCode" :disabled="!selectedProvinceCode">
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
                        <select id="ward" v-model="selectedWardCode" :disabled="!selectedDistrictCode">
                            <option value="">Chọn Phường/Xã</option>
                            <option v-for="ward in wards" :key="ward.code" :value="ward.code">
                                {{ ward.name_with_type || ward.name }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="address">Số Nhà, Tên Đường*</label>
                        <input type="text" id="address" v-model="streetAddress">
                    </div>
                </div>
                <!-- <div class="checkbox-group">
                    <input type="checkbox" id="default-address" v-model="isDefaultAddress">
                    <label for="default-address">Đặt làm địa chỉ mặc định</label>
                </div> -->
                <button class="update-btn" @click="handleUpdateAddress">CẬP NHẬT</button>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
// import debounce from 'lodash.debounce'; // Dòng này sẽ được xóa hoặc comment lại

// Các biến reactive để lưu trữ dữ liệu người dùng và địa chỉ
const userName = ref('Khách');
const userPhone = ref('Chưa có số điện thoại');
const streetAddress = ref(''); // Số nhà, tên đường
const selectedProvinceCode = ref('');
const selectedDistrictCode = ref('');
const selectedWardCode = ref('');
const isDefaultAddress = ref(false); // Thêm biến cho địa chỉ mặc định

const currentAddressId = ref(null);

// Dữ liệu cho các dropdown
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);

const router = useRouter(); // Khởi tạo router

// Hàm để phân tích chuỗi địa chỉ
const parseAddress = (fullAddress) => {
    if (!fullAddress) {
        return {
            street: '',
            ward: '',
            district: '',
            province: ''
        };
    }

    // Tách chuỗi bằng dấu phẩy
    const parts = fullAddress.split(',').map(part => part.trim());

    let street = '';
    let ward = '';
    let district = '';
    let province = '';

    // Logic phân tích ngược từ cuối để đảm bảo chính xác (tỉnh -> huyện -> xã -> đường)
    if (parts.length >= 1) {
        province = parts.pop(); // Phần tử cuối cùng thường là Tỉnh/Thành phố
        if (province.startsWith('TP.')) { // Xử lý trường hợp "TP. HCM"
            province = province.substring(3).trim();
        }
    }
    if (parts.length >= 1) {
        district = parts.pop(); // Kế cuối là Quận/Huyện
    }
    if (parts.length >= 1) {
        ward = parts.pop(); // Tiếp theo là Phường/Xã
    }
    if (parts.length >= 1) {
        street = parts.join(', '); // Phần còn lại là số nhà, tên đường
    }

    // Loại bỏ tiền tố nếu có
    ward = ward.replace(/^(Phường|Xã)\s/i, '');
    district = district.replace(/^(Quận|Huyện|Thành phố)\s/i, '');
    province = province.replace(/^(Tỉnh|Thành phố)\s/i, '');

    return { street, ward, district, province };
};


// Hàm để điền dữ liệu vào các select box (cập nhật để hỗ trợ chọn option đã có)
async function populateSelect(selectRef, dataArray, defaultOptionText, selectedValue = null) {
    selectRef.value = dataArray || []; // Gán trực tiếp mảng dữ liệu vào ref
    if (selectRef.value.length === 0) {
        // Nếu không có dữ liệu, đảm bảo select bị vô hiệu hóa
        if (selectRef === districts) {
            selectedDistrictCode.value = '';
        } else if (selectRef === wards) {
            selectedWardCode.value = '';
        }
        return;
    }

    // Đặt giá trị mặc định nếu có selectedValue
    if (selectedValue) {
        if (selectRef === provinces) {
            selectedProvinceCode.value = selectedValue;
        } else if (selectRef === districts) {
            selectedDistrictCode.value = selectedValue;
        } else if (selectRef === wards) {
            selectedWardCode.value = selectedValue;
        }
    }
}

// Tải dữ liệu Tỉnh/Thành phố
async function fetchProvinces(selectAndPopulate = true) {
    try {
        const response = await fetch('https://vn-public-apis.fpo.vn/provinces/getAll?limit=-1');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const apiData = await response.json();
        if (selectAndPopulate) {
            await populateSelect(provinces, apiData.data.data, "Chọn Tỉnh/Thành phố");
        } else {
            provinces.value = apiData.data.data;
        }
    } catch (error) {
        console.error('Lỗi khi tải danh sách Tỉnh/Thành phố:', error);
    }
}

// Tải dữ liệu Quận/Huyện dựa trên mã Tỉnh đã chọn
async function fetchDistricts(provinceCode, selectAndPopulate = true) {
    if (!provinceCode) {
        districts.value = [];
        selectedDistrictCode.value = '';
        wards.value = [];
        selectedWardCode.value = '';
        return;
    }
    try {
        const response = await fetch(`https://vn-public-apis.fpo.vn/districts/getByProvince?provinceCode=${provinceCode}&limit=-1`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const apiData = await response.json();
        if (selectAndPopulate) {
            await populateSelect(districts, apiData.data.data, "Chọn Quận/Huyện");
        } else {
            districts.value = apiData.data.data;
        }
    } catch (error) {
        console.error('Lỗi khi tải danh sách Quận/Huyện:', error);
        districts.value = [];
    }
}

// Tải dữ liệu Phường/Xã dựa trên mã Huyện đã chọn
async function fetchWards(districtCode, selectAndPopulate = true) {
    if (!districtCode) {
        wards.value = [];
        selectedWardCode.value = '';
        return;
    }
    try {
        const response = await fetch(`https://vn-public-apis.fpo.vn/wards/getByDistrict?districtCode=${districtCode}&limit=-1`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const apiData = await response.json();
        if (selectAndPopulate) {
            await populateSelect(wards, apiData.data.data, "Chọn Phường/Xã");
        } else {
            wards.value = apiData.data.data;
        }
    } catch (error) {
        console.error('Lỗi khi tải danh sách Phường/Xã:', error);
        wards.value = [];
    }
}


// Watchers để tự động tải dữ liệu khi lựa chọn thay đổi
// KHÔNG SỬ DỤNG DEBOUNCE NẾU BẠN KHÔNG CÀI ĐẶT LODASH
watch(selectedProvinceCode, (newCode) => {
    fetchDistricts(newCode, true); // Gọi trực tiếp
});

watch(selectedDistrictCode, (newCode) => {
    fetchWards(newCode, true); // Gọi trực tiếp
});


// Hàm để tìm kiếm code từ tên trong mảng dữ liệu (province, district, ward)
const findCodeByName = (name, dataArray, isProvince = false) => {
    // Chuẩn hóa tên để so sánh (loại bỏ tiền tố, chuyển về chữ thường)
    const normalizedName = name.replace(/^(Phường|Xã|Quận|Huyện|Thành phố|Tỉnh)\s/i, '').trim().toLowerCase();

    for (const item of dataArray) {
        let itemNormalizedName = (item.name_with_type || item.name).replace(/^(Phường|Xã|Quận|Huyện|Thành phố|Tỉnh)\s/i, '').trim().toLowerCase();

        // Xử lý đặc biệt cho TP. HCM
        if (isProvince && (normalizedName === 'hcm' || normalizedName === 'hồ chí minh')) {
            if (itemNormalizedName === 'hồ chí minh') {
                return item.code;
            }
        } else if (itemNormalizedName.includes(normalizedName) || normalizedName.includes(itemNormalizedName)) {
            // Tìm kiếm tương đối
            return item.code;
        }
    }
    return null;
};


onMounted(async () => {
    // console.log('--- onMounted bắt đầu ---');
    const storedUser = localStorage.getItem('user');
    if (!storedUser) {
        // console.log('Không có người dùng trong localStorage. Chuyển hướng.');
        router.push('/');
        return; // Dừng lại nếu không có người dùng
    }

    const user = JSON.parse(storedUser);
    // console.log('Người dùng từ localStorage:', user);
    userName.value = user.ho_ten || 'Chưa có tên';
    userPhone.value = user.sdt || 'Chưa có số điện thoại';

    try {
        // console.log(`Đang gọi API địa chỉ cho người dùng ID: ${user.nguoi_dung_id}`);
        const response = await axios.get(`/dia_chi/nguoi_dung/${user.nguoi_dung_id}`);
        // console.log('Phản hồi API địa chỉ:', response.data);

        if (response.data && response.data.length > 0) { // Đảm bảo response.data tồn tại và là mảng không rỗng
            const addressData = response.data[0];
            currentAddressId.value = addressData.id_dia_chi; // Gán ID địa chỉ
            // console.log('Đã tìm thấy địa chỉ. currentAddressId.value được gán là:', currentAddressId.value);

            const userAddress = addressData.dia_chi;
            const { street, ward, district, province } = parseAddress(userAddress);
            streetAddress.value = street;

            // ... (phần còn lại của logic điền dropdown)
            // Debugging populate dropdowns
            // console.log('Đang điền dropdowns...');
            await fetchProvinces(false); // Fetch all provinces first without populating select
            const foundProvince = provinces.value.find(p => p.name_with_type.toLowerCase().includes(province.toLowerCase()) || p.name.toLowerCase().includes(province.toLowerCase()));
            if (foundProvince) {
                selectedProvinceCode.value = foundProvince.code;
                await fetchDistricts(selectedProvinceCode.value, false);
                const foundDistrict = districts.value.find(d => d.name_with_type.toLowerCase().includes(district.toLowerCase()) || d.name.toLowerCase().includes(district.toLowerCase()));
                if (foundDistrict) {
                    selectedDistrictCode.value = foundDistrict.code;
                    await fetchWards(selectedDistrictCode.value, false);
                    const foundWard = wards.value.find(w => w.name_with_type.toLowerCase().includes(ward.toLowerCase()) || w.name.toLowerCase().includes(ward.toLowerCase()));
                    if (foundWard) {
                        selectedWardCode.value = foundWard.code;
                    } else {
                        console.log('Không tìm thấy phường/xã khớp:', ward);
                    }
                } else {
                    console.log('Không tìm thấy quận/huyện khớp:', district);
                }
            } else {
                console.log('Không tìm thấy tỉnh/thành phố khớp:', province);
            }

        } else {
            console.log('Người dùng chưa có địa chỉ nào trong database, hoặc API trả về mảng rỗng.');
            currentAddressId.value = null; // Đảm bảo là null nếu không có địa chỉ
        }
    } catch (error) {
        console.error('LỖI KHI TẢI ĐỊA CHỈ NGƯỜI DÙNG TRONG onMounted:', error.response?.data || error.message);
        currentAddressId.value = null; // Đảm bảo là null nếu có lỗi
    }

    // Luôn fetch provinces ban đầu để dropdown không rỗng ngay cả khi không có địa chỉ
    if (provinces.value.length === 0) {
        console.log('Lần đầu tải provinces...');
        await fetchProvinces(true);
    }
    // console.log('--- onMounted kết thúc ---');
});

// Xử lý đăng xuất (nếu bạn có)
const handleLogout = () => {
    localStorage.removeItem('user'); // Xóa thông tin người dùng khỏi localStorage
    router.push('/'); // Chuyển hướng đến trang đăng nhập
};

const handleUpdateAddress = async () => {
    // Kiểm tra xem đã chọn đủ Tỉnh/Thành phố, Quận/Huyện, Phường/Xã và nhập Số nhà/Tên đường chưa
    if (!selectedProvinceCode.value || !selectedDistrictCode.value || !selectedWardCode.value || !streetAddress.value.trim()) {
        alert('Vui lòng điền đầy đủ thông tin địa chỉ (Tỉnh/Thành phố, Quận/Huyện, Phường/Xã, Số Nhà/Tên Đường).');
        return;
    }

    // Lấy tên đầy đủ của Tỉnh/Thành phố, Quận/Huyện, Phường/Xã từ code đã chọn
    const provinceName = provinces.value.find(p => p.code === selectedProvinceCode.value)?.name_with_type || '';
    const districtName = districts.value.find(d => d.code === selectedDistrictCode.value)?.name_with_type || '';
    const wardName = wards.value.find(w => w.code === selectedWardCode.value)?.name_with_type || '';

    // Tạo chuỗi địa chỉ đầy đủ
    // Đảm bảo không có dấu phẩy thừa nếu một phần tử nào đó rỗng
    const addressParts = [
        streetAddress.value.trim(),
        wardName,
        districtName,
        provinceName
    ].filter(part => part); // Lọc bỏ các phần tử rỗng

    const fullAddress = addressParts.join(', ');


    const storedUser = localStorage.getItem('user');
    if (!storedUser) {
        alert('Vui lòng đăng nhập để cập nhật địa chỉ.');
        router.push('/'); // Có thể chuyển hướng nếu không có người dùng
        return;
    }
    const user = JSON.parse(storedUser);
    const userId = user.nguoi_dung_id; // Đảm bảo dòng này đã được uncomment

    try {
        if (currentAddressId.value) {
            // Cập nhật địa chỉ hiện có
            console.log(`Đang cập nhật địa chỉ ID: ${currentAddressId.value} cho người dùng ID: ${userId} với địa chỉ: ${fullAddress}`);
            await axios.put(`/dia_chi/${currentAddressId.value}`, {
                nguoi_dung_id: userId,
                dia_chi: fullAddress,
                mac_dinh: isDefaultAddress.value // Thêm trường mặc định
            });
            alert('Cập nhật địa chỉ thành công!');
        } else {
            // Tạo địa chỉ mới nếu chưa có
            console.log(`Đang tạo địa chỉ mới cho người dùng ID: ${userId} với địa chỉ: ${fullAddress}`);
            const response = await axios.post('/dia_chi', {
                nguoi_dung_id: userId,
                dia_chi: fullAddress,
                mac_dinh: isDefaultAddress.value // Thêm trường mặc định
            });
            alert('Thêm địa chỉ mới thành công!');
            // Sau khi POST thành công, gán ID của địa chỉ mới vào currentAddressId
            if (response.data && response.data.id_dia_chi) {
                currentAddressId.value = response.data.id_dia_chi;
                console.log('Đã thêm địa chỉ mới, gán currentAddressId là:', currentAddressId.value);
            }
        }
    } catch (error) {
        console.error('Lỗi khi cập nhật/thêm địa chỉ:', error.response?.data || error.message);
        alert('Cập nhật địa chỉ thất bại. Vui lòng thử lại.');
    }
};
</script>

<style scoped>
/* (Không thay đổi phần style vì lỗi không liên quan đến CSS) */
/* Đã sửa background-color của sidebar */
.sidebar {
    background-color: #f4f6f8;
    /* Màu nền cho sidebar */
    padding: 20px;
    border-radius: 8px;
    min-width: 250px;
    color: black;
    /* box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05); */
    /* Thêm lại box-shadow nếu muốn */
}

/* Giữ nguyên các style khác từ trước */
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
    /* margin-bottom: 20px; */
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
    /* transition: background-color 0.3s ease; */
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
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
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
    /* border-bottom: 1px solid var(--border-color); */
    /* Đã bỏ border-bottom ở đây vì dùng <hr> */
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

.shipping-address-section .form-group input:focus,
.shipping-address-section .form-group select:focus {
    border-color: black;
    outline: none;
}

.shipping-address-section .checkbox-group {
    display: flex;
    align-items: center;
    margin-top: 20px;
    margin-bottom: 25px;
}

.shipping-address-section .checkbox-group input[type="checkbox"] {
    margin-right: 10px;
    width: 18px;
    height: 18px;
}

.shipping-address-section .checkbox-group label {
    font-size: 15px;
    color: var(--text-color);
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

/* Global CSS Variables (cần được đặt ở App.vue hoặc file CSS global nếu muốn dùng chung) */
/* Nếu header/footer cũng dùng các biến này, bạn nên định nghĩa chúng ở cấp độ cao hơn. */
/* Tôi sẽ để chúng ở đây để component này hoạt động độc lập nếu được nhúng vào môi trường khác. */
/* Nhưng lý tưởng là chúng nên ở một file CSS global hoặc App.vue */
:root {
    --primary-blue: #007bff;
    --dark-blue: #0056b3;
    --light-grey: #f8f9fa;
    --border-color: #dee2e6;
    --text-color: #333;
    --dark-text: #212529;
    --header-bg: #e0e0e0;
    /* Đây là của header, có thể bỏ nếu header là component riêng */
    --nav-bg: #0099ff;
    /* Đây là của nav, có thể bỏ nếu nav là component riêng */
    --sidebar-bg: #fff;
    --footer-bg: #343a40;
    /* Đây là của footer, có thể bỏ nếu footer là component riêng */
    --footer-text: #adb5bd;
    /* Đây là của footer, có thể bỏ nếu footer là component riêng */
}

/* Các style chung cho body, container, v.v. cũng nên được đưa ra file CSS global */
/* hoặc vào App.vue nếu bạn chỉ muốn nó ảnh hưởng đến các component trong ứng dụng Vue */
body {
    font-family: 'Roboto', sans-serif;
    line-height: 1.6;
    color: var(--text-color);
    background-color: var(--light-grey);
}
</style>