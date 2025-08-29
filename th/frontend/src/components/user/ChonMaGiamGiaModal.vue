<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Chọn mã giảm giá</h3>
        <button class="close-button" @click="$emit('close')">&times;</button>
      </div>

      <div class="tabs-container">
        <button @click="activeTab = 'all'" :class="{ active: activeTab === 'all' }">
          Tất cả ({{ tabCounts.all }})
        </button>
        <button @click="activeTab = 'shipping'" :class="{ active: activeTab === 'shipping' }">
          Miễn phí ship ({{ tabCounts.shipping }})
        </button>
        <button @click="activeTab = 'product'" :class="{ active: activeTab === 'product' }">
          Giảm giá sản phẩm ({{ tabCounts.product }})
        </button>
        <button @click="activeTab = 'special'" :class="{ active: activeTab === 'special' }">
          Ưu đãi đặc biệt ({{ tabCounts.special }})
        </button>
      </div>

      <div class="modal-body">
        <div v-if="filteredCoupons.length > 0" class="coupon-list">
          <div 
            v-for="coupon in filteredCoupons" 
            :key="coupon.id" 
            class="coupon-item"
            @click="applyAndClose(coupon.code)"
          >
            <div class="coupon-graphic" :class="`graphic-${coupon.category}`">
              <span v-if="coupon.discountType === 'percentage'">{{ coupon.value }}%<br>OFF</span>
              <span v-else-if="coupon.category === 'shipping' && coupon.value === 100">100%<br>OFF</span>
              <span v-else>{{ formatCurrency(coupon.value) }}<br>OFF</span>
            </div>
            <div class="coupon-details">
              <h4 class="coupon-title">{{ coupon.title }}</h4>
              <p class="coupon-description">{{ coupon.description }}</p>
              <div class="coupon-info">
                <span class="coupon-code">Mã: {{ coupon.code }}</span>
                <span class="coupon-expiry">HSD: {{ formatDate(coupon.expiryDate) }}</span>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="no-coupons-message">
          <p>Bạn không có mã giảm giá nào.</p>
        </div>
      </div>

      <div class="modal-footer">
        <button class="clear-selection" @click="clearAndClose">Không sử dụng mã giảm giá</button>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

// --- Props & Emits ---
// Giữ nguyên cấu trúc prop và emit gốc của bạn
const props = defineProps({
  coupons: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(['coupon-selected', 'close']);

// --- State ---
const activeTab = ref('all');
// KHÔNG CẦN selectedCouponCode nữa

// --- Logic xử lý dữ liệu (giữ nguyên từ phiên bản trước) ---
const getCategoryFromCoupon = (coupon) => {
  const title = coupon.ten_chuong_trinh.toLowerCase();
  if (title.includes('ship') || title.includes('vận chuyển')) return 'shipping';
  if (title.includes('đầu tiên') || title.includes('khách hàng mới')) return 'special';
  return 'product';
};

const processedCoupons = computed(() => {
  if (!props.coupons) return [];
  return props.coupons.map(c => ({
    id: c.giam_gia_id,
    code: c.ma_giam_gia,
    title: c.ten_chuong_trinh,
    description: c.mo_ta || `Áp dụng cho đơn hàng đủ điều kiện.`, 
    value: c.gia_tri,
    discountType: c.loai_giam_gia,
    expiryDate: c.ngay_ket_thuc,
    category: getCategoryFromCoupon(c),
  }));
});

const tabCounts = computed(() => {
    const counts = {
        all: processedCoupons.value.length,
        shipping: 0,
        product: 0,
        special: 0,
    };
    processedCoupons.value.forEach(c => {
        if (counts[c.category] !== undefined) counts[c.category]++;
    });
    return counts;
});

const filteredCoupons = computed(() => {
  if (activeTab.value === 'all') return processedCoupons.value;
  return processedCoupons.value.filter(coupon => coupon.category === activeTab.value);
});

// --- Methods (Đã cập nhật theo logic cũ) ---
/**
 * Hàm này sẽ emit mã được chọn VÀ đóng modal ngay lập tức
 */
const applyAndClose = (code) => {
  emit('coupon-selected', code);
  emit('close');
};

/**
 * Hàm này xóa mã giảm giá và đóng modal
 */
const clearAndClose = () => {
  emit('coupon-selected', null); // Gửi null để xóa mã
  emit('close');
}

// --- Helper Functions (Giữ nguyên) ---
const formatCurrency = (value) => {
  // Thay đổi lại hàm formatValue gốc của bạn một chút cho đúng định dạng tiền tệ
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
  return new Date(dateString).toLocaleDateString('vi-VN', options);
};

</script>

<style scoped>
/* CSS gần như giữ nguyên, chỉ xóa các phần không cần thiết */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: #f7f7f7;
  border-radius: 8px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  border-bottom: 1px solid #e0e0e0;
  background-color: white;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
}

.close-button {
  background: none;
  border: none;
  font-size: 1.8rem;
  cursor: pointer;
  color: #888;
}

.tabs-container {
  display: flex;
  background-color: white;
  border-bottom: 1px solid #e0e0e0;
  padding: 0 16px;
}

.tabs-container button {
  padding: 12px 16px;
  border: none;
  background: none;
  cursor: pointer;
  font-size: 0.9rem;
  color: #555;
  border-bottom: 3px solid transparent;
  transition: all 0.2s ease-in-out;
}

.tabs-container button.active {
  color: #007bff;
  font-weight: 600;
  border-bottom-color: #007bff;
}

.modal-body {
  padding: 16px;
  overflow-y: auto;
  flex-grow: 1;
}

.coupon-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.coupon-item {
  display: flex;
  align-items: center;
  background-color: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  cursor: pointer;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
  overflow: hidden;
  padding-left: 16px; /* Thêm padding để thay thế cho nút radio đã xóa */
}

/* Hiệu ứng hover vẫn giữ lại cho đẹp */
.coupon-item:hover {
  border-color: #007bff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.coupon-graphic {
  padding: 16px;
  color: white;
  font-weight: bold;
  font-size: 1.1rem;
  text-align: center;
  min-width: 110px;
  border-right: 2px dashed #f0f0f0;
}
.graphic-special { background-color: #9b59b6; }
.graphic-shipping { background-color: #2ecc71; }
.graphic-product { background-color: #3498db; }


.coupon-details {
  padding: 12px 16px;
  flex-grow: 1;
}

.coupon-title {
  font-weight: 600;
  font-size: 1rem;
  margin: 0 0 4px 0;
}

.coupon-description {
  font-size: 0.85rem;
  color: #666;
  margin: 0 0 8px 0;
}

.coupon-info {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 0.8rem;
}

.coupon-code {
  font-family: monospace;
  background-color: #f0f0f0;
  padding: 2px 5px;
  border-radius: 4px;
}

.coupon-expiry {
  color: #777;
}

.no-coupons-message {
  text-align: center;
  padding: 40px 20px;
  color: #888;
}

.modal-footer {
  display: flex;
  justify-content: flex-start; /* Thay đổi để nút căn lề trái */
  align-items: center;
  padding: 16px 24px;
  border-top: 1px solid #e0e0e0;
  background-color: white;
}

.clear-selection {
  background: none;
  border: none;
  color: #007bff;
  cursor: pointer;
  font-size: 0.9rem;
  padding: 0;
}
</style>