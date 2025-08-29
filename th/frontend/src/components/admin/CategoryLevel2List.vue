<template>
  <div class="page-wrapper">
    <header class="page-header">
      <div>
        <h1 class="page-title">Danh mục cấp 2</h1>
      </div>
      <!-- Thay nút này -->
      <!-- <button @click="showAdd = true" class="btn-add">
        <span>+ Thêm danh mục cấp 2</span>
      </button> -->

      <!-- Bằng router-link như sau -->
      <router-link to="/admin/danh-muc-cap-2/add" class="btn-add">
        <span>+ Thêm danh mục cấp 2</span>
      </router-link>
    </header>

    <div class="content-card">
      <div class="table-container">
        <table class="danhmuc-table">
          <thead>
            <tr>
              <th>Tên danh mục</th>
              <th>Mô tả</th>
              <th>Trạng thái</th>
              <th class="text-center">Hình ảnh</th> 
              <th class="text-center">Hành động</th>
              
            </tr>
          </thead>
          <tbody>
            <tr v-for="cat in categories" :key="cat.id" :class="{ 'is-inactive-row': cat.trang_thai == 0 }">
              <td>
                <span v-if="editId !== cat.id" class="danhmuc-title">{{ cat.ten_danh_muc }}</span>
                <input v-else v-model="editName" class="form-control form-control-sm" />
              </td>
              <td>
                <span v-if="editId !== cat.id" class="danhmuc-description">{{ cat.mo_ta }}</span>
                <input v-else v-model="editDesc" class="form-control form-control-sm" />
              </td>
              <td>
                <span :class="cat.trang_thai == 1 ? 'status-badge is-active' : 'status-badge is-inactive'">
                  {{ cat.trang_thai == 1 ? 'Hiển thị' : 'Ẩn' }}
                </span>
              </td>
              <td class="text-center">
                <img
                  :src="cat.image_url"
                  :alt="cat.ten_danh_muc"
                  class="danhmuc-thumbnail"
                />
              </td>
              <td class="text-center">
                <div class="action-buttons">
                  <!-- Xem danh mục con -->
                  <button class="action-icon" @click="viewChildren(cat.id)" title="Xem danh mục con">
                    <i class="bi bi-diagram-2"></i>
                  </button>
                  <!-- Sửa -->
                  <router-link
                    v-if="editId !== cat.id"
                    :to="`/admin/danh-muc-cap-2/edit/${cat.id}`"
                    class="action-icon"
                    title="Sửa"
                  >
                    <i class="bi bi-pencil"></i>
                  </router-link>
                  <button v-else class="action-icon text-success" @click="saveEdit(cat.id)" title="Lưu">
                    <i class="bi bi-check"></i>
                  </button>
                  <button v-if="editId === cat.id" class="action-icon" @click="cancelEdit" title="Hủy">
                    <i class="bi bi-x"></i>
                  </button>
                  <!-- Ẩn/hiện -->
                  <button
                    :class="cat.trang_thai == 1 ? 'action-icon text-danger' : 'action-icon text-success'"
                    @click="toggleStatus(cat.id)"
                    :title="cat.trang_thai == 1 ? 'Ẩn' : 'Hiển thị lại'"
                  >
                    <i :class="cat.trang_thai == 1 ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                  </button>
                </div>
              </td>
              
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <!-- Form thêm mới -->
    <div v-if="showAdd" class="mb-3">
      <input v-model="newName" placeholder="Tên danh mục" class="form-control mb-1" />
      <input v-model="newDesc" placeholder="Mô tả" class="form-control mb-1" />
      <button @click="addCategory" class="btn btn-success btn-sm">Lưu</button>
      <button @click="showAdd = false" class="btn btn-secondary btn-sm">Hủy</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const categories = ref([]);
const showAdd = ref(false);
const newName = ref('');
const newDesc = ref('');
const editId = ref(null);
const editName = ref('');
const editDesc = ref('');
const router = useRouter();

const getCategories = async () => {
  const res = await axios.get('https://api.sieuthi404.io.vn/api/danh-muc-cha');
  categories.value = res.data;
};

const addCategory = async () => {
  if (!newName.value) {
    alert('Vui lòng nhập tên danh mục!');
    return;
  }
  await axios.post('https://api.sieuthi404.io.vn/api/danh-muc-cha', {
    ten_danh_muc: newName.value,
    mo_ta: newDesc.value,
  });
  newName.value = '';
  newDesc.value = '';
  showAdd.value = false;
  getCategories();
};

