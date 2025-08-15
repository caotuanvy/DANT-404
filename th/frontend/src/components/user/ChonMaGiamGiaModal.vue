<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Chọn mã giảm giá</h3>
        <button class="close-button" @click="$emit('close')">&times;</button>
      </div>
      <div class="modal-body">
        <div v-if="coupons.length > 0" class="coupon-list">
          <div v-for="coupon in coupons" :key="coupon.giam_gia_id" class="coupon-item" @click="selectCoupon(coupon.ma_giam_gia)">
            <div class="coupon-value" :class="coupon.loai_giam_gia">
              <span v-if="coupon.loai_giam_gia === 'percentage'">{{ coupon.gia_tri }}%</span>
              <span v-else>đ{{ formatValue(coupon.gia_tri) }}</span>
            </div>
            <div class="coupon-details">
              <p class="coupon-name">{{ coupon.ten_chuong_trinh }}</p>
              <p class="coupon-code-display">Mã: {{ coupon.ma_giam_gia }}</p>
              <p class="coupon-expiry">HSD: {{ formatDate(coupon.ngay_ket_thuc) }}</p>
            </div>
          </div>
        </div>
        <div v-else class="no-coupons-message">
          <p>Bạn không có mã giảm giá nào.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  coupons: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(['coupon-selected', 'close']);

const selectCoupon = (code) => {
  emit('coupon-selected', code);
};

const formatValue = (value) => {
  return new Intl.NumberFormat('vi-VN').format(value / 1000) + 'k';
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
  return new Date(dateString).toLocaleDateString('vi-VN', options);
};
</script>

<style scoped>
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
  background: white;
  padding: 20px;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #eee;
  padding-bottom: 15px;
  margin-bottom: 15px;
}
.modal-title {
  font-size: 1.2rem;
  font-weight: bold;
}
.close-button {
  background: none;
  border: none;
  font-size: 1.8rem;
  cursor: pointer;
  color: #888;
}
.modal-body {
  overflow-y: auto;
}
.coupon-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}
.coupon-item {
  display: flex;
  align-items: center;
  border: 1px solid #ddd;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  overflow: hidden;
}
.coupon-item:hover {
  border-color: #007bff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.coupon-value {
  padding: 20px;
  color: white;
  font-weight: bold;
  font-size: 1.2rem;
  text-align: center;
  border-right: 2px dashed #ddd;
  min-width: 100px;
}
.coupon-value.fixed_amount { background-color: #3498db; }
.coupon-value.percentage { background-color: #e74c3c; }
.coupon-details {
  padding: 10px 15px;
}
.coupon-name {
  font-weight: bold;
  font-size: 1rem;
  margin: 0 0 5px 0;
}
.coupon-code-display {
  font-family: monospace;
  background-color: #f0f0f0;
  padding: 2px 5px;
  border-radius: 4px;
  display: inline-block;
  margin: 0 0 5px 0;
  font-size: 0.9rem;
}
.coupon-expiry {
  font-size: 0.8rem;
  color: #777;
  margin: 0;
}
.no-coupons-message {
  text-align: center;
  padding: 40px 20px;
  color: #888;
}
</style>