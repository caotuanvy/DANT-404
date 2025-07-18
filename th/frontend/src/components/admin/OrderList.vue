
<template>
  <div class="order-page">
    <div class="page-header">
      <h1 class="page-title">Quản lý đơn hàng</h1>
      <div class="header-actions">
        <button class="btn btn-secondary">Xuất Excel</button>
        <button class="btn btn-primary" @click="handleRefresh">Làm mới</button>      </div>
    </div>

    <div class="box filter-container">
      <div class="main-search-row">
        <div class="search-input-wrapper">
          <i class="icon-search"></i>
          <input
            v-model="localFilters.searchQuery"
            placeholder="Tìm kiếm mã đơn hàng, tên khách hàng..."
            class="main-search-input"
            @keyup.enter="applyFilters"
          />
        </div>
        <a href="#" class="advanced-search-toggle" @click.prevent="isAdvancedSearchVisible = !isAdvancedSearchVisible">
          Tìm kiếm nâng cao
          <i :class="['icon-arrow', { 'expanded': isAdvancedSearchVisible }]"></i>
        </a>
      </div>

      <div v-show="isAdvancedSearchVisible" class="advanced-filters">
        <div class="filter-grid">
          <div class="filter-item">
            <label>Ngày đặt</label>
            <input type="date" v-model="localFilters.date" class="input-filter" />
          </div>
          <div class="filter-item">
            <label>Hình thức thanh toán</label>
            <select v-model="localFilters.paymentMethod" class="input-filter">
              <option value="">Chọn hình thức</option>
              <option v-for="method in paymentMethods" :key="method.id" :value="method.id">
                {{ method.ten_pttt }}
              </option>
            </select>
          </div>
          
          <div class="filter-item">
            <label>Khoảng giá</label>
            <input v-model="localFilters.priceRange" class="input-filter" placeholder="$ 0 - 1,000,000" />
          </div>
        </div>
        <div class="filter-actions">
          <button class="btn btn-secondary" @click="clearFilters">Hủy lọc</button>
          <button class="btn btn-primary" @click="applyFilters">Tìm kiếm</button>
        </div>
      </div>
    </div>

    <div class="status-tabs">
       <button :class="{'tab-btn': true, 'active': selectedStatusTab === null}" @click="setFilterStatus(null)">
        Tất cả <span class="tab-count">{{ countAll }}</span>
      </button>
      <button :class="{'tab-btn': true, 'active': selectedStatusTab === 1}" @click="setFilterStatus(1)">
        Mới đặt <span class="tab-count">{{ countNew }}</span>
      </button>
      <button :class="{'tab-btn': true, 'active': selectedStatusTab === 2}" @click="setFilterStatus(2)">
        Chờ xác nhận <span class="tab-count">{{ countPending }}</span>
      </button>
      <button :class="{'tab-btn': true, 'active': selectedStatusTab === 3}" @click="setFilterStatus(3)">
        Đã xác nhận <span class="tab-count">{{ countConfirmed }}</span>
      </button>
      <button :class="{'tab-btn': true, 'active': selectedStatusTab === 4}" @click="setFilterStatus(4)">
        Đang giao <span class="tab-count">{{ countShipping }}</span>
      </button>
      <button :class="{'tab-btn': true, 'active': selectedStatusTab === 5}" @click="setFilterStatus(5)">
        Hoàn thành <span class="tab-count">{{ countCompleted }}</span>
      </button>
      <button :class="{'tab-btn': true, 'active': selectedStatusTab === 6}" @click="setFilterStatus(6)">
        Đã hủy <span class="tab-count">{{ countCancelled }}</span>
      </button>
    </div>

    <div class="box table-container">
      <table class="order-table">
        <thead>
          <tr>
            <th>MÃ</th>
            <th>KHÁCH HÀNG</th>
            <th>NGÀY ĐẶT</th>
            <th>HÌNH THỨC</th>
            <th>TỔNG GIÁ</th>
            <th>TRẠNG THÁI</th>
            <th>TÌNH TRẠNG</th>
            <th>THAO TÁC</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="order in filteredOrders" :key="order.id">
            <tr>
              <td>#{{ order.id }}</td>
              <td>{{ order.user?.ho_ten || 'Ẩn danh' }}</td>
              <td>{{ formatDate(order.ngay_dat) }}</td>
              <td>{{ order.payment_method?.ten_pttt || 'Không rõ' }}</td>
              <td class="price">
                {{ formatCurrency(
                  typeof order.total_price === 'number' && !isNaN(order.total_price)
                    ? order.total_price
                    : (order.order_items?.reduce((sum, item) => sum + (parseFloat(item.so_luong) * parseFloat(item.don_gia)), 0) || 0)
                ) }}
              </td>
              <td>
                <span :class="['status-pill', getStatusClass(order.trang_thai_don_hang)]">
                  {{ getTrangThai(order.trang_thai_don_hang) }}
                </span>
              </td>
              <td>
                <span :class="['status-pill', getPaymentStatusClass(order.is_paid)]">
                  {{ getPaymentStatus(order.is_paid, order) }}
                </span>
              </td>
              <td class="action-cell">
                <button class="action-menu-btn" @click.stop="toggleActionMenu(order.id)">•••</button>
                <div v-if="activeActionMenu === order.id" class="action-menu">
                  <button class="action-menu-item" @click.stop="handleUpdateClick(order)">
                    Cập nhật trạng thái
                  </button>
                  <button class="action-menu-item" @click.stop="handleExpandClick(order.id)">
                    {{ expandedOrderId === order.id ? 'Ẩn chi tiết' : 'Xem chi tiết' }}
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="expandedOrderId === order.id" class="expanded-details-row">
              <td :colspan="8">
                <div class="details-container">
                  <div class="user-info-section">
                    <h4 class="details-header">Thông tin khách hàng</h4>
                    <div class="user-profile">
                      <img :src="getUserAvatarUrl(order.user)" alt="Avatar" class="user-avatar">
                      <div class="user-details">
                        <strong>{{ order.user?.ho_ten }}</strong>
                        <p><i class="icon-phone"></i>{{ order.user?.sdt || 'Chưa có SĐT' }}</p>
                        <p><i class="icon-address"></i>{{ order.diachi?.dia_chi || 'Chưa có địa chỉ' }}</p>
                      </div>
                    </div>
                  </div>
                  <div class="product-list-section">
                    <h4 class="details-header">Sản phẩm đã đặt</h4>
                    <table class="product-details-table">
                      <thead>
                        <tr>
                          <th>Sản phẩm</th>
                          <th>Số lượng</th>
                          <th>Thành tiền</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="item in order.order_items" :key="item.id">
                          <td>
                            {{ item.bien_the?.san_pham?.ten_san_pham || 'Sản phẩm không rõ' }}
                            <small v-if="item.bien_the" class="product-variant">
                              ({{ item.bien_the.ten_bien_the }})
                            </small>
                          </td>
                          <td>x {{ item.so_luong }}</td>
                          <td class="price">{{ formatCurrency(parseFloat(item.so_luong) * parseFloat(item.don_gia)) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
      <div class="pagination-container">
        <span class="pagination-info">
          Hiển thị {{ filteredOrders.length }} trong {{ total }} kết quả
        </span>
        <div class="pagination-controls">
          <button
            class="btn-page"
            :disabled="currentPage === 1"
            @click="fetchOrders(currentPage - 1)"
          >
            &lt; Trước
          </button>
          <button
            v-for="page in lastPage"
            :key="page"
            class="btn-page"
            :class="{ active: page === currentPage }"
            @click="fetchOrders(page)"
          >
            {{ page }}
          </button>
          <button
            class="btn-page"
            :disabled="currentPage === lastPage"
            @click="fetchOrders(currentPage + 1)"
          >
            Sau &gt;
          </button>
        </div>
      </div>
    </div>

    <div v-if="isStatusModalVisible" class="modal-overlay" @click.self="closeStatusModal">
      <div class="status-modal-container">
        <h3 class="status-modal-title">Cập nhật trạng thái đơn hàng #{{ orderToUpdate.id }}</h3>
        <div class="status-stepper">
          <div
            v-for="step in statusSteps"
            :key="step.id"
            :class="['step-item', getStepClass(step)]"
            @click="selectNextStatus(step)"
          >
            <div class="step-icon-wrapper">
              <div class="step-line"></div>
              <div class="step-icon">
                <i :class="step.icon"></i>
              </div>
            </div>
            <div class="step-label">
              <strong>{{ step.title }}</strong>
            </div>
          </div>
        </div>
        <div class="modal-actions">
          <button
  v-if="orderToUpdate && Number(orderToUpdate.is_paid) === 0"
  class="btn btn-primary"
  @click="confirmPayment(orderToUpdate)"
>
  Xác nhận thanh toán
</button>
          <button class="btn btn-secondary" @click="closeStatusModal">Đóng</button>
  <button
    v-if="pendingStatusId"
    class="btn btn-success"
    @click="commitStatusUpdate"
  >
    Lưu thay đổi
  </button> 
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
           filters: {
                searchQuery: '',
                date: '',
                paymentMethod: '',
                category: '',
                priceRange: '',
                status: null
            },
            allOrders: [],
            paymentMethods: [],
            selectedStatusTab: null,
            isAdvancedSearchVisible: false,
            localFilters: {
                searchQuery: '', date: '', paymentMethod: '', category: '', priceRange: ''
            },
            appliedFilters: {
                searchQuery: '', date: '', paymentMethod: '', category: '', priceRange: ''
            },
            currentPage: 1,
            lastPage: 1,
            total: 0,
            // <<< BỎ: Đã xóa đối tượng pagination
            expandedOrderId: null,
            isStatusModalVisible: false,
            orderToUpdate: null,
            activeActionMenu: null,
            pendingStatusId: null,
            statusSteps: [
                { id: 1, title: 'Đơn Hàng Đã Đặt', icon: 'icon-receipt' },
                { id: 2, title: 'Đã Xác Nhận', icon: 'icon-dollar' },
                { id: 3, title: 'Chờ Lấy Hàng', icon: 'icon-truck' },
                { id: 4, title: 'Đang Giao', icon: 'icon-shipping' },
                { id: 5, title: 'Hoàn Thành', icon: 'icon-star' },
            ],
        };
    },
    computed: {
        // ... các computed properties của bạn không thay đổi
        countAll() { return this.allOrders.length; },
        countNew() { return this.allOrders.filter(o => o.trang_thai_don_hang === 1).length; },
        countPending() { return this.allOrders.filter(o => o.trang_thai_don_hang === 2).length; },
        countConfirmed() { return this.allOrders.filter(o => o.trang_thai_don_hang === 3).length; },
        countShipping() { return this.allOrders.filter(o => o.trang_thai_don_hang === 4).length; },
        countCompleted() { return this.allOrders.filter(o => o.trang_thai_don_hang === 5).length; },
        countCancelled() { return this.allOrders.filter(o => o.trang_thai_don_hang === 6).length; },
        filteredOrders() {
            let filtered = [...this.allOrders];
            if (this.selectedStatusTab !== null) {
                filtered = filtered.filter(order => order.trang_thai_don_hang === this.selectedStatusTab);
            }
            const query = this.appliedFilters.searchQuery.toLowerCase().trim();
            if (query) {
                filtered = filtered.filter(order => {
                    const customerName = order.user?.ho_ten?.toLowerCase() || '';
                    const paymentMethodName = order.payment_method?.ten_pttt?.toLowerCase() || '';
                    const orderStatusText = this.getTrangThai(order.trang_thai_don_hang).toLowerCase();
                    const paymentStatusText = this.getPaymentStatus(order.is_paid, order).toLowerCase();
                    const hasMatchingProduct = order.order_items.some(item =>
                        item.bien_the?.san_pham?.ten_san_pham?.toLowerCase().includes(query)
                    );
                    return (
                        order.id.toString().includes(query) ||
                        customerName.includes(query) ||
                        paymentMethodName.includes(query) ||
                        orderStatusText.includes(query) ||
                        paymentStatusText.includes(query) ||
                        hasMatchingProduct
                    );
                });
            }
            if (this.appliedFilters.date) {
                filtered = filtered.filter(order => order.ngay_dat.startsWith(this.appliedFilters.date));
            }
            if (this.appliedFilters.paymentMethod) {
                filtered = filtered.filter(order => order.phuong_thuc_thanh_toan_id == this.appliedFilters.paymentMethod);
            }
            const priceRange = this.appliedFilters.priceRange.trim();
            if (priceRange) {
                const cleanedRange = priceRange.replace(/[^0-9-]/g, '');
                const parts = cleanedRange.split('-');
                const minPriceString = parts[0] || '0';
                const maxPriceString = parts[1] || '';
                const minPrice = parseInt(minPriceString, 10);
                const maxPrice = maxPriceString ? parseInt(maxPriceString, 10) : Infinity;
                if (!isNaN(minPrice)) {
                    filtered = filtered.filter(order => {
                        const totalPrice = order.total_price;
                        const isAfterMin = totalPrice >= minPrice;
                        const isBeforeMax = maxPrice === Infinity ? true : totalPrice <= maxPrice;
                        return isAfterMin && isBeforeMax;
                    });
                }
            }
            return filtered;
        }
    },
    methods: {
        // <<< THAY ĐỔI: fetchOrders không còn tham số page và lấy tất cả đơn hàng
        async fetchOrders(page = 1) {
            try {
                const res = await axios.get(`http://localhost:8000/api/orders?page=${page}&per_page=10`, {
                    headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
                });
                this.allOrders = res.data.data;
                this.currentPage = res.data.current_page;
                this.lastPage = res.data.last_page;
                this.total = res.data.total;
            } catch (err) {
                console.error('Lỗi khi lấy đơn hàng:', err);
            }
        },
        
        async fetchPaymentMethods() {
            try {
                const res = await axios.get('http://localhost:8000/api/payment-methods', {
                    headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
                });
                this.paymentMethods = res.data;
            } catch (err) {
                console.error('Lỗi khi lấy hình thức thanh toán:', err);
            }
        },
        async confirmPayment(order) {
            try {
                await axios.patch(`http://localhost:8000/api/orders/${order.id}/payment`, 
                    { is_paid: 1 },
                    { headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } }
                );
                // Sau khi xác nhận thanh toán, tải lại đơn hàng
                this.fetchOrders(this.currentPage);
                this.closeStatusModal();
            } catch (err) {
                alert('Xác nhận thanh toán thất bại!');
            }
        },
        applyFilters() {
            this.appliedFilters = { ...this.localFilters };
            this.selectedStatusTab = null;
        },
        clearFilters() {
            this.localFilters = { searchQuery: '', date: '', paymentMethod: '', category: '', priceRange: '' };
            this.appliedFilters = { searchQuery: '', date: '', paymentMethod: '', category: '', priceRange: '' };
            this.fetchOrders();
        },
        setFilterStatus(status) {
            this.selectedStatusTab = status;
            this.appliedFilters = { searchQuery: '', date: '', paymentMethod: '', category: '', priceRange: '' };
            this.localFilters = { searchQuery: '', date: '', paymentMethod: '', category: '', priceRange: '' };
        },
        toggleActionMenu(orderId) { this.activeActionMenu = this.activeActionMenu === orderId ? null : orderId; },
        closeActionMenu() { this.activeActionMenu = null; },
        handleUpdateClick(order) { this.openStatusModal(order); this.closeActionMenu(); },
        handleExpandClick(orderId) { this.toggleExpand(orderId); this.closeActionMenu(); },
        toggleExpand(orderId) { this.expandedOrderId = this.expandedOrderId === orderId ? null : orderId; },
        getUserAvatarUrl(user) {
            if (user && user.avatar) return user.avatar;
            const name = user?.ho_ten || 'Khách Lạ';
            const encodedName = encodeURIComponent(name);
            return `https://ui-avatars.com/api/?name=${encodedName}&background=cfe2ff&color=052c65&font-size=0.5`;
        },
        formatDate(datetime) { if (!datetime) return ''; const date = new Date(datetime); return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')} ${date.toLocaleTimeString('vi-VN')}`; },
        formatCurrency(amount) {
          const value = Number(amount);
          if (isNaN(value)) return '0 ₫';
          return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
        },
        getTrangThai(status) { const statuses = { 1: 'Mới đặt', 2: 'Chờ xác nhận', 3: 'Đã xác nhận', 4: 'Đang giao', 5: 'Hoàn thành', 6: 'Đã hủy' }; return statuses[status] || 'Không rõ'; },
        getStatusClass(status) { const classes = { 1: 'status-new', 2: 'status-pending', 3: 'status-confirmed', 4: 'status-shipping', 5: 'status-completed', 6: 'status-cancelled' }; return classes[status] || 'status-default'; },


      getPaymentStatus(isPaid, order) {
          // Nếu đã thanh toán thì luôn trả về "Đã thanh toán"
          if (Number(isPaid) === 1) return 'Đã thanh toán';
          // Nếu chưa thanh toán, kiểm tra trạng thái
          if ([1,2,3,4].includes(order.trang_thai_don_hang)) return 'Chưa thanh toán';
          if (order.trang_thai_don_hang === 5) return 'Chưa thanh toán';
          if (order.trang_thai_don_hang === 6) return 'Đã hủy';
          return 'Chưa rõ';
      },
        getPaymentStatusClass(isPaid) { if (isPaid === 1 || isPaid === true) return 'status-paid'; if (isPaid === 0 || isPaid === false) return 'status-unpaid'; return 'status-default'; },
        openStatusModal(order) {
            this.orderToUpdate = order;
            this.pendingStatusId = null;
            this.isStatusModalVisible = true;
        },
        closeStatusModal() {
            this.isStatusModalVisible = false;
            this.orderToUpdate = null;
            this.pendingStatusId = null;
        },
        getStepClass(step) {
            if (!this.orderToUpdate) return 'pending';
            const currentStatus = this.orderToUpdate.trang_thai_don_hang;
            if (step.id === this.pendingStatusId) return 'pending-selection';
            if (step.id < currentStatus) return 'completed';
            if (step.id === currentStatus) return 'active';
            if (step.id === currentStatus + 1) return 'next-step';
            return 'pending';
        },
        selectNextStatus(step) {
            if (!this.orderToUpdate) return;
            const currentStatus = this.orderToUpdate.trang_thai_don_hang;
            if (step.id === currentStatus + 1) {
                this.pendingStatusId = step.id;
            }
        },
        commitStatusUpdate() {
            if (!this.pendingStatusId) return;
            this.updateOrderStatus(this.orderToUpdate, this.pendingStatusId);
        },
        async updateOrderStatus(order, newStatus) {
          try {
              await axios.patch(`http://localhost:8000/api/orders/${order.id}/status`,
                  { trang_thai_don_hang: String(newStatus) },
                  { headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } }
              );
              // Nếu chuyển sang trạng thái 5 và chưa thanh toán, gọi luôn xác nhận thanh toán
              if (newStatus === 5 && Number(order.is_paid) === 0) {
                  await this.confirmPayment(order);
              } else {
                  this.closeStatusModal();
                  this.fetchOrders();
              }
          } catch (err) {
              console.error('Lỗi khi cập nhật trạng thái:', err);
              alert('Cập nhật trạng thái thất bại!');
          }
      },
         handleRefresh() {
            // Reset tất cả bộ lọc và trạng thái về mặc định
            this.localFilters = { searchQuery: '', date: '', paymentMethod: '', category: '', priceRange: '' };
            this.appliedFilters = { searchQuery: '', date: '', paymentMethod: '', category: '', priceRange: '' };
            this.selectedStatusTab = null;
            this.currentPage = 1;
            this.expandedOrderId = null;
            this.isAdvancedSearchVisible = false;
            this.activeActionMenu = null;
            this.isStatusModalVisible = false;
            this.orderToUpdate = null;
            this.pendingStatusId = null;
            // Gọi lại API để lấy dữ liệu mới nhất
            this.fetchOrders(1);
            this.fetchPaymentMethods();
        },
    },
    mounted() {
        this.fetchOrders(1);
        this.fetchPaymentMethods();
        window.addEventListener('click', this.closeActionMenu);
    },
    beforeUnmount() {
        window.removeEventListener('click', this.closeActionMenu);
    },
};
</script>
<style scoped>
/* Define color variables */
.order-page {
  --primary-color: #0d6efd;
  --secondary-color: #6c757d;
  --success-color: #0d6efd;
  --warning-color: #ffc107;
  --danger-color: #dc3545;
  --light-color: #f8f9fa;
  --border-color: #dee2e6;
  --text-color: #212529;
  --text-muted: #6c757d;
  --purple-color: #6f42c1;
  --indigo-color: #6610f2;
}