// Xem danh mục con (chuyển sang trang children của danh mục này)
const viewChildren = (id) => {
  router.push(`/admin/categories/${id}/children`);
};

// Sửa
const startEdit = (cat) => {
  editId.value = cat.id;
  editName.value = cat.ten_danh_muc;
  editDesc.value = cat.mo_ta;
};
const saveEdit = async (id) => {
  await axios.put(`https://api.sieuthi404.io.vn/api/danh-muc-cha/${id}`, {
    ten_danh_muc: editName.value,
    mo_ta: editDesc.value,
  });
  editId.value = null;
  editName.value = '';
  editDesc.value = '';
  getCategories();
};
const cancelEdit = () => {
  editId.value = null;
  editName.value = '';
  editDesc.value = '';
};

// Ẩn/hiện
const toggleStatus = async (id) => {
  await axios.put(`https://api.sieuthi404.io.vn/api/danh-muc-cha/${id}/toggle-status`);
  getCategories();
};

onMounted(getCategories);
</script>

<style scoped>
/* Copy toàn bộ CSS từ trang danh mục sản phẩm vào đây */
.page-wrapper {
  background-color: #f3f4f6;
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
  color: #111827;
}
.content-card {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  padding: 1.5rem;
}
.btn-add {
  background-color: #4FC3F7;
  color: white;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1rem;
  border: 1px solid transparent;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.3s;
  text-decoration: none;
  height: 40px;
  width: 180px;
  justify-content: center;
}
.btn-add svg {
  width: 20px;
  height: 20px;
}
.btn-add:hover {
  background-color:#3b82f6;
  transform: scale(1.05);
}
.filter-bar {
  display: flex;
  justify-content: flex-start;
  margin-bottom: 1.5rem;
  gap: 1rem;
}
.search-input-wrapper {
  position: relative;
  width: 100%;
  max-width: 400px;
}
.form-control {
  display: block;
  width: 100%;
  padding: 0.375rem 0.75rem;
  font-size: 1rem;
  font-weight: 400;
  line-height: 1.5;
  color: #495057;
  background-color: #fff;
  background-clip: padding-box;
  border: 1px solid #ced4da;
  border-radius: 0.25rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}
.search-input {
  padding-left: 2.5rem;
  min-width: 300px;
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
.danhmuc-table {
  width: 100%;
  border-collapse: collapse;
  white-space: nowrap;
}
.danhmuc-table th, .danhmuc-table td {
  padding: 1rem;
  text-align: left;
  vertical-align: middle;
  border-bottom: 1px solid #e5e7eb;
}
.danhmuc-table th {
  background-color: #f9fafb;
  color: #6b7280;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
}
.danhmuc-table tbody tr:last-child td {
  border-bottom: none;
}
.w-12 { width: 3rem; }
.text-center { text-align: center; }
.py-8 { padding-top: 2rem; padding-bottom: 2rem; }
.danhmuc-title-cell {
  display: flex;
  flex-direction: column;
}
.danhmuc-title {
  font-weight: 500;
  color: #111827;
  text-decoration: none;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 220px;
  display: block;
}
.danhmuc-description {
  font-size: 0.85rem;
  color: #6b7280;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 220px;
  display: block;
}
.danhmuc-thumbnail {
  width: 80px;
  height: 60px;
  border-radius: 4px;
  object-fit: cover;
  border: 1px solid #e5e7eb;
}
.action-buttons {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.25rem;
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
.action-icon svg, .action-icon i {
  width: 20px;
  height: 20px;
  color: #111827;
  transition: color 0.2s;
}
.action-icon:hover {
  background-color: #e0e7ff;
  transform: scale(1.1);
}
.action-icon.text-danger svg, .action-icon.text-danger i {
  color: #dc2626;
}
.action-icon.text-success svg, .action-icon.text-success i {
  color: #16a34a;
}
.action-icon.text-danger:hover svg, .action-icon.text-danger:hover i {
  color: #b91c1c;
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
  background-color: #dcfce7;
}
.status-badge.is-inactive {
  color: #6b7280;
  background-color: #e5e7eb;
}
.is-inactive-row {
  background-color: #f8f8f8;
  opacity: 0.7;
}
.is-inactive-row .danhmuc-title,
.is-inactive-row .danhmuc-description {
  color: #9ca3af !important;
  font-style: italic;
}
</style>