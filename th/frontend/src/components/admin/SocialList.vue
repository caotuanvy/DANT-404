<template>
  <div class="page-wrapper">
    <header class="page-header">
      <div>
        <h1 class="page-title">Quản lý Mạng Xã Hội</h1>
      </div>
      <button @click="showAddForm" class="btn-add">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        <span>Thêm Mạng Xã Hội</span>
      </button>
    </header>

    <div v-if="showForm" class="content-card form-card">
      <h2 class="form-title">{{ isEditing ? 'Chỉnh sửa Mạng xã hội' : 'Thêm Mạng xã hội mới' }}</h2>
      <form @submit.prevent="saveLink">
        <div class="form-grid">
          <div class="form-group">
            <label for="social-select">Chọn Mạng xã hội</label>
            <select id="social-select" class="form-input" v-model="selectedSocial" required>
              <option :value="null" disabled>-- Vui lòng chọn --</option>
              <option v-for="social in socialNetworks" :key="social.name" :value="social">
                {{ social.name }}
              </option>
            </select>
          </div>
          
          <template v-if="selectedSocial && selectedSocial.name === 'Khác...'">
            <div class="form-group">
                <label for="name-manual">Tên Mạng xã hội</label>
                <input id="name-manual" type="text" class="form-input" v-model="currentLink.Ten_mang_xa_hoi" placeholder="Nhập tên MXH" required>
            </div>
            <div class="form-group">
                <label for="icon-manual">Tên Icon (Font Awesome)</label>
                <input id="icon-manual" type="text" class="form-input" v-model="currentLink.Icon" placeholder="Ví dụ: wechat, telegram" required>
            </div>
          </template>
          
          <div class="form-group">
            <label for="url">Đường dẫn (URL)</label>
            <input id="url" type="url" class="form-input" v-model="currentLink.Link_mang_xa_hoi" placeholder="https://www.facebook.com/your-page" required>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-submit">{{ isEditing ? 'Cập nhật' : 'Lưu' }}</button>
          <button type="button" @click="cancelForm" class="btn-cancel">Hủy</button>
        </div>
      </form>
    </div>


    <div class="content-card">
      <div class="filter-bar">
        <div class="search-box">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input type="text" v-model="searchQuery" placeholder="Tìm kiếm mạng xã hội..." class="search-input">
        </div>
      </div>

      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th class="w-12"><input type="checkbox"></th>
              <th>Tên Mạng Xã Hội</th>
              <th class="text-center">Icon</th>
              <th>Đường dẫn</th>
              <th class="text-center">Trạng thái</th>
              <th class="text-center">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="6" class="text-center py-8">Đang tải dữ liệu...</td>
            </tr>
            <tr v-else-if="filteredLinks.length === 0">
              <td colspan="6" class="text-center py-8">Không tìm thấy dữ liệu.</td>
            </tr>
            <tr v-for="link in filteredLinks" :key="link.Mang_Xa_Hoi_id" class="table-row" :class="{'is-inactive-row': !link.is_active}">
              <td data-label="Chọn"><input type="checkbox"></td>
              <td data-label="Tên MXH" class="font-medium">
                {{ link.Ten_mang_xa_hoi }}
              </td>
              <td data-label="Icon" class="text-center">
                  <i v-if="link.Icon" :class="['fab', `fa-${link.Icon}`]" class="icon-preview"></i>
              </td>
              <td data-label="Đường dẫn">
                <a :href="link.Link_mang_xa_hoi" target="_blank" rel="noopener noreferrer" class="link-url">
                  {{ link.Link_mang_xa_hoi }}
                </a>
              </td>
              <td data-label="Trạng thái" class="text-center">
                <button @click="toggleStatus(link)" class="toggle-switch" :class="{ 'is-active': link.is_active }" :title="link.is_active ? 'Đang kích hoạt. Nhấn để ẩn.' : 'Đang ẩn. Nhấn để kích hoạt.'">
                  <span class="toggle-circle"></span>
                </button>
              </td>
              <td data-label="Hành động">
                <div class="action-buttons">
                  <button @click="showEditForm(link)" class="action-icon" title="Sửa">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
                  </button>
                  <button @click="deleteLink(link.Mang_Xa_Hoi_id)" class="action-icon text-danger" title="Xóa">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
       <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
