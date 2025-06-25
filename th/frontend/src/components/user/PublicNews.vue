<template>
  <div class="vohop-container">
    <div class="vohop-main">
      <h1 class="vohop-title">Tin tức mới nhất</h1>
      <div class="vohop-newsgrid">
        <div class="vohop-newsitem" v-for="n in 6" :key="n">
          <img src="https://via.placeholder.com/400x180" alt="">
          <div class="vohop-newsitem-content">
            <h3>Tiệc tùng linh đình: Khuyến mãi combo pizza</h3>
            <p>Bạn đang tìm kiếm một bữa ăn ngon lành cho buổi tối cùng bạn bè quây quần? Đừng bỏ lỡ combo pizza siêu ưu đãi tháng này!</p>
          </div>
          <div class="vohop-newsitem-footer">
            <span><i class="fa-regular fa-calendar"></i> 24/03/2025</span>
            <button class="vohop-btn">Xem chi tiết</button>
          </div>
        </div>
      </div>
    </div>
    <aside class="vohop-benphai">
      <div class="vohop-danhmuc">
        <h3><i class="fa-solid fa-list"></i> DANH MỤC</h3>
        <ul>
          <li
            v-for="(item, idx) in danhMucList"
            :key="item.label"
            :class="{ active: activeDanhMuc === idx }"
            @click="activeDanhMuc = idx"
          >
            <i :class="`fa-solid ${item.icon}`"></i> {{ item.label }}
          </li>
        </ul>
      </div>
      <div class="vohop-tinnoibat">
        <h3><i class="fa-solid fa-star"></i> TIN TỨC NỔI BẬT</h3>
        <div class="vohop-tinnoibat-item">
          <img src="https://via.placeholder.com/60x60" alt="">
          <span>Khuyến mãi combo pizza cuối tuần</span>
        </div>
        <div class="vohop-tinnoibat-item">
          <img src="https://via.placeholder.com/60x60" alt="">
          <span>Gà rán giòn tan chỉ 29k</span>
        </div>
      </div>
      <div class="vohop-tags">
        <h3><i class="fa-solid fa-tags"></i> Tags</h3>
        <button>#burger</button>
        <button>#khuyến mãi</button>
        <button>#pizza</button>
        <button>#gà rán</button>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { ref } from 'vue'
const activeDanhMuc = ref(0)
const danhMucList = [
  { icon: 'fa-drumstick-bite', label: 'Các món gà' },
  { icon: 'fa-pizza-slice', label: 'Pizza' },
  { icon: 'fa-burger', label: 'Burger' },
  { icon: 'fa-bowl-food', label: 'Mì Ý' },
  { icon: 'fa-bowl-rice', label: 'Cơm' },
  { icon: 'fa-bread-slice', label: 'Bánh' },
  { icon: 'fa-utensils', label: 'Món ăn kèm' },
  { icon: 'fa-mug-hot', label: 'Nước uống' }
]
</script>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css');

:root {
  --main-blue: #33ccff;
  --main-bg: #fff;
  --main-bg-light: #f4fbfd;
  --main-border: #b3eaff;
  --main-shadow: 0 2px 12px rgba(51,204,255,0.08);
}