/* General Styling */
.order-page {
  padding: 24px 32px;
  font-family: 'Public Sans', sans-serif;
  background-color: #f5f5f9;
}

.box {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  margin-bottom: 24px;
}

/* Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.page-title {
  font-size: 24px;
  font-weight: 600;
  color: var(--text-color);
}

.header-actions {
  display: flex;
  gap: 12px;
}

.btn {
  padding: 8px 16px;
  border: 1px solid transparent;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-primary {
  background-color: var(--primary-color);
  color: white;
}
.btn-primary:hover {
  background-color: #0b5ed7;
}

.btn-secondary {
  background-color: white;
  color: var(--secondary-color);
  border-color: var(--border-color);
}
.btn-secondary:hover {
  background-color: var(--light-color);
}


/* Search & Filter Box */
.main-search-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.search-input-wrapper {
  position: relative;
  flex-grow: 1;
  max-width: 400px;
}

.icon-search {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
  width: 16px;
  height: 16px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236c757d' class='bi bi-search' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E");
}

.main-search-input {
  width: 100%;
  padding: 10px 12px 10px 36px;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  font-size: 14px;
}

.advanced-search-toggle {
  color: var(--primary-color);
  text-decoration: none;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 4px;
}

.icon-arrow {
  width: 12px;
  height: 12px;
  transition: transform 0.3s ease;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d6efd' class='bi bi-chevron-down' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
}
.icon-arrow.expanded {
  transform: rotate(180deg);
}

