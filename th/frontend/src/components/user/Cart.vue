<template>
  <div class="cart-page">


    <ChonMaGiamGiaModal
      v-if="showCouponModal"
      :coupons="myCoupons"
      @close="showCouponModal = false"
      @coupon-selected="handleCouponSelection"
    />

    <div class="main-content">
      <div class="left-column">
        <div class="cart-section panel">
          <div class="product-list" v-if="products.length > 0">
            <div v-for="product in products" :key="product.id" class="product-item">
              <img :src="imageBaseUrl + product.image" :alt="product.name" class="product-image" />
              <div class="product-details">
                <h3 class="product-name">{{ product.name }}</h3>
                <p class="product-price">
                  <span v-if="product.originalPrice" class="original">{{ formatPrice(product.originalPrice) }}</span>
                  {{ formatPrice(product.price) }}
                </p>
              </div>
              <div class="product-quantity-control">
                <button @click="decreaseQuantity(product.id)" class="quantity-button">-</button>
                <span class="quantity">{{ product.quantity }}</span>
                <button @click="increaseQuantity(product.id)" class="quantity-button">+</button>
              </div>
               <button @click="removeProduct(product.id)" class="remove-button">
                  <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
          </div>
           <div v-else class="empty-cart-message">
              <p>Giỏ hàng của bạn đang trống.</p>
              <router-link to="/" class="back-to-shop">Tiếp tục mua sắm</router-link>
            </div>
        </div>

        <div class="panel" v-if="products.length > 0">
           <div class="panel-header">
             <h2 class="panel-title">
                <i class="fa-solid fa-location-dot"></i> Địa chỉ giao hàng
             </h2>
             <button class="edit-btn" @click="changeAddress" v-if="!showAddressForm && displayedAddress.dia_chi">
                <i class="fa-solid fa-pen-to-square"></i> Sửa
             </button>
           </div>
          <div v-if="!showAddressForm" class="address-display">
            <div class="address-info-box" v-if="displayedAddress.dia_chi">
                <p class="user-name">{{ displayedAddress.ho_ten }}</p>
                <p>SĐT: {{ displayedAddress.sdt }}</p>
                <p>{{ displayedAddress.dia_chi }}</p>
            </div>
             <button v-else class="change-address-btn" @click="changeAddress">
              Thêm địa chỉ giao hàng
            </button>
          </div>
          <div v-else class="address-edit-form">
            <p v-if="isLoadingAddressData">Đang tải dữ liệu địa chỉ...</p>
            <div v-else>
              <div class="form-group">
                <label for="fullName">Họ và tên</label>
                <input type="text" id="fullName" v-model="displayedAddress.ho_ten" placeholder="Họ và tên người nhận" />
              </div>
              <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <input type="tel" id="phone" v-model="displayedAddress.sdt" placeholder="Số điện thoại" />
              </div>
              <div class="form-group">
                 <label for="streetAddress">Địa chỉ chi tiết</label>
                 <div class="address-grid">
                    <select id="province" v-model="selectedProvinceCode">
                      <option value="">Chọn Tỉnh/Thành phố</option>
                      <option v-for="province in provinces" :key="province.code" :value="province.code">
                        {{ province.name_with_type }}
                      </option>
                    </select>
                    <select id="district" v-model="selectedDistrictCode" :disabled="!selectedProvinceCode">
                      <option value="">Chọn Quận/Huyện</option>
                      <option v-for="district in districts" :key="district.code" :value="district.code">
                        {{ district.name_with_type }}
                      </option>
                    </select>
                    <select id="ward" v-model="selectedWardCode" :disabled="!selectedDistrictCode">
                      <option value="">Chọn Phường/Xã</option>
                      <option v-for="ward in wards" :key="ward.code" :value="ward.code">
                        {{ ward.name_with_type }}
                      </option>
                    </select>
                 </div>
                 <input type="text" id="streetAddress" v-model="streetAddress" placeholder="Số nhà, Tên đường" class="street-input"/>
              </div>

              <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
              <div class="form-actions">
                <button class="cancel-btn" @click="cancelAddressChange">Hủy</button>
                <button class="save-btn" @click="handleUpdateAddress">Lưu</button>
              </div>
            </div>
          </div>
        </div>

        <div class="panel" v-if="products.length > 0">
          <h2 class="panel-title">Phương thức giao hàng</h2>
          <div class="delivery-options">
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
        </div>

        <div class="panel" v-if="products.length > 0">
           <h2 class="panel-title">Phương thức thanh toán</h2>
          <div class="payment-methods">
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
        </div>
      </div>

      <div class="right-column" v-if="products.length > 0">
        <div class="order-summary-panel panel">
            <h2 class="panel-title">Tóm tắt đơn hàng</h2>
            
            <div class="discount-code">
                <div class="discount-label-group">
                    <p class="discount-label"><i class="fa-solid fa-ticket"></i> Mã giảm giá</p>
                    <button class="select-coupon-btn" @click="showCouponModal = true">Chọn mã</button>
                </div>
                <div class="discount-input-group">
                    <input type="text" placeholder="Nhập mã giảm giá" class="discount-input" v-model="couponCode" @input="handleCouponInput" />
                    <button class="apply-button" @click="applyCoupon" :disabled="isLoadingCoupon">
                    {{ isLoadingCoupon ? '...' : 'Áp dụng' }}
                    </button>
                    <button class="remove-coupon-btn" @click="removeCoupon" v-if="couponCode">
    Xóa mã
  </button>
                </div>
                <p v-if="couponErrorMessage" class="error-message">{{ couponErrorMessage }}</p>
            </div>

            <div class="order-summary">
                <div class="summary-item">
                    <span>Tổng tiền hàng ({{ totalItems }} sản phẩm)</span>
                    <span>{{ formatPrice(subtotal) }}</span>
                </div>
                <div class="summary-item">
                    <span>Phí vận chuyển</span>
                    <span>{{ formatPrice(deliveryFee) }}</span>
                </div>
                 <div class="summary-item" v-if="discountAmount > 0">
                    <span>Giảm giá</span>
                    <span class="discount-amount">-{{ formatPrice(discountAmount) }}</span>
                </div>
                <div class="summary-total">
                    <span>Tổng cộng</span>
                    <span class="total-price">{{ formatPrice(totalAmount) }}</span>
                </div>
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
    const imageBaseUrl = 'http://localhost:8000/storage/';
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
            streetAddress.value.trim(),
            foundWard.name_with_type || foundWard.name,
            foundDistrict.name_with_type || foundDistrict.name, 
            foundProvince.name_with_type || foundProvince.name,
        ].join(', ');

        displayedAddress.value.dia_chi = fullAddress;
        displayedAddress.value.ho_ten = displayedAddress.value.ho_ten;
        displayedAddress.value.sdt = displayedAddress.value.sdt;
        
        showAddressForm.value = false;
        
        Swal.fire({
            toast: true, position: 'top-end', icon: 'success', title: 'Địa chỉ giao hàng đã được cập nhật!', showConfirmButton: false, timer: 3000
        });
    };

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


    // Thêm hàm này vào trong setup
    const handleCouponInput = () => {
      if (!couponCode.value) {
        discountAmount.value = 0;
        couponErrorMessage.value = '';
      }
    };

    // Khai báo hàm removeCoupon
    const removeCoupon = () => {
      couponCode.value = '';
      discountAmount.value = 0;
      couponErrorMessage.value = '';
    };

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
      isPlacingOrder,
      handleCouponInput,
      removeCoupon,
    };
  }
};
</script>

