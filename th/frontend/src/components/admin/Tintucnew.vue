<template>
  <div class="page-wrapper">
    <header class="page-header">
      <div>
        <h1 class="page-title">Danh sách tin tức</h1>
      </div>
      <router-link to="/admin/tintuc/add" class="btn-add">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
        <span>Thêm tin tức</span>
      </router-link>
    </header>

    <div class="content-card">
      <div class="row mb-3 g-2 filter-bar">
        <div class="col-md-6">
          <div class="search-input-wrapper">
            <input
              type="text"
              class="form-control search-input"
              placeholder="Tìm kiếm theo tiêu đề hoặc slug..."
              v-model="searchQuery"
            />
            <button
              v-if="searchQuery"
              class="clear-search-btn"
              @click="clearSearch"
              aria-label="Xóa tìm kiếm"
            >
              &times;
            </button>
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select" v-model="categoryFilter">
            <option value="">Tất cả danh mục</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.ten_danh_muc">{{ cat.ten_danh_muc }}</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select" v-model="statusFilter">
            <option value="">Tất cả trạng thái</option>
            <option value="1">Hiển thị</option>
            <option value="0">Ẩn</option>
          </select>
        </div>
      </div>

      <div class="table-container">
        <table class="news-table">
          <thead>
            <tr>
              <th class="w-12"><input type="checkbox"></th>
              <th>Tiêu đề</th>
              <th class="text-center">Hình ảnh</th>
              <th class="text-center">Danh mục</th>
              <th class="text-center">Ngày đăng</th>
              <th class="text-center">Nổi bật</th>
              <th class="text-center">Duyệt</th>
              <th class="text-center">Lượt xem</th>
              <th class="text-center">Trạng thái</th>
              <th class="text-center">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="10" class="text-center py-8">Đang tải dữ liệu...</td>
            </tr>
            <tr v-for="news in filteredNews" :key="news.id" class="table-row" :class="{'is-inactive-row': news.trang_thai != 1}">
              <td><input type="checkbox"></td>
              <td>
                <div class="news-title-cell">
                  <router-link :to="`/tintuc/${news.slug}`" class="news-title news-title-link">
                    {{ truncateText(news.tieude, 6) }}
                  </router-link>
                  <div class="news-slug">{{ truncateText(news.slug, 6) }}</div>
                </div>
              </td>
              <td class="text-center">
                <img
                  :src="getImageUrl(news.hinh_anh)"
                  :alt="news.tieude"
                  class="news-thumbnail"
                  v-if="news.hinh_anh"
                />
                <span v-else class="text-secondary text-sm">N/A</span>
              </td>
              <td class="text-center">
                <span v-if="news.danh_muc" class="badge">
                  {{ news.danh_muc.ten_danh_muc }}
                </span>
                <span v-else class="text-secondary text-sm">N/A</span>
              </td>
              <td class="text-center">{{ news.ngay_dang ? new Date(news.ngay_dang).toLocaleDateString() : 'N/A' }}</td>
              <td class="text-center">
                <button @click="toggleNoiBat(news)" class="toggle-switch" :class="{ 'is-active': news.noi_bat == 1 }">
                  <span class="toggle-circle"></span>
                </button>
              </td>
              <td class="text-center">
                <button @click="toggleDuyet(news)" class="toggle-switch" :class="{ 'is-active': news.duyet_tin_tuc == 1 }">
                  <span class="toggle-circle"></span>
                </button>
              </td>
              <td class="text-center">{{ news.luot_xem || 0 }}</td>
              <td class="text-center">
                <span class="status-badge" :class="news.trang_thai == 1 ? 'is-active' : 'is-inactive'">
                  {{ news.trang_thai == 1 ? 'Hiển thị' : 'Ẩn' }}
                </span>
              </td>
              <td class="text-center">
                <div class="action-buttons">
                  <router-link :to="`/tintuc/${news.slug}`" class="action-icon" title="Xem chi tiết">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z" /><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" /></svg>
                  </router-link>
                  <router-link :to="`/admin/tintuc/${news.id}/edit`" class="action-icon" title="Sửa">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
                  </router-link>
                  <button @click="toggleTrangThai(news)" class="action-icon" :class="news.trang_thai == 1 ? 'text-danger' : 'text-success'" :title="news.trang_thai == 1 ? 'Ẩn tin' : 'Hiện lại tin'">
                    <svg v-if="news.trang_thai == 1" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.367zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-if="!loading && filteredNews.length === 0" class="no-data-message">Không có tin tức nào phù hợp.</p>
      <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const newsList = ref([]);
