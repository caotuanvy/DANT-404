<template>
  <section class="content">
    <h2 class="section-title">Thống kê Doanh thu</h2>

    <div class="filters-panel">
      <h3 class="panel-title">Chọn thời gian thống kê:</h3>
      <div class="filter-controls">
        <div class="filter-item">
          <label for="filterType" class="filter-label">Lọc theo:</label>
          <select id="filterType" v-model="filterType" @change="updateFilterVisibility" class="filter-select">
            <option value="month">Tháng</option>
            <option value="week">Tuần</option>
            <option value="year">Năm</option>
          </select>
        </div>

        <div v-show="showYearInput" class="filter-item">
          <label for="year" class="filter-label">Năm:</label>
          <input type="number" id="year" v-model.number="selectedYear" min="2000" max="2100" class="filter-input">
        </div>

        <div v-show="showMonthInput" class="filter-item">
          <label for="month" class="filter-label">Tháng:</label>
          <input type="number" id="month" v-model.number="selectedMonth" min="1" max="12" class="filter-input">
        </div>

        <div v-show="showWeekInput" class="filter-item">
          <label for="week" class="filter-label">Tuần:</label>
          <input type="number" id="week" v-model.number="selectedWeek" min="1" max="53" class="filter-input">
        </div>

        <button @click="applyFilters" class="btn btn-primary">
          Áp dụng
        </button>
        <button @click="exportData" class="btn btn-success">
          Xuất
        </button>
      </div>
    </div>

    <div v-if="loadingOverall" class="loading-message">Đang tải số liệu tổng quan...</div>
    <div v-else-if="errorOverall" class="error-message">Lỗi khi tải số liệu tổng quan. Vui lòng thử lại.</div>
    <div v-else class="stats-grid">
      <div class="stat-card">
        <div>
          <p class="stat-label">Tổng doanh thu</p>
          <p class="stat-value">{{ formatCurrency(overallStats.totalOverallRevenue) }}</p>
          <p :class="overallStats.overallRevenueGrowth >= 0 ? 'growth-positive' : 'growth-negative'" class="stat-growth">
            <template v-if="overallStats.overallRevenueGrowth === null">
              (Mới)
            </template>
            <template v-else>
              {{ overallStats.overallRevenueGrowth >= 0 ? '↑' : '↓' }}
              {{ formatPercentage(overallStats.overallRevenueGrowth) }}% so với tháng trước
            </template>
          </p>
        </div>
        <div class="stat-icon stat-icon-blue">
          <i class="fas fa-dollar-sign stat-icon-size"></i>
        </div>
      </div>
      <div class="stat-card">
        <div>
          <p class="stat-label">Tổng đơn hàng</p>
          <p class="stat-value">{{ overallStats.totalOrders }}</p>
          <p :class="overallStats.orderCountGrowth >= 0 ? 'growth-positive' : 'growth-negative'" class="stat-growth">
            <template v-if="overallStats.orderCountGrowth === null">
              (Mới)
            </template>
            <template v-else>
              {{ overallStats.orderCountGrowth >= 0 ? '↑' : '↓' }}
              {{ formatPercentage(overallStats.orderCountGrowth) }}% so với tháng trước
            </template>
          </p>
        </div>
        <div class="stat-icon stat-icon-green">
          <i class="fas fa-shopping-cart stat-icon-size"></i>
        </div>
      </div>
      <div class="stat-card">
        <div>
          <p class="stat-label">Giá trị đơn hàng TB</p>
          <p class="stat-value">{{ formatCurrency(overallStats.avgOrderValue) }}</p>
          <p :class="overallStats.avgOrderValueGrowth >= 0 ? 'growth-positive' : 'growth-negative'" class="stat-growth">
            <template v-if="overallStats.avgOrderValueGrowth === null">
              (Mới)
            </template>
            <template v-else>
              {{ overallStats.avgOrderValueGrowth >= 0 ? '↑' : '↓' }}
              {{ formatPercentage(overallStats.avgOrderValueGrowth) }}% so với tháng trước
            </template>
          </p>
        </div>
        <div class="stat-icon stat-icon-purple">
          <i class="fas fa-chart-line stat-icon-size"></i>
        </div>
      </div>
      <div class="stat-card">
        <div>
          <p class="stat-label">Doanh thu tháng hiện tại</p>
          <p class="stat-value">{{ formatCurrency(overallStats.currentMonthRevenue) }}</p>
          <p :class="overallStats.currentMonthRevenueGrowth >= 0 ? 'growth-positive' : 'growth-negative'" class="stat-growth">
            <template v-if="overallStats.currentMonthRevenueGrowth === null">
              (Mới)
            </template>
            <template v-else>
              {{ overallStats.currentMonthRevenueGrowth >= 0 ? '↑' : '↓' }}
              {{ formatPercentage(overallStats.currentMonthRevenueGrowth) }}% so với tháng trước
            </template>
          </p>
        </div>
        <div class="stat-icon stat-icon-orange">
          <i class="fas fa-calendar-alt stat-icon-size"></i>
        </div>
      </div>
    </div>

    <div class="chart-panel">
      <h3 class="panel-title">Biểu đồ doanh thu theo {{ displayFilterType }}</h3>
      <div v-if="loadingRevenue" class="loading-message">Đang tải biểu đồ...</div>
      <div v-else-if="errorRevenue" class="error-message">Lỗi khi tải dữ liệu biểu đồ. Vui lòng thử lại.</div>
      <div v-else-if="revenueChartData.labels.length > 0 && !(revenueChartData.datasets[0].data.every(val => val === 0) && revenueChartData.datasets[1].data.every(val => val === 0))" class="chart-wrapper">
        <canvas ref="revenueChartCanvas" id="revenueChart"></canvas>
      </div>
      <div v-else class="no-data-message">
        Không có dữ liệu doanh thu hoặc đơn hàng để hiển thị biểu đồ cho khoảng thời gian đã chọn.
      </div>
    </div>

    <div class="detailed-table-panel">
      <h3 class="panel-title">Chi tiết doanh thu</h3>
      <div v-if="loadingRevenue" class="loading-message">Đang tải chi tiết doanh thu...</div>
      <div v-else-if="errorRevenue" class="error-message">Lỗi khi tải chi tiết doanh thu. Vui lòng thử lại.</div>
      <div v-else-if="detailedTableData.length === 0" class="no-data-message">
        Không có dữ liệu chi tiết doanh thu cho khoảng thời gian đã chọn.
      </div>
      <div v-else class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th scope="col" class="table-header">
                Thời gian
              </th>
              <th scope="col" class="table-header">
                Doanh thu
              </th>
              <th scope="col" class="table-header">
                Đơn hàng
              </th>
              <th scope="col" class="table-header">
                Tăng trưởng
              </th>
              <th scope="col" class="table-header">
                Hành động
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in detailedTableData" :key="index">
              <td class="table-cell table-cell-main">
                {{ item.label }}
              </td>
              <td class="table-cell">
                {{ formatCurrency(item.revenue) }}
              </td>
              <td class="table-cell">
                {{ item.orders }}
              </td>
              <td class="table-cell" :class="item.growth >= 0 ? 'growth-positive' : 'growth-negative'">
                <template v-if="item.growth === null">
                  (Mới)
                </template>
                <template v-else>
                  {{ item.growth >= 0 ? '↑' : '↓' }} {{ formatPercentage(item.growth) }}%
                </template>
              </td>
              <td class="table-cell table-cell-actions">
                <a href="#" class="action-link">
                  <i class="fas fa-eye"></i> </a>
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <th scope="col" class="table-footer-header">
                Tổng cộng
              </th>
              <th scope="col" class="table-footer-value">
                {{ formatCurrency(detailedTableData.reduce((sum, item) => sum + (item.revenue || 0), 0)) }}
              </th>
              <th scope="col" class="table-footer-value">
                {{ detailedTableData.reduce((sum, item) => sum + (item.orders || 0), 0) }}
              </th>
              <th colspan="2"></th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, computed, watch, nextTick } from 'vue';