.vohop-container {
  display: flex;
  max-width: 1200px;
  margin: 0 auto;
  padding: 32px 64px;
  gap: 36px;
  background: var(--main-bg-light);
  min-height: 100vh;
}
.vohop-main {
  flex: 3;
}
.vohop-title {
  font-size: 2.2rem;
  font-weight: 700;
  color: var(--main-blue);
  margin-bottom: 28px;
  letter-spacing: 1px;
  text-shadow: 0 2px 8px rgba(51,204,255,0.10);
}
.vohop-newsgrid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 28px;
}
.vohop-newsitem {
  background: var(--main-bg);
  border-radius: 14px;
  overflow: hidden;
  box-shadow: var(--main-shadow);
  display: flex;
  flex-direction: column;
  transition: box-shadow 0.2s, transform 0.2s;
  cursor: pointer;
  border: 1.5px solid var(--main-border);
}
.vohop-newsitem:hover {
  box-shadow: 0 6px 24px rgba(51,204,255,0.18);
  transform: translateY(-4px) scale(1.02);
  border-color: var(--main-blue);
}
.vohop-newsitem img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  border-bottom: 1px solid #e3f7fb;
}
.vohop-newsitem-content {
  padding: 18px 16px 10px 16px;
  flex: 1;
}
.vohop-newsitem h3 {
  font-size: 1.1rem;
  margin: 0 0 8px;
  color: #222;
  font-weight: 600;
}
.vohop-newsitem p {
  font-size: 14px;
  color: #666;
  flex-grow: 1;
  margin: 0;
}
.vohop-newsitem-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  border-top: 1px solid #e3f7fb;
  background: #eafaff;
}
.vohop-newsitem-footer span {
  font-size: 13px;
  color: var(--main-blue);
  display: flex;
  align-items: center;
  gap: 5px;
}
.vohop-btn {
  background: var(--main-bg);
  border: 1.5px solid var(--main-blue);
  color: var(--main-blue);
  padding: 6px 16px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 500;
  transition: background 0.2s, color 0.2s, border 0.2s;
}
.vohop-btn:hover {
  background: #1ba6d9;
  color: #fff;
  border-color: #1ba6d9;
}
.vohop-benphai {
  flex: 1;
  min-width: 270px;
}
.vohop-benphai h3 {
  margin-top: 0;
  font-size: 1.08rem;
  border-bottom: 1.5px solid var(--main-border);
  padding-bottom: 7px;
  color: var(--main-blue);
  font-weight: 700;
  letter-spacing: 0.5px;
  display: flex;
  align-items: center;
  gap: 7px;
}
.vohop-danhmuc, .vohop-tinnoibat, .vohop-tags {
  background: var(--main-bg);
  padding: 18px 16px;
  margin-bottom: 22px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(51,204,255,0.04);
  border: 1.5px solid var(--main-border);
}
.vohop-danhmuc ul {
  list-style: none;
  padding: 0;
  margin: 0;
}
.vohop-danhmuc li {
  margin-bottom: 10px;
  color: #333;
  font-size: 15px;
  display: flex;
  align-items: center;
  gap: 7px;
  transition: color 0.2s, background 0.2s;
  cursor: pointer;
}
.vohop-danhmuc li:hover {
  color: var(--main-blue);
}
.vohop-danhmuc li.active {
  background: #eafaff;
  color: var(--main-blue);
  border-radius: 6px;
  font-weight: 700;
}
.vohop-tinnoibat-item img {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 7px;
  margin-bottom: 0;
  border: 1.5px solid var(--main-border);
  background: #eafaff;
}
.vohop-tinnoibat-item {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
  cursor: pointer;
  transition: background 0.2s, color 0.2s, box-shadow 0.2s;
}
.vohop-tinnoibat-item:hover,
.vohop-tinnoibat-item:focus {
  background: #eafaff;
  color: var(--main-blue);
  box-shadow: 0 2px 8px #33ccff44;
}
.vohop-tinnoibat-item:active {
  background: #33ccff;
  color: #fff;
  box-shadow: 0 2px 12px #33ccff55;
}
.vohop-tinnoibat-item span {
  font-size: 14px;
  color: #333;
  font-weight: 500;
}
.vohop-tags button {
  border: 1.5px solid var(--main-blue);
  background: #eafaff;
  color: var(--main-blue);
  border-radius: 4px;
  padding: 5px 14px;
  font-size: 13px;
  margin: 4px 6px 4px 0;
  cursor: pointer;
  transition: background 0.2s, color 0.2s, border 0.2s, box-shadow 0.2s;
}
/* Xóa hiệu ứng hover và focus, chỉ giữ lại hiệu ứng khi nhấn */
.vohop-tags button:active {
  background: #1ba6d9;
  color: #fff;
  border-color: #1ba6d9;
  box-shadow: 0 2px 12px #33ccff55;
}
</style>