.advanced-filters {
  border-top: 1px solid var(--border-color);
  margin-top: 16px;
  padding-top: 20px;
}

.filter-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}
.filter-item label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 6px;
}
.input-filter {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid var(--border-color);
    border-radius: 6px;
}

.filter-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 20px;
}


/* Status Tabs */
.status-tabs {
  display: flex;
  gap: 8px;
  border-bottom: 1px solid var(--border-color);
  margin-bottom: 24px;
  flex-wrap: wrap;
}
.tab-btn {
  padding: 10px 16px;
  border: none;
  border-bottom: 2px solid transparent;
  background: none;
  cursor: pointer;
  color: var(--text-muted);
  font-weight: 500;
  display: flex;
  gap: 8px;
  align-items: center;
}
.tab-btn.active {
  color: var(--primary-color);
  border-bottom-color: var(--primary-color);
}
.tab-count {
  background-color: #e9ecef;
  color: var(--text-muted);
  padding: 2px 8px;
  border-radius: 10px;
  font-size: 12px;
}
.tab-btn.active .tab-count {
  background-color: #cfe2ff;
  color: var(--primary-color);
}

/* Order Table */
.order-table {
  width: 100%;
  border-collapse: collapse;
}
.order-table th, .order-table td {
  padding: 12px 16px;
  text-align: left;
  vertical-align: middle;
  border-bottom: 1px solid var(--border-color);
}
.order-table th {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  min-width: 120px;
  width: 10%;
}
.order-table td {
  font-size: 14px;
  color: var(--text-color);
}
.order-table td.price {
  font-weight: 600;
}

