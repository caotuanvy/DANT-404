<template>
  <div class="d-flex justify-content-center align-items-center min-vh-100 bg-light py-5 px-3">
    <div class="card shadow-lg p-4 p-sm-5 mx-auto" style="max-width: 900px; width: 100%; border-radius: 0.5rem;">
      <div v-if="successMessage" class="text-center mb-4 mb-sm-5">
        <svg class="text-success mx-auto mb-3" width="64" height="64" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" d="M10.293 15.707a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L9 13.586l3.293-3.293a1 1 0 011.414 1.414l-4 4z" clip-rule="evenodd"></path>
          <path fill-rule="evenodd" d="M12 22a10 10 0 100-20 10 10 0 000 20zM12 2a8 8 0 100 16 8 8 0 000-16z" clip-rule="evenodd"></path>
        </svg>
        <h1 class="fs-3 fw-bold text-dark mb-2">Đặt hàng thành công!</h1>
        <p class="text-secondary">Cảm ơn bạn đã mua hàng. Đơn hàng của bạn đã được xác nhận thành công.</p>
      </div>
      <div v-else-if="errorMessage" class="text-center mb-4 mb-sm-5">
        <svg class="text-danger mx-auto mb-3" width="64" height="64" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 2a10 10 0 100 20 10 10 0 000-20zM12 4a8 8 0 110 16 8 8 0 010-16zm-1 5a1 1 0 012 0v5a1 1 0 01-2 0V9zm0 7a1 1 0 012 0v1a1 1 0 01-2 0v-1z"></path>
        </svg>
        <h1 class="fs-3 fw-bold text-danger mb-2">Thanh toán thất bại!</h1>
        <p class="text-secondary">{{ errorMessage }}</p>
      </div>
      <div v-if="order && !isLoading">
        <div class="bg-white border rounded-lg p-4 mb-4">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
            <div>
              <h2 class="fs-5 fw-semibold text-dark mb-1">Đơn hàng #{{ order.id }}</h2>
              <p class="text-muted small mb-3 mb-md-0">Ngày đặt ngày {{ order.placedDate }}</p>
            </div>
            <div class="d-flex flex-column flex-sm-row gap-3 flex-wrap justify-content-md-start w-100 w-md-auto">
              <button class="btn btn-primary d-flex align-items-center justify-content-center py-2 px-3 small">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="me-2" style="width: 1.1rem; height: 1.1rem;">
                  <path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 100 13.5 6.75 6.75 0 000-13.5zM2.25 10.5a8.25 8.25 0 1114.59 5.28L21.42 21.42a.75.75 0 11-1.06 1.06l-4.59-4.59A8.25 8.25 0 012.25 10.5z" clip-rule="evenodd" />
                </svg>
                Theo dõi đơn hàng
              </button>
              <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center py-2 px-3 small">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="me-2" style="width: 1.1rem; height: 1.1rem;">
                  <path fill-rule="evenodd" d="M12 2.25a.75.75 0 01.75.75v11.69l3.245-3.245a.75.75 0 111.06 1.06l-4.5 4.5a.75.75 0 01-1.06 0l-4.5-4.5a.75.75 0 111.06-1.06L11.25 14.69V3a.75.75 0 01.75-.75z" clip-rule="evenodd" />
                  <path fill-rule="evenodd" d="M12 15.75a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0v-3.75a.75.75 0 01.75-.75zM15.75 18a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5a.75.75 0 01.75-.75zM8.25 18a.75.75 0 01-.75.75v1.5a.75.75 0 01-1.5 0v-1.5a.75.75 0 01.75-.75z" clip-rule="evenodd" />
                </svg>
                Tải hóa đơn
              </button>
            </div>
          </div>
        </div>
        <div class="mb-4">
          <h3 class="fs-5 fw-semibold text-dark mb-3">Sản phẩm đã đặt</h3>
          <div class="d-flex flex-column gap-3">
            <div v-for="item in order.items" :key="item.id" class="d-flex align-items-center">
              <img :src="item.image" :alt="item.name" class="img-fluid rounded me-3" style="width: 64px; height: 64px; object-fit: cover;">
              <div class="flex-grow-1">
                <p class="fw-medium text-dark mb-0">{{ item.name }}</p>
                <p class="text-muted small mb-0">
                  <span v-if="item.color && item.color !== 'Không'">Màu: {{ item.color }} &bull; </span>
                  <span v-if="item.size">Kích thước: {{ item.size }} &bull; </span>
                  Số lượng: {{ item.qty }}
                </p>
              </div>
              <p class="fw-semibold text-dark mb-0">{{ formatCurrency(item.price * item.qty) }}</p>
            </div>
          </div>
        </div>

        <div class="border-top pt-3 mb-4">
          <div class="d-flex justify-content-between text-secondary mb-2">
            <span>Tạm tính</span>
            <span>{{ formatCurrency(order.subtotal) }}</span>
          </div>
          <div class="d-flex justify-content-between text-secondary mb-2">
            <span>Phí vận chuyển</span>
            <span>{{ formatCurrency(order.shipping) }}</span>
          </div>
          <div class="d-flex justify-content-between fw-bold fs-5 text-dark border-top pt-2 mt-2">
            <span>Tổng cộng</span>
            <span>{{ formatCurrency(order.total) }}</span>
          </div>
        </div>

        <div class="row g-4 mb-4">
          <div class="col-md-6">
            <div class="bg-gray-100 p-4 rounded-lg h-100">
              <h3 class="fs-5 fw-semibold text-dark mb-3">Thông tin giao hàng</h3>
              <p class="text-secondary mb-1">Dự kiến giao hàng</p>
              <p class="fw-semibold text-success mb-3">{{ order.deliveryInfo.estimatedDelivery }}</p>              <p class="text-secondary mb-0">{{ order.deliveryInfo.shippingAddress.name }}</p>
              <p class="text-secondary mb-0">{{ order.deliveryInfo.shippingAddress.street }}</p>
              <p class="text-secondary mb-0">{{ order.deliveryInfo.shippingAddress.city }} {{ order.deliveryInfo.shippingAddress.zip }}</p>
              <p class="text-secondary mb-0">{{ order.deliveryInfo.shippingAddress.country }}</p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-gray-100 p-4 rounded-lg h-100">
              <h3 class="fs-5 fw-semibold text-dark mb-3">Thông tin thanh toán</h3>
              <p class="text-secondary mb-1">Phương thức thanh toán</p>
              <p class="text-secondary mb-3">{{ order.paymentInfo.method }}</p>              <p class="text-secondary mb-0">{{ order.paymentInfo.billingAddress.name }}</p>
              <p class="text-secondary mb-0">{{ order.paymentInfo.billingAddress.street }}</p>
              <p class="text-secondary mb-0">{{ order.paymentInfo.billingAddress.city }} {{ order.paymentInfo.billingAddress.zip }}</p>
              <p class="text-secondary mb-0">{{ order.paymentInfo.billingAddress.country }}</p>
            </div>
          </div>
        </div>

        <div class="bg-gray-100 p-4 rounded-lg mb-4">
          <h3 class="fs-5 fw-semibold text-dark mb-3">Thông tin liên hệ</h3>
          <div class="d-flex align-items-center mb-2">
            <svg class="text-secondary me-2" width="20" height="20" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-4 8H7a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2z"></path>
            </svg>
            <span class="text-secondary">{{ order.contactInfo.email }}</span>
          </div>
          <div class="d-flex align-items-center">
            <svg class="text-secondary me-2" width="20" height="20" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
            </svg>
            <span class="text-secondary">{{ order.contactInfo.phone }}</span>
          </div>
        </div>

        <div class="bg-info-subtle p-4 rounded-lg mb-4">
          <h3 class="fs-5 fw-semibold text-info mb-3">Bước tiếp theo</h3>
          <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
            <li class="d-flex align-items-start text-info">
              <svg class="text-info me-2 mt-1 flex-shrink-0" width="20" height="20" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M16.704 4.704a1 1 0 01-1.408 0l-7.5-7.5a1 1 0 011.408-1.408l7.5 7.5a1 1 0 010 1.408zM19.99 10.99A10.003 10.003 0 0012 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10a1 1 0 01-2 0zM12 20a8 8 0 100-16 8 8 0 000 16z" clip-rule="evenodd" />
              </svg>
              Bạn sẽ nhận được email xác nhận trong ít phút.
            </li>
            <li class="d-flex align-items-start text-info">
              <svg class="text-info me-2 mt-1 flex-shrink-0" width="20" height="20" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M10.293 15.707a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L9 13.586l3.293-3.293a1 1 0 011.414 1.414l-4 4z" clip-rule="evenodd"></path>
                <path fill-rule="evenodd" d="M12 22a10 10 0 100-20 10 10 0 000 20zM12 2a8 8 0 100 16 8 8 0 000-16z" clip-rule="evenodd"></path>
              </svg>
              Chúng tôi sẽ thông báo khi đơn hàng được giao cho đơn vị vận chuyển
            </li>
            <li class="d-flex align-items-start text-info">
              <svg class="text-info me-2 mt-1 flex-shrink-0" width="20" height="20" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14m-5 0l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14m-5 0L4.447 17.276A1 1 0 013 15.382v-6.764a1 1 0 011.447-.894L9 10m-2 4h.01" clip-rule="evenodd" />
              </svg>
              Theo dõi gói hàng bằng mã vận đơn chúng tôi sẽ gửi
            </li>
          </ul>
        </div>
        <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
          <button class="btn btn-primary fw-semibold py-3 px-4">
            Tiếp tục mua sắm
          </button>
          <button class="btn btn-outline-secondary fw-semibold py-3 px-4">
            Liên hệ hỗ trợ
          </button>
        </div>
      </div>
      <div v-else class="text-center py-5">
        <h2 v-if="isLoading" class="text-dark">Đang tải thông tin đơn hàng...</h2>
        <h2 v-else class="text-danger">Không tìm thấy đơn hàng hoặc có lỗi xảy ra.</h2>
        <p class="text-secondary">Vui lòng kiểm tra lại ID đơn hàng hoặc thử lại sau.</p>
        <router-link to="/" class="btn btn-primary mt-3">Về trang chủ</router-link>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { useRoute } from 'vue-router';
