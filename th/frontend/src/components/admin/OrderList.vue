<template>
  <div class="order-page">
    <h2 class="title">Quản lý/Đơn hàng</h2>

    <div class="box search-order-id-box">
      <p class="section-title">Tìm kiếm mã đơn hàng</p>
      <div class="search-row">
        <input
          v-model="searchQuery"
          inputmode="numeric"
          pattern="[0-9]*"
          placeholder="Tìm kiếm mã đơn hàng..."
          class="input search-input-field"
          @input="searchQuery = searchQuery.replace(/\D/g, '')"
          @keyup.enter="fetchOrders"
        />
        <button class="btn search-btn" @click="fetchOrders">🔍</button>
      </div>
    </div>

    <div class="box filter-box">
      <p class="section-title">Tìm kiếm đơn hàng nâng cao</p>
      <div class="grid-container">
        <input class="input" placeholder="Ngày đặt (DD/MM/YYYY)" />
        <select class="input">
          <option>Chọn hình thức thanh toán</option>
        </select>
        <select class="input">
          <option>Chọn danh mục</option>
        </select>
        <input class="input" placeholder="Khoảng giá" />
      </div>
      <div class="btn-group">
        <button class="btn search-filter-btn">Tìm kiếm</button>
        <button class="btn clear-filter-btn">Huỷ lọc</button>
      </div>

      <div class="status-tabs">
        <button
          :class="{ 'tab-btn': true, 'active': selectedStatusTab === null }"
          @click="setFilterStatus(null)"
        >
          Tất cả
        </button>
        <button
          :class="{ 'tab-btn': true, 'active': selectedStatusTab === 1 }"
          @click="setFilterStatus(1)"
        >
          Mới đặt
        </button>
        <button
          :class="{ 'tab-btn': true, 'active': selectedStatusTab === 2 }"
          @click="setFilterStatus(2)"
        >
          Chờ xác nhận
        </button>
        <button
          :class="{ 'tab-btn': true, 'active': selectedStatusTab === 3 }"
          @click="setFilterStatus(3)"
        >
          Đã xác nhận
        </button>
        <button
          :class="{ 'tab-btn': true, 'active': selectedStatusTab === 4 }"
          @click="setFilterStatus(4)"
        >
          Đang giao hàng
        </button>
        <button
          :class="{ 'tab-btn': true, 'active': selectedStatusTab === 5 }"
          @click="setFilterStatus(5)"
        >
          Đã hủy
        </button>
      </div>
    </div>

    <div class="order-table-container box">
      <table class="order-table">
        <thead>
          <tr>
            <th>Mã</th>
            <th>Họ tên</th>
            <th>Ngày đặt</th>
            <th>HT Thanh toán</th>
            <th>Tổng giá</th>
            <th>Tình trạng</th>
            <th>Trạng thái thanh toán</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody id="order-body">
          <tr v-for="order in orders" :key="order.id">
            <td class="border px-2 text-center">{{ order.id }}</td>
            <td class="border px-2 text-left">{{ order.user?.ho_ten || 'Ẩn danh' }}</td>
            <td class="border px-2 text-left">{{ formatDate(order.ngay_tao) }}</td>
            <td class="border px-2 text-left">
              {{ order.payment_method?.ten_pttt || 'Không rõ' }}
            </td>
            <td class="border px-2 text-right">
              {{ formatCurrency(order.order_items?.reduce((sum, item) => sum + (item.so_luong * item.don_gia), 0) || 0) }}
            </td>
            <td class="border px-4 text-center status-cell"> <div v-if="editingOrderId === order.id" class="status-edit-popover">
                <button @click="updateAndSaveStatus(order, 2)" :class="{'popover-option-btn': true, 'active-status': newStatus === 2}">Chờ xác nhận</button>
                <button @click="updateAndSaveStatus(order, 3)" :class="{'popover-option-btn': true, 'active-status': newStatus === 3}">Đã xác nhận</button>
                <button @click="updateAndSaveStatus(order, 4)" :class="{'popover-option-btn': true, 'active-status': newStatus === 4}">Đang giao hàng</button>
                <button @click="updateAndSaveStatus(order, 5)" :class="{'popover-option-btn': true, 'active-status': newStatus === 5}">Đã hủy</button>
              </div>
              <div v-else>
                {{ getTrangThai(order.trang_thai) }}
              </div>
            </td>
            <td class="border px-2 text-center">
              {{ getPaymentStatus(order.is_paid) }} 
            </td>
            <td class="border px-2 text-center action-buttons-cell">
              <button @click="openOrderDetail(order)" class="action-btn">Chi tiết</button>
              <button v-if="editingOrderId === order.id" @click="saveStatus(order)" class="action-btn">Cập nhật</button>
              <button v-else @click="startEditStatus(order)" class="action-btn">Sửa trạng thái</button>
              <button
                v-if="editingOrderId === order.id"
                @click="cancelEdit"
                class="action-btn text-red-600"
              >Hủy</button>
              <button
                v-else
                @click="deleteOrder(order.id)"
                class="action-btn text-red-600"
              >Ẩn</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showDetail && selectedOrder" class="modal-overlay">
      <div class="order-invoice" id="print-area">
        <button
          @click="closeOrderDetail"
          style="
            position: absolute;
            top: 12px;
            right: 16px;
            background: transparent;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            color: black;
          "
          aria-label="Đóng"
        >×</button>
        <div class="invoice-header">
          <h2>HÓA ĐƠN BÁN HÀNG</h2>
          <p><b>Mã đơn hàng:</b> #{{ selectedOrder.id }}</p>
          <p><b>Ngày đặt:</b> {{ formatDate(selectedOrder.ngay_tao) }}</p>
        </div>
        <div class="invoice-info">
          <div>
            <b>Khách hàng:</b> {{ selectedOrder.user?.ho_ten }}<br>
            <b>Phương thức thanh toán:</b> {{ selectedOrder.payment_method?.ten_pttt }}<br>
            <b>Tình trạng:</b> {{ getTrangThai(selectedOrder.trang_thai) }}
          </div>
          <div>
            <b>Địa chỉ:</b> {{ selectedOrder.diachi?.dia_chi || '...' }}<br>
            <b>SĐT:</b> {{ selectedOrder.user?.sdt || '...' }}
          </div>
        </div>
        <table class="invoice-table">
          <thead>
            <tr>
              <th>STT</th>
              <th>Tên sản phẩm</th>
              <th>Biến thể</th>
              <th>Số lượng</th>
              <th>Đơn giá</th>
              <th>Thành tiền</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, idx) in selectedOrder.order_items" :key="item.id">
              <td>{{ idx + 1 }}</td>
              <td>{{ item.bien_the?.san_pham?.ten_san_pham || 'Không rõ' }}</td>
              <td>
                {{ item.bien_the?.ten_bien_the || '' }}
                <span v-if="item.bien_the?.mau_sac">Màu: {{ item.bien_the.mau_sac }}</span>
                <span v-if="item.bien_the?.kich_thuoc"> - Size: {{ item.bien_the.kich_thuoc }}</span>
              </td>
              <td>{{ item.so_luong }}</td>
              <td>{{ formatCurrency(item.don_gia) }}</td>
              <td>{{ formatCurrency(item.so_luong * item.don_gia) }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="5" style="text-align:right"><b>Tổng cộng:</b></td>
              <td><b>{{ formatCurrency(selectedOrder.order_items?.reduce((sum, item) => sum + (item.so_luong * item.don_gia), 0) || 0) }}</b></td>
            </tr>
          </tfoot>
        </table>

        <div style="text-align:center; margin-top: 16px;">
          <button class="btn search-btn print-invoice-btn" @click="printOrderDetail">In hóa đơn</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      orders: [],
      searchQuery: '',
      selectedOrder: null, // Đơn hàng đang xem chi tiết
      showDetail: false,
      editingOrderId: null,
      newStatus: null,
      selectedStatusTab: null, // New data property for status filtering
    };
  },
  methods: {
    async fetchOrders() {
      try {
        const params = { search: this.searchQuery };
        if (this.selectedStatusTab !== null) {
          params.status = this.selectedStatusTab; // Add status filter if a tab is selected
        }
        const res = await axios.get('http://localhost:8000/api/orders', {
          params: params,
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        });
        this.orders = res.data;
      } catch (err) {
        console.error('Lỗi khi lấy đơn hàng:', err);
      }
    },
    setFilterStatus(status) {
      this.selectedStatusTab = status;
      this.fetchOrders(); // Re-fetch orders with the new status filter
    },
    // The approveOrder and rejectOrder methods are kept here for completeness,
    // though they are not directly used by the current action buttons in the table.
    async approveOrder(id) {
      await axios.patch(`http://localhost:8000/api/orders/${id}/approve`, {}, {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
      });
      this.fetchOrders();
    },
    async rejectOrder(id) {
      await axios.patch(`http://localhost:8000/api/orders/${id}/reject`, {}, {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
      });
      this.fetchOrders();
    },
    formatDate(datetime) {
      const date = new Date(datetime);
      return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN');
    },
    formatCurrency(amount) {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    },
    getTrangThai(status) {
      switch (status) {
        case 1: return 'Mới đặt';
        case 2: return 'Chờ xác nhận';
        case 3: return 'Đã xác nhận';
        case 4: return 'Đang giao hàng';
        case 5: return 'Đã hủy';
        default: return 'Không rõ';
      }
    },
    // Placeholder function for payment status
    // You need to ensure your backend provides a 'payment_status' or 'is_paid' field in the order data.
    getPaymentStatus(isPaid) {
      if (isPaid === 1 || isPaid === true) {
        return 'Đã thanh toán';
      } else if (isPaid === 0 || isPaid === false) {
        return 'Chưa thanh toán';
      }
      return 'Chưa rõ'; // Default if data is missing or unexpected
    },
    startEditStatus(order) {
      this.editingOrderId = order.id;
      // Set newStatus to the current order's status to pre-fill the select
      this.newStatus = order.trang_thai; 
    },
    async saveStatus(order) {
      try {
        await axios.patch(`http://localhost:8000/api/orders/${order.id}/status`, {
          trang_thai: this.newStatus
        }, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        this.editingOrderId = null;
        this.fetchOrders();
      } catch (err) {
        console.error('Lỗi khi cập nhật trạng thái:', err);
        alert('Cập nhật trạng thái thất bại!');
      }
    },
    // New method to update newStatus and then save
    updateAndSaveStatus(order, statusValue) {
      this.newStatus = statusValue;
      this.saveStatus(order);
    },
    async deleteOrder(orderId) {
      if (confirm('Bạn có chắc muốn ẩn đơn hàng này?')) {
        try {
          await axios.patch(`http://localhost:8000/api/orders/${orderId}/hide`, {}, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
          });
          this.fetchOrders();
        } catch (err) {
          console.error('Lỗi khi ẩn đơn hàng:', err);
          alert('Ẩn đơn hàng thất bại!');
        }
      }
    },
    cancelEdit() {
      this.editingOrderId = null;
      this.newStatus = null;
    },
    openOrderDetail(order) {
      this.selectedOrder = order;
      this.showDetail = true;
    },
    closeOrderDetail() {
      this.showDetail = false;
      this.selectedOrder = null;
    },
    printOrderDetail() {
      window.print();
    },
  },
  mounted() {
    this.fetchOrders();
  },
};
</script>

