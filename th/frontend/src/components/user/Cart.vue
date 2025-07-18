<template>
  <div class="cart-page">
    <header class="header">
      <h1 class="header-title">Giỏ hàng ({{ totalItems }} sản phẩm)</h1>
    </header>

    <div class="main-content">
      <div class="product-list" v-if="products.length > 0">
        <div v-for="product in products" :key="product.id" class="product-item">
         <img :src="'http://localhost:8000/storage/' + product.image" :alt="product.name" class="product-image" />
          <div class="product-details">
            <h3 class="product-name">{{ product.name }}</h3>
            <p class="product-weight">{{ product.weight }}</p>
            <p class="product-price">
              <span v-if="product.originalPrice" class="original">{{
                formatPrice(product.originalPrice)
              }}</span>
              {{ formatPrice(product.price) }}
            </p>
          </div>
          <div class="product-quantity-control">
            <button @click="decreaseQuantity(product.id)" class="quantity-button">
              -
            </button>
            <span class="quantity">{{ product.quantity }}</span>
            <button @click="increaseQuantity(product.id)" class="quantity-button">
              +
            </button>
            <button @click="removeProduct(product.id)" class="remove-button">
              <img
                src="https://img.icons8.com/material-outlined/24/000000/trash--v1.png"
                alt="Xóa"
              />
            </button>
          </div>

        </div>

        <div class="discount-code">
          <p class="discount-label">Mã giảm giá</p>
          <div class="discount-input-group">
            <input type="text" placeholder="Nhập mã giảm giá" class="discount-input" v-model="discountCode" />
            <button class="apply-button">Áp dụng</button>
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
          <div
            class="delivery-option"
            :class="{ selected: deliveryMethod === 'standard' }"
            @click="deliveryMethod = 'standard'"
          >
            <input type="radio" id="standard" value="standard" v-model="deliveryMethod" />
            <div class="option-details">
              <span class="option-name">Giao hàng tiêu chuẩn</span>
              <span class="option-time">3-5 ngày làm việc</span>
            </div>
            <span class="option-price">{{ formatPrice(15000) }}</span>
          </div>
          <div
            class="delivery-option"
            :class="{ selected: deliveryMethod === 'express' }"
            @click="deliveryMethod = 'express'"
          >
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
          <div
            class="payment-method"
            :class="{ selected: paymentMethod === 'cod' }"
            @click="paymentMethod = 'cod'"
          >
            <input type="radio" id="cod" value="cod" v-model="paymentMethod" />
            <div class="option-details">
              <span class="option-name">Thanh toán khi nhận hàng (COD)</span>
              <span class="option-description">Trả tiền mặt khi đơn hàng được giao đến bạn.</span>
            </div>
          </div>
          <div
            class="payment-method"
            :class="{ selected: paymentMethod === 'bank_transfer' }"
            @click="paymentMethod = 'bank_transfer'"
          >
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
import { onMounted, ref, watch } from "vue";
import axios from "axios";
import Swal from "sweetalert2";