import { ref, onMounted } from 'vue';

export default {
  name: 'paymentsuccess',
  props: {
    orderId: {
      type: [String, Number],
      required: false
    }
  },
  setup(props) {
    const route = useRoute();
    const order = ref(null);
    const isLoading = ref(true);
    const successMessage = ref(false);
    const errorMessage = ref(null);

    const getOrderId = () => {
      // Ưu tiên lấy từ query parameter của VNPAY
      if (route.query.vnp_TxnRef) {
        return route.query.vnp_TxnRef;
      }
      // Nếu không, lấy từ route parameter (cho các trường hợp khác)
      return props.orderId;
    };

    const fetchOrderDetails = async (id) => {
      if (!id) {
        console.error("No Order ID provided. Cannot load order details.");
        isLoading.value = false;
        return;
      }

      try {
        const token = localStorage.getItem('token');
        // Gọi đúng endpoint API để lấy chi tiết đơn hàng
        const res = await axios.get(`http://localhost:8000/api/orders/${id}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        if (res.data) {
          order.value = mapOrderApiToView(res.data);
          // Kiểm tra và cập nhật thông báo thành công/thất bại
          successMessage.value = route.query.success === '1' || route.query.vnp_ResponseCode === '00';
          if (!successMessage.value) {
            errorMessage.value = 'Thanh toán thất bại. Mã lỗi VNPAY: ' + route.query.vnp_ResponseCode;
          }
        }
      } catch (err) {
        console.error('Lỗi khi lấy thông tin đơn hàng:', err.response?.data || err.message);
        errorMessage.value = 'Không thể tải thông tin đơn hàng. Vui lòng thử lại.';
        order.value = null;
      } finally {
        isLoading.value = false;
      }
    };

    const mapOrderApiToView = (apiOrder) => {
      const subtotalCalc = (apiOrder.order_items || []).reduce((sum, item) => sum + (parseFloat(item.so_luong) * parseFloat(item.don_gia)), 0);
      const shippingFee = parseFloat(apiOrder.phi_van_chuyen || 0);
      const totalCalc = parseFloat(apiOrder.tong_tien || 0);

      return {
        id: apiOrder.id,
        placedDate: formatDate(apiOrder.ngay_dat),
        items: (apiOrder.order_items || []).map(item => ({
          id: item.id,
          name: item.product_variant?.product?.ten_san_pham || 'Sản phẩm không rõ',
          color: item.product_variant?.mau_sac || 'Không',
          size: item.product_variant?.kich_thuoc || 'Không',
          qty: item.so_luong,
          price: parseFloat(item.don_gia),
          image: item.product_variant?.product?.hinh_anh_san_pham && item.product_variant.product.hinh_anh_san_pham.length > 0
            ? `http://localhost:8000/storage/${item.product_variant.product.hinh_anh_san_pham[0].duongdan}`
            : '/images/no-image.png',
        })),
        subtotal: subtotalCalc,
        shipping: shippingFee,
        total: totalCalc,

        deliveryInfo: {
          estimatedDelivery: apiOrder.ngay_giao_du_kien || 'Dự kiến trong 3-5 ngày làm việc',
          shippingAddress: {
            name: apiOrder.diachi?.ho_ten || apiOrder.user?.ho_ten || '',
            street: apiOrder.diachi?.dia_chi || 'Chưa có địa chỉ chi tiết',
            city: apiOrder.diachi?.tinh_thanh || '',
            zip: apiOrder.diachi?.ma_buu_dien || '',
            country: 'Việt Nam'
          }
        },
        paymentInfo: {
          method: apiOrder.phuong_thuc_thanh_toan?.ten_pttt || 'Không xác định',
          billingAddress: {
            name: apiOrder.diachi?.ho_ten || apiOrder.user?.ho_ten || '',
            street: apiOrder.diachi?.dia_chi || '',
            city: apiOrder.diachi?.tinh_thanh || '',
            zip: apiOrder.diachi?.ma_buu_dien || '',
            country: 'Việt Nam'
          }
        },
        contactInfo: {
          email: apiOrder.user?.email || '',
          phone: apiOrder.user?.sdt || ''
        }
      };
    };

    const formatDate = (dateStr) => {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      return `${d.getDate().toString().padStart(2, '0')}/${(d.getMonth() + 1).toString().padStart(2, '0')}/${d.getFullYear()}`;
    };

    const formatCurrency = (value) => {
      if (value === null || typeof value === 'undefined') return '0 VND';
      return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
      }).format(value);
    };

    onMounted(() => {
      const id = getOrderId();
      if (id) {
        fetchOrderDetails(id);
      } else {
        console.error("Không có ID đơn hàng để xử lý.");
        errorMessage.value = 'Không có ID đơn hàng để xử lý. Vui lòng thử lại.';
        isLoading.value = false;
      }
    });

    return {
      order,
      isLoading,
      successMessage,
      errorMessage,
      formatCurrency,
    };
  },
};
</script>