/* Status Pills */
.status-pill {
  padding: 4px 12px;
  border-radius: 16px;
  font-size: 12px;
  font-weight: 500;
  display: inline-block;
}
.status-pill.status-pending { background-color: #fff3cd; color: #664d03; }
.status-pill.status-completed { background-color: #d1e7dd; color: #0f5132; }
.status-pill.status-shipping { background-color: #e2d9f3; color: var(--purple-color); }
.status-pill.status-cancelled { background-color: #f8d7da; color: #58151c; }
.status-pill.status-confirmed { background-color: #e0cffc; color: var(--indigo-color); }
.status-pill.status-new { background-color: #cfe2ff; color: #052c65; }
.status-pill.status-paid { background-color: #d1e7dd; color: #0f5132; }
.status-pill.status-unpaid { background-color: #fff3cd; color: #664d03; }
.status-pill.status-default { background-color: #e9ecef; color: #495057; }

/* Action Menu */
.action-cell {
  position: relative;
  text-align: center;
  width: 60px;
}
.action-menu-btn {
  background: none;
  border: none;
  font-size: 24px;
  font-weight: bold;
  color: var(--text-muted);
  cursor: pointer;
  padding: 0 12px;
  line-height: 1;
  border-radius: 50%;
  width: 36px;
  height: 36px;
}
.action-menu-btn:hover {
  background-color: var(--light-color);
}
.action-menu {
  position: absolute;
  top: 90%;
  right: 16px;
  z-index: 10;
  background-color: white;
  min-width: 180px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  border: 1px solid var(--border-color);
  padding: 8px 0;
  display: flex;
  flex-direction: column;
}
.action-menu-item {
  background: none;
  border: none;
  text-align: left;
  padding: 10px 16px;
  width: 100%;
  cursor: pointer;
  font-size: 14px;
  color: var(--text-color);
}
.action-menu-item:hover {
  background-color: var(--light-color);
}

/* Expanded Details Row */
.expanded-details-row > td {
    background-color: #f8f9fa;
    padding: 20px 24px;
    border-bottom: 1px solid #ddd;
}
.details-container {
    display: flex;
    gap: 24px;
    align-items: flex-start;
}
.user-info-section {
    flex: 0 0 350px;
}
.product-list-section {
    flex: 1;
}
.details-header {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding-bottom: 0;
    border-bottom: none;
}
.user-profile {
    background-color: #fff;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.user-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    flex-shrink: 0;
}
.user-details {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.user-details strong {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-color);
}
.user-details p {
    margin: 0;
    color: var(--secondary-color);
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.user-details p i {
    width: 14px;
    height: 14px;
    opacity: 0.8;
}
.icon-phone { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236c757d' class='bi bi-telephone' viewBox='0 0 16 16'%3E%3Cpath d='M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.568 17.568 0 0 0 4.168 6.608 17.569 17.569 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.678.678 0 0 0-.58-.122l-2.19.547a1.745 1.745 0 0 1-1.657-.459L5.482 8.062a1.745 1.745 0 0 1-.46-1.657l.548-2.19a.678.678 0 0 0-.122-.58L3.654 1.328zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.678.678 0 0 0 .178.643l2.457 2.457a.678.678 0 0 0 .644.178l2.189-.547a1.745 1.745 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.612l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.634 18.634 0 0 1-7.01-4.42 18.634 18.634 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877L1.885.513z'/%3E%3C/svg%3E"); }
.icon-address { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236c757d' class='bi bi-geo-alt' viewBox='0 0 16 16'%3E%3Cpath d='M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A31.493 31.493 0 0 1 8 14.58a31.481 31.481 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94zM8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10z'/%3E%3Cpath d='M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6z'/%3E%3C/svg%3E"); }
.product-details-table {
    width: 100%;
    border-collapse: collapse;
    background-color: #fff;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.product-details-table th, .product-details-table td {
    text-align: left;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-color);
    font-size: 14px;
}
.product-details-table tbody tr:last-child td {
    border-bottom: none;
}
.product-details-table th {
  background-color: #fdfdfd;
  color: var(--text-muted);
  font-weight: 600;
}
.product-variant {
  display: block;
  color: var(--text-muted);
  font-size: 12px;
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 16px;
    margin-top: 16px;
}
.pagination-info {
    font-size: 14px;
    color: var(--text-muted);
}
.pagination-controls {
    display: flex;
    gap: 4px;
}
.btn-page {
    border: 1px solid var(--border-color);
    background-color: white;
    color: var(--text-muted);
    border-radius: 6px;
    padding: 6px 12px;
    cursor: pointer;
}
.btn-page.active {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

/* Modal Overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

/* Status Update Modal */
.status-modal-container {
  background: white;
  padding: 24px 32px;
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}
.status-modal-title {
  text-align: center;
  margin-top: 0;
  margin-bottom: 32px;
  color: var(--text-color);
}
.status-stepper {
  display: flex;
  justify-content: space-between;
}
.step-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  position: relative;
  flex: 1;
}
.step-icon-wrapper {
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  margin-bottom: 8px;
}
.step-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  border: 2px solid #ccc;
  background-color: #fff;
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2;
  transition: all 0.3s ease;
}
.step-line {
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 3px;
  background-color: #ccc;
  z-index: 1;
  transform: translateY(-50%);
}
.step-item:first-child .step-line { left: 50%; }
.step-item:last-child .step-line { right: 50%; }
.step-label strong {
  font-size: 14px;
  color: var(--text-muted);
}
.step-label span {
  font-size: 12px;
  color: #aaa;
  display: block;
}
.step-item.completed .step-icon {
  background-color: var(--success-color);
  border-color: var(--success-color);
  color: white;
}
.step-item.completed .step-line {
  background-color: var(--success-color);
}
.step-item.completed .step-label strong {
  color: var(--text-color);
}
.step-item.active .step-icon {
  border-color: var(--success-color);
  background-color: var(--success-color);
  color: white;
}
.step-item.active .step-label strong {
  color: var(--success-color);
  font-weight: 700;
}
.step-item.next-step .step-icon {
  border-color: var(--primary-color);
  cursor: pointer;
}
.step-item.next-step:hover .step-icon {
  background-color: #cfe2ff;
}
.step-item.next-step .step-label strong {
  color: var(--primary-color);
}
.step-icon i {
  width: 24px;
  height: 24px;
  background-color: currentColor;
  display: inline-block;
  -webkit-mask-size: contain;
  mask-size: contain;
  -webkit-mask-repeat: no-repeat;
  mask-repeat: no-repeat;
  -webkit-mask-position: center;
  mask-position: center;
}

.btn-success {
  background-color: var(--success-color);
  color: white;
}
.btn-success:hover {
  background-color: #0b5ed7;
}

/* Kiểu cho trạng thái đang được chọn nhưng chưa lưu */
.step-item.pending-selection .step-icon {
  border-color: var(--warning-color);
  background-color: #fffbe6;
  box-shadow: 0 0 10px rgba(255, 193, 7, 0.5);
}
.step-item.pending-selection .step-label strong {
    color: #664d03;
}
.icon-receipt { -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-receipt' viewBox='0 0 16 16'%3E%3Cpath d='M1.92.506a.5.5 0 0 1 .434.14L3 1.293l.646-.647a.5.5 0 0 1 .708 0L5 1.293l.646-.647a.5.5 0 0 1 .708 0L7 1.293l.646-.647a.5.5 0 0 1 .708 0L9 1.293l.646-.647a.5.5 0 0 1 .708 0L11 1.293l.646-.647a.5.5 0 0 1 .708 0L13 1.293l.646-.647a.5.5 0 0 1 .434-.14L15 1.293v1.293l-1.146 1.147a.5.5 0 0 1-.708 0L12 2.586l-.646.647a.5.5 0 0 1-.708 0L10 2.586l-.646.647a.5.5 0 0 1-.708 0L8 2.586l-.646.647a.5.5 0 0 1-.708 0L6 2.586l-.646.647a.5.5 0 0 1-.708 0L4 2.586l-.646.647a.5.5 0 0 1-.708 0L1.92 3.707V.506zm-.5 0v15a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5v-15a.5.5 0 0 0-.5-.5h-13a.5.5 0 0 0-.5.5z'/%3E%3C/svg%3E"); }
.icon-dollar { -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-cash-stack' viewBox='0 0 16 16'%3E%3Cpath d='M1 3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1H1zm7 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4z'/%3E%3Cpath d='M0 5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V5zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V7a2 2 0 0 1-2-2H3z'/%3E%3C/svg%3E"); }
.icon-truck { -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-truck' viewBox='0 0 16 16'%3E%3Cpath d='M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-4 0a1.5 1.5 0 0 1-1.5-1.5V3.5zM1 4v6.5A.5.5 0 0 0 1.5 11h1.55a2.5 2.5 0 0 1 4.9 0H10a.5.5 0 0 0 .5-.5V4H1z'/%3E%3C/svg%3E"); }
.icon-shipping { -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-box-arrow-in-up' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M3.5 10a.5.5 0 0 1-.5-.5v-8a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-.5-.5h-2a.5.5 0 0 0 0 1h2A1.5 1.5 0 0 0 14 9.5v-8A1.5 1.5 0 0 0 12.5 0h-9A1.5 1.5 0 0 0 2 1.5v8A1.5 1.5 0 0 0 3.5 11h2a.5.5 0 0 0 0-1h-2z'/%3E%3Cpath fill-rule='evenodd' d='M7.646 4.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707V14.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3z'/%3E%3C/svg%3E"); }
.icon-star { -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-star' viewBox='0 0 16 16'%3E%3Cpath d='M2.866 14.85c-.078.444.36.791.746.593l4.39-2.256 4.389 2.256c.386.198.824-.149.746-.592l-.83-4.73 3.522-3.356c.33-.314.16-.888-.282-.95l-4.898-.696L8.465.792a.513.513 0 0 0-.927 0L5.35 5.12l-4.898.696c-.441.062-.612.636-.283.95l3.523 3.356-.83 4.73zm4.905-2.767-3.686 1.894.698-3.965-2.922-2.802 4.01-.575L8 1.22l1.769 3.602 4.01.575-2.922 2.802.698 3.965-3.686-1.894a.503.503 0 0 0-.461 0z'/%3E%3C/svg%3E"); }
.modal-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 32px;
    gap: 12px;
}
</style>
