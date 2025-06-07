<template>
    <div class="p-4">
      <h1 class="text-2xl font-bold mb-4">Danh sách người dùng</h1>
  
      <div v-if="loading" class="text-gray-500">Đang tải dữ liệu...</div>
      <div v-else-if="users.length === 0" class="text-gray-500">Không có người dùng nào.</div>
  
      <table v-else class="min-w-full border border-gray-300">
        <thead class="bg-gray-100">
          <tr>
            <th class="border px-4 py-2">Mã người dùng</th>
            <th class="border px-4 py-2">Tên người dùng</th>
            <th class="border px-4 py-2">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.user_id" class="hover:bg-gray-50">
            <td class="border px-4 py-2">{{ user.user_id }}</td>
            <td class="border px-4 py-2">{{ user.username }}</td>
            <td class="border px-4 py-2">
              <button
                @click="deleteUser(user.user_id)"
                class="px-3 py-1 rounded bg-red-500 text-white hover:bg-red-600"
              >
                Xoá
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    name: 'CustomersList',
    data() {
      return {
        users: [],
        loading: true,
      };
    },
    mounted() {
      this.fetchUsers();
    },
    methods: {
      async fetchUsers() {
        try {
          const response = await axios.get('http://localhost:8000/api/users');
          this.users = response.data;
        } catch (error) {
          console.error('Lỗi khi tải danh sách người dùng:', error);
        } finally {
          this.loading = false;
        }
      },
      async deleteUser(userId) {
        if (confirm('Bạn có chắc chắn muốn xóa tài khoản này?')) {
          try {
            await axios.delete(`http://localhost:8000/api/users/${userId}`);
            this.users = this.users.filter(user => user.user_id !== userId);
          } catch (error) {
            console.error('Lỗi khi xóa tài khoản:', error);
          }
        }
      }
    }
  }
  </script>
  
 
  <style scoped>
 

.p-4 {
  padding: 1rem;
}


.text-2xl {
  font-size: 1.5rem;
  font-weight: bold;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 0.8rem;
  text-align: left;
  border: 1px solid #ddd;
}

th {
  background-color: #f1f1f1;
  color: #333;
  font-weight: bold;
}

tr:hover {
  background-color: #f9fafb;
}

.text-gray-500 {
  color: #6b7280;
}


.bg-blue-500 {
  background-color: #3b82f6;
}

.bg-blue-500:hover {
  background-color: #2563eb;
}

.text-white {
  color: white;
}

.px-4 {
  padding-left: 1rem;
  padding-right: 1rem;
}

.py-2 {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}

.rounded {
  border-radius: 0.375rem;
}

.mb-4 {
  margin-bottom: 1rem;
}


.text-red-500 {
  color: #ef4444;
}
</style>