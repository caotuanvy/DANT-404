<template>
  <div class="cart-page">
    <header class="header">
      <h1 class="header-title">Giỏ hàng ({{ totalItems }} sản phẩm)</h1>
    </header>

    <ChonMaGiamGiaModal
      v-if="showCouponModal"
      :coupons="myCoupons"
      @close="showCouponModal = false"
      @coupon-selected="handleCouponSelection"
    />

    <div class="main-content">
      <div class="product-list" v-if="products.length > 0">
        <div v-for="product in products" :key="product.id" class="product-item">
          <img :src="imageBaseUrl + product.image" :alt="product.name" class="product-image" />
          <div class="product-details">
            <h3 class="product-name">{{ product.name }}</h3>
            <p class="product-weight">{{ product.weight }}</p>
            <p class="product-price">
              <span v-if="product.originalPrice" class="original">{{ formatPrice(product.originalPrice) }}</span>
              {{ formatPrice(product.price) }}
            </p>
            <p class="product-item-total-price">
              Tổng: {{ formatPrice(product.total_item_price) }}
            </p>
          </div>
          <div class="product-quantity-control">
            <button @click="decreaseQuantity(product.id)" class="quantity-button">-</button>
            <span class="quantity">{{ product.quantity }}</span>
            <button @click="increaseQuantity(product.id)" class="quantity-button">+</button>
            <button @click="removeProduct(product.id)" class="remove-button">
              <i class="fa-solid fa-trash-can"></i>
            </button>
          </div>
        </div>

        <div class="discount-code">
          <div class="discount-label-group">
            <p class="discount-label">Mã giảm giá</p>
            <button class="select-coupon-btn" @click="showCouponModal = true">Chọn hoặc nhập mã</button>
          </div>
          <div class="discount-input-group">
            <input type="text" placeholder="Nhập mã giảm giá" class="discount-input" v-model="couponCode" />
            <button class="apply-button" @click="applyCoupon" :disabled="isLoadingCoupon">
              {{ isLoadingCoupon ? 'Đang xử lý...' : 'Áp dụng' }}
            </button>
          </div>
          <p v-if="couponErrorMessage" class="error-message">{{ couponErrorMessage }}</p>
          <div v-if="discountAmount > 0" class="discount-info">
            <p>Đã giảm: -{{ formatPrice(discountAmount) }}</p>
            <h4>Thành tiền: {{ formatPrice(subtotal - discountAmount) }}</h4>
          </div>
        </div>
      </div>
      <div v-else class="empty-cart-message">
        <p>Giỏ hàng của bạn đang trống.</p>
        <router-link to="/" class="back-to-shop">Tiếp tục mua sắm</router-link>
      </div>

      <div class="order-summary-panel" v-if="products.length > 0">
        <div class="delivery-address">
          <h2 class="panel-title">Địa chỉ giao hàng</h2>
          <div v-if="!showAddressForm">
            <p><strong>Người nhận:</strong> {{ displayedAddress.ho_ten }}</p>
            <p><strong>SĐT:</strong> {{ displayedAddress.sdt }}</p>
            <p><strong>Địa chỉ:</strong> {{ displayedAddress.dia_chi || 'Chưa có địa chỉ' }}</p>
            <button class="change-address-btn" @click="changeAddress">
              {{ displayedAddress.dia_chi ? 'Thay đổi địa chỉ' : 'Thêm địa chỉ' }}
            </button>
          </div>
          <div v-else class="address-edit-form">
            <p v-if="isLoadingAddressData">Đang tải dữ liệu địa chỉ...</p>
            <div v-else>
              <div class="form-group">
                <label for="fullName">Họ tên người nhận</label>
                <input type="text" id="fullName" v-model="displayedAddress.ho_ten" placeholder="Họ tên" />
              </div>
              <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <input type="tel" id="phone" v-model="displayedAddress.sdt" placeholder="Số điện thoại" />
              </div>
              <div class="form-group">
                <label for="province">Tỉnh/Thành phố</label>
                <select id="province" v-model="selectedProvinceCode">
                  <option value="">Chọn Tỉnh/Thành phố</option>
                  <option v-for="province in provinces" :key="province.code" :value="province.code">
                    {{ province.name_with_type }}
                  </option>
                </select>
              </div>
              <div class="form-group">
                <label for="district">Quận/Huyện</label>
                <select id="district" v-model="selectedDistrictCode" :disabled="!selectedProvinceCode">
                  <option value="">Chọn Quận/Huyện</option>
                  <option v-for="district in districts" :key="district.code" :value="district.code">
                    {{ district.name_with_type }}
                  </option>
                </select>
              </div>
              <div class="form-group">
                <label for="ward">Phường/Xã</label>
                <select id="ward" v-model="selectedWardCode" :disabled="!selectedDistrictCode">
                  <option value="">Chọn Phường/Xã</option>
                  <option v-for="ward in wards" :key="ward.code" :value="ward.code">
                    {{ ward.name_with_type }}
                  </option>
                </select>
              </div>
              <div class="form-group">
                <label for="streetAddress">Số nhà, Tên đường</label>
                <input type="text" id="streetAddress" v-model="streetAddress" placeholder="VD: 123 Nguyễn Văn Linh" />
              </div>
              <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
              <div class="form-actions">
                <button class="save-btn" @click="handleUpdateAddress">Lưu địa chỉ</button>
                <button class="cancel-btn" @click="cancelAddressChange">Hủy</button>
              </div>
            </div>
          </div>
        </div>

        <div class="delivery-options">
          <h2 class="panel-title">Phương thức giao hàng</h2>
          <div class="delivery-option" :class="{ selected: deliveryMethod === 'standard' }" @click="deliveryMethod = 'standard'">
            <input type="radio" id="standard" value="standard" v-model="deliveryMethod" />
            <div class="option-details">
              <span class="option-name">Giao hàng tiêu chuẩn</span>
              <span class="option-time">3-5 ngày làm việc</span>
            </div>
            <span class="option-price">{{ formatPrice(15000) }}</span>
          </div>
          <div class="delivery-option" :class="{ selected: deliveryMethod === 'express' }" @click="deliveryMethod = 'express'">
            <input type="radio" id="express" value="express" v-model="deliveryMethod" />
            <div class="option-details">
              <span class="option-name">Giao hàng nhanh</span>
              <span class="option-time">1-2 ngày làm việc</span>
            </div>
            <span class="option-price">{{ formatPrice(25000) }}</span>
          </div>
        </div>

        <div class="payment-methods">
          <h2 class="panel-title">Phương thức thanh toán</h2>
          <div class="payment-method" :class="{ selected: paymentMethod === 'cod' }" @click="paymentMethod = 'cod'">
            <input type="radio" id="cod" value="cod" v-model="paymentMethod" />
            <div class="option-details">
              <span class="option-name">Thanh toán khi nhận hàng (COD)</span>
              <span class="option-description">Trả tiền mặt khi đơn hàng được giao đến bạn.</span>
            </div>
          </div>
          <div class="payment-method" :class="{ selected: paymentMethod === 'vnpay' }" @click="paymentMethod = 'vnpay'">
            <input type="radio" id="vnpay" value="vnpay" v-model="paymentMethod" />
            <div class="option-details">
              <span class="option-name">Thanh toán VNPAY-QR</span>
              <span class="option-description">Quét mã QR bằng ứng dụng ngân hàng.</span>
            </div>
          </div>
        </div>

        <div class="order-summary">
          <h2 class="panel-title">Tóm tắt đơn hàng</h2>
          <div class="summary-item">
            <span>Tổng tiền hàng</span>
            <span>{{ formatPrice(subtotal) }}</span>
          </div>
          <div class="summary-item" v-if="discountAmount > 0">
            <span>Giảm giá</span>
            <span>-{{ formatPrice(discountAmount) }}</span>
          </div>
          <div class="summary-item">
            <span>Phí vận chuyển</span>
            <span>{{ formatPrice(deliveryFee) }}</span>
          </div>
          <div class="summary-total">
            <span>Tổng cộng</span>
            <span>{{ formatPrice(totalAmount) }}</span>
          </div>
          <button class="place-order-button" @click="placeOrder" :disabled="isPlacingOrder">
            {{ isPlacingOrder ? 'Đang xử lý...' : `ĐẶT HÀNG (${formatPrice(totalAmount)})` }}
          </button>
          <p class="terms-text">
            Bằng cách đặt hàng, bạn đồng ý với Điều khoản và điều kiện của chúng tôi.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted, ref, watch, computed } from "vue";