<style scoped>
/* General styles */
.cart-page {
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  background-color: #f0f2f5;
  min-height: 100vh;
}

.header {
  background-color: #fff;
  padding: 20px 5%;
  border-bottom: 1px solid #e0e0e0;
}

.header-title {
  font-size: 24px;
  font-weight: 600;
  margin: 0;
}

.header-subtitle {
    margin: 4px 0 0;
    color: #6c757d;
}

.main-content {
  display: flex;
  flex-wrap: wrap;
  padding: 24px 5%;
  gap: 24px;
  align-items: flex-start;
}

.left-column {
  flex: 2;
  display: flex;
  flex-direction: column;
  gap: 24px;
  min-width: 500px;
}

.right-column {
  flex: 1;
  min-width: 350px;
  position: sticky;
  top: 24px;
}

.panel {
  background-color: #fff;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #dee2e6;
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.panel-title {
  font-size: 18px;
  font-weight: 600;
  margin: 0px 0px 5px 0px;
}

/* Product List */
.product-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.product-item {
  display: flex;
  align-items: center;
  gap: 16px;
}

.product-image {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #eee;
}

.product-details {
  flex-grow: 1;
}

.product-name {
  font-size: 15px;
  font-weight: 500;
  margin: 0 0 4px 0;
}

.product-price {
  font-size: 16px;
  color: #212529;
  font-weight: 600;
  margin: 0;
}

.product-quantity-control {
  display: flex;
  align-items: center;
  border: 1px solid #ced4da;
  border-radius: 6px;
}

.quantity-button {
  background-color: transparent;
  border: none;
  width: 32px;
  height: 32px;
  font-size: 18px;
  cursor: pointer;
  color: #495057;
}

.quantity {
  font-size: 15px;
  font-weight: 500;
  padding: 0 8px;
}
.remove-button {
  background: transparent;
  border: none;
  cursor: pointer;
  color: #6c757d;
  font-size: 16px;
}
.remove-button:hover {
    color: #dc3545;
}

/* Address Section */
.edit-btn {
    background: none;
    border: none;
    color: #007bff;
    font-weight: 500;
    cursor: pointer;
    font-size: 15px;
}
.edit-btn:hover {
    text-decoration: underline;
    background: none;
}
.address-info-box {
    background-color: #e7f3ff;
    border: 1px solid #bce0ff;
    border-radius: 8px;
    padding: 16px;
    color: #343a40;
}
.address-info-box p {
    margin: 0 0 6px 0;
    font-size: 15px;
    line-height: 1.5;
}
.address-info-box p:last-child {
    margin-bottom: 0;
}
.address-info-box .user-name {
    font-weight: 600;
    font-size: 16px;
    position: relative;
    padding-left: 14px;
    margin-bottom: 8px;
}
.address-info-box .user-name::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 6px;
    height: 6px;
    background-color: #007bff;
    border-radius: 50%;
}
.change-address-btn {
    background-color: #007bff;
    color: white;
    padding: 10px 18px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 500;
    transition: background-color 0.2s ease;
}
.change-address-btn:hover {
  background-color: #0056b3;
}

