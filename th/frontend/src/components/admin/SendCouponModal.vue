<template>
  <div class="modal-overlay" @click.self="emit('close')">
    <div class="modal-content">
      <h3>Gửi mã "{{ couponToSend.ma_giam_gia }}"</h3>
      
      <div class="form-group">
        <label for="user-search">Tìm kiếm người dùng (theo email hoặc tên)</label>
        <input id="user-search" v-model="searchTerm" @input="debouncedSearchUsers" placeholder="Nhập tối thiểu 3 ký tự...">
      </div>

      <div v-if="isSearching" class="loading-text">Đang tìm...</div>

      <div v-if="searchResults.length > 0" class="user-list">
        <div v-for="user in searchResults" :key="user.nguoi_dung_id" class="user-item">
          <div class="user-info">
            <strong>{{ user.ho_ten }}</strong>
            <small>{{ user.email }}</small>
          </div>
          <button class="btn btn-sm btn-success" @click="sendToOneUser(user)" :disabled="isSending">
            {{ isSending ? 'Đang gửi...' : 'Gửi' }}
          </button>
        </div>
      </div>
      <div v-if="!isSearching && searchTerm.length >= 3 && searchResults.length === 0" class="no-results">
        Không tìm thấy người dùng nào.
      </div>
      
      <hr class="form-divider">

      <div class="send-all-section">
        <p>Hoặc gửi mã này cho tất cả người dùng trong hệ thống.</p>
        <button class="btn btn-warning" @click="sendToAllUsers" :disabled="isSending">
          <i class="bi bi-broadcast me-2"></i> 
          {{ isSending ? 'Đang gửi...' : 'Gửi cho tất cả' }}
        </button>
      </div>

      <div class="form-actions">
        <button type="button" class="btn-secondary" @click="emit('close')">Đóng</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({ couponToSend: { type: Object, required: true } });
const emit = defineEmits(['close']);

const searchTerm = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const isSending = ref(false);
let debounceTimer = null;

const searchUsers = async () => {
  if (searchTerm.value.length < 3) {
    searchResults.value = [];
    return;
  }
  isSearching.value = true;
  try {
    // [SỬA] Trỏ đến API đã tạo
    const response = await axios.get(`/admin/users/search?term=${searchTerm.value}`);
    searchResults.value = response.data;
  } catch (error) {
    console.error('Lỗi khi tìm người dùng:', error);
    alert('Không thể tìm kiếm người dùng.');
  } finally {
    isSearching.value = false;
  }
};

const debouncedSearchUsers = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        searchUsers();
    }, 500);
};

const sendCouponToOneUser = async (user) => {
    if (confirm(`Bạn có chắc muốn gửi mã này cho người dùng "${user.ho_ten}"?`)) {
        isSending.value = true;
        try {
            await axios.post(`/admin/giam-gia/${props.couponToSend.giam_gia_id}/send-to-users`, {
                user_ids: [user.nguoi_dung_id]
            });
            alert('Gửi mã thành công!');
        } catch (error) {
            console.error('Lỗi khi gửi mã:', error);
            const errorMessage = error.response?.data?.message || 'Có lỗi xảy ra khi gửi mã.';
            alert(errorMessage);
        } finally {
            isSending.value = false;
        }
    }
};

const sendToAllUsers = async () => {
    if (confirm('Bạn có chắc chắn muốn gửi mã này cho TẤT CẢ người dùng? Hành động này không thể hoàn tác.')) {
        isSending.value = true;
        try {
            // [SỬA] Gọi đến API send-to-all mới
            await axios.post(`/admin/giam-gia/${props.couponToSend.giam_gia_id}/send-to-all`);
            alert('Gửi mã cho tất cả người dùng thành công!');
            emit('close');
        } catch (error) {
            console.error('Lỗi khi gửi mã cho tất cả:', error);
            const errorMessage = error.response?.data?.message || 'Có lỗi xảy ra.';
            alert(errorMessage);
        } finally {
            isSending.value = false;
        }
    }
};
</script>

<style scoped>
/* (Dán CSS của GiamGiaForm vào đây và tùy chỉnh nếu cần) */
.modal-overlay {
  position: fixed; top: 0; left: 0; width: 100%; height: 100%;
  background-color: rgba(0, 0, 0, 0.6); display: flex;
  justify-content: center; align-items: center; z-index: 1000;
}
.modal-content {
  background: white; padding: 2rem; border-radius: 8px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); width: 100%;
  max-width: 600px; max-height: 90vh; overflow-y: auto;
}
h3 { margin-top: 0; margin-bottom: 1.5rem; }
.form-group { display: flex; flex-direction: column; margin-bottom: 1rem; }
.form-group label { margin-bottom: 0.5rem; font-weight: 500; }
.form-group input {
  width: 100%; padding: 0.75rem; border: 1px solid #ccc;
  border-radius: 4px; box-sizing: border-box; font-size: 1rem;
}
.form-divider { border: none; border-top: 1px solid #eee; margin: 2rem 0; }
.form-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; }
button {
  padding: 0.75rem 1.5rem; border: none; border-radius: 4px;
  cursor: pointer; font-weight: bold; transition: background-color 0.2s;
}
button:disabled { background-color: #ccc; cursor: not-allowed; }
.btn-secondary { background-color: #f1f1f1; color: #333; }
.user-list { margin-top: 1rem; max-height: 200px; overflow-y: auto; border: 1px solid #eee; border-radius: 4px; }
.user-item { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 1rem; border-bottom: 1px solid #eee; }
.user-item:last-child { border-bottom: none; }
.user-info { display: flex; flex-direction: column; }
.user-info small { color: #6c757d; }
.loading-text, .no-results { padding: 1rem; text-align: center; color: #6c757d; }
.send-all-section { margin-top: 1rem; padding: 1rem; background-color: #fff3cd; border-radius: 4px; text-align: center; }
.btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
.btn-success { color: #fff; background-color: #198754; border-color: #198754; }
.btn-warning { color: #000; background-color: #ffc107; border-color: #ffc107; }
</style>