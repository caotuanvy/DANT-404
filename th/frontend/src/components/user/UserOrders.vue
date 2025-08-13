<template>
    <h1>Đơn hàng đã mua</h1>
    <hr />

    <div class="order-status-tabs">
      <button
        v-for="status in orderStatuses"
        :key="status.value"
        :class="{ active: currentStatus === status.value }"
        @click="filterOrdersByStatus(status.value)"
      >
        {{ status.label }}
      </button>
    </div>

    <div class="order-list">
      <div v-if="loading" class="loading-message">Đang tải đơn hàng...</div>
      <div v-else-if="filteredOrders.length === 0" class="no-orders-message">
        Không có đơn hàng nào trong trạng thái này.
      </div>
      <div v-else>
        <div v-for="order in filteredOrders" :key="order.id" class="order-item">
          <div class="order-header">
            <span class="order-current-status">{{ getStatusLabel(order.trang_thai_don_hang) }}</span>
            <span class="order-date">Ngày đặt: {{ formatDate(order.ngay_dat) }}</span>
          </div>

          <div class="order-address">
            Giao đến: {{ order.diachi?.dia_chi || "Không rõ địa chỉ" }}
          </div>

          <div
            v-for="item in order.order_items"
            :key="item.id"
            class="order-product-item"
          >
            <img
  :src="getImageUrl(item.bien_the.san_pham.hinh_anh_san_pham)"
  alt="Product Image"
  class="product-image"
/>
            <div class="product-details">
              <span class="product-name">
                {{ item.bien_the?.san_pham?.ten_san_pham || "Không rõ tên sản phẩm" }}
              </span>
              <span
                class="product-variant"
                v-if="item.bien_the?.mau_sac || item.bien_the?.kich_thuoc"
              >
                Phân loại:
                {{ item.bien_the.mau_sac || "Màu không xác định" }}
                {{ item.bien_the.kich_thuoc ? "- " + item.bien_the.kich_thuoc : "" }}
              </span>
              <span class="product-quantity">Số lượng: {{ item.so_luong }}</span>
              <span class="product-price">Đơn giá: {{ formatCurrency(item.don_gia) }}</span>
              <span class="product-subtotal">
                Tổng: {{ formatCurrency(item.so_luong * item.don_gia) }}
              </span>
            </div>
          </div>

          <div class="order-footer">
            <div class="payment-shipping-info">
              <span>Phương thức thanh toán: </span>
              {{ order.payment_method?.ten_pttt || "Không rõ" }}
              <br />
              <span>Phí vận chuyển: </span> {{ formatCurrency(order.phi_van_chuyen) }}
            </div>
            <div class="total-and-action">
              <span class="order-total-amount">
                Tổng tiền đơn hàng:
                {{ formatCurrency(calculateOrderTotal(order)) }}
              </span>
              <button
                v-if="order.trang_thai_don_hang === 1"
                class="btn-cancel-order"
                @click="confirmCancelOrder(order.id)"
              >
                Hủy đơn hàng
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import Swal from 'sweetalert2'; // Import SweetAlert2

const router = useRouter();
const allOrders = ref([]);
const currentStatus = ref("all");
const loading = ref(true);
const getImageUrl = (images) => {
  if (images && images.length > 0) {
    // Trả về đường dẫn của ảnh từ API
    return `http://127.0.0.1:8000/storage/${images[0].duongdan}`;
  }
  // Trả về ảnh placeholder trong thư mục public
  return '/placeholder-image.jpg';
};

const orderStatuses = [
  { label: "Tất cả", value: "all" },
  { label: "Chờ xử lý", value: 1 },
  { label: "Đã xác nhận", value: 2 },
  { label: "Đang giao hàng", value: 3 },
  { label: "Đã giao", value: 4 },
  { label: "Đã hủy", value: 5 },
];

const getStatusLabel = (statusId) => {
  const status = orderStatuses.find((s) => s.value === statusId);
  return status ? status.label : "Không xác định";
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(
    value
  );
};

const formatDate = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  return date.toLocaleDateString("vi-VN", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
  });
};

const filteredOrders = computed(() => {
  if (currentStatus.value === "all") return allOrders.value;
  return allOrders.value.filter((order) => order.trang_thai_don_hang === currentStatus.value);
});

const filterOrdersByStatus = (status) => {
  currentStatus.value = status;
};

