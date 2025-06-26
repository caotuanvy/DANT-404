
<template>
  <div class="product-page" v-if="product">
    <div class="product-container">
      <div class="product-images">
        <img :src="imgUrl(product.hinh_anh[0])" alt="Ảnh sản phẩm" class="main-img" v-if="product.hinh_anh && product.hinh_anh.length" />
        <div class="thumbnails">
          <img v-for="(img, idx) in product.hinh_anh" :key="idx" :src="imgUrl(img)" class="thumb" />
        </div>
      </div>

      <div class="product-info">
        <h2 class="product-name">{{ product.product_name }}</h2>
        <div class="rating">
          ⭐⭐⭐⭐☆ <span class="rating-count">(128 reviews)</span>
        </div>

        <div class="price-box">
          <template v-if="product?.khuyen_mai > 0">
            <span class="sale">{{ formatCurrency(finalPrice) }}</span>
            <span class="original">{{ formatCurrency(selectedVariant.gia) }}</span>
            <span class="discount">
              -{{ product.khuyen_mai }}%
            </span>
          </template>
          <template v-else>
            <span class="sale">{{ formatCurrency(selectedVariant?.gia || 0) }}</span>
          </template>
        </div>

        <div class="stock">🟢 In Stock ({{ selectedVariant?.so_luong_ton_kho || 0 }} available)</div>

        <div class="section">
          <label>Màu sắc:</label>
          <div class="color-options">
            <div
              v-for="variant in product.variants"
              :key="variant.san_pham_bien_the_id"
              class="color-dot"
              :style="{ backgroundColor: convertColor(variant.mau_sac) }"
              :class="{ selected: selectedVariantId === variant.san_pham_bien_the_id }"
              @click="selectedVariantId = variant.san_pham_bien_the_id"
            ></div>
          </div>
        </div>

        <div class="section">
          <label>Kích thước:</label>
          <div class="size-options">
            <div class="size-btn selected">One Size</div>
          </div>
        </div>

        <div class="quantity">
          <label>Số lượng:</label>
          <input type="number" v-model="quantity" min="1" :max="selectedVariant?.so_luong_ton_kho" />
        </div>

        <button class="add-cart-btn" @click="addToCart">🛒 Add to Cart</button>

        <div class="description">
          <h3>Mô tả</h3>
          <p>{{ product.description }}</p>
        </div>

        <div class="section key-features">
          <h3>Key Features:</h3>
          <ul>
            <li>• Hand-picked organic apples</li>
            <li>• Variety of popular and flavorful types</li>
            <li>• Crisp texture and sweet taste</li>
            <li>• Excellent source of fiber and vitamins</li>
            <li>• Perfect for snacks, salads, and baking</li>
            <li>• Grown with sustainable farming practices</li>
          </ul>
        </div>

        <div class="section shipping-returns">
          <h3>Shipping & Returns</h3>
          <div class="shipping-option">
            <input type="radio" id="standard" name="shipping" checked>
            <label for="standard">Standard Shipping: $5.00 <br> <small>Delivers in 3-5 business days</small></label>
          </div>
          <div class="shipping-option">
            <input type="radio" id="express" name="shipping">
            <label for="express">Express Shipping: $12.00 <br> <small>Delivers in 1-2 business days</small></label>
          </div>
          <p class="return-policy">Easy 30-day returns. <a href="#">Learn More</a></p>
        </div>
      </div>
    </div>

    <div class="product-reviews-section">
      <div class="review-summary">
        <h3>Reviews (128)</h3>
        <div class="overall-rating">
          <span class="rating-number">4.8</span>
          <span class="stars">⭐⭐⭐⭐⭐</span>
          <span class="total-reviews">128 reviews</span>
        </div>
        <button class="write-review-btn">Write a review</button>
      </div>

      <div class="rating-breakdown">
        <div class="rating-bar-item">
          <span class="star-count">5 Stars</span>
          <div class="bar-bg">
            <div class="bar-fill" style="width: 80%;"></div>
          </div>
          <span class="percentage">80%</span>
        </div>
        <div class="rating-bar-item">
          <span class="star-count">4 Stars</span>
          <div class="bar-bg">
            <div class="bar-fill" style="width: 15%;"></div>
          </div>
          <span class="percentage">15%</span>
        </div>
        <div class="rating-bar-item">
          <span class="star-count">3 Stars</span>
          <div class="bar-bg">
            <div class="bar-fill" style="width: 3%;"></div>
          </div>
          <span class="percentage">3%</span>
        </div>
        <div class="rating-bar-item">
          <span class="star-count">2 Stars</span>
          <div class="bar-bg">
            <div class="bar-fill" style="width: 1%;"></div>
          </div>
          <span class="percentage">1%</span>
        </div>
        <div class="rating-bar-item">
          <span class="star-count">1 Star</span>
          <div class="bar-bg">
            <div class="bar-fill" style="width: 1%;"></div>
          </div>
          <span class="percentage">1%</span>
        </div>
      </div>

      <div class="customer-reviews">
        <h4>Top reviews</h4>
        <div class="review-item">
          <div class="review-header">
            <span class="review-stars">⭐⭐⭐⭐⭐</span>
            <span class="review-date">2 days ago</span>
          </div>
          <p class="review-text">This product exceeded my expectations! The quality is outstanding, and I'm very happy with my purchase. Highly recommend.</p>
          <div class="review-meta">
            <span>By John Doe</span> | <span>Verified Purchase</span>
          </div>
        </div>
        <div class="review-item">
          <div class="review-header">
            <span class="review-stars">⭐⭐⭐⭐☆</span>
            <span class="review-date">1 week ago</span>
          </div>
          <p class="review-text">Good value for money. The product itself is well-made and durable. Shipping was a bit slow, but overall a solid delivery.</p>
          <div class="review-meta">
            <span>By Jane Smith</span> | <span>Verified Purchase</span>
          </div>
        </div>
        <div class="review-item">
          <div class="review-header">
            <span class="review-stars">⭐⭐⭐⭐⭐</span>
            <span class="review-date">2 weeks ago</span>
          </div>
          <p class="review-text">Absolutely delicious! These organic apples were crisp, sweet, and perfectly fresh. Definitely my go-to for healthy snacks now.</p>
          <div class="review-meta">
            <span>By Emily White</span> | <span>Verified Purchase</span>
          </div>
        </div>
      </div>
    </div>

    <div class="you-might-also-like">
      <h2>You Might Also Like</h2>
      <div class="related-products-grid">
        <div class="related-product-card">
          <img src="https://via.placeholder.com/150/FF6347/FFFFFF?text=Organic+Pomegranates" alt="Organic Pomegranates" />
          <p class="related-product-name">Organic Pomegranates</p>
          <p class="related-product-price">$5.99</p>
        </div>
        <div class="related-product-card">
          <img src="https://via.placeholder.com/150/FF4500/FFFFFF?text=Fresh+Strawberries" alt="Fresh Strawberries" />
          <p class="related-product-name">Fresh Strawberries</p>
          <p class="related-product-price">$4.99</p>
        </div>
        <div class="related-product-card">
          <img src="https://via.placeholder.com/150/B22222/FFFFFF?text=Organic+Tomatoes" alt="Organic Tomatoes" />
          <p class="related-product-name">Organic Tomatoes</p>
          <p class="related-product-price">$2.49</p>
        </div>
        <div class="related-product-card">
          <img src="https://via.placeholder.com/150/8B0000/FFFFFF?text=Macin+Bottle" alt="Macin Bottle" />
          <p class="related-product-name">Macin Bottle</p>
          <p class="related-product-price">$6.99</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const product = ref(null);