const links = ref([]);
const errorMessage = ref('');
const loading = ref(true);
const searchQuery = ref('');

const showForm = ref(false);
const isEditing = ref(false);
const initialFormState = {
  Mang_Xa_Hoi_id: null,
  Ten_mang_xa_hoi: '',
  Link_mang_xa_hoi: '',
  Icon: '',
  is_active: true,
};
const currentLink = ref({ ...initialFormState });
const socialNetworks = ref([
  { name: 'Facebook', icon: 'facebook-f' },
  { name: 'Instagram', icon: 'instagram' },
  { name: 'TikTok', icon: 'tiktok' },
  { name: 'YouTube', icon: 'youtube' },
  { name: 'Zalo', icon: 'zalo' }, 
  { name: 'Twitter', icon: 'twitter' },
  { name: 'LinkedIn', icon: 'linkedin-in' },
  { name: 'GitHub', icon: 'github' },
  { name: 'Khác...', icon: '' } 
]);

const selectedSocial = ref(null);


watch(selectedSocial, (newSelection) => {
  if (newSelection) {
    
    if (newSelection.name === 'Khác...') {
      currentLink.value.Ten_mang_xa_hoi = '';
      currentLink.value.Icon = '';
    } else {
      currentLink.value.Ten_mang_xa_hoi = newSelection.name;
      currentLink.value.Icon = newSelection.icon;
    }
  }
});
const filteredLinks = computed(() => {
  if (!searchQuery.value) return links.value;
  const query = searchQuery.value.toLowerCase();
  return links.value.filter(link =>
    link.Ten_mang_xa_hoi.toLowerCase().includes(query) ||
    link.Link_mang_xa_hoi.toLowerCase().includes(query)
  );
});

const API_BASE_URL = 'https://api.sieuthi404.io.vn/api/admin/social-links'; 
const getAuthHeaders = () => ({
  headers: {
    Authorization: `Bearer ${localStorage.getItem('token')}`,
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

const getLinks = async () => {
  loading.value = true;
  errorMessage.value = '';
  try {
    const res = await axios.get(API_BASE_URL, getAuthHeaders());
    links.value = res.data.map(link => ({ ...link, is_active: !!link.is_active }));
  } catch (error) {
    console.error('Lỗi khi lấy danh sách:', error);
    errorMessage.value = 'Không thể tải dữ liệu. Vui lòng kiểm tra lại đường truyền hoặc token.';
  } finally {
    loading.value = false;
  }
};

const saveLink = async () => {
  if (selectedSocial.value && selectedSocial.value.name !== 'Khác...') {
    currentLink.value.Icon = selectedSocial.value.icon;
  }
  const dataToSend = { ...currentLink.value, is_active: currentLink.value.is_active ? 1 : 0 };

  try {
    const action = isEditing.value ? 'Cập nhật' : 'Thêm mới';
    if (isEditing.value) {
      await axios.put(`${API_BASE_URL}/${currentLink.value.Mang_Xa_Hoi_id}`, dataToSend, getAuthHeaders());
    } else {
      await axios.post(API_BASE_URL, dataToSend, getAuthHeaders());
    }
    await getLinks();
    cancelForm();

    // Thông báo thành công
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'success',
      title: `${action} thành công!`,
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });

  } catch (error) {
    console.error('Lỗi khi lưu:', error.response?.data || error.message);
    // Thông báo lỗi
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'error',
      title: 'Thao tác thất bại! Vui lòng kiểm tra lại.',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });
  }
};


