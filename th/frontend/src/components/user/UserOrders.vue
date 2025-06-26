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
          <span class="order-current-status">{{ getStatusLabel(order.trang_thai) }}</span>
          <span class="order-date">Ngày đặt: {{ formatDate(order.ngay_tao) }}</span>
        </div>

        <div class="order-address">
          Giao đến: {{ order.diachi?.dia_chi || "Không rõ địa chỉ" }}
        </div>

        <div v-for="item in order.order_items" :key="item.id" class="order-product-item">
  <img
    :src="item.bien_the?.san_pham?.anh_dai_dien || '/placeholder-image.jpg'"
    alt="Product Image"
    class="product-image"
  />
  <div class="product-details">
    <span class="product-name">
      {{ item.bien_the?.san_pham?.ten_san_pham || 'Không rõ tên sản phẩm' }}
    </span>
    <span class="product-variant" v-if="item.bien_the?.mau_sac || item.bien_the?.kich_thuoc">
      Phân loại:
      {{ item.bien_the.mau_sac || 'Màu không xác định' }}
      {{ item.bien_the.kich_thuoc ? '- ' + item.bien_the.kich_thuoc : '' }}
    </span>
    <span class="product-quantity">Số lượng: {{ item.so_luong }}</span>
    <span class="product-price">Đơn giá: {{ formatCurrency(item.don_gia) }}</span>
    <span class="product-total">
      Tổng: {{ formatCurrency(item.so_luong * item.don_gia) }}
    </span>
  </div>
</div>


        <div class="order-footer">
          <div>
            <span>Phương thức thanh toán: </span>
            {{ order.payment_method?.ten_pttt || "Không rõ" }}
          </div>
          <div>
            <span>Phí vận chuyển: </span> {{ formatCurrency(order.phi_van_chuyen) }}
          </div>
          <div>
            <span class="order-total-amount">
              Tổng tiền đơn hàng:
              {{ formatCurrency(calculateOrderTotal(order)) }}
            </span>
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

const router = useRouter();
const allOrders = ref([]);
const currentStatus = ref("all");
const loading = ref(true);

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
  return allOrders.value.filter((order) => order.trang_thai === currentStatus.value);
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

  const user = JSON.parse(storedUser);
  const token = localStorage.getItem("token");

  try {
    const response = await axios.get("http://localhost:8000/api/user/orders", {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    allOrders.value = response.data;
    console.log("Fetched orders:", response.data);
  } catch (error) {
    console.error("Lỗi khi tải đơn hàng:", error.response?.data || error.message);
    allOrders.value = [];
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

</script>

<style scoped>
/* Các styles chung cho main-content (nếu chưa có trong UserAccountLayout) */
.main-content {
  flex-grow: 1;
  padding: 20px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.main-content h1 {
  font-size: 24px;
  color: #333;
  margin-bottom: 15px;
}

.main-content hr {
  border: none;
  border-top: 1px solid #eee;
  margin-bottom: 20px;
}

/* Styles cho tab trạng thái đơn hàng */
.order-status-tabs {
  display: flex;
  justify-content: flex-start; /* Căn trái */
  gap: 10px;
  margin-bottom: 20px;
  flex-wrap: wrap; /* Cho phép xuống dòng nếu quá nhiều tab */
}

.order-status-tabs button {
  background-color: #f0f0f0;
  color: #555;
  border: 1px solid #ddd;
  padding: 10px 15px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.3s ease;
  white-space: nowrap; /* Ngăn text bị xuống dòng */
}

.order-status-tabs button:hover {
  background-color: #e0e0e0;
  border-color: #ccc;
}

.order-status-tabs button.active {
  background-color: #4a90e2; /* Màu xanh của bạn */
  color: white;
  border-color: #4a90e2;
  font-weight: bold;
}

/* Styles cho danh sách đơn hàng */
.order-list {
  min-height: 200px; /* Đảm bảo có khoảng trống khi không có đơn hàng */
}

.loading-message,
.no-orders-message {
  text-align: center;
  padding: 30px;
  color: #777;
  font-size: 16px;
}

.order-item {
  border: 1px solid #eee;
  border-radius: 8px;
  margin-bottom: 20px;
  overflow: hidden;
  background-color: #fff;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.order-header {
  background-color: #f9f9f9;
  padding: 10px 15px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  color: #666;
}
.order-item .order-address{
    margin: 12px;
}

.order-current-status {
  font-weight: bold;
  color: #4a90e2; /* Hoặc màu sắc tương ứng với trạng thái */
}

.order-product-item {
  display: flex;
  align-items: center;
  padding: 15px;
  border-bottom: 1px dashed #eee; /* Đường kẻ đứt quãng giữa các sản phẩm */
}

.order-product-item:last-of-type {
  border-bottom: none; /* Không có đường kẻ dưới cùng nếu là sản phẩm cuối cùng */
}

.product-image {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 4px;
  margin-right: 15px;
  border: 1px solid #ddd;
}

.product-details {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.product-name {
  font-weight: bold;
  color: #333;
  font-size: 16px;
}

.product-variant,
.product-quantity,
.product-price {
  font-size: 14px;
  color: #666;
}

.product-total {
  font-size: 15px;
  font-weight: bold;
  color: #e74c3c; /* Màu đỏ cho tổng tiền sản phẩm */
  margin-top: 5px;
}

.order-footer {
  padding: 10px 15px;
  background-color: #f9f9f9;
  border-top: 1px solid #eee;
  text-align: right;
  font-size: 16px;
  font-weight: bold;
  color: #333;
}

.order-total-amount {
  color: #e74c3c;
}

.btn-detail {
  background-color: #28a745;
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 5px;
  cursor: pointer;
  margin-left: 10px;
  transition: background-color 0.3s ease;
}

.btn-detail:hover {
  background-color: #218838;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .order-product-item {
    flex-direction: column;
    align-items: flex-start;
  }
  .product-image {
    margin-right: 0;
    margin-bottom: 10px;
  }
}
</style>