const errorMessage = ref('');
const loading = ref(false);
const searchQuery = ref('');
const categoryFilter = ref('');
const statusFilter = ref('');
const categories = ref([]);

const clearSearch = () => {
  searchQuery.value = '';
};

const truncateText = (text, maxLength) => {
  if (!text) return '';
  if (text.length > maxLength) {
    return text.substring(0, maxLength) + '...';
  }
  return text;
};

const filteredNews = computed(() => {
  let currentNews = newsList.value;

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    currentNews = currentNews.filter(news => {
      const title = news.tieude ? String(news.tieude).toLowerCase() : '';
      const slug = news.slug ? String(news.slug).toLowerCase() : '';
      return title.includes(query) || slug.includes(query);
    });
  }

  if (categoryFilter.value !== '') {
    currentNews = currentNews.filter(news => {
      const ten1 = news.danh_muc && news.danh_muc.ten_danh_muc ? news.danh_muc.ten_danh_muc : '';
      const ten2 = news.ten_danh_muc ? news.ten_danh_muc : '';
      return ten1 === categoryFilter.value || ten2 === categoryFilter.value;
    });
  }

  if (statusFilter.value !== '') {
    currentNews = currentNews.filter(news => String(news.trang_thai) === statusFilter.value);
  }

  return currentNews;
});

const getNews = async () => {
  loading.value = true;
  errorMessage.value = '';
  try {
    const res = await axios.get('http://localhost:8000/api/tintuc', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    newsList.value = res.data;
    console.log('Tin tức:', newsList.value);
  } catch (error) {
    errorMessage.value = 'Lỗi khi lấy tin tức: ' + (error.response?.data?.message || error.message);
  } finally {
    loading.value = false;
  }
};

const getCategories = async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/danh-muc-tin-tuc');
    categories.value = res.data;
  } catch (error) {
    // Handle error if needed
  }
};

