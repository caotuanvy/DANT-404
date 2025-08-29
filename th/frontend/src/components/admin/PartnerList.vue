<template>
  <div class="page-wrapper">
    <div class="page-header">
      <div>
        <h1 class="page-title">Quản lý Đối tác</h1>
        <p class="page-subtitle">Quản lý và chỉnh sửa thông tin các đối tác.</p>
      </div>
      <button class="btn btn-add" @click="handleAddNewPartner">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Thêm Đối tác mới
      </button>
    </div>

    <div class="content-card">
      <div class="filter-bar">
        <div class="search-box">
          <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
          </svg>
          <input type="text" class="search-input" placeholder="Tìm kiếm theo tên đối tác..." v-model="searchQuery">
        </div>
      </div>

      <p v-if="loading" class="no-data-message">Đang tải dữ liệu...</p>
      <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>

      <div v-if="!loading && filteredPartners.length > 0" class="table-container">
        <table class="product-table">
          <thead>
            <tr>
              <th class="w-12 text-center">ID</th>
              <th>Tên Đối tác</th>
              <th>Thương hiệu</th>
              <th class="text-center">Số sản phẩm liên kết</th>
              <th class="text-center">Trạng thái</th>
              <th class="text-center">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="partner in filteredPartners" :key="partner.id">
              <td class="text-center" data-label="ID">{{ partner.id }}</td>
              <td data-label="Tên Đối tác">
                <div class="product-name-cell">
                  <span class="product-name">{{ partner.tenDoiTac }}</span>
                </div>
              </td>
              <td data-label="Thương hiệu">
                <div class="image-list-cell">
                  <img :src="partner.logo" :alt="partner.tenDoiTac" class="product-thumbnail">
                </div>
              </td>
              <td class="text-center" data-label="Số sản phẩm">{{ partner.products_count }}</td>
              <td data-label="Trạng thái">
                <button @click="toggleStatus(partner.id)" 
                        :class="['btn-status', partner.trang_thai === 1 ? 'is-active' : 'is-inactive']">
                  {{ partner.trang_thai === 1 ? 'Đang hiển thị' : 'Đã ẩn' }}
                </button>
              </td>
              <td data-label="Hành động">
                <div class="action-buttons">
                  <button class="action-icon" @click="handleEditPartner(partner.id)" title="Sửa">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
                  </button>
                  <button class="action-icon text-danger" @click="handleDeletePartner(partner.id)" title="Xóa">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-if="!loading && filteredPartners.length === 0" class="no-data-message">Không tìm thấy đối tác nào phù hợp.</p>
    </div>

    <div v-if="showModal" class="modal-overlay">
      <div class="modal-container">
        <h2 class="modal-title">{{ isEditing ? 'Chỉnh sửa Đối tác' : 'Thêm Đối tác mới' }}</h2>
        <form @submit.prevent="savePartner">
          <div class="form-group">
            <label for="partnerName">Tên Đối tác:</label>
            <input
              type="text"
              id="partnerName"
              v-model="currentPartner.tenDoiTac"
              required
            >
          </div>
          <div class="form-group">
            <label for="partnerLogo">URL Logo:</label>
            <input type="url" id="partnerLogo" v-model="currentPartner.logo" required>
          </div>
          <div class="modal-actions">
            <button type="button" class="btn btn-secondary" @click="closeModal">Hủy</button>
            <button type="submit" class="btn btn-primary">{{ isEditing ? 'Lưu' : 'Thêm' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const partners = ref([]);
const loading = ref(true);
const errorMessage = ref('');
const searchQuery = ref('');
const showModal = ref(false);
const isEditing = ref(false);
const currentPartner = ref({ id: null, tenDoiTac: '', logo: '' });

const fetchPartners = async () => {
  loading.value = true;
  errorMessage.value = '';
  try {
    const response = await axios.get('/partners', {
      params: { search: searchQuery.value }
    });
    partners.value = response.data;
  } catch (error) {
    errorMessage.value = 'Không thể tải dữ liệu đối tác. Vui lòng thử lại sau.';
    console.error('Lỗi khi tải đối tác:', error);
    Swal.fire({
      icon: 'error',
      title: 'Lỗi!',
      text: 'Không thể tải dữ liệu đối tác. Vui lòng thử lại sau.'
    });
  } finally {
    loading.value = false;
  }
};

const handleAddNewPartner = () => {
  isEditing.value = false;
  currentPartner.value = { id: null, tenDoiTac: '', logo: '' };
  showModal.value = true;
};

const handleEditPartner = async (id) => {
  try {
    const response = await axios.get(`/partners/${id}`);
    currentPartner.value = {
      id: response.data.id,
      tenDoiTac: response.data.tenDoiTac,
      logo: response.data.logo
    };
    isEditing.value = true;
    showModal.value = true;
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Lỗi!',
      text: 'Không thể tải thông tin đối tác để chỉnh sửa.'
    });
    console.error('Lỗi khi tải thông tin đối tác:', error);
  }
};

