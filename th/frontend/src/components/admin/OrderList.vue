<template>
  <div class="order-page">
   
  <h2 class="title">Quản lý/Đơn hàng</h2>

  <!-- Tìm kiếm mã đơn hàng -->
  <div class="box">
    <p class="section-title">Tìm kiếm mã đơn hàng</p>
    <div class="search-row">
      <input v-model="searchQuery" placeholder="Tìm kiếm" class="input" @keyup.enter="fetchOrders" />
      <button class="btn search-btn" @click="fetchOrders">🔍</button>    </div>
  </div>

  <!-- Bộ lọc -->
  <div class="box">
    <p class="section-title">Tìm kiếm đơn hàng</p>
    <div class="grid-container">
      <input class="input" placeholder="DD/MM/YYYY to DD/MM/YYYY" />
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
            <td class="border px-2 text-right">{{ formatCurrency(order.don_gia || 0) }}</td>
            <td class="border px-2 text-center">{{ getTrangThai(order.trang_thai) }}</td>
            <td class="border px-2 text-center">
              <button @click="approveOrder(order.id)" class="action-btn">Chi tiết</button>
              <button @click="rejectOrder(order.id)" class="action-btn ml-2">Cập nhật</button>
              <button @click="deleteOrder(order.id)" class="action-btn ml-2 text-red-600">Ẩn</button>
            </td>
          </tr>
        </tbody>
    </table>
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
      if (status === 'approved') return 'Đã xác nhận';
      if (status === 'shipping') return 'Đang giao hàng';
      if (status === 'rejected') return 'Đã hủy';
      return 'Mới đặt';
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
</style>