import axios from 'axios';
import Chart from 'chart.js/auto';
import JSONToCSV from 'json-to-csv-export';

const overallStats = ref({
  totalOverallRevenue: 0,
  totalOrders: 0,
  avgOrderValue: 0,
  currentMonthRevenue: 0,
  overallRevenueGrowth: 0,
  orderCountGrowth: 0,
  avgOrderValueGrowth: 0,
  currentMonthRevenueGrowth: 0,
});
const loadingOverall = ref(true);
const errorOverall = ref(false);
const filterType = ref('month');
const selectedYear = ref(new Date().getFullYear());
const selectedMonth = ref(new Date().getMonth() + 1);
const selectedWeek = ref(getCurrentWeekNumber());

const revenueChartData = ref({
  labels: [],
  datasets: [
    {
      label: 'Doanh thu',
      data: [],
      backgroundColor: 'rgba(75, 192, 192, 0.6)',
      borderColor: 'rgba(75, 192, 192, 1)',
      borderWidth: 1,
      order: 1,
      yAxisID: 'y'
    },
    {
      label: 'Số đơn hàng',
      data: [],
      type: 'line',
      backgroundColor: 'rgba(153, 102, 255, 0.2)',
      borderColor: 'rgba(153, 102, 255, 1)',
      borderWidth: 2,
      fill: true,
      tension: 0.3,
      pointRadius: 3,
      pointBackgroundColor: 'rgba(153, 102, 255, 1)',
      order: 0,
      yAxisID: 'y1'
    }
  ]
});
const detailedTableData = ref([]);
const loadingRevenue = ref(true);
const errorRevenue = ref(false);
let chartInstance = null;