const fetchOrders = async () => {
  loading.value = true;
  const storedUser = localStorage.getItem("user");
  if (!storedUser) {
    router.push("/");
    loading.value = false;
    return;
  }

  const token = localStorage.getItem("token");

  try {
    const response = await axios.get("/user/orders", {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    allOrders.value = response.data;
  } catch (error) {
    console.error("Lỗi khi tải đơn hàng:", error.response?.data || error.message);
    allOrders.value = [];
    Swal.fire({ // Dùng SweetAlert2 cho thông báo lỗi tải
        icon: 'error',
        title: 'Lỗi!',
        text: 'Không thể tải danh sách đơn hàng. Vui lòng thử lại sau.'
    });
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchOrders();
});

const calculateOrderTotal = (order) => {
  const itemsTotal = order.order_items?.reduce((sum, item) => {
    return sum + item.so_luong * item.don_gia;
  }, 0) || 0;

  const shipping = parseFloat(order.phi_van_chuyen) || 0;

  return itemsTotal + shipping;
};

const confirmCancelOrder = async (orderId) => {
  // Định nghĩa danh sách các lý do hủy
  const cancelReasons = {
    'Sai địa chỉ giao hàng': 'Nhập sai địa chỉ giao hàng',
    'Sai thông tin người nhận': 'Sai thông tin người nhận',
    'Muốn thay đổi sản phẩm hoặc số lượng': 'Muốn thay đổi sản phẩm hoặc số lượng',
    'Muốn thay đổi phương thức thanh toán': 'Muốn thay đổi phương thức thanh toán',
    'Lý do khác': 'Lý do khác'
  };

  const result = await Swal.fire({
    title: 'Bạn có chắc chắn?',
    text: 'Bạn sẽ không thể hoàn tác thao tác này sau khi hủy đơn hàng!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Có, hủy đơn hàng!',
    cancelButtonText: 'Không, giữ lại đơn hàng',
    
    // Thêm input để người dùng chọn lý do
    input: 'select',
    inputOptions: cancelReasons,
    inputPlaceholder: 'Chọn một lý do',
    inputValidator: (value) => {
      return new Promise((resolve) => {
        if (value) {
          resolve();
        } else {
          resolve('Vui lòng chọn một lý do hủy!');
        }
      });
    }
  });

  // Kiểm tra nếu người dùng bấm 'Có' và đã chọn lý do
  if (!result.isConfirmed || !result.value) {
    return; // Người dùng đã chọn không hủy hoặc chưa chọn lý do
  }

  const cancelReasonValue = result.value; // Lấy giá trị đã chọn
  const token = localStorage.getItem("token");
  const order = allOrders.value.find((o) => o.id === orderId);

  if (!order) {
    Swal.fire({
      icon: 'error',
      title: 'Lỗi!',
      text: 'Không tìm thấy đơn hàng để hủy.'
    });
    return;
  }

  try {
    // Gửi lý do hủy lên API
    await axios.post(`/orders/${order.id}/cancel`, {
      ly_do_huy: cancelReasonValue 
    }, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    order.trang_thai_don_hang = 5; // Cập nhật trạng thái thành 'Đã hủy'
    Swal.fire(
      'Đã hủy!',
      'Đơn hàng của bạn đã được hủy thành công.',
      'success'
    );
  } catch (error) {
    console.error("Lỗi khi hủy đơn hàng:", error.response?.data || error.message);
    Swal.fire({
      icon: 'error',
      title: 'Hủy thất bại!',
      text: 'Có lỗi xảy ra khi hủy đơn hàng. Vui lòng thử lại.'
    });
  }
};
</script>

<style scoped>
/* Các styles CSS giữ nguyên như bạn đã cung cấp */
.user-orders-container {
  padding: 20px;
  background-color: #f8f8f8;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  max-width: 1000px;
  margin: 20px auto;
}

h1 {
  font-size: 28px;
  color: #333;
  margin-bottom: 15px;
  /* text-align: center; */
  font-weight: 700;
}

hr {
  border: none;
  border-top: 2px solid #eee;
  margin-bottom: 25px;
}

/* --- Order Status Tabs --- */
.order-status-tabs {
  display: flex;
  /* justify-content: center; */
  gap: 12px;
  margin-bottom: 30px;
  flex-wrap: wrap;
}

.order-status-tabs button {
  background-color: #e9ecef;
  color: #495057;
  border: 1px solid #ced4da;
  padding: 10px 22px;
  border-radius: 25px;
  cursor: pointer;
  font-size: 15px;
  font-weight: 600;
  transition: all 0.3s ease;
  white-space: nowrap;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  flex-shrink: 0; /* Prevent shrinking on smaller screens */
}

.order-status-tabs button:hover {
  background-color: #cfe2ff;
  border-color: #a7d9ff;
  color: #0056b3;
  transform: translateY(-3px);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.order-status-tabs button.active {
  background-color: #007bff;
  color: white;
  border-color: #007bff;
  font-weight: bold;
  box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
  transform: translateY(-2px);
}

/* --- Order List & Messages --- */
.order-list {
  min-height: 250px;
}

.loading-message,
.no-orders-message {
  text-align: center;
  padding: 40px;
  color: #777;
  font-size: 18px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

/* --- Individual Order Item --- */
.order-item {
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  margin-bottom: 25px;
  overflow: hidden;
  background-color: #fff;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s ease-in-out;
}

.order-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
}

.order-header {
  background-color: #f5f5f5;
  padding: 12px 20px;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  color: #555;
  font-weight: 500;
}

.order-current-status {
  font-weight: bold;
  color: #28a745; /* Success color for status */
  font-size: 15px;
}

.order-address {
  margin: 15px 20px;
  font-size: 15px;
  color: #444;
  line-height: 1.5;
}

/* --- Order Product Item --- */
.order-product-item {
  display: flex;
  align-items: flex-start; /* Align items to the top */
  padding: 15px 20px;
  border-bottom: 1px dashed #e9ecef;
}

.order-product-item:last-of-type {
  border-bottom: none;
}

.product-image {
  width: 90px;
  height: 90px;
  object-fit: cover;
  border-radius: 6px;
  margin-right: 18px;
  border: 1px solid #ddd;
  flex-shrink: 0; /* Prevent image from shrinking */
}

.product-details {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.product-name {
  font-weight: 600;
  color: #333;
  font-size: 16px;
  margin-bottom: 2px;
}

.product-variant,
.product-quantity,
.product-price {
  font-size: 14px;
  color: #666;
}

.product-subtotal {
  font-size: 15px;
  font-weight: bold;
  color: #dc3545; /* Red for individual item total */
  margin-top: 8px;
}

/* --- Order Footer --- */
.order-footer {
  padding: 15px 20px;
  background-color: #f9f9f9;
  border-top: 1px solid #e0e0e0;
  display: flex;
  flex-direction: column;
  align-items: flex-end; /* Align content to the right */
  gap: 8px;
  font-size: 15px;
  color: #333;
}

.order-footer div span {
  font-weight: 600;
  color: #555;
}

.total-and-action {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-top: 10px;
  width: 100%; /* Take full width for alignment */
  justify-content: flex-end; /* Align content to the right */
}

.order-total-amount {
  font-size: 18px;
  font-weight: bold;
  color: #007bff; /* Blue for total amount */
}

.btn-cancel-order {
  background-color: #dc3545;
  color: white;
  border: none;
  padding: 10px 18px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 15px;
  font-weight: 600;
  transition: background-color 0.3s ease, transform 0.2s ease;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.btn-cancel-order:hover {
  background-color: #c82333;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* --- Responsive Adjustments --- */
@media (max-width: 768px) {
  .user-orders-container {
    padding: 15px;
    margin: 15px;
  }

  h1 {
    font-size: 24px;
  }

  .order-status-tabs {
    justify-content: center; /* Center tabs on smaller screens */
    gap: 8px;
  }

  .order-status-tabs button {
    padding: 8px 15px;
    font-size: 14px;
  }

  .order-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
    padding: 10px 15px;
  }

  .order-product-item {
    flex-direction: column;
    align-items: flex-start;
    padding: 15px;
  }
  .product-image {
    margin-right: 0;
    margin-bottom: 12px;
  }

  .product-details {
    width: 100%; /* Take full width */
  }

  .order-footer {
    align-items: center; /* Center content in footer on smaller screens */
    text-align: center;
    padding: 15px;
  }

  .total-and-action {
    flex-direction: column;
    gap: 10px;
    width: 100%;
    align-items: center; /* Center items in action block */
  }

  .btn-cancel-order {
    width: 80%; /* Make button wider */
    max-width: 250px; /* Limit max width */
  }
}

@media (max-width: 480px) {
  .order-status-tabs button {
    width: 100%; /* Full width buttons for very small screens */
    text-align: center;
  }
}
</style>