<style scoped>
/* General Styling */
.order-page {
  padding: 20px;
  font-family: Arial, sans-serif;
  background-color: #f8f9fa; /* Light grey background for the page */
  min-height: 100vh;
}

.title {
  font-size: 24px;
  font-weight: bold;
  margin-bottom: 25px;
  color: #333;
}

/* Card/Box Styling */
.box {
  background: white;
  border-radius: 10px; /* Slightly more rounded corners */
  padding: 25px;
  margin-bottom: 25px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); /* Enhanced shadow */
}

.section-title {
  font-weight: 600;
  margin-bottom: 18px;
  font-size: 18px;
  color: #333;
}

/* Search Bar Styling */
.search-row {
  display: flex;
  gap: 10px;
  max-width: 500px;
}

.input {
  padding: 12px 15px; /* Slightly more padding */
  border: 1px solid #dcdcdc; /* Lighter border color */
  border-radius: 8px; /* Consistent border radius */
  width: 100%;
  box-sizing: border-box; /* Include padding in element's total width */
  font-size: 16px;
  color: #333;
}

.search-btn {
  background-color: #2196f3;
  color: white;
  padding: 12px 20px; /* Consistent padding */
  border-radius: 8px; /* Consistent border radius */
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0; /* Prevent shrinking */
}