const revenueChartCanvas = ref(null);
const showYearInput = computed(() => filterType.value === 'month' || filterType.value === 'week' || filterType.value === 'year');
const showMonthInput = computed(() => filterType.value === 'month');
const showWeekInput = computed(() => filterType.value === 'week');

const displayFilterType = computed(() => {
  if (filterType.value === 'month') return 'tháng';
  if (filterType.value === 'week') return 'tuần';
  if (filterType.value === 'year') return 'năm';
  return '';
});

// --- Helper Functions ---
function formatCurrency(value) {
  if (value === null || value === undefined) return '0 ₫';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
}

function getCurrentWeekNumber() {
  const date = new Date();
  date.setHours(0, 0, 0, 0);
  date.setDate(date.getDate() + 3 - (date.getDay() + 6) % 7);
  const week1 = new Date(date.getFullYear(), 0, 4);
  return 1 + Math.round(((date.getTime() - week1.getTime()) / 86400000 - 3 + (week1.getDay() + 6) % 7) / 7);
}

function calculateGrowth(current, previous) {
  if (previous === 0) {
    return current > 0 ? null : 0; 
  }
  if (current === 0) return -100; 
  return ((current - previous) / previous) * 100;
}
function formatPercentage(value) {
  if (value === null || isNaN(value)) {
    return '(Mới)';
  }
  return parseFloat(value.toFixed(1));
}
async function fetchOverallStatistics() {
  loadingOverall.value = true;
  errorOverall.value = false;
  const token = localStorage.getItem('token');
  const headers = { Authorization: `Bearer ${token}` };

  try {
    const response = await axios.get('/analytics/overall', { headers });
    const data = response.data;
    overallStats.value = {
      totalOverallRevenue: data.totalOverallRevenue || 0,
      totalOrders: data.totalOrders || 0,
      avgOrderValue: data.avgOrderValue || 0,
      currentMonthRevenue: data.currentMonthRevenue || 0,
      overallRevenueGrowth: data.overallRevenueGrowth !== undefined ? data.overallRevenueGrowth : null, 
      orderCountGrowth: data.orderCountGrowth !== undefined ? data.orderCountGrowth : null,
      avgOrderValueGrowth: data.avgOrderValueGrowth !== undefined ? data.avgOrderValueGrowth : null,
      currentMonthRevenueGrowth: data.currentMonthRevenueGrowth !== undefined ? data.currentMonthRevenueGrowth : null,
    };
  } catch (e) {
    console.error('Lỗi khi tải số liệu tổng quan:', e);
    errorOverall.value = true;
  } finally {
    loadingOverall.value = false;
  }
}

