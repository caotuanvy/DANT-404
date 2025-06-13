<template>
  <div class="order-page">
   
  <h2 class="title">Quản lý/Đơn hàng</h2>

  <!-- Tìm kiếm mã đơn hàng -->
  <div class="box">
    <p class="section-title">Tìm kiếm mã đơn hàng</p>
    <div class="search-row">
      <input
        v-model="searchQuery" inputmode="numeric" pattern="[0-9]*" placeholder="Tìm kiếm" class="input" @input="searchQuery = searchQuery.replace(/\D/g, '')" @keyup.enter="fetchOrders"/>
          <button class="btn search-btn" @click="fetchOrders">🔍</button>    </div>
    </div>

  <!-- Bộ lọc -->
  <div class="box">
    <p class="section-title">Tìm kiếm đơn hàng</p>
    <div class="grid-container">
      <input class="input" placeholder="DD/MM/YYYY" />
      <select class="input">
        <option>Chọn tình trạng</option>
      </select>
      <select class="input">
        <option>Chọn hình thức thanh toán</option>
      </select>
      <select class="input">
        <option>Chọn danh mục</option>
      </select>
      <select class="input">
        <option>Chọn danh mục</option>
      </select>
      <select class="input">
        <option>Chọn danh mục</option>
      </select>
      <input class="input" placeholder="Khoảng giá" />
    </div>
    <div class="btn-group">
      <button class="btn search-btn">Tìm kiếm</button>
      <button class="btn clear-btn">Huỷ lọc</button>
    </div>
  
</div>



    <!-- Bảng danh sách đơn hàng -->
<div class="order-table-container">
  <table class="order-table">
    <thead>
      <tr>
        <th><input type="checkbox" /></th>
        <th>STT</th>
        <th>Mã</th>
        <th>Họ tên</th>
        <th>Ngày đặt</th>
        <th>HT Thanh toán</th>
        <th>Tổng giá</th>
        <th>Tình trạng</th>
        <th>Thời gian</th>
        <th>Thao tác</th>
      </tr>
    </thead>
    <tbody id="order-body">
          <tr v-for="(order, index) in orders" :key="order.id">
            <td class="border px-2">
              <input type="checkbox" />
            </td>
            <td class="border px-2 text-center">{{ index + 1 }}</td>
            <td class="border px-2 text-center">{{ order.id }}</td>
            <td class="border px-2 text-center">{{ order.user?.ho_ten || 'Ẩn danh' }}</td>
            <td class="border px-2 text-center">{{ formatDate(order.ngay_tao) }}</td>
            <td class="border px-2 text-center">
                {{ order.payment_method?.ten_pttt || 'Không rõ' }}</td>
            <td class="border px-2 text-right"> {{ formatCurrency(order.order_items?.reduce((sum, item) => sum + (item.so_luong * item.don_gia), 0) || 0) }}</td>            
            <td class="border px-2 text-center">{{ getTrangThai(order.trang_thai) }}</td>
            <td class="border px-2 text-center"> {{ getTimeAgo(order.ngay_tao) }} </td>
            <td class="border px-2 text-center">
              <button @click="openOrderDetail(order)" class="action-btn">Chi tiết</button>
              <button @click="rejectOrder(order.id)" class="action-btn ml-2">Cập nhật</button>
              <button @click="deleteOrder(order.id)" class="action-btn ml-2 text-red-600">Ẩn</button>
            </td>
          </tr>
        </tbody>
    </table>
  </div>
