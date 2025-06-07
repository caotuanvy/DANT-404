<template>
    <div>
      <h2>Quản lý đơn hàng</h2>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Người đặt</th>
            <th>Ngày đặt</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.order_id">
            <td>{{ order.order_id }}</td>
            <td>{{ order.user?.username || 'Ẩn danh' }}</td>
            <td>{{ formatDate(order.order_date) }}</td>
            <td>{{ formatCurrency(order.total) }}</td>
            <td>
              <span v-if="order.status === 'approved'">Đã duyệt</span>
              <span v-else-if="order.status === 'rejected'">Đã từ chối</span>
              <span v-else>Chờ duyệt</span>
            </td>
            <td>
              <button
                @click="approveOrder(order.order_id)"
                :disabled="order.status === 'approved'"
              >
                ✅ Duyệt
              </button>
              <button
                @click="rejectOrder(order.order_id)"
                :disabled="order.status === 'rejected'"
              >
                ❌ Từ chối
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    data() {
      return {
        orders: [],
      };
    },
    methods: {
      async fetchOrders() {
        try {
          const res = await axios.get('http://localhost:8000/api/orders', {
            headers: {
              Authorization: `Bearer ${localStorage.getItem('token')}`,
            },
          });
          this.orders = res.data;
        } catch (err) {
          console.error('Lỗi khi lấy đơn hàng:', err);
          alert('Không thể tải đơn hàng.');
        }
      },
      async approveOrder(id) {
        try {
          await axios.patch(`http://localhost:8000/api/orders/${id}/approve`, {}, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem('token')}`,
            },
          });
          this.fetchOrders();
        } catch (err) {
          console.error('Lỗi khi duyệt:', err);
          alert('Không thể duyệt đơn hàng.');
        }
      },
      async rejectOrder(id) {
        try {
          await axios.patch(`http://localhost:8000/api/orders/${id}/reject`, {}, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem('token')}`,
            },
          });
          this.fetchOrders();
        } catch (err) {
          console.error('Lỗi khi từ chối:', err);
          alert('Không thể từ chối đơn hàng.');
        }
      },
      formatDate(datetime) {
        const date = new Date(datetime);
        return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN');
      },
      formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
          style: 'currency',
          currency: 'VND',
        }).format(amount);
      },
    },
    mounted() {
      this.fetchOrders();
    },
  };
  </script>
  
  <style scoped>
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
  }
  th,
  td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: center;
  }
  button {
    margin: 0 4px;
    padding: 5px 10px;
    cursor: pointer;
  }
  </style>
  