const savePartner = async () => {
  try {
    if (isEditing.value) {
      console.log("Dữ liệu gửi đi:", currentPartner.value);
      await axios.put(`/partners/${currentPartner.value.id}`, {
        tenDoiTac: currentPartner.value.tenDoiTac,
        logo: currentPartner.value.logo
      });
      Swal.fire({
        icon: 'success',
        title: 'Thành công!',
        text: 'Cập nhật đối tác thành công!'
      });
    } else {
      await axios.post('/partners', {
        tenDoiTac: currentPartner.value.tenDoiTac, 
        logo: currentPartner.value.logo 
      });
      Swal.fire({
        icon: 'success',
        title: 'Thành công!',
        text: 'Thêm đối tác thành công!'
      });
    }
    
    closeModal();
    fetchPartners();
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Lỗi!',
      text: 'Lỗi khi lưu đối tác. Vui lòng kiểm tra dữ liệu.'
    });
    console.error('Lỗi khi lưu đối tác:', error);
  }
};

const handleDeletePartner = async (id) => {
  const result = await Swal.fire({
    title: 'Bạn có chắc chắn?',
    text: `Bạn sẽ không thể hoàn tác hành động xóa đối tác có ID "${id}"!`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Vâng, xóa đi!',
    cancelButtonText: 'Hủy'
  });

  if (result.isConfirmed) {
    try {
      await axios.delete(`/partners/${id}`);
      Swal.fire({
        title: 'Đã xóa!',
        text: 'Đối tác đã được xóa thành công.',
        icon: 'success'
      });
      fetchPartners();
    } catch (error) {
      Swal.fire({
        title: 'Lỗi!',
        text: 'Lỗi khi xóa đối tác. Vui lòng thử lại.',
        icon: 'error'
      });
      console.error('Lỗi khi xóa đối tác:', error);
    }
  }
};

const closeModal = () => {
  showModal.value = false;
};

const toggleStatus = async (id) => {
  try {
    await axios.patch(`/partners/${id}/status`);

    await fetchPartners();

    Swal.fire({
      title: 'Cập nhật trạng thái thành công!',
      text: 'Thành Công',
      icon: 'success'
    });
    
  } catch (error) {
    Swal.fire({
      title: 'Lỗi!',
      text: 'Không thể cập nhật trạng thái. Vui lòng thử lại.',
      icon: 'error'
    });
    console.error("Lỗi khi cập nhật trạng thái:", error);
  }
};

const filteredPartners = computed(() => {
  const query = searchQuery.value.toLowerCase();
  return partners.value.filter(partner => 
    partner.tenDoiTac.toLowerCase().includes(query)
  );
});

onMounted(() => {
  fetchPartners();
});

watch(searchQuery, () => {
  fetchPartners();
});
</script>

<style scoped>
/* Giữ nguyên các style đã có và thêm style cho nút trạng thái */

.btn-status {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.2s ease;
}

.btn-status.is-active {
  background-color: #d1fae5; /* Màu nền xanh nhạt */
  color: #065f46; /* Màu chữ xanh đậm */
}

.btn-status.is-inactive {
  background-color: #fee2e2; /* Màu nền đỏ nhạt */
  color: #991b1b; /* Màu chữ đỏ đậm */
}

/* Các style khác giữ nguyên */
.page-wrapper {
  background-color: var(--color-bg-page);
  margin-left: -0px !important;
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
  color: var(--color-text-primary);
}
.page-subtitle {
  color: var(--color-text-secondary);
  margin-top: 0.25rem;
}
.content-card {
  background-color: var(--color-bg-card);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 1.5rem;
}
.error-message {
  color: var(--color-red);
  margin-top: 1rem;
}
.no-data-message {
  padding: 2rem;
  text-align: center;
  color: var(--color-text-secondary);
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1rem;
  border: 1px solid transparent;
  border-radius: var(--radius);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}
.btn-add {
  background-color: #4FC3F7;
  color: white;
  transition: background-color 0.3s ease, transform 0.3s ease;
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
}
.btn-secondary {
  background-color: var(--color-bg-card);
  color: var(--color-gray);
  border-color: var(--color-border);
}
.btn-secondary:hover {
  background-color: #f9fafb;
}