import axios from "axios";
import Swal from "sweetalert2";
import { useRouter, useRoute } from 'vue-router';
import ChonMaGiamGiaModal from './ChonMaGiamGiaModal.vue';

export default {
  name: "CartPage",
  components: {
    ChonMaGiamGiaModal,
  },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const products = ref([]);
    const deliveryMethod = ref("standard");
    const paymentMethod = ref("cod");
    const showAddressForm = ref(false);
    const displayedAddress = ref({ ho_ten: "", sdt: "", dia_chi: "", id_dia_chi: null });
    const provinces = ref([]);
    const districts = ref([]);
    const wards = ref([]);
    const selectedProvinceCode = ref('');
    const selectedDistrictCode = ref('');
    const selectedWardCode = ref('');
    const streetAddress = ref('');
    const isLoadingAddressData = ref(false);
    const errorMessage = ref('');
    const discountAmount = ref(0);
    const couponCode = ref("");
    const isLoadingCoupon = ref(false);
    const couponErrorMessage = ref("");
    const myCoupons = ref([]);
    const showCouponModal = ref(false);
    const imageBaseUrl = import.meta.env.VITE_IMAGE_BASE_URL;
    const isPlacingOrder = ref(false);

    // Computed properties
    const totalItems = computed(() => {
      return products.value.reduce((acc, product) => acc + product.quantity, 0);
    });
    const subtotal = computed(() => {
      return products.value.reduce((acc, product) => acc + product.price * product.quantity, 0);
    });
    const deliveryFee = computed(() => {
      if (deliveryMethod.value === "standard") return 15000;
      if (deliveryMethod.value === "express") return 25000;
      return 0;
    });
    const totalAmount = computed(() => {
      return Math.max(0, subtotal.value - discountAmount.value) + deliveryFee.value;
    });

    // Helper functions
    const formatPrice = (price) => {
      return new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(price);
    };

    // Address-related functions
    const parseAddress = (fullAddress) => {
        if (!fullAddress) {
            return { street: '', ward: '', district: '', province: '' };
        }
        const parts = fullAddress.split(',').map(part => part.trim());
        const [province, district, ward, ...streetParts] = parts.reverse();
        const street = streetParts.reverse().join(', ').trim();

        const cleanName = (name) => {
            if (!name) return '';
            return name.replace(/^(Tỉnh|Thành phố|Quận|Huyện|Phường|Xã)\s/i, '').trim();
        };

        return {
            street: street,
            ward: cleanName(ward),
            district: cleanName(district),
            province: cleanName(province)
        };
    };

    const loadInitialAddress = async () => {
        const user = JSON.parse(localStorage.getItem("user"));
        const userId = user?.nguoi_dung_id || user?.id;
        if (!userId) { return; }
        try {
            const response = await axios.get(`http://localhost:8000/api/dia_chi/nguoi_dung/${userId}`);
            const addresses = response.data;
            let defaultAddress = addresses && Array.isArray(addresses) && addresses.length > 0 ? addresses[0] : null;
            if (defaultAddress) {
                displayedAddress.value = {
                    ho_ten: defaultAddress.ho_ten || user.ho_ten,
                    sdt: defaultAddress.sdt || user.sdt,
                    dia_chi: defaultAddress.dia_chi,
                    id_dia_chi: defaultAddress.id_dia_chi || defaultAddress.id
                };
                showAddressForm.value = false;
            } else {
                displayedAddress.value = { ho_ten: user.ho_ten || "", sdt: user.sdt || "", dia_chi: "" };
                showAddressForm.value = true;
            }
        } catch (error) {
            console.error("Lỗi khi tải địa chỉ ban đầu:", error);
            displayedAddress.value = { ho_ten: user.ho_ten || "", sdt: user.sdt || "", dia_chi: "" };
            showAddressForm.value = true;
        }
    };

    const changeAddress = async () => {
        showAddressForm.value = true;
        isLoadingAddressData.value = true;
        errorMessage.value = '';
        try {
            await fetchProvinces();
        } catch (error) {
            console.error("Lỗi khi tải dữ liệu địa chỉ:", error);
            errorMessage.value = "Không thể tải dữ liệu địa chỉ. Vui lòng thử lại.";
        } finally {
            isLoadingAddressData.value = false;
        }
    };

    const cancelAddressChange = () => {
        showAddressForm.value = false;
        errorMessage.value = '';
        loadInitialAddress();
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

    const handleUpdateAddress = async () => {
    errorMessage.value = '';
    // Giữ nguyên phần validate ở đầu
    if (!selectedProvinceCode.value || !selectedDistrictCode.value || !selectedWardCode.value || !streetAddress.value.trim() || !displayedAddress.value.ho_ten || !displayedAddress.value.sdt) {
        errorMessage.value = 'Vui lòng điền đầy đủ thông tin Họ tên, SĐT và địa chỉ.';
        return;
    }
    const foundProvince = provinces.value.find(p => p.code === selectedProvinceCode.value);
    const foundDistrict = districts.value.find(d => d.code === selectedDistrictCode.value);
    const foundWard = wards.value.find(w => w.code === selectedWardCode.value);
    
    if (!foundProvince || !foundDistrict || !foundWard) {
        errorMessage.value = 'Dữ liệu địa chỉ không hợp lệ. Vui lòng chọn lại.';
        return;
    }
    
    const fullAddress = [
    foundProvince.name_with_type || foundProvince.name,
    foundDistrict.name_with_type || foundDistrict.name, 
    foundWard.name_with_type || foundWard.name,
    streetAddress.value.trim()
].join(', ');

    // Thay đổi logic tại đây
    // Bỏ qua việc gọi API để lưu vào database
    
    // Cập nhật giá trị tạm thời cho lần đặt hàng này
    displayedAddress.value.dia_chi = fullAddress;
    // Cần đảm bảo các trường tên, sđt cũng được cập nhật
    displayedAddress.value.ho_ten = displayedAddress.value.ho_ten;
    displayedAddress.value.sdt = displayedAddress.value.sdt;
    
    // Đóng form và thông báo thành công
    showAddressForm.value = false;
    
    Swal.fire({
        toast: true, position: 'top-end', icon: 'success', title: 'Địa chỉ giao hàng đã được thay đổi!', showConfirmButton: false, timer: 3000
    });
};

    // Cart and Order related functions
    const updateCartItem = async (productId, quantityChange) => {
      const product = products.value.find((p) => p.id === productId);
      if (!product) return;

      try {
        const response = await axios.post(`http://localhost:8000/api/cart/add`, {
          san_pham_bien_the_id: product.id,
          quantity: quantityChange
        });

        if (response.data.cart_item && response.data.cart_item.so_luong > 0) {
          product.quantity = response.data.cart_item.so_luong;
          product.total_item_price = response.data.cart_item.thanh_tien;
        } else {
          products.value = products.value.filter(p => p.id !== productId);
        }

        Swal.fire({
          toast: true, position: 'top-end', icon: 'success',
          title: 'Cập nhật giỏ hàng thành công!', showConfirmButton: false, timer: 1500
        });
      } catch (error) {
        console.error("Lỗi khi cập nhật giỏ hàng:", error.response?.data || error.message);
        Swal.fire("Lỗi", error.response?.data?.message || "Không thể cập nhật giỏ hàng.", "error");
      }
    };

    const increaseQuantity = (productId) => updateCartItem(productId, 1);
    const decreaseQuantity = (productId) => {
      const product = products.value.find((p) => p.id === productId);
      if (product && product.quantity > 1) {
        updateCartItem(productId, -1);
      } else {
        removeProduct(productId);
      }
    };

    const removeProduct = async (productId) => {
      const result = await Swal.fire({
        title: 'Xóa sản phẩm?', text: "Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?",
        icon: 'warning', showCancelButton: true, confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33', confirmButtonText: 'Đồng ý', cancelButtonText: 'Hủy'
      });
      if (result.isConfirmed) {
        try {
          const user = JSON.parse(localStorage.getItem("user"));
          const userId = user?.nguoi_dung_id || user?.id;
          await axios.delete(`http://localhost:8000/api/cart/${userId}/${productId}`);
          products.value = products.value.filter(p => p.id !== productId);
          Swal.fire({
            toast: true, position: 'top-end', icon: 'success',
            title: 'Đã xóa sản phẩm!', showConfirmButton: false, timer: 1500
          });
        } catch (error) {
          console.error("Lỗi khi xóa sản phẩm:", error);
          Swal.fire("Lỗi", "Không thể xóa sản phẩm.", "error");
        }
      }
    };

    const fetchMyCoupons = async () => {
      try {
        const response = await axios.get('http://localhost:8000/api/my-coupons');
        myCoupons.value = response.data;
      } catch (error) {
        console.error("Lỗi khi tải mã giảm giá của tôi:", error);
      }
    };
    
    const handleCouponSelection = (selectedCode) => {
      couponCode.value = selectedCode;
      showCouponModal.value = false;
      applyCoupon();
    };
    
    const applyCoupon = async () => {
      if (!couponCode.value) {
        couponErrorMessage.value = "Vui lòng nhập mã giảm giá.";
        return;
      }
      isLoadingCoupon.value = true;
      couponErrorMessage.value = '';
      discountAmount.value = 0;
      const cartItemsPayload = products.value.map(item => ({
        id: item.id,
        quantity: item.quantity,
        price: item.price
      }));

      try {
        const response = await axios.post('http://localhost:8000/api/coupon/apply', {
          ma_giam_gia: couponCode.value,
          cart_items: cartItemsPayload
        });
        discountAmount.value = response.data.discount_amount;
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Áp dụng mã thành công!', showConfirmButton: false, timer: 2000 });
      } catch (error) {
        discountAmount.value = 0;
        couponErrorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra.';
        Swal.fire("Lỗi", couponErrorMessage.value, "error");
      } finally {
        isLoadingCoupon.value = false;
      }
    };

    const handleVnPayReturn = async () => {
        const query = route.query;
        if (query.vnp_ResponseCode === '00') {
            Swal.fire({
                icon: 'success',
                title: 'Thanh toán thành công!',
                text: 'Đơn hàng của bạn đã được xác nhận. Chúng tôi sẽ xử lý đơn hàng sớm nhất.',
                showConfirmButton: true
            }).then(() => {
                const user = JSON.parse(localStorage.getItem("user") || "null");
                const userId = user?.nguoi_dung_id || user?.id;
                if (userId) {
                    axios.delete(`http://localhost:8000/api/cart/clear/${userId}`)
                        .catch(err => console.error("Lỗi khi xóa giỏ hàng:", err));
                }
                products.value = [];
                router.replace({ name: 'paymentsuccess', query: { success: '1', payment_method: 'vnpay', order_id: query.vnp_TxnRef } });
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Thanh toán thất bại!',
                text: `Mã lỗi VNPAY: ${query.vnp_ResponseCode}. Vui lòng thử lại.`,
                showConfirmButton: true
            }).then(() => {
                router.replace({ name: 'cart' });
            });
        }
    };

    const placeOrder = async () => {
      if (isPlacingOrder.value) return; 
      isPlacingOrder.value = true;

      const user = JSON.parse(localStorage.getItem("user") || "null");
      if (!products.value.length) {
        Swal.fire("Giỏ hàng trống", "Vui lòng thêm sản phẩm.", "warning");
        isPlacingOrder.value = false;
        return;
      }
      
      if (!displayedAddress.value.ho_ten || !displayedAddress.value.sdt || !displayedAddress.value.dia_chi) {
          Swal.fire("Lỗi thông tin", "Vui lòng nhập đầy đủ Họ tên, SĐT và Địa chỉ giao hàng.", "error");
          isPlacingOrder.value = false;
          return;
      }

      const payload = {
        phuong_thuc_thanh_toan_id: paymentMethod.value === 'cod' ? 1 : 2,
        // Sửa lỗi: Gửi các trường dữ liệu riêng lẻ thay vì chỉ id
        ten_nguoi_nhan: displayedAddress.value.ho_ten,
        sdt_nguoi_nhan: displayedAddress.value.sdt,
        dia_chi_giao_hang: displayedAddress.value.dia_chi,
        phi_van_chuyen: deliveryFee.value,
        ma_giam_gia: couponCode.value || null,
        ghi_chu: 'Đặt hàng'
      };

      try {
        if (paymentMethod.value === 'vnpay') {
          const cartPayload = products.value.map(p => ({
            san_pham_bien_the_id: p.id,
            so_luong: p.quantity,
            don_gia: p.price,
            thanh_tien: p.total_item_price ?? p.price * p.quantity
          }));
          
          const { data } = await axios.post('http://localhost:8000/api/create-vnpay-payment', {
            ...payload, 
            cart: cartPayload,
            total: totalAmount.value,
            user_id: user?.nguoi_dung_id || user?.id,
          });

          if (data.payment_url) {
            window.location.href = data.payment_url;
          } else {
            throw new Error("Không lấy được URL thanh toán");
          }
        } else if (paymentMethod.value === 'cod') {
          const response = await axios.post('http://localhost:8000/api/orders/store', payload, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem('token')}`
            }
          });

          if (response.data.order_id) {
            Swal.fire({
              icon: 'success',
              title: 'Đặt hàng thành công!',
              text: 'Đơn hàng của bạn đã được ghi nhận và sẽ được giao sớm nhất có thể.',
              showConfirmButton: false,
              timer: 3000
            });
            
            products.value = [];
            
            router.push({
              name: 'paymentsuccess', 
              query: { 
                success: '1', 
                order_id: response.data.order_id,
                payment_method: 'cod'
              } 
            });
          } else {
            throw new Error("Không nhận được mã đơn hàng từ server.");
          }
        } else {
          Swal.fire("Lỗi", "Vui lòng chọn phương thức thanh toán.", "warning");
        }
      } catch (err) {
        console.error("Lỗi khi tạo đơn hàng:", err.response?.data || err.message);
        Swal.fire("Lỗi", err.response?.data?.message || "Không thể tạo đơn hàng. Vui lòng thử lại.", "error");
      } finally {
        isPlacingOrder.value = false;
      }
    };

    // Watchers
    watch(selectedProvinceCode, async (newCode, oldCode) => {
      if (newCode && newCode !== oldCode) {
        isLoadingAddressData.value = true;
        errorMessage.value = '';
        selectedDistrictCode.value = '';
        selectedWardCode.value = '';
        await fetchDistricts(newCode);
        isLoadingAddressData.value = false;
      }
    });

    watch(selectedDistrictCode, async (newCode, oldCode) => {
      if (newCode && newCode !== oldCode) {
        isLoadingAddressData.value = true;
        errorMessage.value = '';
        selectedWardCode.value = '';
        await fetchWards(newCode);
        isLoadingAddressData.value = false;
      }
    });
    
    // OnMounted
    onMounted(async () => {
        const user = JSON.parse(localStorage.getItem("user"));
        const userId = user?.nguoi_dung_id || user?.id;
        if (!userId) { 
          Swal.fire("Lỗi", "Vui lòng đăng nhập để xem giỏ hàng.", "error");
          router.push('/login');
          return; 
        }

        if (route.query.vnp_ResponseCode) {
            await handleVnPayReturn();
            return;
        }

        try {
            const [cartRes, addressRes] = await Promise.all([
                axios.get(`http://localhost:8000/api/cart/${userId}`),
                axios.get(`http://localhost:8000/api/dia_chi/nguoi_dung/${userId}`),
                fetchMyCoupons(),
            ]);

            products.value = cartRes.data && cartRes.data.items ? cartRes.data.items : [];
            const addresses = addressRes.data;
            
            if (addresses && addresses.length > 0) {
                const defaultAddress = addresses[0];
                displayedAddress.value = { 
                    ho_ten: defaultAddress.ho_ten || user.ho_ten, 
                    sdt: defaultAddress.sdt || user.sdt, 
                    dia_chi: defaultAddress.dia_chi,
                    id_dia_chi: defaultAddress.id_dia_chi || defaultAddress.id
                };
            } else {
                displayedAddress.value = { ho_ten: user.ho_ten || "", sdt: user.sdt || "", dia_chi: "" };
            }
        } catch (err) {
            console.error("Lỗi khi tải dữ liệu ban đầu:", err);
            Swal.fire("Lỗi", "Không thể tải dữ liệu giỏ hàng hoặc địa chỉ.", "error");
        }
    });


    return {
      products, deliveryMethod, paymentMethod, showAddressForm, 
      displayedAddress, provinces, districts, wards, selectedProvinceCode, 
      selectedDistrictCode, selectedWardCode, streetAddress, 
      isLoadingAddressData, errorMessage, totalItems, subtotal, deliveryFee, 
      totalAmount, formatPrice, increaseQuantity, decreaseQuantity, 
      removeProduct, placeOrder, discountAmount, couponCode, 
      isLoadingCoupon, couponErrorMessage, applyCoupon, myCoupons, 
      showCouponModal, handleCouponSelection, changeAddress, cancelAddressChange,
      handleUpdateAddress,
      imageBaseUrl,
      isPlacingOrder
    };
  }
};
</script>

