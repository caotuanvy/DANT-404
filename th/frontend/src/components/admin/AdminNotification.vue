<template>
  <div class="admin-notification">
    <button @click="toggleDropdown" class="notification-btn">
      <i class="bi bi-bell"></i>
      <span v-if="unreadCount > 0" class="badge">{{ unreadCount }}</span>
    </button>
    <div v-if="showDropdown" class="notification-dropdown">
      <div v-for="noti in notifications" :key="noti.id" class="notification-item" :class="{ unread: !noti.da_xem }">
        <strong>{{ noti.loai_thong_bao }}</strong>
        <p>{{ noti.mo_ta }}</p>
        <div class="notification-content">{{ noti.tin_bao }}</div>
        <small>{{ formatDate(noti.ngay_tao) }}</small>
      </div>
      <div v-if="notifications.length === 0" class="notification-empty">Không có thông báo mới.</div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  data() {
    return {
      notifications: [],
      showDropdown: false,
    };
  },
  computed: {
    unreadCount() {
      return this.notifications.filter(n => !n.da_xem).length;
    }
  },
  methods: {
    async fetchNotifications() {
      console.log('fetchNotifications called'); // kiểm tra số lần gọi
      try {
        const res = await axios.get('/Notifications', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        this.notifications = res.data;
      } catch (err) {}
    },
    async markAllAsRead() {
      await axios.post('/Notifications/read-all', {}, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      });
      this.fetchNotifications();
    },
    async toggleDropdown() {
      this.showDropdown = !this.showDropdown;
      if (this.showDropdown) {
        await this.fetchNotifications();
        await this.markAllAsRead();
        await this.fetchNotifications();
      }
    },
    handleClickOutside(event) {
      if (
        this.showDropdown &&
        !this.$el.contains(event.target)
      ) {
        this.showDropdown = false;
      }
    },
    formatDate(dt) {
      if (!dt) return '';
      return new Date(dt).toLocaleString('vi-VN');
    }
  },
  mounted() {
    console.log('AdminNotification mounted'); // kiểm tra mount lại
    window.addEventListener('click', this.handleClickOutside);
  },
  beforeUnmount() {
    window.removeEventListener('click', this.handleClickOutside);
  }
};
</script>

<style scoped>
.admin-notification { position: relative; display: inline-block; }
.notification-btn { background: none; border: none; cursor: pointer; position: relative; }
.icon-bell { font-size: 24px; }
.badge { background: red; color: white; border-radius: 50%; padding: 2px 8px; font-size: 12px; position: absolute; top: -8px; right: -8px; }
.notification-dropdown { position: absolute; right: 0; top: 36px; background: white; border: 1px solid #eee; border-radius: 8px; width: 320px; max-height: 400px; overflow-y: auto; box-shadow: 0 2px 8px rgba(0,0,0,0.1); z-index: 100; }
.notification-item { padding: 12px; border-bottom: 1px solid #eee; }
.notification-item.unread { background: #f8f9fa; }
.notification-empty { padding: 16px; text-align: center; color: #888; }
.notification-content { margin: 4px 0 8px 0; }
</style>