const selectedVariantId = ref(null);
const quantity = ref(1);
const route = useRoute();

const formatCurrency = (value) =>
  new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value); // Changed to USD for image consistency

const selectedVariant = computed(() => {
  if (!product.value || !product.value.variants) return null;
  return product.value.variants.find(v => v.san_pham_bien_the_id === selectedVariantId.value);
});


const addToCart = async () => {
  if (!selectedVariant.value) return;
  try {
    // Lấy token từ localStorage (nếu dùng JWT)
    const token = localStorage.getItem('token');
    await axios.post(
      '/cart/add',
      {
        san_pham_bien_the_id: selectedVariant.value.san_pham_bien_the_id,
        quantity: Number(quantity.value)
      },
      {
        headers: token ? { Authorization: `Bearer ${token}` } : {},
        withCredentials: true // Nếu backend dùng cookie/session
      }
    );
    alert('Đã thêm vào giỏ hàng!');
  } catch (e) {
    // Debug lỗi BE nếu cần
    if (e.response) {
      console.error('Lỗi BE:', e.response.data);
      if (e.response.data && e.response.data.message) {
        alert('Lỗi: ' + e.response.data.message);
      }
    }
    // Nếu lỗi (chưa đăng nhập), fallback lưu localStorage
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const idx = cart.findIndex(
      item => item.san_pham_bien_the_id === selectedVariant.value.san_pham_bien_the_id
    );
    if (idx !== -1) {
      cart[idx].quantity += Number(quantity.value);
    } else {
      cart.push({
        san_pham_bien_the_id: selectedVariant.value.san_pham_bien_the_id,
        product_name: product.value.product_name,
        mau_sac: selectedVariant.value.mau_sac,
        kich_thuoc: selectedVariant.value.kich_thuoc,
        gia: selectedVariant.value.gia,
        hinh_anh: product.value.hinh_anh,
        quantity: Number(quantity.value)
      });
    }
    localStorage.setItem('cart', JSON.stringify(cart));
    alert('Đã thêm vào giỏ hàng (local)!');
  }
};

