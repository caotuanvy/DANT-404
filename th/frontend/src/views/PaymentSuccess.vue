<template>
  <div class="payment-success-page">
    <div class="success-icon">
      <i class="fas fa-check-circle"></i>
    </div>
    <h2>Thanh toán thành công!</h2>
    <p>Cảm ơn bạn đã mua hàng tại FOF Mart.</p>
    <router-link to="/" class="back-home-btn">Về trang chủ</router-link>
  </div>
</template>

<script>
export default {
  name: "PaymentSuccess"
};
</script>

<style scoped>
.payment-success-page {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  text-align: center;
}
.success-icon {
  font-size: 64px;
  color: #33ccff;
  margin-bottom: 20px;
}
h2 {
  color: #33ccff;
  margin-bottom: 10px;
}
.back-home-btn {
  margin-top: 24px;
  padding: 10px 24px;
  background: #33ccff;
  color: #fff;
  border-radius: 6px;
  text-decoration: none;
  font-weight: bold;
  transition: background 0.2s;
}
.back-home-btn:hover {
  background: #269bc2;
}
</style>