const deleteLink = (id) => {
  Swal.fire({
    title: 'Bạn có chắc chắn không?',
    text: "Bạn sẽ không thể hoàn tác hành động này!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Đồng ý, xóa nó!',
    cancelButtonText: 'Hủy'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.delete(`${API_BASE_URL}/${id}`, getAuthHeaders());
        await getLinks();
      
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Đã xóa liên kết thành công!',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });

      } catch (error) {
        console.error('Lỗi khi xóa:', error);
        

        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'error',
          title: 'Xóa thất bại!',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });
      }
    }
  });
};

const toggleStatus = async (link) => {
  const newStatus = !link.is_active;
  link.is_active = newStatus; 

  try {
    await axios.patch(`${API_BASE_URL}/${link.Mang_Xa_Hoi_id}/status`, 
      { is_active: newStatus ? 1 : 0 }, 
      getAuthHeaders()
    );
  } catch (error) {
    link.is_active = !newStatus; 
    console.error('Lỗi khi cập nhật trạng thái:', error);
    alert('Cập nhật trạng thái thất bại!');
  }
};

const showAddForm = () => {
  isEditing.value = false;
  currentLink.value = { ...initialFormState };
  selectedSocial.value = null; 
  showForm.value = true;
};

const showEditForm = (link) => {
  isEditing.value = true;
  currentLink.value = { ...link, is_active: !!link.is_active }; 
  selectedSocial.value = socialNetworks.value.find(s => s.name === link.Ten_mang_xa_hoi) 
                        || socialNetworks.value.find(s => s.name === 'Khác...');
  showForm.value = true;
};

const cancelForm = () => {
  showForm.value = false;
};

onMounted(getLinks);
</script>

<style scoped>
/* Các biến màu và style chung */
:root {
  --color-primary: #4FC3F7;
  --color-primary-hover: #039BE5;
  --color-bg-page: #f3f4f6;
  --color-bg-card: #ffffff;
  --color-border: #d1d5db;
  --color-text-primary: #111827;
  --color-text-secondary: #6b7280;
  --color-green: #16a34a;
  --color-red: #dc2626;
  --color-gray: #4b5563;
  --radius: 8px;
  --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
}

.page-wrapper {
  background-color: var(--color-bg-page);
  padding: 2rem;
 
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
.content-card {
  background-color: var(--color-bg-card);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 1.5rem;
  margin-bottom: 2rem;
}
.error-message { color: var(--color-red); margin-top: 1rem; }
.no-data-message { padding: 2rem; text-align: center; color: var(--color-text-secondary); }

/* Nút Thêm Mới */
.btn-add {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.6rem 1rem;
  border-radius: var(--radius);
  font-weight: 500;
  cursor: pointer;
  background-color: #4FC3F7;
  color: white;
  transition: background-color 0.3s ease, transform 0.3s ease;
  border: none;
}
.btn-add svg { width: 20px; height: 20px; }
.btn-add:hover { background-color:#3b82f6; transform: scale(1.05); }

/* Thanh tìm kiếm */
.filter-bar { display: flex; justify-content: space-between; margin-bottom: 1.5rem; }
.search-box { position: relative; }
.search-icon {
  position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%);
  width: 1.25rem; height: 1.25rem; color: #9ca3af;
}
.search-input {
  border: 1px solid var(--color-border);
  border-radius: var(--radius);
  padding: 0.6rem 0.6rem 0.6rem 2.5rem;
  transition: border-color 0.2s, box-shadow 0.2s;
  min-width: 300px;
}
.search-input:focus {
  outline: none; border-color: var(--color-primary);
  box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-primary) 20%, transparent);
}