onMounted(async () => {
  const id = route.params.id;
  try {
    const res = await axios.get(`http://localhost:8000/api/products/${id}`); // Assuming your API is at /api/products
    product.value = res.data;
    // Set a dummy product data if API call fails for demonstration purposes
    if (!product.value) {
      product.value = {
        product_name: "Premium Organic Apple Variety Pack",
        description: "Enjoy the fresh taste of organic apples with our premium variety pack. Each apple is hand-picked for quality, crispness, and delicious flavor, perfect for healthy snacking and meals.",
        hinh_anh: ["https://via.placeholder.com/340/8A2BE2/FFFFFF?text=Apple+Variety+Pack", "https://via.placeholder.com/64/8A2BE2/FFFFFF?text=Thumb1", "https://via.placeholder.com/64/8A2BE2/FFFFFF?text=Thumb2", "https://via.placeholder.com/64/8A2BE2/FFFFFF?text=Thumb3"],
        khuyen_mai: 13, // 13% discount for $12.99 from $14.99
        variants: [
          { san_pham_bien_the_id: 1, mau_sac: "đỏ", gia: 14.99, so_luong_ton_kho: 24, kich_thuoc: "One Size" },
          { san_pham_bien_the_id: 2, mau_sac: "xanh", gia: 14.99, so_luong_ton_kho: 15, kich_thuoc: "One Size" },
          { san_pham_bien_the_id: 3, mau_sac: "vàng", gia: 14.99, so_luong_ton_kho: 10, kich_thuoc: "One Size" }
        ],
        // You can add more dummy data if needed
      };
    }
    // Chọn biến thể đầu tiên mặc định
    if (product.value.variants && product.value.variants.length > 0) {
      selectedVariantId.value = product.value.variants[0].san_pham_bien_the_id;
    }
  } catch (error) {
    console.error("Error fetching product data:", error);
    // Fallback to dummy data if API fails
    product.value = {
      product_name: "Premium Organic Apple Variety Pack",
      description: "Enjoy the fresh taste of organic apples with our premium variety pack. Each apple is hand-picked for quality, crispness, and delicious flavor, perfect for healthy snacking and meals.",
      hinh_anh: ["https://via.placeholder.com/340/8A2BE2/FFFFFF?text=Apple+Variety+Pack", "https://via.placeholder.com/64/8A2BE2/FFFFFF?text=Thumb1", "https://via.placeholder.com/64/8A2BE2/FFFFFF?text=Thumb2", "https://via.placeholder.com/64/8A2BE2/FFFFFF?text=Thumb3"],
      khuyen_mai: 13, // 13% discount for $12.99 from $14.99
      variants: [
        { san_pham_bien_the_id: 1, mau_sac: "đỏ", gia: 14.99, so_luong_ton_kho: 24, kich_thuoc: "One Size" },
        { san_pham_bien_the_id: 2, mau_sac: "xanh", gia: 14.99, so_luong_ton_kho: 15, kich_thuoc: "One Size" },
        { san_pham_bien_the_id: 3, mau_sac: "vàng", gia: 14.99, so_luong_ton_kho: 10, kich_thuoc: "One Size" }
      ],
      // You can add more dummy data if needed
    };
    if (product.value.variants && product.value.variants.length > 0) {
      selectedVariantId.value = product.value.variants[0].san_pham_bien_the_id;
    }
  }
});