.search-btn:hover {
  background-color: #1976d2; /* Darker blue on hover */
}

/* Filter Section Styling */
.grid-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); /* Adaptive columns */
  gap: 15px; /* Increased gap */
  margin-bottom: 20px;
}

.btn-group {
  display: flex;
  gap: 12px;
  width: 100%; /* Adjust width to fit container */
  max-width: 300px; /* Limit max width for button group */
  margin-top: 20px;
}

.search-filter-btn {
  background-color: #28a745; /* Green for search filter */
  color: white;
  padding: 12px 25px;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s ease;
  flex-grow: 1; /* Allow buttons to grow */
}

.search-filter-btn:hover {
  background-color: #218838;
}

.clear-filter-btn {
  background-color: #dc3545; /* Red for clear filter */
  color: white;
  padding: 12px 25px;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s ease;
  flex-grow: 1; /* Allow buttons to grow */
}

.clear-filter-btn:hover {
  background-color: #c82333;
}

/* Status Tabs Styling */
.status-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 25px;
  padding-top: 10px;
  border-top: 1px solid #eee; /* Light line above tabs */
}

.tab-btn {
  padding: 10px 20px;
  border: 1px solid #ccc;
  border-bottom: none;
  border-radius: 8px 8px 0 0; /* Rounded top corners */
  background-color: #f0f0f0;
  cursor: pointer;
  font-weight: 500;
  color: #555;
  transition: all 0.3s ease;
  font-size: 15px;
}