.address-edit-form .form-group {
  margin-bottom: 16px;
}
.address-edit-form label {
  display: block;
  font-size: 14px;
  color: #495057;
  margin-bottom: 6px;
  font-weight: 500;
}
.address-edit-form input,
.address-edit-form select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ced4da;
  border-radius: 6px;
  font-size: 15px;
  box-sizing: border-box;
}
.address-edit-form input:focus,
.address-edit-form select:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 0 2px rgba(0,123,255,.25);
}
.address-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 10px;
}
.street-input {
    margin-top: 10px;
}
.address-edit-form .form-actions {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}
.address-edit-form .save-btn,
.address-edit-form .cancel-btn {
  padding: 10px 20px;
  border: 1px solid;
  border-radius: 6px;
  cursor: pointer;
  font-size: 15px;
  font-weight: 500;
  margin-top: 16px
}
.address-edit-form .save-btn {
  background-color: #33ccff;
  color: white;
  border-color: #33ccff;
}
.address-edit-form .save-btn:hover {
    background-color: #2497d5;
    border-color: #2497d5;
  }

.address-edit-form .cancel-btn {
  background-color: #f8f9fa;
  color: #343a40;
  border-color: #ced4da;
}
.address-edit-form .cancel-btn:hover {
    background-color: #d5d5d5;
    border-color: #d5d5d5;
  }