const imgUrl = (img) => {
  // This is a placeholder. In a real application, you'd likely have a base URL for your images.
  // For the dummy data, we are using placeholder images directly.
  return img.startsWith('http') ? img : `http://localhost:8000/storage/${img}`;
};

const finalPrice = computed(() => {
  if (!selectedVariant.value) return 0;
  const price = Number(selectedVariant.value.gia) || 0;
  const percent = Number(product.value?.khuyen_mai) || 0;
  if (percent > 0) {
    return price * (1 - percent / 100); // Calculate discount
  }
  return price;
});

const convertColor = (colorName) => {
  switch (colorName.toLowerCase()) {
    case 'đen': return '#000';
    case 'trắng': return '#fff';
    case 'xám': return '#ccc';
    case 'xanh': return '#1e40af'; // A shade of blue
    case 'hồng': return '#f472b6'; // A shade of pink
    case 'đỏ': return '#dc2626'; // A shade of red, similar to the image
    case 'vàng': return '#facc15'; // A shade of yellow
    default: return '#ddd';
  }
};
</script>

<style scoped>
.product-page {
  padding: 40px 20px;
  font-family: 'Segoe UI', sans-serif;
  background-color: #f5f5f5; /* Light grey background for the whole page */
}

.product-container {
  display: flex;
  gap: 40px;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
  padding: 32px;
  max-width: 1100px;
  margin: auto;
}

.product-images {
  flex: 1;
}

.main-img {
  width: 100%;
  max-width: 340px; /* Adjust if necessary */
  height: auto; /* Maintain aspect ratio */
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  object-fit: cover; /* Ensures image covers the area without distortion */
}

.thumbnails {
  display: flex;
  gap: 8px;
  margin-top: 12px;
}
.thumb {
  width: 64px;
  height: 64px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #ddd;
  cursor: pointer;
  transition: border-color 0.2s;
}
.thumb:hover {
  border-color: #2563eb;
}

.product-info {
  flex: 1.2;
}

.product-name {
  font-size: 28px;
  font-weight: bold;
  margin-bottom: 8px;
  color: #333;
}

.rating {
  font-size: 16px;
  color: #f59e0b; /* Gold color for stars */
  margin-bottom: 10px;
  display: flex;
  align-items: center;
}
.rating-count {
  color: #555;
  font-size: 14px;
  margin-left: 6px;
}

.price-box {
  margin: 16px 0;
  display: flex;
  align-items: center;
  gap: 12px;
}
.sale {
  font-size: 28px; /* Slightly larger for emphasis */
  color: #dc2626; /* Red for sale price */
  font-weight: bold;
}
.original {
  text-decoration: line-through;
  color: #888;
  font-size: 18px; /* Slightly smaller than sale price */
}
.discount {
  background: #fee2e2; /* Light red background */
  color: #dc2626; /* Red text */
  padding: 4px 8px; /* More padding */
  font-size: 14px;
  border-radius: 4px;
  font-weight: 600;
}

.stock {
  color: #22c55e; /* Green for in stock */
  font-size: 15px;
  margin-bottom: 12px;
  font-weight: 500;
}

.section {
  margin: 20px 0; /* More spacing between sections */
}
.section label {
  font-weight: bold;
  margin-bottom: 8px;
  display: block;
  color: #333;
}
.color-options {
  display: flex;
  gap: 12px; /* More gap */
  margin-top: 8px;
}
.color-dot {
  width: 36px; /* Slightly larger */
  height: 36px; /* Slightly larger */
  border-radius: 50%;
  border: 2px solid transparent;
  cursor: pointer;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); /* Subtle shadow */
  transition: all 0.2s ease-in-out;
}
.color-dot.selected {
  border: 2px solid #2563eb; /* Blue border when selected */
  transform: scale(1.1); /* Slight enlarge effect */
  box-shadow: 0 2px 8px rgba(37, 99, 235, 0.4); /* Stronger shadow when selected */
}

.size-options {
  display: flex;
  gap: 10px;
  margin-top: 8px;
}
.size-btn {
  padding: 10px 18px; /* More padding */
  border: 1px solid #ccc;
  border-radius: 6px;
  cursor: pointer;
  background-color: #f9f9f9; /* Light background */
  transition: all 0.2s ease-in-out;
  font-weight: 500;
  color: #555;
}
.size-btn:hover {
  border-color: #2563eb;
  color: #2563eb;
}
.size-btn.selected {
  background: #2563eb;
  color: #fff;
  border-color: #2563eb;
  box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
}

