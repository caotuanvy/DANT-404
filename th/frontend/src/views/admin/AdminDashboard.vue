<template>
  <section class="content">
    <h2>📊 Thống kê tổng quan</h2>
    <template>
  <form @submit.prevent="goToSearch">
    <input v-model="searchText" placeholder="Tìm kiếm..." />
    <button type="submit">🔍</button>
  </form>
</template>
    <div v-if="loading" class="text-gray-500">Đang tải dữ liệu...</div>
    <div v-else>
      <ul>
        <li>Tổng sản phẩm: {{ totalProducts }}</li>
        <li>Tổng đơn hàng: {{ totalOrders }}</li>
        <li>Tổng khách hàng: {{ totalCustomers }}</li>
        <li>Tổng doanh thu: {{ formatCurrency(totalRevenue) }}</li>
      </ul>

      <!-- Biểu đồ doanh thu -->
      <div>
        <canvas id="revenueChart" width="400" height="200"></canvas>
      </div>
    </div>

    <div v-if="error" class="text-red-500 mt-4">
      Lỗi khi tải dữ liệu. Vui lòng thử lại sau.
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, watchEffect, nextTick } from 'vue';
import axios from 'axios';
import Chart from 'chart.js/auto';

// Các biến để lưu trữ dữ liệu
const totalProducts = ref(0);
const totalOrders = ref(0);
const totalCustomers = ref(0);
const totalRevenue = ref(0);
const revenueData = ref([]);
const loading = ref(true);
const error = ref(false);


const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};


onMounted(async () => {
  const token = localStorage.getItem('token');
  const headers = { Authorization: `Bearer ${token}` };

  try {
    const [p, o, c] = await Promise.all([
      axios.get('/products', { headers }),
      axios.get('/orders', { headers }),
      axios.get('/users', { headers })  
    ]);

   
    totalProducts.value = p.data.length;
    totalOrders.value = o.data.length;
    totalCustomers.value = c.data.length;

 
    totalRevenue.value = o.data.reduce((sum, order) => sum + (parseFloat(order.total) || 0), 0);


   
    revenueData.value = o.data.map(order => ({
    date: new Date(order.created_at).toLocaleDateString(),
    revenue: parseFloat(order.total) || 0
  }));


  } catch (e) {
    console.error('Lỗi khi load thống kê:', e);
    error.value = true;
  } finally {
    loading.value = false;
  }
});

watchEffect(() => {
  if (revenueData.value.length > 0) {
    nextTick(() => {  
      const ctx = document.getElementById('revenueChart').getContext('2d');
      if (ctx) {  
        new Chart(ctx, {
          type: 'line',
          data: {
            labels: revenueData.value.map(item => item.date),
            datasets: [{
              label: 'Doanh thu',
              data: revenueData.value.map(item => item.revenue),
              borderColor: '#4caf50',
              fill: false,
              tension: 0.1
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: {
                position: 'top',
              },
              tooltip: {
                callbacks: {
                  label: function(tooltipItem) {
                    return `$${tooltipItem.raw}`;
                  }
                }
              }
            }
          }
        });
      }
    });
  }
});
</script>

<style scoped>
.content {
  padding: 20px;
}

h2 {
  font-size: 1.75rem;
  font-weight: bold;
  color: #333;
}

ul {
  list-style-type: none;
  padding-left: 0;
}

li {
  font-size: 1rem;
  color: #333;
  margin: 10px 0;
}

.text-gray-500 {
  color: #6b7280;
}

.text-red-500 {
  color: #ef4444;
}


canvas {
  max-width: 100%;
  height: auto;
}
</style>