.tab-btn:hover {
  background-color: #e0e0e0;
  color: #333;
}

.tab-btn.active {
  background-color: #007bff; /* Primary blue for active tab */
  color: white;
  border-color: #007bff;
  transform: translateY(1px);
  box-shadow: 0 -3px 8px rgba(0, 0, 0, 0.1);
}

/* Order Table Styling */
.order-table-container {
  padding: 20px; /* Padding inside the card */
  /* .box class already applies background, border-radius, box-shadow */
}

.order-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed; /* Ensures fixed column widths */
}

.order-table th,
.order-table td {
  border: 1px solid #e0e0e0; /* Lighter border for cells */
  padding: 12px 10px; /* More padding in cells */
  text-align: center;
}

.order-table th {
  background-color: #eaf6fd; /* Light blue header */
  font-weight: bold;
  color: #333;
  text-transform: uppercase;
  font-size: 14px;
}

/* Specific width for 'Tình trạng' column */
.order-table th:nth-child(6) {
  width: 180px; /* Fixed width for the status column header */
}

/* Text alignment for specific columns */
.order-table td:nth-child(2), /* Họ tên */
.order-table td:nth-child(3), /* Ngày đặt */
.order-table td:nth-child(4) /* HT Thanh toán */ {
  text-align: left;
}
.order-table td:nth-child(5) { /* Tổng giá */
  text-align: right;
  font-weight: bold;
}

/* Action Buttons within table */
.action-buttons-cell {
  min-width: 280px; /* Ensure enough space for all buttons */
  white-space: nowrap; /* Prevent text wrapping in cell if content is dynamic */
  display: flex; /* Make it a flex container */
  justify-content: center; /* Center buttons horizontally */
  align-items: center; /* Align buttons vertically */
  gap: 10px; /* Added: Consistent spacing between buttons */
}

