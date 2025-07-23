<template>
  <section class="content p-4">
    <header class="page-header">
      <h2 class="page-title">Danh sách các trang tĩnh</h2>
      <router-link to="/admin/trang-tinh/add" class="btn-add">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
        <span>Thêm trang tĩnh</span>
      </router-link>
    </header>

    <div class="content-card">
      <table class="w-full text-left">
        <thead class="bg-gray-100">
          <tr>
            <th>#</th>
            <th>Tiêu đề trang</th>
            <th>Tên trang (slug)</th>
            <th class="text-center">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(page, index) in pages" :key="page.Trang_tinh_id || index">
            <td>{{ index + 1 }}</td>
            <td v-if="editingId === page.Trang_tinh_id">
              <input v-model="editTieuDe" class="edit-input" />
            </td>
            <td v-else>{{ page.Tieu_de_trang }}</td>
            <td v-if="editingId === page.Trang_tinh_id">
              <input v-model="editTenTrang" class="edit-input" />
            </td>
            <td v-else>{{ page.Ten_trang }}</td>

            <td>
              <div class="action-buttons">
                <template v-if="editingId === page.Trang_tinh_id">
                  <button @click="saveEdit(page.Trang_tinh_id)" class="action-icon text-success" title="Lưu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                  <button @click="cancelEdit" class="action-icon text-danger" title="Hủy">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </template>

                <template v-else>
                   <router-link :to="`/admin/trang-tinh/${page.Ten_trang}`" class="action-icon" title="Xem/Sửa nội dung">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.022 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                      </svg>
                  </router-link>
                  <button @click="startEdit(page)" class="action-icon" title="Sửa tiêu đề/slug">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
                  </button>
                  <button @click="() => deletePage(page.Trang_tinh_id)" class="action-icon text-danger" title="Xóa">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </template>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>

<script setup>
// Script của bạn giữ nguyên, không cần thay đổi
import { ref, onMounted } from 'vue'
import axios from 'axios'

const pages = ref([])
const editingId = ref(null)
const editTieuDe = ref('')
const editTenTrang = ref('')

const fetchPages = async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/admin/trang-tinh', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })
    pages.value = res.data
  } catch (error) {
    console.error('Không thể tải danh sách trang:', error)
  }
}

const deletePage = async (id) => {
  if (!confirm('Bạn có chắc muốn xóa trang này?')) return

  try {
    await axios.delete(`http://localhost:8000/api/admin/trang-tinh/${id}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })
    pages.value = pages.value.filter(page => page.Trang_tinh_id !== id)
    alert('Xóa thành công')
  } catch (error) {
    console.error('Lỗi khi xóa:', error)
    alert('Xóa không thành công!')
  }
}

const startEdit = (page) => {
  editingId.value = page.Trang_tinh_id
  editTieuDe.value = page.Tieu_de_trang
  editTenTrang.value = page.Ten_trang
}

const cancelEdit = () => {
  editingId.value = null
  editTieuDe.value = ''
  editTenTrang.value = ''
}

const saveEdit = async (id) => {
  try {
    await axios.post(`http://localhost:8000/api/admin/trang-tinh/${id}`, {
      Tieu_de_trang: editTieuDe.value,
      Ten_trang: editTenTrang.value
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })

    const index = pages.value.findIndex(p => p.Trang_tinh_id === id)
    if (index !== -1) {
      pages.value[index].Tieu_de_trang = editTieuDe.value
      pages.value[index].Ten_trang = editTenTrang.value
    }

    alert('Cập nhật thành công')
    cancelEdit()
  } catch (error) {
    console.error('Lỗi khi cập nhật:', error)
    alert('Cập nhật thất bại!')
  }
}

onMounted(fetchPages)
</script>

<style scoped>
/* Biến màu cho nhất quán */
:root {
  --color-primary: #4FC3F7;
  --color-red: #dc2626;
  --color-green: #16a34a;
  --color-text-primary: #111827;
  --radius: 8px;
  --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
}

/* Layout chung */
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
  background-color: #ffffff;
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 1.5rem;
}

/* Nút Thêm */
.btn-add {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1rem;
  border-radius: var(--radius);
  font-weight: 500;
  cursor: pointer;
  background-color: #4FC3F7;
  color: white;
  transition: background-color 0.3s ease, transform 0.3s ease;
  text-decoration: none;
}
.btn-add svg {
  width: 20px;
  height: 20px;
}
.btn-add:hover {
  background-color: #3b82f6;
  transform: scale(1.05);
}


/* Style bảng */
table {
  width: 100%;
  border-collapse: collapse;
}
table th, table td {
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  vertical-align: middle;
}
table th {
  background-color: #f9fafb;
  color: #6b7280;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
}
.text-center {
  text-align: center;
}
.edit-input {
  width: 100%;
  padding: 0.5rem;
  border-radius: 5px;
  border: 1px solid #ccc;
}
.edit-input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-primary) 20%, transparent);
}


/* NÚT BẤM ICON - PHẦN QUAN TRỌNG */
.action-buttons {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.75rem;
}
.action-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px; /* Tăng kích thước để dễ bấm hơn */
  height: 32px;
  padding: 0;
  border-radius: 50%; /* Bo tròn */
  border: none;
  cursor: pointer;
  transition: background-color 0.2s, transform 0.2s;
  background-color: #f3f4f6; /* Màu nền xám nhạt */
}
.action-icon svg {
    width: 20px;
    height: 20px;
    color: #374151; /* Màu icon mặc định */
}
.action-icon:hover {
  background-color: var(--color-primary);
  transform: scale(1.1);
}
.action-icon:hover svg {
  color: white; /* Đổi màu icon khi hover */
}

/* Màu riêng cho nút xóa và lưu */
.action-icon.text-danger:hover {
  background-color: var(--color-red);
}
.action-icon.text-danger:hover svg {
  color: white;
}
.action-icon.text-success:hover {
  background-color: var(--color-green);
}
.action-icon.text-success:hover svg {
  color: white;
}
</style>