.quantity {
  margin-top: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.quantity label {
  font-weight: bold;
  color: #333;
  margin-bottom: 0; /* Reset margin for alignment */
}
.quantity input {
  width: 70px; /* Wider input */
  padding: 8px 10px; /* More padding */
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 16px;
  text-align: center;
  -moz-appearance: textfield; /* Hide arrows for Firefox */
}
/* Hide arrows for Chrome, Safari, Edge */
.quantity input::-webkit-outer-spin-button,
.quantity input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}


.add-cart-btn {
  margin-top: 30px; /* More space */
  padding: 14px 28px; /* Larger button */
  background: #2563eb;
  color: white;
  font-weight: 700; /* Bolder text */
  border: none;
  border-radius: 8px;
  font-size: 17px; /* Slightly larger font */
  cursor: pointer;
  transition: background 0.3s ease, transform 0.1s ease;
  width: 100%; /* Make button full width */
  max-width: 300px; /* Max width for the button */
  display: block; /* To center with margin auto */
  margin-left: 0; /* Ensure it's not affected by previous margin-left */
}
.add-cart-btn:hover {
  background: #1e40af;
  transform: translateY(-1px); /* Slight lift effect */
}
.add-cart-btn:active {
  transform: translateY(0);
}

.description {
  margin-top: 24px;
  border-top: 1px solid #eee; /* Separator line */
  padding-top: 20px;
}
.description h3 {
  font-size: 20px;
  font-weight: bold;
  margin-bottom: 10px;
  color: #333;
}
.description p {
  margin-top: 8px;
  color: #444;
  line-height: 1.7; /* Better readability */
}

/* Key Features styling */
.key-features ul {
  list-style: none;
  padding: 0;
  margin-top: 10px;
}
.key-features ul li {
  margin-bottom: 8px;
  color: #444;
  font-size: 15px;
  line-height: 1.5;
}

/* Shipping & Returns Styling */
.shipping-returns {
  border-top: 1px solid #eee;
  padding-top: 20px;
}
.shipping-returns h3 {
  font-size: 20px;
  font-weight: bold;
  margin-bottom: 15px;
  color: #333;
}
.shipping-option {
  display: flex;
  align-items: flex-start;
  margin-bottom: 12px;
}
.shipping-option input[type="radio"] {
  margin-top: 4px;
  margin-right: 10px;
}
.shipping-option label {
  font-weight: normal;
  color: #444;
  cursor: pointer;
}
.shipping-option label small {
  display: block;
  color: #777;
  font-size: 13px;
  margin-top: 2px;
}
.return-policy {
  margin-top: 15px;
  font-size: 14px;
  color: #555;
}
.return-policy a {
  color: #2563eb;
  text-decoration: none;
}
.return-policy a:hover {
  text-decoration: underline;
}

/* Product Reviews Section */
.product-reviews-section {
  max-width: 1100px;
  margin: 40px auto;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
  padding: 32px;
  display: flex;
  flex-wrap: wrap; /* Allows wrapping for smaller screens */
  gap: 40px;
}

.review-summary {
  flex: 1;
  min-width: 280px; /* Minimum width before wrapping */
}
.review-summary h3 {
  font-size: 22px;
  font-weight: bold;
  margin-bottom: 15px;
  color: #333;
}
.overall-rating {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 15px;
}
.overall-rating .rating-number {
  font-size: 48px;
  font-weight: bold;
  color: #333;
}
.overall-rating .stars {
  font-size: 24px;
  color: #f59e0b;
}
.overall-rating .total-reviews {
  font-size: 16px;
  color: #777;
  margin-left: 5px;
}
.write-review-btn {
  background: #2563eb;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
  transition: background 0.3s ease;
}
.write-review-btn:hover {
  background: #1e40af;
}

.rating-breakdown {
  flex: 2;
  min-width: 350px; /* Minimum width before wrapping */
}
.rating-bar-item {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
  gap: 10px;
}
.rating-bar-item .star-count {
  width: 80px;
  color: #555;
  font-size: 15px;
}
.rating-bar-item .bar-bg {
  flex-grow: 1;
  height: 8px;
  background-color: #eee;
  border-radius: 4px;
  overflow: hidden;
}
.rating-bar-item .bar-fill {
  height: 100%;
  background-color: #22c55e; /* Green for fill */
  border-radius: 4px;
}
.rating-bar-item .percentage {
  width: 40px;
  text-align: right;
  color: #555;
  font-size: 15px;
}