<style scoped>
/* General styles */
.cart-page {
  font-family: Arial, sans-serif;
  background-color: #f5f5f5;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.header {
  display: flex;
  align-items: center;
  padding: 15px;
  background-color: #fff;
  border-bottom: 1px solid #eee;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.back-button {
  background: none;
  border: none;
  font-size: 16px;
  cursor: pointer;
  margin-right: 20px;
  color: #333;
}

.header-title {
  font-size: 18px;
  font-weight: bold;
  flex-grow: 1;
  text-align: center;
}

.main-content {
  display: flex;
  flex-wrap: wrap;
  padding: 20px;
  gap: 20px;
  flex-grow: 1;
}

.product-list {
  flex: 2;
  min-width: 400px;
  background-color: #fff;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.product-item {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #eee;
}

.product-item:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.product-image {
  width: 70px;
  height: 70px;
  object-fit: cover;
  border-radius: 8px;
  margin-right: 15px;
}

.product-details {
  flex-grow: 1;
}

.product-name {
  font-size: 16px;
  font-weight: bold;
  margin-bottom: 5px;
}

.product-weight {
  font-size: 14px;
  color: #777;
  margin-bottom: 5px;
}

.product-price {
  font-size: 16px;
  color: #333;
  font-weight: bold;
  display: flex;
  align-items: baseline;
}

