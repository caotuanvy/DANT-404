<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

// Các biến phản ứng để lưu trữ dữ liệu người dùng và địa chỉ
const userName = ref('Khách');
const userPhone = ref('Chưa có số điện thoại');
const streetAddress = ref(''); // Số nhà, tên đường
const selectedProvinceCode = ref('');
const selectedDistrictCode = ref('');
const selectedWardCode = ref('');
const isDefaultAddress = ref(false); // Thêm biến cho mặc định địa chỉ
const currentAddressId = ref(null); // Dữ liệu cho ID địa chỉ hiện tại

// Biến để hiển thị thông báo lỗi
const errorMessage = ref('');

// Biến trạng thái để kiểm soát nút CẬP NHẬT
const isLoadingAddressData = ref(false);

// Dữ liệu cho các tỉnh/thành phố, quận/huyện, phường/xã thả xuống
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);

const router = useRouter(); // Khởi tạo router

// Hàm để phân tích chuỗi địa chỉ
// Giả định thứ tự trong DB là: Tỉnh, Huyện, Xã, Đường (phần còn lại)
const parseAddress = (fullAddress) => {
    if (!fullAddress) {
        return { street: '', ward: '', district: '', province: '' };
    }

    const parts = fullAddress.split(',').map(part => part.trim());

    let province = '';
    let district = '';
    let ward = '';
    let street = '';

    // Cần cẩn thận với định dạng chuỗi nếu nó không tuân theo một quy tắc nghiêm ngặt.
    // Ví dụ: "Tỉnh A, Huyện B, Xã C, Số 123 Đường D"
    // Hoặc "Số 123 Đường D, Xã C, Huyện B, Tỉnh A" (phổ biến hơn ở VN)
    // Dựa trên yêu cầu của bạn "thứ tự trên database là tỉnh huyện xã đường",
    // chúng ta sẽ phân tích từ đầu chuỗi.
    if (parts.length >= 4) { // Ít nhất phải có Tỉnh, Huyện, Xã, và một phần của Đường
        province = parts[0];
        district = parts[1];
        ward = parts[2];
        street = parts.slice(3).join(', '); // Phần còn lại là số nhà, tên đường
    } else if (parts.length === 3) { // Tỉnh, Huyện, Xã
        province = parts[0];
        district = parts[1];
        ward = parts[2];
    } else if (parts.length === 2) { // Tỉnh, Huyện
        province = parts[0];
        district = parts[1];
    } else if (parts.length === 1) { // Chỉ có Tỉnh
        province = parts[0];
    }

    // Loại bỏ tiền tố (chú ý: việc này có thể gây sai lệch nếu tên tỉnh/huyện/xã tự nhiên có chứa từ này)
    ward = ward.replace(/^(Phường|Xã)\s/i, '');
    district = district.replace(/^(Quận|Huyện|Thành phố)\s/i, '');
    province = province.replace(/^(Tỉnh|Thành phố)\s/i, '');

    return { street, ward, district, province };
};

// Hàm để điền dữ liệu vào các select box
async function populateSelect(selectRef, dataArray, selectedValue = null) {
    selectRef.value = dataArray || [];
    // Không cần gán selectedValue ở đây, v-model sẽ tự động làm điều đó
    // khi dataArray được cập nhật và selected...Code được gán giá trị
}

// Tải dữ liệu Tỉnh/Thành phố
async function fetchProvinces() {
    try {
        const response = await fetch('https://vn-public-apis.fpo.vn/provinces/getAll?limit=-1');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const apiData = await response.json();
        await populateSelect(provinces, apiData.data.data);
    } catch (error) {
        console.error('Lỗi khi tải danh sách Tỉnh/Thành phố:', error);
        provinces.value = []; // Đảm bảo làm rỗng nếu có lỗi
    }
}