<style scoped>
/* Bootstrap styles are implicitly handled by Bootstrap classes */
/* Custom CSS để điều chỉnh các chi tiết nhỏ và ghi đè Bootstrap nếu cần */

/* Tông màu xám nhạt cho nền của các khối con (tương tự bg-gray-50 trong Tailwind) */
.bg-gray-100 {
  background-color: #f8f9fa !important; /* Bootstrap's bg-light is similar, but this is lighter */
}

/* Các SVG icons của bạn */
svg {
  fill: currentColor; /* Để màu sắc được kế thừa từ text-success, text-secondary, text-info */
}

/* Điều chỉnh font-weight và font-size cho các tiêu đề và văn bản */
h1 {
  font-size: 2.25rem !important; /* Larger than Bootstrap's default h1 */
}

h2.fs-5 {
  font-size: 1.25rem !important;
}

h3.fs-5 {
  font-size: 1.15rem !important;
}

/* Các button hành động */
.btn-primary {
  background-color: #0d6efd !important;
  border-color: #0d6efd !important;
}
.btn-primary:hover {
  background-color: #0b5ed7 !important;
  border-color: #0b5ed7 !important;
}

.btn-outline-secondary {
  color: #6c757d !important;
  border-color: #dee2e6 !important;
}
.btn-outline-secondary:hover {
  background-color: #e9ecef !important;
  border-color: #e9ecef !important;
  color: #545b62 !important;
}

/* Màu sắc cho phần "What's Next" */
.bg-info-subtle {
  background-color: #e0f2fe !important; /* Tương tự bg-blue-50 */
}
.text-info {
  color: #0d6efd !important; /* Màu xanh của Bootstrap primary */
}
.text-info svg {
  color: #0d6efd !important; /* Ensure SVG color matches text */
}
.text-success {
  color: #28a745 !important; /* Màu xanh lá cây nhấn mạnh */
}
</style>