.product-price .original {
  text-decoration: line-through;
  color: #aaa;
  font-weight: normal;
  font-size: 14px;
  margin-right: 8px;
}

.product-item-total-price {
  font-size: 14px;
  color: #555;
  margin-top: 5px;
  font-weight: bold;
}

.product-quantity-control {
  display: flex;
  align-items: center;
}

.quantity-button {
  background-color: #f0f0f0;
  border: 1px solid #ddd;
  border-radius: 4px;
  width: 30px;
  height: 30px;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 18px;
  cursor: pointer;
  color: #333;
  transition: background-color 0.2s;
}

.quantity-button:hover {
  background-color: #e0e0e0;
}

.quantity {
  margin: 0 10px;
  font-size: 16px;
  font-weight: bold;
}

.remove-button {
  background: none;
  border: none;
  cursor: pointer;
  margin-left: 15px;
  padding: 0;
}

.remove-button img {
  width: 20px;
  height: 20px;
  opacity: 0.6;
  transition: opacity 0.2s;
}

.remove-button:hover img {
  opacity: 1;
}

/* Discount code section */
.discount-code {
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid #eee;
}

.discount-label-group {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.discount-label {
  font-size: 15px;
  color: #777;
  margin-bottom: 0;
}

.select-coupon-btn {
    background: none;
    border: none;
    color: #33ccff;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
}

.discount-input-group {
  display: flex;
  gap: 10px;
}

.discount-input {
  flex-grow: 1;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 15px;
  outline: none;
}

.discount-input:focus {
  border-color: #33ccff;
}

.apply-button {
  background-color: #33ccff;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 15px;
  font-weight: bold;
  transition: background-color 0.2s;
}

.apply-button:hover {
  background-color: #269bc2;
}

/* Styles for order summary and delivery/payment options */
.order-summary-panel {
  flex: 1;
  min-width: 300px;
  background-color: #fff;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.panel-title {
  font-size: 17px;
  font-weight: bold;
  margin-bottom: 15px;
  color: #333;
  border-bottom: 1px solid #eee;
  padding-bottom: 10px;
}

/* Delivery Address Section */
.delivery-address {
  margin-bottom: 20px;
}

.delivery-address p {
  margin-bottom: 8px;
  font-size: 15px;
  color: #555;
}

.delivery-address strong {
  color: #333;
}

.change-address-btn {
  background-color: #33ccff;
  color: white;
  padding: 8px 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
  margin-top: 10px;
  transition: background-color 0.2s ease;
}

.change-address-btn:hover {
  background-color: #269bc2;
}

.address-edit-form .form-group {
  margin-bottom: 15px;
}

.address-edit-form label {
  display: block;
  font-size: 14px;
  color: #666;
  margin-bottom: 5px;
  font-weight: bold;
}

.address-edit-form input,
.address-edit-form select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 15px;
  box-sizing: border-box;
}