async function fetchRevenueData() {
  loadingRevenue.value = true;
  errorRevenue.value = false;
  const token = localStorage.getItem('token');
  const headers = { Authorization: `Bearer ${token}` };

  let apiUrl = `/analytics/revenue?type=${filterType.value}&year=${selectedYear.value}`;
  if (filterType.value === 'month') {
    apiUrl += `&month=${selectedMonth.value}`;
  } else if (filterType.value === 'week') {
    apiUrl += `&week=${selectedWeek.value}`;
  }

  try {
    const response = await axios.get(apiUrl, { headers });
    const apiData = response.data.data;

    revenueChartData.value.labels = apiData.labels || [];
    revenueChartData.value.datasets[0].data = apiData.datasets[0]?.data || [];
    revenueChartData.value.datasets[1].data = apiData.datasets[1]?.data || [];

    detailedTableData.value = apiData.labels.map((label, index) => {
      const revenue = apiData.datasets[0]?.data[index] || 0;
      const orders = apiData.datasets[1]?.data[index] || 0;

      const previousRevenue = index > 0 ? (apiData.datasets[0]?.data[index - 1] || 0) : 0;
      const growth = calculateGrowth(revenue, previousRevenue); // Không gọi .toFixed(1) ở đây nữa

      return {
        label: label,
        revenue: revenue,
        orders: orders,
        growth: growth, // Lưu giá trị gốc, để formatPercentage xử lý sau
      };
    });

  } catch (e) {
    console.error('Lỗi khi tải dữ liệu doanh thu:', e);
    errorRevenue.value = true;
    revenueChartData.value.labels = [];
    revenueChartData.value.datasets[0].data = [];
    revenueChartData.value.datasets[1].data = [];
    detailedTableData.value = [];
  } finally {
    loadingRevenue.value = false;
  }
}

function updateChart() {
  const ctx = revenueChartCanvas.value;
  if (!ctx) {
    console.warn("Canvas element not found for chart initialization.");
    return;
  }

  if (chartInstance) {
    chartInstance.destroy();
  }

  if (revenueChartData.value.labels.length > 0 &&
      !(revenueChartData.value.datasets[0].data.every(val => val === 0) &&
        revenueChartData.value.datasets[1].data.every(val => val === 0))) {

    chartInstance = new Chart(ctx, {
      type: 'bar',
      data: revenueChartData.value,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            type: 'linear',
            display: true,
            position: 'left',
            beginAtZero: true,
            title: {
              display: true,
              text: 'Doanh thu (₫)'
            },
            ticks: {
              callback: function(value) {
                return formatCurrency(value);
              }
            }
          },
          y1: {
            type: 'linear',
            display: true,
            position: 'right',
            beginAtZero: true,
            title: {
              display: true,
              text: 'Số đơn hàng'
            },
            grid: {
              drawOnChartArea: false
            }
          }
        },
        plugins: {
          legend: {
            display: true,
            position: 'top',
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                let label = context.dataset.label || '';
                if (label) {
                  label += ': ';
                }
                if (context.parsed.y !== null) {
                  if (context.dataset.label === 'Doanh thu') {
                    label += formatCurrency(context.parsed.y);
                  } else if (context.dataset.label === 'Số đơn hàng') {
                    label += context.parsed.y + ' đơn';
                  } else {
                    label += context.parsed.y;
                  }
                }
                return label;
              }
            }
          }
        }
      }
    });
  } else {
    if (chartInstance) {
      chartInstance.destroy();
      chartInstance = null;
    }
  }
}

