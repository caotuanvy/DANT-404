<template>
  <div class="cart-page">
    <header class="header">
      <h1 class="header-title">Giỏ hàng ({{ totalItems }} sản phẩm)</h1>
    </header>

    <div class="main-content">
      <div class="product-list" v-if="products.length > 0">
        <div v-for="product in products" :key="product.id" class="product-item">
         <img :src="'https://localhost:8000/storage/' + product.image" :alt="product.name" class="product-image" />
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
            <input type="text" placeholder="Nhập mã giảm giá" class="discount-input" />
            <button class="apply-button">Áp dụng</button>
          </div>
        </div>
      </div>

      <!-- ✅ Nếu giỏ hàng trống -->
      <div v-if="products.length === 0" class="empty-cart-message">
  <p>Giỏ hàng của bạn đang trống.</p>
</div>


      <!-- ✅ Thanh toán chỉ hiển thị khi có sản phẩm -->
      <div class="order-summary-panel" v-if="products.length > 0">
        <!-- Địa chỉ giao hàng -->
        <!-- Phương thức giao hàng -->
        <!-- Phương thức thanh toán -->
        <!-- Tóm tắt đơn hàng -->
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
            provinceName,
            districtName,
            wardName,
            this.streetAddress.trim()
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
                // 'mac_dinh' đã bị loại bỏ khỏi payload
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
                ho_ten: this.displayedAddress.ho_ten,
                sdt: this.displayedAddress.sdt,
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
        phuong_thuc_thanh_toan_id: 1,
        hinh_thuc_giao_hang: this.deliveryMethod,
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
              // 'mac_dinh' đã bị loại bỏ khỏi dia_chi_moi
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
  toast: true, // Biến nó thành một "toast" (thông báo nhỏ)
  position: 'top-end', // Đặt vị trí ở góc phải trên cùng (top-end)
  icon: 'success', // Vẫn giữ icon thành công
  title: 'Đặt hàng thành công!', // Tiêu đề của thông báo
  showConfirmButton: false, // Ẩn nút xác nhận
  timer: 3000, // Tự động đóng sau 3 giây (3000ms)
  timerProgressBar: true // Hiển thị thanh tiến trình đếm ngược thời gian đóng
});

        this.products = [];
      } catch (error) {
        console.error("Lỗi khi đặt hàng:", error);
        console.log(error.response?.data);
        Swal.fire("Lỗi", "Không thể đặt hàng. Vui lòng thử lại sau.", "error");
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
            // Nếu không dùng mac_dinh, bạn có thể lấy địa chỉ đầu tiên
            // hoặc địa chỉ gần nhất (nếu bạn sắp xếp theo id_dia_chi DESC ở backend)
            defaultAddress = addresses[0];
            // Nếu bạn muốn lấy địa chỉ cuối cùng được thêm vào, hãy đảm bảo query API của bạn
            // (trong DiaChiController@index) cũng order by id_dia_chi DESC.
        }

        if (defaultAddress) {
            this.displayedAddress = {
                ho_ten: user.ho_ten || "",
                sdt: user.sdt || "",
                dia_chi: defaultAddress.dia_chi || "",
            };
            this.currentAddressId = defaultAddress.id_dia_chi || defaultAddress.id;
            await this.fillAddressFormFromDisplayed();
            this.showAddressForm = false;
        } else {
            console.log('Người dùng chưa có địa chỉ nào.');
            this.currentAddressId = null;
            this.showAddressForm = true;
            this.selectedProvinceCode = '';
            this.selectedDistrictCode = '';
            this.selectedWardCode = '';
            this.streetAddress = '';
            this.displayedAddress = { ho_ten: user.ho_ten || "", sdt: user.sdt || "", dia_chi: "" };
        }
    } catch (err) {
        console.error("Lỗi khi lấy địa chỉ người dùng:", err.response?.data || err.message);
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
  margin-top: 16px;
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
  background-color: #e2f8ff; /* Light green background */
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
  background-color: #33ccff; /* Orange */
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