.address-edit-form .form-actions {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}

.address-edit-form .save-btn,
.address-edit-form .cancel-btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 15px;
  font-weight: bold;
  transition: background-color 0.2s;
  margin: 0px;
}

.address-edit-form .save-btn {
  background-color: #33ccff;
  color: white;
}

.address-edit-form .save-btn:hover {
  background-color: #2facd5;
}

.address-edit-form .cancel-btn {
  background-color: #fb2e2e;
  color: #ffffff;
  margin-top: 0px;
}

.address-edit-form .cancel-btn:hover {
  background-color: #b81e1e;
}

/* Error message for address */
.error-message {
  color: #dc3545;
  font-size: 14px;
  margin-top: 10px;
}

/* Delivery Options */
.delivery-options {
  margin-bottom: 20px;
}

.delivery-option,
.payment-method {
  display: flex;
  align-items: center;
  padding: 15px;
  border: 1px solid #eee;
  border-radius: 8px;
  margin-bottom: 10px;
  cursor: pointer;
  transition: all 0.2s;
}

.delivery-option:hover,
.payment-method:hover {
  border-color: #33ccff;
  box-shadow: 0 0 5px rgba(76, 175, 80, 0.2);
}

.delivery-option.selected,
.payment-method.selected {
  border-color: #33ccff;
  background-color: #e2f8ff;
}

