

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
              <img src="https://img.icons8.com/material-outlined/24/000000/trash--v1.png" alt="Xóa" />
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
          <div class="payment-method" :class="{ selected: paymentMethod === 'bank_transfer' }" @click="paymentMethod = 'bank_transfer'">
            <input type="radio" id="bank_transfer" value="bank_transfer" v-model="paymentMethod" />
            <div class="option-details">
              <span class="option-name">Chuyển khoản ngân hàng</span>
              <span class="option-description">Thanh toán qua chuyển khoản ngân hàng.</span>
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
          <button class="place-order-button" @click="placeOrder">
            ĐẶT HÀNG ({{ formatPrice(totalAmount) }})
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
import { useRouter } from 'vue-router';
const isPlacingOrder = ref(false); 
import ChonMaGiamGiaModal from './ChonMaGiamGiaModal.vue'; // Tên file đã đổi

export default {
  name: "CartPage",
  components: {
    ChonMaGiamGiaModal,
  },
  setup() {
    const router = useRouter();
    const products = ref([]);
    const deliveryMethod = ref("standard");
    const paymentMethod = ref("cod");
    const discountCode = ref("");
    const showAddressForm = ref(false);
    const displayedAddress = ref({ ho_ten: "", sdt: "", dia_chi: "" });
    const provinces = ref([]);
    const districts = ref([]);
    const wards = ref([]);
    const selectedProvinceCode = ref('');
    const selectedDistrictCode = ref('');
    const selectedWardCode = ref('');
    const streetAddress = ref('');
    const currentAddressId = ref(null);
    const isLoadingAddressData = ref(false);
    const errorMessage = ref('');
    const discountAmount = ref(0);
    const couponCode = ref("");
    const isLoadingCoupon = ref(false);
    const couponErrorMessage = ref("");
    const myCoupons = ref([]);
    const showCouponModal = ref(false);
    const imageBaseUrl = import.meta.env.VITE_IMAGE_BASE_URL;

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

    const formatPrice = (price) => {
      return new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(price);
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
        couponErrorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra.';
      } finally {
        isLoadingCoupon.value = false;
      }
    };

    // ... (Toàn bộ các hàm xử lý địa chỉ như `fetchProvinces`, `handleUpdateAddress`, etc. giữ nguyên)
const placeOrder = async () => {
  isPlacingOrder.value = true;

  // 1. Kiểm tra điều kiện cơ bản
  if (!currentAddressId.value) {
    Swal.fire("Lỗi", "Vui lòng chọn hoặc lưu địa chỉ giao hàng.", "error");
    isPlacingOrder.value = false;
    return;
  }
  const paymentMethodId = paymentMethod.value === 'cod' ? 1 : 2; // Giả định 1: COD, 2: Bank

  // 2. Chuẩn bị dữ liệu để gửi đi
  const orderPayload = {
    phuong_thuc_thanh_toan_id: paymentMethodId,
    dia_chi_id: currentAddressId.value,
    phi_van_chuyen: deliveryFee.value,
    // ----- CHỈNH SỬA TẠI ĐÂY -----
    // Thêm mã giảm giá vào payload nếu có
    ma_giam_gia: discountAmount.value > 0 ? couponCode.value : null,
    // -----------------------------
  };

  try {
    // 3. Gọi API store của OrderController
    const response = await axios.post('/orders/store', orderPayload);
    
    await Swal.fire({
        icon: 'success',
        title: 'Đặt hàng thành công!',
        text: `Mã đơn hàng của bạn là #${response.data.order_id}`,
        allowOutsideClick: false,
    });
    
 router.push({ 
      name: 'PaymentSuccess', 
      params: { orderId: response.data.order_id } 
    });
  } catch (error) {
    console.error("Lỗi khi đặt hàng:", error.response?.data || error.message);
    const errorMessage = error.response?.data?.message || "Không thể đặt hàng. Vui lòng thử lại.";
    Swal.fire("Đặt hàng thất bại", errorMessage, "error");
  } finally {
    isPlacingOrder.value = false;
  }
};
    onMounted(async () => {
        const user = JSON.parse(localStorage.getItem("user"));
        const userId = user?.nguoi_dung_id || user?.id;
        if (!userId) { 
            Swal.fire("Lỗi", "Vui lòng đăng nhập để xem giỏ hàng.", "error");
            router.push('/login');
            return; 
        }

        // Tải đồng thời giỏ hàng, địa chỉ và mã giảm giá
        await Promise.all([
            axios.get(`http://localhost:8000/api/cart/${userId}`),
            axios.get(`http://localhost:8000/api/dia_chi/nguoi_dung/${userId}`),
            fetchMyCoupons(),
            // fetchProvinces() // Logic địa chỉ của bạn
        ]).then(([cartRes, addressRes]) => {
            // Xử lý giỏ hàng
            if (cartRes.data && cartRes.data.items) { products.value = cartRes.data.items; } 
            else { products.value = []; }

            // Xử lý địa chỉ
            const addresses = addressRes.data;
            let defaultAddress = (addresses && Array.isArray(addresses) && addresses.length > 0) ? addresses[0] : null;
            if (defaultAddress) {
                displayedAddress.value = { ho_ten: defaultAddress.ho_ten || user.ho_ten, sdt: defaultAddress.sdt || user.sdt, dia_chi: defaultAddress.dia_chi };
                currentAddressId.value = defaultAddress.id_dia_chi || defaultAddress.id;
            } else {
                displayedAddress.value = { ho_ten: user.ho_ten || "", sdt: user.sdt || "", dia_chi: "" };
                showAddressForm.value = true;
            }
        }).catch(err => {
            console.error("Lỗi khi tải dữ liệu ban đầu:", err);
            Swal.fire("Lỗi", "Không thể tải dữ liệu giỏ hàng hoặc địa chỉ.", "error");
        });
    });

    return {
      products, deliveryMethod, paymentMethod, showAddressForm, 
      displayedAddress, provinces, districts, wards, selectedProvinceCode, 
      selectedDistrictCode, selectedWardCode, streetAddress, currentAddressId, 
      isLoadingAddressData, errorMessage, totalItems, subtotal, deliveryFee, 
      totalAmount, formatPrice, increaseQuantity, decreaseQuantity, 
      removeProduct, placeOrder, discountAmount, couponCode, 
      isLoadingCoupon, couponErrorMessage, applyCoupon, myCoupons, 
      showCouponModal, handleCouponSelection,
      imageBaseUrl ,
      isPlacingOrder
      // ... (trả về các hàm xử lý địa chỉ của bạn)
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
  flex: 2; /* Takes more space */
  min-width: 400px; /* Đảm bảo đủ rộng trên màn hình lớn */
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
  display: flex; /* Dùng flex để dễ căn chỉnh giá gốc và giá hiện tại */
  align-items: baseline;
}

/* Style cho giá gốc (giá bị gạch ngang) */
.product-price .original {
  text-decoration: line-through;
  color: #aaa;
  font-weight: normal;
  font-size: 14px; /* Nhỏ hơn một chút */
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
  padding: 0; /* Loại bỏ padding mặc định của button */
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

.discount-label {
  font-size: 15px;
  color: #777;
  margin-bottom: 10px;
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
  outline: none; /* Bỏ outline khi focus */
}

.discount-input:focus {
  border-color: #33ccff; /* Highlight border on focus */
}

.apply-button {
  background-color: #33ccff; /* Green */
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
  box-sizing: border-box; /* Ensures padding doesn't add to width */
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
  background-color: #33ccff; /* Blue */
  color: white;
}

.address-edit-form .save-btn:hover {
  background-color: #2facd5;
}

.address-edit-form .cancel-btn {
  background-color: #fb2e2e;
  color: #ffffff;
  margin-top: 0px; /* Adjusted to align with save button */
}

.address-edit-form .cancel-btn:hover {
  background-color: #b81e1e;
}

/* Error message for address */
.error-message {
  color: #dc3545; /* Red */
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
  background-color: #e2f8ff; /* Light blue background for selected */
}

.delivery-option input[type="radio"],
.payment-method input[type="radio"] {
  margin-right: 15px;
  transform: scale(1.2); /* Make radio button slightly larger */
  accent-color: #33ccff; /* Change radio button color */
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
  display: block; /* Ensures it goes to the next line */
}

.option-price {
  font-weight: bold;
  color: #33ccff;
  font-size: 15px;
  margin-left: auto; /* Pushes price to the right */
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
  background-color: #33ccff; /* Blue */
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
    min-width: unset; /* Remove min-width for smaller screens */
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
  align-items: center; /* Căn giữa theo chiều ngang */
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