</div>
<!-- order detail modal -->
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
    <button class="btn search-btn" @click="printOrderDetail">In hóa đơn</button>
    
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
    };
  },
  methods: {
    async fetchOrders() {
      try {
      const res = await axios.get('http://localhost:8000/api/orders', {
        params: { search: this.searchQuery },
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });
      this.orders = res.data;
    } catch (err) {
      console.error('Lỗi khi lấy đơn hàng:', err);
    }
    },
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
        case 1: return 'Chờ xác nhận';
        case 2: return 'Đã xác nhận';
        case 3: return 'Đang giao hàng';
        case 4: return 'Đã hủy';
        default: return 'Không rõ';
      }
    },
    getTimeAgo(datetime) {
      const now = new Date();
      const date = new Date(datetime);
      const diffMs = now - date;
      const diffMins = Math.floor(diffMs / 60000);
      if (diffMins < 60) return `${diffMins} phút trước`;
      const diffHours = Math.floor(diffMins / 60);
      if (diffHours < 24) return `${diffHours} giờ trước`;
      const diffDays = Math.floor(diffHours / 24);
      return `${diffDays} ngày trước`;
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

.action-btn {
  background: none;
  border: none;
  padding: 4px 8px;
  color: #007bff;
  cursor: pointer;
  font-weight: 500;
}

.action-btn:hover {
  text-decoration: underline;
}

.action-btn.text-red-600 {
  color: #e53935;
}

.order-page {
  padding: 20px;
  font-family: Arial, sans-serif;
}
.order-search-container {
  padding: 24px;
  background: #f9f9fb;
  font-family: sans-serif;
}

.title {
  font-size: 20px;
  font-weight: bold;
  margin-bottom: 20px;
}

.box {
  background: white;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.section-title {
  font-weight: 600;
  margin-bottom: 16px;
}

.search-row {
  display: flex;
  gap: 8px;
  max-width: 500px;
}

.input {
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  width: 100%;
}

.grid-container {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  margin-bottom: 16px;
}

.btn-group {
  display: flex;
  gap: 12px;
  width: 250px;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
}

.search-btn {
  background-color: #2196f3;
  color: white;
}

.clear-btn {
  background-color: #f3213a;
  color: white;
}
.order-table-container {
  background-color: #f8f9fa;
}

.order-table {
  width: 100%;
  border-collapse: collapse;
  background-color: #fff;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 0 6px rgba(0, 0, 0, 0.1);
}

.order-table th,
.order-table td {
  border: 1px solid #ddd;
  padding: 10px 8px;
  text-align: center;
}

.order-table th {
  background-color: #f1f1f1;
  font-weight: bold;
}

.order-table td:nth-child(3),
.order-table td:nth-child(4),
.order-table td:nth-child(5),
.order-table td:nth-child(6) {
  text-align: left;
}

.status-new {
  color: #2196f3;
  font-weight: 600;
}

.status-shipping {
  color: #ff9800;
  font-weight: 600;
}

.status-confirmed {
  color: #999;
  font-weight: 600;
}

.actions button {
  background: none;
  border: none;
  color: #007bff;
  margin: 0 5px;
  cursor: pointer;
}

.actions button.edit {
  color: #ff9800;
}

.actions button.delete {
  color: #e53935;
}
/* Thêm vào <style scoped> */
.modal-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal-content {
  background: #fff;
  padding: 32px 24px;
  border-radius: 8px;
  min-width: 400px;
  max-width: 90vw;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 2px 16px rgba(0,0,0,0.2);
}
.order-invoice {
  font-family: 'Arial', sans-serif;
  background: #fff;
  color: #222;
  padding: 32px 40px;
  max-width: 800px;
  margin: 0 auto;
  border: 1px solid #eee;
  border-radius: 8px;
}
.invoice-header {
  text-align: center;
  margin-bottom: 24px;
}
.invoice-header h2 {
  margin: 0 0 8px 0;
  font-size: 2rem;
  letter-spacing: 2px;
}
.invoice-info {
  display: flex;
  justify-content: space-between;
  margin-bottom: 24px;
  font-size: 1rem;
}
.invoice-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 32px;
}
.invoice-table th, .invoice-table td {
  border: 1px solid #ccc;
  padding: 8px 10px;
  text-align: center;
}
.invoice-table th {
  background: #f5f5f5;
  font-weight: bold;
}
@media print {
  body * { visibility: hidden; }
  #print-area, #print-area * { visibility: visible; }
  #print-area { position: absolute; left: 0; top: 0; width: 100vw; }
  .order-invoice { box-shadow: none; border: none; }
}
</style>