// Tải dữ liệu Quận/Huyện dựa trên mã Tỉnh đã chọn
async function fetchDistricts(provinceCode) {
    if (!provinceCode) {
        districts.value = [];
        wards.value = []; // Reset wards khi không có provinceCode
        return;
    }
    try {
        const response = await fetch(`https://vn-public-apis.fpo.vn/districts/getByProvince?provinceCode=${provinceCode}&limit=-1`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const apiData = await response.json();
        await populateSelect(districts, apiData.data.data);
    } catch (error) {
        console.error('Lỗi khi tải danh sách Quận/Huyện:', error);
        districts.value = [];
        wards.value = []; // Reset wards khi có lỗi district
    }
}

// Tải dữ liệu Phường/Xã dựa trên mã Huyện đã chọn
async function fetchWards(districtCode) {
    if (!districtCode) {
        wards.value = [];
        return;
    }
    try {
        const response = await fetch(`https://vn-public-apis.fpo.vn/wards/getByDistrict?districtCode=${districtCode}&limit=-1`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const apiData = await response.json();
        await populateSelect(wards, apiData.data.data);
    } catch (error) {
        console.error('Lỗi khi tải danh sách Phường/Xã:', error);
        wards.value = [];
    }
}

// Watchers để tự động tải dữ liệu khi lựa chọn thay đổi
watch(selectedProvinceCode, async (newCode, oldCode) => {
    // Chỉ chạy khi mã tỉnh thực sự thay đổi hoặc từ rỗng sang có giá trị
    if (newCode !== oldCode) {
        isLoadingAddressData.value = true; // Bắt đầu tải, vô hiệu hóa nút
        errorMessage.value = ''; // Xóa lỗi cũ
        selectedDistrictCode.value = ''; // Reset Quận/Huyện ngay lập tức
        selectedWardCode.value = '';    // Reset Phường/Xã ngay lập tức

        await fetchDistricts(newCode); // Tải quận/huyện cho tỉnh mới
        isLoadingAddressData.value = false; // Tải xong, kích hoạt lại nút
    }
});

watch(selectedDistrictCode, async (newCode, oldCode) => {
    // Chỉ chạy khi mã huyện thực sự thay đổi hoặc từ rỗng sang có giá trị
    if (newCode !== oldCode) {
        isLoadingAddressData.value = true; // Bắt đầu tải, vô hiệu hóa nút
        errorMessage.value = ''; // Xóa lỗi cũ
        selectedWardCode.value = ''; // Reset Phường/Xã ngay lập tức

        await fetchWards(newCode); // Tải phường/xã cho huyện mới
        isLoadingAddressData.value = false; // Tải xong, kích hoạt lại nút
    }
});

watch(selectedWardCode, () => {
    errorMessage.value = ''; // Xóa lỗi khi người dùng thay đổi lựa chọn
});

watch(streetAddress, () => {
    errorMessage.value = ''; // Xóa lỗi khi người dùng thay đổi địa chỉ đường
});


onMounted(async () => {
    const storedUser = localStorage.getItem('user');
    if (!storedUser) {
        router.push('/');
        return;
    }

    const user = JSON.parse(storedUser);
    userName.value = user.ho_ten || 'Chưa có tên';
    userPhone.value = user.sdt || 'Chưa có số điện thoại';

    isLoadingAddressData.value = true; // Vô hiệu hóa nút trong quá trình tải ban đầu

    try {
        // Luôn tải danh sách tỉnh trước
        await fetchProvinces();

        const response = await axios.get(`/dia_chi/nguoi_dung/${user.nguoi_dung_id}`);

        if (response.data && response.data.length > 0) {
            const addressData = response.data[0];
            currentAddressId.value = addressData.id_dia_chi;

            const userAddress = addressData.dia_chi;
            const { street, ward, district, province } = parseAddress(userAddress);
            streetAddress.value = street;

            // Tìm và gán mã tỉnh
            const foundProvince = provinces.value.find(p =>
                p.name_with_type.toLowerCase().includes(province.toLowerCase()) ||
                p.name.toLowerCase().includes(province.toLowerCase())
            );

            if (foundProvince) {
                selectedProvinceCode.value = foundProvince.code;

                // Tải danh sách huyện cho tỉnh đã tìm thấy
                // Watcher selectedProvinceCode sẽ tự động fetchDistricts,
                // nhưng chúng ta cần đảm bảo nó hoàn thành trước khi tìm huyện
                // Dùng Promise.all để chờ tất cả các promise bên trong hoàn thành
                await Promise.all([
                    (async () => {
                        await fetchDistricts(selectedProvinceCode.value); // Fetch districts
                        const foundDistrict = districts.value.find(d =>
                            d.name_with_type.toLowerCase().includes(district.toLowerCase()) ||
                            d.name.toLowerCase().includes(district.toLowerCase())
                        );
                        if (foundDistrict) {
                            selectedDistrictCode.value = foundDistrict.code;

                            // Tải danh sách xã cho huyện đã tìm thấy
                            await Promise.all([
                                (async () => {
                                    await fetchWards(selectedDistrictCode.value); // Fetch wards
                                    const foundWard = wards.value.find(w =>
                                        w.name_with_type.toLowerCase().includes(ward.toLowerCase()) ||
                                        w.name.toLowerCase().includes(ward.toLowerCase())
                                    );
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
            console.log('Người dùng chưa có địa chỉ nào trong database, hoặc API trả về mảng rỗng.');
            currentAddressId.value = null; // Đảm bảo là null nếu không có địa chỉ
        }
    } catch (error) {
        console.error('LỖI KHI TẢI ĐỊA CHỈ NGƯỜI DÙNG TRONG onMounted:', error.response?.data || error.message);
        currentAddressId.value = null; // Đảm bảo là null nếu có lỗi
    } finally {
        isLoadingAddressData.value = false; // Bất kể thành công hay thất bại, kích hoạt lại nút
    }
});

// Xử lý đăng xuất (nếu bạn có)
const handleLogout = () => {
    localStorage.removeItem('user'); // Xóa thông tin người dùng khỏi localStorage
    router.push('/'); // Chuyển hướng đến trang đăng nhập
};

const handleUpdateAddress = async () => {
    // Xóa thông báo lỗi cũ mỗi khi cố gắng cập nhật
    errorMessage.value = '';

    // Kiểm tra xem đã chọn đủ Tỉnh/Thành phố, Quận/Huyện, Phường/Xã và nhập Số nhà/Tên đường chưa
    if (!selectedProvinceCode.value || !selectedDistrictCode.value || !selectedWardCode.value || !streetAddress.value.trim()) {
        errorMessage.value = 'Vui lòng điền đầy đủ thông tin địa chỉ (Tỉnh/Thành phố, Quận/Huyện, Phường/Xã, Số Nhà/Tên Đường).';
        return;
    }

    // --- BẮT ĐẦU LOGIC KIỂM TRA MỚI ---
    // 1. Kiểm tra xem Huyện đã chọn có thuộc Tỉnh đã chọn không
    const foundDistrictInSelectedProvince = districts.value.some(d => d.code === selectedDistrictCode.value);
    if (!foundDistrictInSelectedProvince) {
        errorMessage.value = 'Huyện/Quận đã chọn không hợp lệ cho Tỉnh/Thành phố hiện tại. Vui lòng chọn lại.';
        return;
    }

    // 2. Kiểm tra xem Xã đã chọn có thuộc Huyện đã chọn không
    const foundWardInSelectedDistrict = wards.value.some(w => w.code === selectedWardCode.value);
    if (!foundWardInSelectedDistrict) {
        errorMessage.value = 'Phường/Xã đã chọn không hợp lệ cho Quận/Huyện hiện tại. Vui lòng chọn lại.';
        return;
    }
    // --- KẾT THÚC LOGIC KIỂM TRA MỚI ---

    // Lấy tên đầy đủ của Tỉnh/Thành phố, Quận/Huyện, Phường/Xã từ code đã chọn
    const provinceName = provinces.value.find(p => p.code === selectedProvinceCode.value)?.name_with_type || '';
    const districtName = districts.value.find(d => d.code === selectedDistrictCode.value)?.name_with_type || '';
    const wardName = wards.value.find(w => w.code === selectedWardCode.value)?.name_with_type || '';

    // Tạo chuỗi địa chỉ đầy đủ theo thứ tự: Tỉnh, Huyện, Xã, Đường (đúng theo yêu cầu của bạn)
    const addressParts = [
        provinceName,
        districtName,
        wardName,
        streetAddress.value.trim()
    ].filter(part => part); // Vẫn lọc bỏ các phần tử rỗng

    const fullAddress = addressParts.join(', '); // Chuỗi địa chỉ sẽ được ghép theo thứ tự này

    const storedUser = localStorage.getItem('user');
    if (!storedUser) {
        errorMessage.value = 'Vui lòng đăng nhập để cập nhật địa chỉ.';
        router.push('/');
        return;
    }
    const user = JSON.parse(storedUser);
    const userId = user.nguoi_dung_id;

    try {
        isLoadingAddressData.value = true; // Vô hiệu hóa nút khi đang gửi dữ liệu
        if (currentAddressId.value) {
            // Cập nhật địa chỉ hiện có
            console.log(`Đang cập nhật địa chỉ ID: ${currentAddressId.value} cho người dùng ID: ${userId} với địa chỉ: ${fullAddress}`);
            await axios.put(`/dia_chi/${currentAddressId.value}`, {
                nguoi_dung_id: userId,
                dia_chi: fullAddress,
                mac_dinh: isDefaultAddress.value
            });
            Swal.fire({
                title: 'Cập nhật địa chỉ thành công!',
                icon: 'success',
                confirmButtonText: 'Ok'
            });
        } else {
            // Tạo địa chỉ mới nếu chưa có
            console.log(`Đang tạo địa chỉ mới cho người dùng ID: ${userId} với địa chỉ: ${fullAddress}`);
            const response = await axios.post('/dia_chi', {
                nguoi_dung_id: userId,
                dia_chi: fullAddress,
                mac_dinh: isDefaultAddress.value
            });
            Swal.fire({
                title: 'Thêm địa chỉ mới thành công!',
                icon: 'success',
                confirmButtonText: 'Ok'
            });
            if (response.data && response.data.id_dia_chi) {
                currentAddressId.value = response.data.id_dia_chi;
                console.log('Đã thêm địa chỉ mới, gán currentAddressId là:', currentAddressId.value);
            }
        }
    } catch (error) {
        console.error('Lỗi khi cập nhật/thêm địa chỉ:', error.response?.data || error.message);
        errorMessage.value = 'Cập nhật địa chỉ thất bại. Vui lòng thử lại.';
    } finally {
        isLoadingAddressData.value = false; // Kích hoạt lại nút sau khi hoàn tất (thành công hoặc thất bại)
    }
};
</script>

<template>
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
        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
        <button class="update-btn" @click="handleUpdateAddress" :disabled="isLoadingAddressData">CẬP NHẬT</button>
    </div>
</template>

<style scoped>
/* Thêm style cho thông báo lỗi */
.error-message {
    color: red;
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
</style>