.delivery-option input[type="radio"],
.payment-method input[type="radio"] {
  margin-right: 15px;
  transform: scale(1.2);
  accent-color: #33ccff;
}

.option-details {
  flex-grow: 1;
}

.option-name {
  font-weight: bold;
  font-size: 16px;
  color: #333;
}

.option-time,
.option-description {
  font-size: 14px;
  color: #777;
  display: block;
}

.option-price {
  font-weight: bold;
  color: #33ccff;
  font-size: 15px;
  margin-left: auto;
}

/* Payment Methods */
.payment-methods {
  margin-bottom: 20px;
}

/* Order Summary */
.order-summary {
  padding-top: 15px;
  border-top: 1px solid #eee;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
  font-size: 15px;
  color: #555;
}

.summary-total {
  display: flex;
  justify-content: space-between;
  margin-top: 15px;
  font-size: 18px;
  font-weight: bold;
  color: #333;
  padding-top: 10px;
  border-top: 1px solid #eee;
}

.place-order-button {
  width: 100%;
  padding: 15px;
  background-color: #33ccff;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 18px;
  font-weight: bold;
  cursor: pointer;
  margin-top: 20px;
  transition: background-color 0.2s;
}

.place-order-button:hover {
  background-color: #1a92ba;
}

.terms-text {
  font-size: 12px;
  color: #999;
  text-align: center;
  margin-top: 15px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .main-content {
    flex-direction: column;
  }

  .product-list,
  .order-summary-panel {
    min-width: unset;
    width: 100%;
  }
}
.empty-cart-message {
  font-size: 1.2rem;
  color: #555;
  text-align: center;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 40px;
}

.back-to-shop {
  display: inline-block;
  margin-top: 12px;
  padding: 8px 16px;
  background-color: #3498db;
  color: white;
  text-decoration: none;
  border-radius: 4px;
  font-weight: bold;
}
.back-to-shop:hover {
  background-color: #2980b9;
}

.discount-info {
  margin-top: 10px;
  color: #27ae60;
  font-weight: bold;
}
</style>