/* Delivery & Payment Options */
.delivery-option, .payment-method {
  display: flex;
  align-items: flex-start;
  padding: 16px;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  margin-bottom: 12px;
  cursor: pointer;
  transition: all 0.2s;
}
.delivery-option:last-child, .payment-method:last-child {
    margin-bottom: 0;
}
.delivery-option:hover, .payment-method:hover {
  border-color: #007bff;
}
.delivery-option.selected, .payment-method.selected {
  border-color: #007bff;
  background-color: #e7f3ff;
  box-shadow: 0 0 0 1px #007bff;
}
.delivery-option input[type="radio"], .payment-method input[type="radio"] {
  margin-right: 15px;
  margin-top: 20px;
  transform: scale(1.2);
  accent-color: #007bff;
}
.option-details { flex-grow: 1; }
.option-name {
  font-weight: 500;
  font-size: 16px;
  color: #212529;
}
.option-time, .option-description {
  font-size: 14px;
  color: #6c757d;
  display: block;
  margin-top: 4px;
}
.option-price {
  font-weight: 500;
  color: #212529;
  font-size: 15px;
}

/* Right Column: Order Summary */
.order-summary-panel {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.discount-code {
  padding-bottom: 20px;
  border-bottom: 1px solid #e9ecef;
}
.discount-label-group {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.discount-label {
  font-size: 15px;
  color: #343a40;
  font-weight: 500;
  margin: 0;
}
.select-coupon-btn {
    background: none; border: none; color: #33ccff;
    font-size: 14px; font-weight: 500; cursor: pointer;
}
.discount-input-group { display: flex; }
.discount-input {
  flex-grow: 1;
  padding: 8px 12px;
  border: 1px solid #ced4da;
  border-radius: 6px 0 0 6px;
  font-size: 15px;
  outline: none;
}
.discount-input:focus {
  border-color: #007bff;
}
.apply-button {
  background-color: #f8f9fa;
  color: #343a40;
  padding: 8px 16px;
  border: 1px solid #ced4da;
  border-left: 0;
  border-radius: 0 6px 6px 0;
  cursor: pointer;
  font-size: 15px;
  font-weight: 500;
}
.apply-button:hover { background-color: #e9ecef; }
.apply-button:disabled { cursor: not-allowed; opacity: 0.7; }

.remove-coupon-btn {
  background: none;
  border: none;
  color: #dc3545;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  margin-left: 8px;
}
.remove-coupon-btn:hover {
  text-decoration: underline;
}

/* Order Summary Details */
.order-summary {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.summary-item {
  display: flex;
  justify-content: space-between;
  font-size: 15px;
  color: #495057;
}
.discount-amount {
    color: #28a745;
}
.summary-total {
  display: flex;
  justify-content: space-between;
  margin-top: 12px;
  font-size: 18px;
  font-weight: 600;
  color: #212529;
  padding-top: 16px;
  border-top: 1px solid #e9ecef;
}
.total-price {
    color: #dc3545;
}

.place-order-button {
  width: 100%;
  padding: 12px;
  background-color: #33ccff;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  margin-top: 10px;
}
.place-order-button:hover { background-color: #2497d5; }
.place-order-button:disabled { background-color: #6c757d; cursor: not-allowed; }

.terms-text {
  font-size: 12px;
  color: #6c757d;
  text-align: center;
  margin-top: 0;
}

/* Empty Cart & Misc */
.empty-cart-message {
  padding: 40px 0;
  text-align: center;
}
.back-to-shop {
  display: inline-block;
  margin-top: 12px;
  padding: 8px 16px;
  background-color: #007bff;
  color: white;
  text-decoration: none;
  border-radius: 4px;
  font-weight: bold;
}
.error-message {
  color: #dc3545;
  font-size: 14px;
  margin-top: 8px;
}

/* Responsive adjustments */
@media (max-width: 992px) {
  .main-content {
    flex-direction: column;
  }
  .left-column, .right-column {
    min-width: 100%;
    width: 100%;
  }
   .right-column {
    position: static;
  }
}
@media (max-width: 576px) {
    .address-grid {
        grid-template-columns: 1fr;
    }
}
</style>
