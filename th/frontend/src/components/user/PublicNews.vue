<template>
    <div class="container">
    <div class="main">
      <h1>Tin tức</h1>
      <div class="news-grid">
        <!-- Mẫu tin tức -->
        <div class="news-item">
          <img src="https://via.placeholder.com/400x180" alt="">
          <div class="news-item-content">
            <h3>Tiệc tùng linh đình: Khuyến mãi combo pizza</h3>
            <p>Bạn đang tìm kiếm một bữa ăn ngon lành cho buổi tối cùng bẹ quây quán?</p>
          </div>
          <div class="news-item-footer">
            <span>24/03/2025</span>
            <button class="btn">Xem chi tiết</button>
          </div>
        </div>
        <!-- Nhân bản thêm nhiều tin tức khác tại đây -->
      </div>
    </div>
    <div class="sidebar">
      <div class="categories">
        <h3>DANH MỤC</h3>
        <ul>
          <li>Các món gà</li>
          <li>Pizza</li>
          <li>Burger</li>
          <li>Mì Ý</li>
          <li>Cơm</li>
          <li>Bánh</li>
          <li>Món ăn kèm</li>
          <li>Nước uống</li>
        </ul>
      </div>
      <div class="featured-news">
        <h3>TIN TỨC NỔI BẬT</h3>
        <div class="featured-news-item">
          <img src="https://via.placeholder.com/60x60" alt="">
          <span>Khuyến mãi combo pizza...</span>
        </div>
        <!-- Thêm nhiều item tương tự -->
      </div>
      <div class="tags">
        <h3>Tags</h3>
        <button>burger khuyến mãi</button>
        <button>gà rán</button>
        <button>Khuyến mãi</button>
      </div>
    </div>
  </div>
</template>
<style scoped>
.container {
      display: flex;
      padding: 20px;
      gap: 30px;
    }
    .main {
      flex: 3;
    }
    .sidebar {
      flex: 1;
    }
    h1 {
      font-size: 32px;
      margin-bottom: 20px;
    }
    .news-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
    }
    .news-item {
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      display: flex;
      flex-direction: column;
    }
    .news-item img {
      width: 100%;
      height: 180px;
      object-fit: cover;
    }
    .news-item-content {
      padding: 15px;
      flex: 1;
    }
    .news-item h3 {
      font-size: 16px;
      margin: 0 0 10px;
    }
    .news-item p {
      font-size: 14px;
      color: #666;
      flex-grow: 1;
    }
    .news-item-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 15px;
      border-top: 1px solid #eee;
    }
    .news-item-footer span {
      font-size: 13px;
      color: #999;
    }
    .btn {
      background: none;
      border: 1px solid #ff6b00;
      color: #ff6b00;
      padding: 6px 12px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 13px;
    }
    .sidebar h3 {
      margin-top: 0;
      font-size: 16px;
      border-bottom: 1px solid #ddd;
      padding-bottom: 5px;
    }
    .categories, .featured-news, .tags {
      background: #fff;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .categories ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .categories li {
      margin-bottom: 8px;
      color: #333;
    }
    .featured-news img {
      width: 100%;
      height: 60px;
      object-fit: cover;
      border-radius: 5px;
      margin-bottom: 5px;
    }
    .featured-news-item {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
    }
    .featured-news-item span {
      font-size: 13px;
      color: #333;
    }
    .tags button {
      border: 1px solid #ff6b00;
      background: none;
      color: #ff6b00;
      border-radius: 4px;
      padding: 4px 10px;
      font-size: 12px;
      margin: 4px;
      cursor: pointer;
    }
</style>