.customer-reviews {
  width: 100%; /* Take full width below summary and breakdown */
  margin-top: 30px;
  border-top: 1px solid #eee;
  padding-top: 20px;
}
.customer-reviews h4 {
  font-size: 20px;
  font-weight: bold;
  margin-bottom: 20px;
  color: #333;
}
.review-item {
  border: 1px solid #eee;
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 20px;
  background-color: #fcfcfc;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}
.review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}
.review-stars {
  color: #f59e0b;
  font-size: 18px;
}
.review-date {
  font-size: 14px;
  color: #888;
}
.review-text {
  font-size: 15px;
  color: #333;
  line-height: 1.6;
  margin-bottom: 10px;
}
.review-meta {
  font-size: 13px;
  color: #666;
}
.review-meta span:first-child {
  font-weight: bold;
}


/* You Might Also Like Section */
.you-might-also-like {
  max-width: 1100px;
  margin: 40px auto;
  padding: 32px;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
}
.you-might-also-like h2 {
  font-size: 24px;
  font-weight: bold;
  margin-bottom: 25px;
  text-align: center;
  color: #333;
}
.related-products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 25px;
  justify-items: center;
}
.related-product-card {
  text-align: center;
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  padding: 15px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  width: 100%; /* Ensure cards take full grid column width */
}
.related-product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
.related-product-card img {
  width: 100%;
  max-width: 150px;
  height: 150px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 10px;
}
.related-product-name {
  font-weight: 600;
  color: #333;
  margin-bottom: 5px;
  font-size: 16px;
}
.related-product-price {
  font-weight: bold;
  color: #2563eb;
  font-size: 15px;
}

/* Responsive adjustments */
@media (max-width: 992px) {
  .product-container {
    flex-direction: column;
    align-items: center;
    gap: 30px;
    padding: 25px;
  }
  .main-img {
    max-width: 100%;
  }
  .product-info {
    width: 100%;
  }
  .add-cart-btn {
    max-width: none; /* Allow button to take full width on smaller screens */
  }
  .product-reviews-section {
    flex-direction: column;
    gap: 30px;
    padding: 25px;
  }
  .review-summary, .rating-breakdown {
    min-width: unset; /* Remove min-width to allow full flexibility */
    width: 100%;
  }
}

@media (max-width: 768px) {
  .product-page {
    padding: 20px 10px;
  }
  .product-container {
    padding: 20px;
  }
  .product-name {
    font-size: 24px;
  }
  .sale {
    font-size: 24px;
  }
  .original {
    font-size: 16px;
  }
  .add-cart-btn {
    font-size: 15px;
    padding: 12px 20px;
  }
  .overall-rating .rating-number {
    font-size: 40px;
  }
  .overall-rating .stars {
    font-size: 20px;
  }
  .overall-rating .total-reviews {
    font-size: 14px;
  }
  .you-might-also-like h2 {
    font-size: 20px;
  }
  .related-products-grid {
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 15px;
  }
  .related-product-card img {
    max-width: 120px;
    height: 120px;
  }
  .related-product-name {
    font-size: 14px;
  }
  .related-product-price {
    font-size: 13px;
  }
}

@media (max-width: 480px) {
  .product-page {
    padding: 15px 5px;
  }
  .product-container {
    padding: 15px;
    gap: 20px;
  }
  .thumbnails {
    justify-content: center; /* Center thumbnails */
  }
  .thumb {
    width: 50px;
    height: 50px;
  }
  .price-box {
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
  }
  .stock {
    text-align: center;
    width: 100%;
  }
  .section label {
    text-align: center;
    width: 100%;
  }
  .color-options, .size-options {
    justify-content: center;
  }
  .quantity {
    justify-content: center;
  }
  .add-cart-btn {
    padding: 10px 15px;
    font-size: 14px;
  }
  .product-reviews-section {
    padding: 15px;
  }
  .review-summary {
    text-align: center;
  }
  .overall-rating {
    justify-content: center;
  }
  .write-review-btn {
    width: 100%;
  }
  .rating-bar-item .star-count,
  .rating-bar-item .percentage {
    font-size: 13px;
  }
  .review-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
  }
  .review-date {
    align-self: flex-end;
  }
  .related-products-grid {
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
  }
  .related-product-card img {
    max-width: 100px;
    height: 100px;
  }
}
</style>

```