<template>
  <section class="content p-4">
    <h2 class="text-2xl font-bold mb-4">Danh sách các trang tĩnh</h2>
    <router-link to="/admin/trang-tinh/add" class="btn-add">
      + Thêm trang tĩnh
    </router-link>
    <table class="w-full border border-gray-300 text-left">
      <thead class="bg-gray-100">
        <tr>
          <th>#</th>
          <th>Tiêu đề trang</th>
          <th>Tên trang (slug)</th>
          <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(page, index) in pages" :key="page.Trang_tinh_id || index">
          <td>{{ index + 1 }}</td>
          <td v-if="editingId === page.Trang_tinh_id">
            <input v-model="editTieuDe" class="border p-1 w-full" />
          </td>
          <td v-else>{{ page.Tieu_de_trang }}</td>
          <td v-if="editingId === page.Trang_tinh_id">
            <input v-model="editTenTrang" class="border p-1 w-full" />
          </td>
          <td v-else>{{ page.Ten_trang }}</td>

          <td>
            <div class="action-buttons flex gap-2">
              <router-link
                :to="`/admin/trang-tinh/${page.Ten_trang}`"
                class="btn-detail text-blue-600 underline"
              >
                Xem/Sửa nội dung
              </router-link>
              <button
                v-if="editingId === page.Trang_tinh_id"
                @click="saveEdit(page.Trang_tinh_id)"
                class="btn-edit bg-gray-300 px-2 rounded"
              >
                Lưu
              </button>
              <button
                v-if="editingId === page.Trang_tinh_id"
                @click="cancelEdit"
                class="bg-gray-400 text-white px-2 rounded"
              >
                Hủy
              </button>

              <button
                v-else
                @click="startEdit(page)"
                class="btn-edit bg-yellow-500 text-white px-2 rounded"
              >
                Sửa tiêu đề/slug
              </button>
              <button
                @click="() => deletePage(page.Trang_tinh_id)"
                class="btn-delete bg-red-500 text-white px-2 rounded"
              >
                Xóa
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </section>
</template>

<script setup>
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
table th, table td {
  padding: 8px;
  border: 1px solid #ccc;
}
input {
  border: 1px solid #ccc;
  padding: 4px;
}
</style>