const applyFilters = () => {
  fetchRevenueData();
};

const exportData = () => {
  if (detailedTableData.value.length === 0) {
    alert('Không có dữ liệu để xuất.');
    return;
  }
  const dataToExport = detailedTableData.value.map(item => ({
    'Thời gian': item.label,
    'Doanh thu': item.revenue,
    'Số đơn hàng': item.orders,
    'Tăng trưởng (%)': item.growth === null ? 'N/A' : formatPercentage(item.growth), // Sử dụng formatPercentage
  }));
  const date = new Date();
  const fileName = `ThongKeDoanhThu_${filterType.value}_${selectedYear.value}${filterType.value === 'month' ? `_${selectedMonth.value}` : ''}${filterType.value === 'week' ? `_W${selectedWeek.value}` : ''}_${date.getFullYear()}${(date.getMonth() + 1).toString().padStart(2, '0')}${date.getDate().toString().padStart(2, '0')}.csv`;
  const csvExporter = new JSONToCSV({
    data: dataToExport,
    filename: fileName,
    delimiter: ',',
    quoteChar: '"',
  });
  csvExporter.export();

  alert('Dữ liệu đã được xuất thành công!');
};

// --- Lifecycle Hook và Watchers ---
onMounted(() => {
  fetchOverallStatistics();
  fetchRevenueData();
});

watch([filterType, selectedYear, selectedMonth, selectedWeek], () => {
  updateFilterVisibility();
  fetchRevenueData();
});

watch(revenueChartData, async (newData) => {
  await nextTick();
  updateChart();
}, { deep: true });

const updateFilterVisibility = () => {
  // Logic này có thể bị thiếu, bạn có thể thêm nếu cần điều khiển hiển thị riêng biệt
  // Ví dụ:
  // showMonthInput.value = filterType.value === 'month';
  // showWeekInput.value = filterType.value === 'week';
  // showYearInput.value = filterType.value === 'month' || filterType.value === 'week' || filterType.value === 'year';
};
</script>

<style scoped>
/* GENERAL STYLES */
.content {
  padding: 1.5rem;
  background-color: #f9fafb;
  flex-grow: 1;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #333;
}

.section-title {
  font-size: 2.25rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 2rem;
  text-align: center;
}

.panel-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 1rem;
  border-bottom: 1px solid #f0f0f0;
  padding-bottom: 1rem;
}

.filters-panel,
.chart-panel,
.detailed-table-panel {
  background-color: #ffffff;
  padding: 1.5rem;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  margin-bottom: 2rem;
  border: 1px solid #e0e0e0;
  box-sizing: border-box;
}

.filter-controls {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  gap: 1rem;
}

.filter-item {
}

.filter-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.25rem;
}

.filter-select,
.filter-input {
  margin-top: 0.25rem;
  display: block;
  width: 100%;
  padding-left: 0.75rem;
  padding-right: 2.5rem;
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
  font-size: 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  outline: none;
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.filter-select {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2016%2016%22%20fill%3D%22%234a4a4a%22%3E%3Cpath%20d%3D%22M7.247%2011.14L2.451%205.658C1.885%205.013%202.345%204%203.204%204h9.592c.859%200%201.319%201.013.753%201.658L8.753%2011.14c-.183.2-.497.2-.68%200z%22%2F%2F%3E%3C%2Fsvg%3E');
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 0.8rem 0.8rem;
}

.filter-select:focus,
.filter-input:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 1px #6366f1, 0 0 0 3px rgba(99, 102, 241, 0.5); /* focus:ring-indigo-500 with offset */
}