.action-btn {
  background: none;
  border: none;
  padding: 6px 10px; /* Slightly more padding */
  color: #007bff; /* Blue for primary actions */
  cursor: pointer;
  font-weight: 500;
  font-size: 14px;
  transition: text-decoration 0.2s ease, color 0.2s ease;
}

.action-btn:hover {
  text-decoration: underline;
  color: #0056b3;
}

.action-btn.text-red-600 {
  color: #dc3545; /* Red for destructive actions */
}
.action-btn.text-red-600:hover {
  color: #c82333;
}

/* Styles for the custom status edit popover */
.status-cell {
  position: relative; /* Establish positioning context for absolute popover */
}

.status-edit-popover {
  position: absolute;
  top: 100%; /* Position below the TD */
  left: 50%; /* Center horizontally */
  transform: translateX(-50%); /* Adjust for perfect centering */
  background: white;
  border: 1px solid #ddd;
  border-radius: 5px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); /* Soft shadow */
  padding: 10px;
  z-index: 1001; /* Ensure it's on top of other elements */
  min-width: 180px; /* Adjusted: Increased to prevent text overflow */
  display: flex;
  flex-direction: column;
  gap: 5px; /* Space between buttons */
}

.popover-option-btn {
  background: none;
  border: 1px solid #eee;
  padding: 8px 12px;
  border-radius: 5px;
  cursor: pointer;
  text-align: left;
  font-size: 14px; /* Consistent font size */
  color: #333;
  transition: background-color 0.2s ease, border-color 0.2s ease;
  white-space: nowrap; /* Added: Prevent text wrapping within the button */
}

.popover-option-btn:hover {
  background-color: #f0f0f0;
  border-color: #ccc;
}

.popover-option-btn.active-status {
  background-color: #007bff; /* Highlight active status */
  color: white;
  border-color: #007bff;
}

/* Modal Styling */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.4); /* Slightly darker overlay */
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.order-invoice {
  font-family: 'Arial', sans-serif;
  background: #fff;
  color: #222;
  padding: 40px 50px; /* More padding */
  max-width: 750px; /* Adjust max-width */
  margin: 0 auto;
  border: 1px solid #eee;
  border-radius: 10px; /* Consistent border radius */
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3); /* Stronger shadow for modal */
  position: relative;
}

.invoice-header {
  text-align: center;
  margin-bottom: 30px;
}
.invoice-header h2 {
  margin: 0 0 10px 0;
  font-size: 2.5rem; /* Larger title */
  letter-spacing: 2px;
  color: #333;
  text-transform: uppercase;
}
.invoice-header p {
  font-size: 1.1rem;
  color: #555;
}
.invoice-header p b {
  color: #000;
}

.invoice-info {
  display: flex;
  justify-content: space-between;
  margin-bottom: 30px;
  font-size: 1.05rem;
  line-height: 1.6;
}
.invoice-info b {
  color: #000;
}

.invoice-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 35px;
}
.invoice-table th, .invoice-table td {
  border: 1px solid #e0e0e0;
  padding: 10px 12px;
  text-align: center;
}
.invoice-table th {
  background: #eaf6fd; /* Consistent header color */
  font-weight: bold;
  color: #333;
}
.invoice-table tfoot td {
  font-size: 1.1rem;
  padding-top: 15px;
  padding-bottom: 15px;
}
.invoice-table tfoot b {
  color: #000;
}

.print-invoice-btn {
  background-color: #2196f3;
  color: white;
  padding: 12px 30px;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s ease;
  font-size: 1rem;
}
.print-invoice-btn:hover {
  background-color: #1976d2;
}

@media print {
  body * {
    visibility: hidden;
  }
  #print-area,
  #print-area * {
    visibility: visible;
  }
  #print-area {
    position: absolute;
    left: 0;
    top: 0;
    width: 100vw;
  }
  .order-invoice {
    box-shadow: none;
    border: none;
  }
  /* Hide close button when printing */
  .order-invoice > button[aria-label="Đóng"] {
    display: none;
  }
}
</style>