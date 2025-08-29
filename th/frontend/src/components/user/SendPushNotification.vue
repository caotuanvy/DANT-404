<template>
  <div class="notification-sender-container">
    <h1>Gửi Thông Báo Đẩy</h1>

    <div v-if="successMessage" class="alert success">
      {{ successMessage }}
    </div>

    <div v-if="errorMessage" class="alert error">
      <ul>
        <li v-for="(error, index) in errorMessage" :key="index">{{ error }}</li>
      </ul>
    </div>

    <form @submit.prevent="sendNotification">
      <div class="form-group">
        <label for="title">Tiêu đề thông báo:</label>
        <input type="text" id="title" v-model="form.title" required placeholder="Ví dụ: Đơn hàng của bạn đã được giao!">
      </div>

      <div class="form-group">
        <label for="body">Nội dung thông báo:</label>
        <textarea id="body" v-model="form.body" rows="4" required placeholder="Ví dụ: Đơn hàng #12345 của bạn đã được giao thành công."></textarea>
      </div>

      <div class="form-group">
        <label for="user_id">Gửi đến người dùng cụ thể (tùy chọn):</label>
        <select id="user_id" v-model="form.user_id">
          <option value="">-- Chọn một người dùng --</option>
          <option v-for="user in users" :key="user.nguoi_dung_id" :value="user.nguoi_dung_id">
            {{ user.ho_ten }} ({{ user.email }})
          </option>
        </select>
        <small>Nếu bạn chọn một người dùng, thông báo sẽ chỉ gửi đến người đó. Nếu không, hãy nhập chủ đề.</small>
      </div>

      <div class="form-group">
        <label for="topic">Gửi đến chủ đề (tùy chọn):</label>
        <input type="text" id="topic" v-model="form.topic" placeholder="Ví dụ: all_users, promotions">
        <small>Nếu bạn muốn gửi đến nhiều người dùng cùng lúc, hãy sử dụng chủ đề. Ví dụ: 'all_users' cho tất cả người dùng đã đăng ký chủ đề đó.</small>
      </div>

      <button type="submit" :disabled="isLoading">
        <span v-if="isLoading">Đang gửi...</span>
        <span v-else>Gửi Thông Báo</span>
      </button>
    </form>
  </div>
</template>

<script>
import axios from 'axios'; // Đảm bảo bạn đã cài đặt axios: npm install axios

export default {
  name: 'NotificationSender',
  data() {
    return {
      form: {
        title: '',
        body: '',
        user_id: '',
        topic: '',
      },
      users: [],
      isLoading: false,
      successMessage: '',
      errorMessage: null, 
    };
  },
  async created() {
    // Tải danh sách người dùng khi component được tạo
    await this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      try {
        // *** ĐÃ THAY ĐỔI: Lấy token với khóa 'token' thay vì 'sanctum_token' ***
        const authToken = localStorage.getItem('token'); 
        
        if (!authToken) {
          console.warn('Token xác thực không có. Không thể tải danh sách người dùng. Vui lòng đăng nhập.');
          return;
        }

        // Gọi API Laravel để lấy danh sách người dùng
        const response = await axios.get('https://api.sieuthi404.io.vn/api/admin/users-for-notification', {
          headers: {
            'Authorization': `Bearer ${authToken}`, // Sử dụng authToken đã lấy được
            'Accept': 'application/json',
          },
        });
        this.users = response.data;
      } catch (error) {
        console.error('Lỗi khi tải danh sách người dùng:', error.response ? error.response.data : error.message);
        this.errorMessage = ['Không thể tải danh sách người dùng. Vui lòng kiểm tra kết nối hoặc quyền.'];
      }
    },

    async sendNotification() {
      this.isLoading = true;
      this.successMessage = '';
      this.errorMessage = null;

      try {
        // *** ĐÃ THAY ĐỔI: Lấy token với khóa 'token' thay vì 'sanctum_token' ***
        const authToken = localStorage.getItem('token'); 
        if (!authToken) {
          this.errorMessage = ['Bạn chưa đăng nhập hoặc không có quyền.'];
          this.isLoading = false;
          return;
        }

        const payload = {
          title: this.form.title,
          body: this.form.body,
        };

        if (this.form.user_id) {
          payload.user_id = this.form.user_id;
        } else if (this.form.topic) {
          payload.topic = this.form.topic;
        } else {
          this.errorMessage = ['Vui lòng chọn người dùng hoặc nhập chủ đề để gửi thông báo.'];
          this.isLoading = false;
          return;
        }

        // Gọi API Laravel để gửi thông báo
        const response = await axios.post('https://api.sieuthi404.io.vn/api/admin/notifications/send', payload, {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${authToken}`, // Sử dụng authToken đã lấy được
          },
        });

        this.successMessage = response.data.message;
        // Reset form sau khi gửi thành công
        this.form.title = '';
        this.form.body = '';
        this.form.user_id = '';
        this.form.topic = '';

      } catch (error) {
        console.error('Lỗi khi gửi thông báo:', error.response ? error.response.data : error.message);
        if (error.response && error.response.data && error.response.data.errors) {
            // Lỗi xác thực từ Laravel
            this.errorMessage = Object.values(error.response.data.errors).flat();
        } else if (error.response && error.response.data && error.response.data.message) {
            // Lỗi khác từ Laravel (ví dụ: không tìm thấy user)
            this.errorMessage = [error.response.data.message];
        } else {
            this.errorMessage = ['Đã xảy ra lỗi khi gửi thông báo.'];
        }
      } finally {
        this.isLoading = false;
      }
    },
  },
};
</script>

<style scoped>
.notification-sender-container {
  max-width: 600px;
  margin: 30px auto;
  padding: 25px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
}

h1 {
  text-align: center;
  color: #333;
  margin-bottom: 25px;
}
form {
  box-shadow: none !important;
}
.form-group {
  margin-bottom: 18px;
  
}

label {
  display: block;
  margin-bottom: 7px;
  font-weight: bold;
  color: #555;
}

input[type="text"],
textarea,
select {
  width: calc(100% - 20px);
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  box-sizing: border-box;
  font-size: 1em;
}

textarea {
  resize: vertical;
  min-height: 80px;
}

button {
  background-color: #007bff;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1.1em;
  width: 100%;
  transition: background-color 0.3s ease;
}

button:hover:not(:disabled) {
  background-color: #0056b3;
}

button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.alert {
  padding: 12px;
  margin-bottom: 20px;
  border-radius: 5px;
  font-size: 0.95em;
}

.alert.success {
  background-color: #d4edda;
  color: #155724;
  border-color: #c3e6cb;
}

.alert.error {
  background-color: #f8d7da;
  color: #721c24;
  border-color: #f5c6cb;
}

small {
  display: block;
  margin-top: 5px;
  color: #888;
  font-size: 0.85em;
}
</style>
  