.btn {
  padding: 0.625rem 1.25rem;
  margin-top: auto;
  font-weight: 500;
  border-radius: 0.375rem;
  color: #ffffff;
  transition: background-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out; /* transition duration-150 ease-in-out */
  border: none;
  cursor: pointer;
  outline: none;
}

.btn:focus {
  box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.5), 0 0 0 4px rgba(99, 102, 241, 0.5); /* focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 */
}

.btn-primary {
  background-color: #4FC3F7;
}

.btn-primary:hover {
  background-color: #3b82f6;
}

.btn-primary:focus {
  box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.5), 0 0 0 4px rgba(99, 102, 241, 0.5); /* focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 */
}


.btn-success {
  background-color: #10b981;
}

.btn-success:hover {
  background-color: #059669;
}

.btn-success:focus {
  box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.5), 0 0 0 4px rgba(16, 185, 129, 0.5); /* focus:ring-2 focus:ring-offset-2 focus:ring-green-500 */
}

.loading-message,
.error-message,
.no-data-message {
  text-align: center;
  padding-top: 2rem;
  padding-bottom: 2rem;
  color: #4b5563;
}

.loading-message {
  padding-top: 1rem;
  padding-bottom: 1rem;
}

.error-message {
  color: #ef4444;
}


.stats-grid {
  display: grid;
  grid-template-columns: repeat(1, minmax(0, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}


@media (min-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

/* STAT CARD */
.stat-card {
  background-color: #ffffff;
  padding: 1.5rem;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); /* shadow-md */
  display: flex;
  align-items: center;
  justify-content: space-between;
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.stat-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
  margin-top: 0.25rem;
}

.stat-growth {
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.growth-positive {
  color: #10b981;
}

.growth-negative {
  color: #ef4444;
}

.stat-icon {
  padding: 0.75rem;
  border-radius: 9999px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.stat-icon-size {
  font-size: 1.25rem;
}

.stat-icon-blue {
  background-color: #e0f2fe;
  color: #2563eb;
}

.stat-icon-green {
  background-color: #d1fae5;
  color: #059669;
}

.stat-icon-purple {
  background-color: #ede9fe;
  color: #7c3aed;
}

.stat-icon-orange {
  background-color: #fff7ed;
  color: #ea580c;
}

.chart-wrapper {
  position: relative;
  height: 400px;
  width: 100%;
}

.chart-wrapper ::v-deep canvas#revenueChart {
  width: 100% !important;
  height: 100% !important;
  display: block;
}

.table-responsive {
  overflow-x: auto;
}

.data-table {
  min-width: 100%;
  border-collapse: collapse;
}

.data-table thead {
  background-color: #f9fafb;
}

.data-table thead th {
  padding: 0.75rem 1.5rem;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 500;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 2px solid #e5e7eb;
}

.data-table tbody {
  background-color: #ffffff;
}

.data-table tbody tr {
  border-bottom: 1px solid #e5e7eb;
}

.data-table tbody tr:nth-child(even) {
  background-color: #fcfcfc;
}

.table-cell {
  padding: 1rem 1.5rem;
  white-space: nowrap;
  font-size: 0.875rem;
  color: #6b7280;
}

.table-cell-main {
  font-weight: 500;
  color: #111827;
}

.table-cell-actions {
  text-align: right;
  font-weight: 500;
}

.action-link {
  color: #4f46e5;
  text-decoration: none;
  transition: color 0.15s ease-in-out;
}

.action-link:hover {
  color: #3730a3;
}

.data-table tfoot {
  background-color: #f9fafb;
  border-top: 2px solid #e0e0e0;
}

.table-footer-header {
  padding: 0.75rem 1.5rem;
  text-align: left;
  font-size: 1rem;
  font-weight: 700;
  color: #374151;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.table-footer-value {
  padding: 0.75rem 1.5rem;
  text-align: left;
  font-size: 1rem;
  font-weight: 700;
  color: #4f46e5;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

@media (max-width: 639px) {
  .filter-select,
  .filter-input {
    font-size: 0.875rem;
  }
}
</style>