.filter-bar {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}
.search-box {
  position: relative;
}
.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  width: 1.25rem;
  height: 1.25rem;
  color: var(--color-text-tertiary);
}
.search-input, .filter-select {
  border: 1px solid var(--color-border);
  border-radius: var(--radius);
  padding: 0.6rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.search-input {
  padding-left: 2.5rem;
  min-width: 300px;
}
.search-input:focus, .filter-select:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-primary) 20%, transparent);
}

.table-container {
  overflow-x: auto;
}
.product-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
}
.product-table th, .product-table td {
  padding: 1rem;
  text-align: left;
  vertical-align: middle;
  border-bottom: 1px solid rgba(209, 203, 203, 0.373) !important;
}
.product-table th {
  background-color: #f9fafb;
  color: var(--color-text-secondary);
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
}
.product-table tbody tr:last-child td {
  border-bottom: none;
}
.w-12 { width: 3rem; }
.text-center { text-align: center; }
.py-8 { padding-top: 2rem; padding-bottom: 2rem; }
.font-medium { font-weight: 500; }
.text-red-600 { color: var(--color-red); }
.text-sm { font-size: 0.875rem; }

.product-name-cell {
  display: flex;
  flex-direction: column;
}
.product-name {
  font-weight: 500;
  color: var(--color-text-primary);
  text-decoration: none;
}
.product-variant-count {
  font-size: 0.875rem;
  color: var(--color-text-secondary);
}

.image-list-cell {
  display: flex;
  gap: 5px;
  flex-wrap: wrap;
  justify-content: center;
  max-width: 140px;
}
.product-thumbnail {
  width: 40px;
  height: 40px;
  border-radius: 4px;
  object-fit: cover;
  border: 1px solid var(--color-border);
}
.description-cell {
  white-space: normal;
  max-width: 250px;
  min-width: 150px;
  overflow: hidden;
  text-overflow: ellipsis;
  color: var(--color-text-secondary);
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
  font-weight: 500;
  text-transform: capitalize;
}
.status-badge.is-active {
  color: var(--color-green);
  background-color: #5ed389;
}
.status-badge.is-inactive {
  color: var(--color-text-secondary);
  background-color: var(--color-border);
}
.is-inactive-row {
  opacity: 0.6;
  background-color: #fafafa;
}
.is-inactive-row .product-name {
  text-decoration: line-through;
}

.action-buttons {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.75rem;
}
.action-icon {
  width: 20px;
  padding: 0;
  background-color: var(--color-text-primary);
  border-radius: 50%;
  border: none;
  cursor: pointer;
  transition: background-color 0.2s, transform 0.2s;
}
.action-icon svg {
  width: 20px;
  height: 20px;
  color: rgb(0, 0, 0);
}
.action-icon:hover {
  background-color: var(--color-primary);
  transform: scale(1.1);
}
.action-icon.text-danger:hover {
  background-color: var(--color-red);
}
.action-icon.text-success:hover {
  background-color: var(--color-green);
}
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start; 
    gap: 1rem; 
    margin-bottom: 1.5rem;
  }

  .page-title {
    font-size: 1.5rem;
  }
  .btn-add{
    width: 100%;
    justify-content: center;
  }

  .filter-bar {
    flex-direction: column;
  }

  .search-box, .search-input {
    width: 100%;
    min-width: unset;
    max-width: unset;
  }

  .table-container {
    overflow-x: hidden; 
  }

  .product-table thead {
    display: none; 
  }

  .product-table tbody, .product-table tr, .product-table td {
    display: block; 
    width: 100% !important;
  }

  .product-table tr {
    margin-bottom: 1.5rem;
    border: 1px solid #e5e7eb;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 0;
  }

  .product-table td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f3f4f6;
    text-align: right; 
    position: relative;
    white-space: normal;
  }

  .product-table td:last-child {
    border-bottom: none;
  }

  .product-table td::before {
    content: attr(data-label); 
    position: absolute;
    left: 1rem;
    font-weight: 600;
    color: var(--color-text-primary);
  }

  .product-table td[data-label="Chọn"],
  .product-table td[data-label="Ghim"] {
    display: flex;
    justify-content: flex-end;
  }

  .product-table td[data-label="Tên sản phẩm"] {
    background-color: #f9fafb;
    font-size: 1.1rem;
    text-align: left; 
  }
  
  .product-table td[data-label="Tên sản phẩm"]::before {
    display: none;
  }
  
  .image-list-cell {
    justify-content: flex-end; 
  }

  .action-buttons {
    justify-content: flex-end;
  }
}
</style>