export default {
  name: "CartPage",
  data() {
    return {
      products: [],
      deliveryMethod: "standard",
      paymentMethod: "cod",
      discountCode: "",
      showAddressForm: false,
      displayedAddress: {
        ho_ten: "",
        sdt: "",
        dia_chi: "",
      },
      provinces: [],
      districts: [],
      wards: [],
      selectedProvinceCode: '',
      selectedDistrictCode: '',
      selectedWardCode: '',
      streetAddress: '',
      // isDefaultAddress: false, // <-- Có thể xóa biến này
      currentAddressId: null,
      isLoadingAddressData: false,
      errorMessage: '',
      
    };
  },

  computed: {
    totalItems() {
      return this.products.reduce((acc, product) => acc + product.quantity, 0);
    },
    subtotal() {
      return this.products.reduce(
        (acc, product) => acc + product.price * product.quantity,
        0
      );
    },
    deliveryFee() {
      if (this.deliveryMethod === "standard") {
        return 15000;
      } else if (this.deliveryMethod === "express") {
        return 25000;
      } else {
        return 0;
      }
    },
    totalAmount() {
      return this.subtotal + this.deliveryFee;
    },
  },
  methods: {
    formatPrice(price) {
      return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
      }).format(price);
    },
    increaseQuantity(productId) {
      const product = this.products.find((p) => p.id === productId);
      if (product) {
        product.quantity++;
      }
    },
    decreaseQuantity(productId) {
      const product = this.products.find((p) => p.id === productId);
      if (product && product.quantity > 1) {
        product.quantity--;
      }
    },
    changeAddress() {
      this.showAddressForm = true;
      this.errorMessage = '';
      this.fillAddressFormFromDisplayed();
    },
    cancelAddressChange() {
        this.showAddressForm = false;
        this.errorMessage = '';
        this.fillAddressFormFromDisplayed();
    },
    parseAddress(fullAddress) {
        if (!fullAddress) {
            return { street: '', ward: '', district: '', province: '' };
        }

        const parts = fullAddress.split(',').map(part => part.trim());

        let province = '';
        let district = '';
        let ward = '';
        let street = '';

        // Improved parsing logic, more robust for varying address formats
        // This assumes the format is generally "street, ward, district, province"
        // and tries to match known prefixes for ward/district/province.
        const wardPrefixes = ['Phường', 'Xã', 'Thị trấn'];
        const districtPrefixes = ['Quận', 'Huyện', 'Thành phố', 'Thị xã'];
        const provincePrefixes = ['Tỉnh', 'Thành phố'];

        let remainingParts = [...parts];

        // Try to identify province from the last part
        for (let i = remainingParts.length - 1; i >= 0; i--) {
            const part = remainingParts[i];
            const foundProvincePrefix = provincePrefixes.find(prefix => part.startsWith(prefix));
            if (foundProvincePrefix || this.provinces.some(p => p.name_with_type === part || p.name === part)) {
                province = part;
                remainingParts.splice(i, 1);
                break;
            }
        }

        // Try to identify district from remaining parts, usually before province
        for (let i = remainingParts.length - 1; i >= 0; i--) {
            const part = remainingParts[i];
            const foundDistrictPrefix = districtPrefixes.find(prefix => part.startsWith(prefix));
            if (foundDistrictPrefix || this.districts.some(d => d.name_with_type === part || d.name === part)) {
                district = part;
                remainingParts.splice(i, 1);
                break;
            }
        }

        // Try to identify ward from remaining parts, usually before district
        for (let i = remainingParts.length - 1; i >= 0; i--) {
            const part = remainingParts[i];
            const foundWardPrefix = wardPrefixes.find(prefix => part.startsWith(prefix));
            if (foundWardPrefix || this.wards.some(w => w.name_with_type === part || w.name === part)) {
                ward = part;
                remainingParts.splice(i, 1);
                break;
            }
        }

        // The rest is street address
        street = remainingParts.join(', ');

        return { street, ward, district, province };
    },
    async fetchProvinces() {
        try {
            const cachedProvinces = localStorage.getItem('provinces');
            if (cachedProvinces) {
                this.provinces = JSON.parse(cachedProvinces);
                return;
            }

            const response = await fetch('https://vn-public-apis.fpo.vn/provinces/getAll?limit=-1');
            if (!response.ok) {
                if (response.status === 429) {
                    throw new Error('Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau vài giây.');
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const apiData = await response.json();
            this.provinces = apiData.data.data;
            localStorage.setItem('provinces', JSON.stringify(this.provinces));
        } catch (error) {
            console.error('Lỗi khi tải danh sách Tỉnh/Thành phố:', error.message);
            this.provinces = [];
        }
    },
    async fetchDistricts(provinceCode) {
        if (!provinceCode) {
            this.districts = [];
            this.wards = [];
            return;
        }

        try {
            const cacheKey = `districts_${provinceCode}`;
            const cachedDistricts = localStorage.getItem(cacheKey);
            if (cachedDistricts) {
                this.districts = JSON.parse(cachedDistricts);
                return;
            }

            const response = await fetch(`https://vn-public-apis.fpo.vn/districts/getByProvince?provinceCode=${provinceCode}&limit=-1`);
            if (!response.ok) {
                if (response.status === 429) {
                    throw new Error('Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau vài giây.');
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const apiData = await response.json();
            this.districts = apiData.data.data;
            localStorage.setItem(cacheKey, JSON.stringify(this.districts));
        } catch (error) {
            console.error('Lỗi khi tải danh sách Quận/Huyện:', error.message);
            this.districts = [];
            this.wards = [];
        }
    },
    async fetchWards(districtCode) {
        if (!districtCode) {
            this.wards = [];
            return;
        }

        try {
            const cacheKey = `wards_${districtCode}`;
            const cachedWards = localStorage.getItem(cacheKey);
            if (cachedWards) {
                this.wards = JSON.parse(cachedWards);
                return;
            }

            const response = await fetch(`https://vn-public-apis.fpo.vn/wards/getByDistrict?districtCode=${districtCode}&limit=-1`);
            if (!response.ok) {
                if (response.status === 429) {
                    throw new Error('Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau vài giây.');
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const apiData = await response.json();
            this.wards = apiData.data.data;
            localStorage.setItem(cacheKey, JSON.stringify(this.wards));
        } catch (error) {
            console.error('Lỗi khi tải danh sách Phường/Xã:', error.message);
            this.wards = [];
        }
    },
    async fillAddressFormFromDisplayed() {
        if (this.displayedAddress.dia_chi) {
            const { street, ward, district, province } = this.parseAddress(this.displayedAddress.dia_chi);
            this.streetAddress = street;

            const foundProvince = this.provinces.find(p =>
                p.name_with_type.toLowerCase().includes(province.toLowerCase()) ||
                p.name.toLowerCase().includes(province.toLowerCase())
            );

            if (foundProvince) {
                this.selectedProvinceCode = foundProvince.code;
                await this.fetchDistricts(this.selectedProvinceCode);
                const foundDistrict = this.districts.find(d =>
                    d.name_with_type.toLowerCase().includes(district.toLowerCase()) ||
                    d.name.toLowerCase().includes(district.toLowerCase())
                );
                if (foundDistrict) {
                    this.selectedDistrictCode = foundDistrict.code;
                    await this.fetchWards(this.selectedDistrictCode);
                    const foundWard = this.wards.find(w =>
                        w.name_with_type.toLowerCase().includes(ward.toLowerCase()) ||
                        w.name.toLowerCase().includes(ward.toLowerCase())
                    );
                    if (foundWard) {
                        this.selectedWardCode = foundWard.code;
                    } else {
                        console.warn('Không tìm thấy phường/xã khớp khi điền form:', ward);
                    }
                } else {
                    console.warn('Không tìm thấy quận/huyện khớp khi điền form:', district);
                }
            } else {
                console.warn('Không tìm thấy tỉnh/thành phố khớp khi điền form:', province);
            }
        } else {
            this.selectedProvinceCode = '';
            this.selectedDistrictCode = '';
            this.selectedWardCode = '';
            this.streetAddress = '';
        }
    },
    async handleUpdateAddress() {
        this.errorMessage = '';

        if (!this.displayedAddress.ho_ten.trim()) {
            this.errorMessage = 'Vui lòng nhập họ tên người nhận.';
            return;
        }
        if (!this.displayedAddress.sdt.trim()) {
            this.errorMessage = 'Vui lòng nhập số điện thoại người nhận.';
            return;
        }

        if (!this.selectedProvinceCode || !this.selectedDistrictCode || !this.selectedWardCode || !this.streetAddress.trim()) {
            this.errorMessage = 'Vui lòng điền đầy đủ thông tin địa chỉ (Tỉnh/Thành phố, Quận/Huyện, Phường/Xã, Số Nhà/Tên Đường).';
            return;
        }

        const foundDistrictInSelectedProvince = this.districts.some(d => d.code === this.selectedDistrictCode);
        if (!foundDistrictInSelectedProvince) {
            this.errorMessage = 'Huyện/Quận đã chọn không hợp lệ cho Tỉnh/Thành phố hiện tại. Vui lòng chọn lại.';
            return;
        }

        const foundWardInSelectedDistrict = this.wards.some(w => w.code === this.selectedWardCode);
        if (!foundWardInSelectedDistrict) {
            this.errorMessage = 'Phường/Xã đã chọn không hợp lệ cho Quận/Huyện hiện tại. Vui lòng chọn lại.';
            return;
        }

        const provinceName = this.provinces.find(p => p.code === this.selectedProvinceCode)?.name_with_type || '';
        const districtName = this.districts.find(d => d.code === this.selectedDistrictCode)?.name_with_type || '';
        const wardName = this.wards.find(w => w.code === this.selectedWardCode)?.name_with_type || '';

        const fullAddress = [
            this.streetAddress.trim(),
            wardName,
            districtName,
            provinceName,
        ].filter(part => part).join(', ');

        const user = JSON.parse(localStorage.getItem("user"));
        const userId = user?.nguoi_dung_id || user?.id;

        if (!userId) {
            Swal.fire("Lỗi", "Không tìm thấy người dùng.", "error");
            return;
        }

        try {
            this.isLoadingAddressData = true;
            let addressPayload = {
                nguoi_dung_id: userId,
                dia_chi: fullAddress,
                ho_ten: this.displayedAddress.ho_ten.trim(),
                sdt: this.displayedAddress.sdt.trim(),
            };

            let response;
            if (this.currentAddressId) {
                response = await axios.put(`http://localhost:8000/api/dia_chi/${this.currentAddressId}`, addressPayload);
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
                response = await axios.post('http://localhost:8000/api/dia_chi', addressPayload);
                this.currentAddressId = response.data.data?.id_dia_chi || response.data.data?.id;
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Thêm địa chỉ mới thành công!',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            }

            this.displayedAddress = {
                ho_ten: addressPayload.ho_ten,
                sdt: addressPayload.sdt,
                dia_chi: fullAddress,
            };
            this.showAddressForm = false;
            this.errorMessage = '';
        } catch (error) {
            console.error("Lỗi khi cập nhật/thêm địa chỉ:", error.response?.data || error.message);
            this.errorMessage = error.response?.data?.message || "Cập nhật địa chỉ thất bại. Vui lòng thử lại.";
        } finally {
            this.isLoadingAddressData = false;
        }
    },
    async removeProduct(productId) {
    try {
      const result = await Swal.fire({
        title: 'Bạn có chắc chắn muốn xóa sản phẩm này?',
        text: "Sản phẩm sẽ bị loại bỏ khỏi giỏ hàng của bạn!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Có, xóa nó đi!',
        cancelButtonText: 'Hủy bỏ'
      });

      if (result.isConfirmed) {
        const user = JSON.parse(localStorage.getItem("user"));
        const userId = user?.nguoi_dung_id || user?.id;

        if (!userId) {
          Swal.fire("Lỗi", "Không tìm thấy thông tin người dùng. Vui lòng đăng nhập.", "error");
          return;
        }
        // Assuming product.id in `products` array is `bien_the_id` or similar unique identifier from backend cart
        const itemIdentifier = this.products.find(p => p.id === productId)?.bien_the_id || productId;
        await axios.delete(`http://localhost:8000/api/gio-hang/${userId}/${itemIdentifier}`);
        this.products = this.products.filter(p => p.id !== productId);

        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Sản phẩm đã được xóa khỏi giỏ hàng!',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });
      }
    } catch (error) {
      console.error("Lỗi khi xóa sản phẩm:", error.response?.data || error.message);
      Swal.fire("Lỗi", "Không thể xóa sản phẩm. Vui lòng thử lại.", "error");
    }
    },

    async placeOrder() {
      const user = JSON.parse(localStorage.getItem("user"));
      const userId = user?.nguoi_dung_id || user?.id;

      if (!userId) {
        Swal.fire("Lỗi", "Không tìm thấy người dùng.", "error");
        return;
      }

      // Đảm bảo có địa chỉ trước khi đặt hàng
      if (!this.displayedAddress.dia_chi || !this.displayedAddress.ho_ten || !this.displayedAddress.sdt) {
          Swal.fire("Lỗi", "Vui lòng nhập hoặc chọn địa chỉ giao hàng.", "error");
          return;
      }

      const total = this.products.reduce(
        (sum, item) => sum + item.quantity * item.price,
        0
      );

      let orderData = {
        nguoi_dung_id: userId,
        tong_tien: total,
        phuong_thuc_thanh_toan_id: this.paymentMethod === 'cod' ? 1 : 2, // Map 'cod' to 1, 'bank_transfer' to 2 (giả định)
        hinh_thuc_giao_hang: this.deliveryMethod,
        phi_van_chuyen: this.deliveryFee, // Thêm phí vận chuyển vào payload
        san_pham: this.products.map((item) => ({
          bien_the_id: item.bien_the_id || item.id,
          so_luong: item.quantity,
          don_gia: item.price,
        })),
      };

      if (this.currentAddressId) {
          orderData.dia_chi_id = this.currentAddressId;
      } else {
          orderData.dia_chi_moi = {
              ho_ten: this.displayedAddress.ho_ten,
              sdt: this.displayedAddress.sdt,
              dia_chi: this.displayedAddress.dia_chi,
          };
      }

      try {
        const token = localStorage.getItem("token");
        await axios.post("http://localhost:8000/api/orders", orderData, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Đặt hàng thành công!',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        this.products = [];
      } catch (error) {
        console.error("Lỗi khi đặt hàng:", error);
        console.log(error.response?.data);
        let errorMsg = "Không thể đặt hàng. Vui lòng thử lại sau.";
        if (error.response && error.response.data && error.response.data.message) {
            errorMsg = error.response.data.message;
        }
        Swal.fire("Lỗi", errorMsg, "error");
      }
    },
  },
  async mounted() {
    const user = JSON.parse(localStorage.getItem("user"));
    const userId = user?.nguoi_dung_id || user?.id;
    if (!userId) {
      Swal.fire("Lỗi", "Không tìm thấy thông tin người dùng. Vui lòng đăng nhập.", "error");
      return;
    }

    await this.fetchProvinces();

    this.isLoadingAddressData = true;

    try {
        const resAddress = await axios.get(`http://localhost:8000/api/dia_chi/nguoi_dung/${userId}`);
        const addresses = resAddress.data; // Giả định backend trả về trực tiếp mảng địa chỉ

        let defaultAddress = null;

        if (addresses && Array.isArray(addresses) && addresses.length > 0) {
            // Lấy địa chỉ mới nhất (nếu backend sắp xếp theo id_dia_chi DESC)
            // Hoặc bạn có thể thêm logic để chọn địa chỉ mặc định nếu có trường `mac_dinh`
            defaultAddress = addresses[0]; 
        }

        if (defaultAddress) {
            this.displayedAddress = {
                ho_ten: defaultAddress.ho_ten || user.ho_ten || "",
                sdt: defaultAddress.sdt || user.sdt || "",
                dia_chi: defaultAddress.dia_chi || "",
            };
            this.currentAddressId = defaultAddress.id_dia_chi || defaultAddress.id;
            await this.fillAddressFormFromDisplayed();
            this.showAddressForm = false;
        } else {
            console.log('Người dùng chưa có địa chỉ nào.');
            this.currentAddressId = null;
            this.showAddressForm = true; // Hiển thị form để người dùng nhập địa chỉ mới
            this.selectedProvinceCode = '';
            this.selectedDistrictCode = '';
            this.selectedWardCode = '';
            this.streetAddress = '';
            // Gán thông tin họ tên, sdt từ user nếu có, để form có dữ liệu ban đầu
            this.displayedAddress = { ho_ten: user.ho_ten || "", sdt: user.sdt || "", dia_chi: "" };
        }
    } catch (err) {
        console.error("Lỗi khi lấy địa chỉ người dùng:", err.response?.data || err.message);
        // Nếu có lỗi, vẫn hiển thị form để người dùng nhập địa chỉ
        this.displayedAddress = { ho_ten: user.ho_ten || "", sdt: user.sdt || "", dia_chi: "" };
        this.currentAddressId = null;
        this.showAddressForm = true;
        this.errorMessage = "Không thể tải địa chỉ giao hàng. Vui lòng nhập địa chỉ mới.";
    } finally {
        this.isLoadingAddressData = false;
    }

    axios
      .get(`http://localhost:8000/api/gio-hang/nguoi-dung/${userId}`)
      .then((res) => {
        this.products = res.data;
      })
      .catch((err) => {
        console.error("Lỗi khi tải giỏ hàng:", err);
        Swal.fire("Lỗi", "Không thể tải giỏ hàng từ hệ thống.", "error");
      });
  },
  watch: {
    selectedProvinceCode: {
      handler: async function (newCode, oldCode) {
        if (newCode !== oldCode) {
          this.isLoadingAddressData = true;
          this.errorMessage = '';
          this.selectedDistrictCode = '';
          this.selectedWardCode = '';
          await this.fetchDistricts(newCode);
          this.isLoadingAddressData = false;
        }
      },
      immediate: false,
    },
    selectedDistrictCode: {
      handler: async function (newCode, oldCode) {
        if (newCode !== oldCode) {
          this.isLoadingAddressData = true;
          this.errorMessage = '';
          this.selectedWardCode = '';
          await this.fetchWards(newCode);
          this.isLoadingAddressData = false;
        }
      },
      immediate: false,
    },
    selectedWardCode() {
        this.errorMessage = '';
    },
    streetAddress() {
        this.errorMessage = '';
    }
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
</style>