/* Bảng dữ liệu */
.table-container { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; white-space: nowrap; }
.data-table th, .data-table td { padding: 1rem; text-align: left; vertical-align: middle; border-bottom: 1px solid #f3f4f6; }
.data-table th {
  background-color: #f9fafb; color: var(--color-text-secondary);
  font-size: 0.75rem; text-transform: uppercase; font-weight: 600;
}
.w-12 { width: 3rem; }
.text-center { text-align: center; }
.py-8 { padding-top: 2rem; padding-bottom: 2rem; }
.font-medium { font-weight: 500; }
.icon-preview { font-size: 1.5rem; color: var(--color-text-primary); }
.link-url {
  color: var(--color-primary-hover); text-decoration: none;
  white-space: normal; word-break: break-all;
}
.link-url:hover { text-decoration: underline; }

/* Nút gạt trạng thái */
.toggle-switch {
  position: relative; display: inline-block;
  width: 40px; height: 22px; background-color: #ccc;
  border-radius: 9999px; transition: background-color 0.2s;
  cursor: pointer; border: none; padding: 0;
}
.toggle-switch.is-active { background-color: #16a34a }
.toggle-circle {
  position: absolute; top: 2px; left: 2px;
  width: 18px; height: 18px; background-color: white;
  border-radius: 50%; transition: transform 0.2s;
}
.toggle-switch.is-active .toggle-circle { transform: translateX(18px); }
.is-inactive-row { opacity: 0.6; background-color: #fafafa; }
.is-inactive-row .link-url { text-decoration: line-through; }


.action-buttons { display: flex; justify-content: center; align-items: center; gap: 0.75rem; }
.action-icon {
  display: flex; align-items: center; justify-content: center;
  width: 32px; height: 32px; padding: 0;
  background-color: #f3f4f6; border-radius: 50%; border: none;
  cursor: pointer; transition: background-color 0.2s, transform 0.2s;
}
.action-icon svg { width: 18px; height: 18px; color: var(--color-gray); }
.action-icon:hover { background-color: var(--color-primary); transform: scale(1.1); }
.action-icon:hover svg { color: #007bff }
.action-icon.text-danger:hover { background-color: var(--color-red); }

/* Form Styles */
.form-card { margin-bottom: 2rem; }
.form-title { font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem; }
.form-grid { display: grid; grid-template-columns: repeat(1, 1fr); gap: 1.5rem; }
@media (min-width: 768px) { .form-grid { grid-template-columns: repeat(3, 1fr); } }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 0.875rem; }
.form-input, .form-group select {
  width: 100%; padding: 0.6rem 0.75rem; border: 1px solid gray;
  border-radius: var(--radius); background-color: white;
}
.form-actions { margin-top: 1.5rem; display: flex; gap: 1rem; }
.btn-submit {
  background-color: #007bff; padding: 0.6rem 1.5rem;
  border-radius: var(--radius); border: none; cursor: pointer;
}
.btn-cancel {
  background-color: var(--color-bg-card); color: var(--color-gray); border: 1px solid var(--color-border);
  padding: 0.6rem 1.5rem; border-radius: var(--radius); cursor: pointer;
}

/* Responsive cho bảng */
@media (max-width: 768px) {
  .page-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
  .btn-add { width: 100%; }
  .search-box, .search-input { width: 100%; min-width: unset; }
  .data-table thead { display: none; }
  .data-table tbody, .data-table tr, .data-table td { display: block; width: 100% !important; }
  .data-table tr { margin-bottom: 1.5rem; border: 1px solid #e5e7eb; border-radius: var(--radius); }
  .data-table td {
    padding: 0.75rem 1rem; border-bottom: 1px solid #f3f4f6;
    text-align: right; position: relative; white-space: normal;
  }
  .data-table td:last-child { border-bottom: none; }
  .data-table td::before {
    content: attr(data-label); position: absolute; left: 1rem;
    font-weight: 600; color: var(--color-text-primary);
  }
  .action-buttons, .data-table td[data-label="Icon"], .data-table td[data-label="Trạng thái"], .data-table td[data-label="Chọn"] {
    display: flex; justify-content: flex-end; align-items: center;
  }
}
</style>