const toggleNoiBat = async (news) => {
  const oldStatus = news.noi_bat;
  const newStatus = oldStatus === 1 ? 0 : 1;
  news.noi_bat = newStatus;
  try {
    await axios.put(`http://localhost:8000/api/tintuc/${news.id}`, {
      noi_bat: newStatus,
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
  } catch (error) {
    news.noi_bat = oldStatus;
    alert('Cập nhật trạng thái nổi bật thất bại!');
  }
};

const toggleDuyet = async (news) => {
  const oldStatus = news.duyet_tin_tuc;
  const newStatus = oldStatus === 1 ? 0 : 1;
  news.duyet_tin_tuc = newStatus;
  try {
    await axios.put(`http://localhost:8000/api/tintuc/${news.id}`, {
      duyet_tin_tuc: newStatus,
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
  } catch (error) {
    news.duyet_tin_tuc = oldStatus;
    alert('Cập nhật trạng thái duyệt thất bại!');
  }
};

const toggleTrangThai = async (news) => {
  const oldStatus = news.trang_thai;
  const newStatus = oldStatus === 1 ? 0 : 1;
  const action = newStatus === 1 ? 'hiển thị' : 'vô hiệu hóa';
  if (!confirm(`Bạn có chắc muốn ${action} tin tức này?`)) return;
  news.trang_thai = newStatus;
  try {
    await axios.put(`http://localhost:8000/api/tintuc/${news.id}`, {
      trang_thai: newStatus,
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    alert(`Đã ${action} tin tức thành công!`);
  } catch (error) {
    news.trang_thai = oldStatus;
    alert('Thao tác thất bại!');
  }
};

const getImageUrl = (url) => {
  if (!url) return 'https://placehold.co/80x60?text=No+Image';
  return url.startsWith('http') ? url : `http://localhost:8000/storage/${url}`;
};

onMounted(() => {
  getNews();
  getCategories();
});
</script>

<style scoped>
.page-wrapper {
  background-color: #f3f4f6;
  padding: 2rem;
  min-height: 100vh;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}
.page-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: #111827;
}
.content-card {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  padding: 1.5rem;
}
.error-message {
  color: #dc2626;
  margin-top: 1rem;
}
.no-data-message {
  padding: 2rem;
  text-align: center;
  color: #6b7280;
}
.btn-add {
  background-color: #4FC3F7;
  color: white;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1rem;
  border: 1px solid transparent;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: bbackground-color 0.3s ease, transform 0.3s ease;
  width: 180px;
  height: 40px;
}
.btn-add svg {
  width: 20px;
  height: 20px;
}
.btn-add:hover {
  background-color:#3b82f6;
  transform: scale(1.05);
}
.search-input {
  width: 100%;
  max-width: 400px;
  padding: 0.6rem 1rem;
  border-radius: 5px !important;
  border: 1px solid gray !important;
  font-size: 0.875rem;
  padding-left: 2.5rem;
  min-width: 300px;
}
.filter-bar {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}
.search-input-wrapper {
  position: relative;
}
.clear-search-btn {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: transparent;
  border: none;
  font-size: 1.2rem;
  color: #888;
  cursor: pointer;
  padding: 0 6px;
}
.clear-search-btn:hover {
  color: #dc2626;
}
.table-container {
  overflow-x: auto;
}
.news-table {
  width: 100%;
  border-collapse: collapse;
  white-space: nowrap;
}
.news-table th, .news-table td {
  padding: 1rem;
  text-align: left;
  vertical-align: middle;
  border-bottom: 1px solid #e5e7eb;
}
.news-table th {
  background-color: #f9fafb;
  color: #6b7280;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
}
.news-table tbody tr:last-child td {
  border-bottom: none;
}
.w-12 { width: 3rem; }
.text-center { text-align: center; }
.py-8 { padding-top: 2rem; padding-bottom: 2rem; }
.news-title-cell {
  display: flex;
  flex-direction: column;
}
.news-title {
  font-weight: 500;
  color: #111827;
  text-decoration: none;
  /* Các thuộc tính CSS cho ellipsis đã được di chuyển xuống news-title-cell để tránh xung đột */
}
.news-title-link:hover {
  text-decoration: underline;
  color: #2563eb;
}
.news-slug {
  font-size: 0.85rem;
  color: #6b7280;
  /* Các thuộc tính CSS cho ellipsis đã được di chuyển xuống news-title-cell để tránh xung đột */
}
.news-thumbnail {
  width: 80px;
  height: 60px;
  border-radius: 4px;
  object-fit: cover;
  border: 1px solid #e5e7eb;
}
.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.8rem;
  font-weight: 500;
  background-color: #e0e7ff;
  color: #3730a3;
}
.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: capitalize;
}
.status-badge.is-active {
  color: #16a34a;
  background-color: #5ed389;
}
.status-badge.is-inactive {
  color: #6b7280;
  background-color: #e5e7eb;
}
.toggle-switch {
  position: relative;
  display: inline-block;
  width: 36px;
  height: 20px;
  background-color: #ccc;
  border-radius: 9999px;
  transition: background-color 0.2s;
  cursor: pointer;
  border: none;
  padding: 0;
}
.toggle-switch.is-active {
  background-color: #16a34a;
}
.toggle-circle {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 16px;
  height: 16px;
  background-color: white;
  border-radius: 50%;
  transition: transform 0.2s;
}
.toggle-switch.is-active .toggle-circle {
  transform: translateX(16px);
}
.action-buttons {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.25rem; /* Đã thay đổi khoảng cách ở đây */
  flex-wrap: nowrap;
}
.action-icon {
  width: 32px;
  height: 32px;
  padding: 0;
  background-color: transparent;
  border-radius: 50%;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s, transform 0.2s;
}
.action-icon svg {
  width: 20px;
  height: 20px;
  color: #111827;
  transition: color 0.2s;
}
.action-icon:hover {
  background-color: #e0e7ff;
  transform: scale(1.1);
}
.action-icon.text-danger:hover svg {
  color: #dc2626;
  background: none;
}
.action-icon.text-success:hover svg {
  color: #16a34a;
  background: none;
}
.is-inactive-row {
  background-color: #f3f4f6;
  opacity: 0.6;
} 
.is-inactive-row .news-title {
  color: #9ca3af;
}
</style>