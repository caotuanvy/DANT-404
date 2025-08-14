<template>
  <div class="admin-chat-container">
    <div v-if="!isMinimized" class="chat-main-panel">
      <div class="conversations-list">
        <h4>Đoạn chat</h4>
        <ul>
          <li v-for="conv in conversations" :key="conv.id" @click="selectConversation(conv)"
              :class="{ 'selected-user': selectedConversationId === conv.id }">
            <div class="user-info">
              <img :src="conv.user?.anh_dai_dien" alt="Avatar" class="user-avatar" />
              <div class="user-details">
                <span class="user-name">{{ conv.user?.ho_ten || 'Tên người dùng không khả dụng' }}</span>
              </div>
            </div>
          </li>
        </ul>
      </div>

      <div v-if="selectedConversationId" class="chat-panel">
        <div class="chat-header">
          <h4>{{ selectedConversation?.user?.ho_ten || 'Người dùng' }}</h4>
          <div class="header-buttons">
            <button @click="toggleMinimize" class="header-btn">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>

        <div class="chat-body">
          <div ref="messagesContainer" class="messages">
            <div v-for="msg in messages" :key="msg.id"
                 :class="{'message-sent': isMyMessage(msg), 'message-received': !isMyMessage(msg)}">
              {{ msg.content }}
            </div>
          </div>

          <div class="chat-footer">
            <input v-model="newMessage"
                   :disabled="isSending"
                   placeholder="Nhập tin nhắn..."
                   @keyup.enter="sendMessage">
            <button :disabled="isSending" @click="sendMessage">
              <i v-if="!isSending" class="fas fa-paper-plane"></i>
              <i v-else class="fas fa-spinner fa-spin"></i>
            </button>
          </div>

          <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
          </div>
        </div>
      </div>

      <div v-else class="no-chat-selected">
        Chọn một người dùng để bắt đầu trò chuyện.
      </div>
    </div>

    <div v-if="isMinimized" class="minimized-widget">
      <button class="restore-btn" @click="toggleMinimize">
        <i class="fas fa-comment-alt"></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import axios from 'axios';

// --- State Management ---
const conversations = ref([]);
const messages = ref([]);
const newMessage = ref('');
const selectedConversationId = ref(null);
const selectedConversation = ref(null);
const adminId = ref(null);
const errorMessage = ref('');
const isSending = ref(false);
const isMinimized = ref(true);

// --- DOM References ---
const messagesContainer = ref(null);

// --- Polling Intervals ---
let messagePollingInterval = null;
let conversationPollingInterval = null;

// --- API & Data Fetching Functions ---
const fetchConversations = async () => {
  try {
    const response = await axios.get('/chat/admin/conversations');
    conversations.value = response.data;
  } catch (error) {
    console.error('Lỗi khi lấy danh sách cuộc trò chuyện:', error);
    errorMessage.value = 'Không thể lấy danh sách cuộc trò chuyện.';
  }
};

const fetchMessages = async () => {
  if (!selectedConversationId.value) return;
  try {
    const response = await axios.get('/chat/get-messages', {
      params: { conversation_id: selectedConversationId.value }
    });
    messages.value = response.data;
    errorMessage.value = '';
    await nextTick();
    scrollToBottom();
  } catch (error) {
    console.error('Lỗi khi lấy tin nhắn:', error);
    errorMessage.value = 'Không thể lấy tin nhắn. Vui lòng thử lại sau.';
  }
};

const sendMessage = async () => {
  if (newMessage.value.trim() === '' || !selectedConversationId.value || isSending.value) return;

  isSending.value = true;
  errorMessage.value = '';

  const tempMessage = {
    id: Date.now(),
    content: newMessage.value,
    sender_id: adminId.value,
  };

  messages.value.push(tempMessage);
  await nextTick();
  scrollToBottom();

  try {
    await axios.post('/chat/send-message', {
      content: tempMessage.content,
      conversation_id: selectedConversationId.value,
      sender_id: adminId.value,
    });
    newMessage.value = '';
    // Sau khi gửi tin nhắn thành công, cập nhật lại cả messages và conversations
    await fetchMessages();
    await fetchConversations();
  } catch (error) {
    console.error('Lỗi khi gửi tin nhắn:', error);
    errorMessage.value = 'Gửi tin nhắn thất bại. Vui lòng kiểm tra lại backend.';
    messages.value = messages.value.filter(msg => msg.id !== tempMessage.id); // Xóa tin nhắn tạm nếu gửi thất bại
  } finally {
    isSending.value = false;
  }
};

const updateConversationAdmin = async (conv) => {
  try {
    await axios.post('/chat/update-conversation', {
      conversation_id: conv.id,
      admin_id: adminId.value,
    });
    await fetchConversations();
  } catch (error) {
    console.error('Lỗi khi cập nhật admin_id:', error);
  }
};

// --- User Interaction & UI Functions ---
const selectConversation = async (conv) => {
  selectedConversationId.value = conv.id;
  selectedConversation.value = conv;

  if (!conv.admin_id && adminId.value) {
    await updateConversationAdmin(conv);
  }

  fetchMessages();
};

const toggleMinimize = () => {
  isMinimized.value = !isMinimized.value;
  if (!isMinimized.value) {
    nextTick(() => {
      scrollToBottom();
    });
  }
};

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

// --- Utility Functions ---
const isMyMessage = (message) => {
  return parseInt(message.sender_id) === parseInt(adminId.value);
};

// --- Lifecycle Hooks ---
onMounted(() => {
  const user = JSON.parse(localStorage.getItem('user'));
  if (user && user.vai_tro_id === 1) {
    adminId.value = user.nguoi_dung_id;
    fetchConversations();
    conversationPollingInterval = setInterval(fetchConversations, 5000);
  } else {
    console.warn('Cảnh báo: Không tìm thấy ID admin trong localStorage. Vui lòng đăng nhập.');
  }
});

onUnmounted(() => {
  clearInterval(messagePollingInterval);
  clearInterval(conversationPollingInterval);
});

// --- Watchers ---
watch(selectedConversationId, (newVal) => {
  // Clear any existing message polling interval
  if (messagePollingInterval) {
    clearInterval(messagePollingInterval);
    messagePollingInterval = null;
  }
  // Start a new polling interval for the selected conversation
  if (newVal) {
    fetchMessages();
    messagePollingInterval = setInterval(fetchMessages, 3000);
  }
});
</script>

<style scoped>
/*
 * ----------------------------------------------------
 * --- General Layout & Sizing ---
 * ----------------------------------------------------
 */
.admin-chat-container {
  position: fixed;
  bottom: 0;
  right: 0;
  margin: 20px;
  z-index: 1000;
}

.chat-main-panel {
  display: flex;
  width: 800px;
  height: 500px;
  background-color: #ffffff;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
}

/*
 * ----------------------------------------------------
 * --- Conversations List Panel ---
 * ----------------------------------------------------
 */
.conversations-list {
  width: 300px;
  padding: 15px;
  background-color: #f8f9fa;
  border-right: 1px solid #e0e0e0;
  overflow-y: auto;
  flex-shrink: 0;
}

.conversations-list h4 {
  margin-top: 0;
  padding-bottom: 10px;
  border-bottom: 1px solid #e0e0e0;
}

.conversations-list ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.conversations-list li {
  padding: 10px;
  margin-bottom: 5px;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.conversations-list li:hover {
  background-color: #e9ecef;
}

.conversations-list .selected-user {
  background-color: #33ccff;
  color: white;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  background-color: #e0e0e0;
}

.user-name {
  font-weight: bold;
}

.user-details {
  display: flex;
  flex-direction: column;
}

/*
 * ----------------------------------------------------
 * --- Chat Panel (Main) ---
 * ----------------------------------------------------
 */
.chat-panel {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  min-width: 500px;
}

.chat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 15px;
  background-color: #33ccff;
  color: white;
}

.chat-header h4 {
  margin: 0;
}

.header-btn {
  background: none;
  border: none;
  color: white;
  font-size: 1.2rem;
  cursor: pointer;
}

.chat-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 0; /* Crucial for flex + overflow to work */
  background-color: #f8f9fa;
}

/*
 * ----------------------------------------------------
 * --- Messages Display ---
 * ----------------------------------------------------
 */
.messages {
  flex: 1;
  min-height: 0;
  padding: 10px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}

.message-sent, .message-received {
  width: fit-content;
  max-width: 70%;
  padding: 8px 12px;
  margin-bottom: 10px;
  border-radius: 18px;
  word-wrap: break-word;
}

.message-sent {
  align-self: flex-end;
  background-color: #a8e8fd;
}

.message-received {
  align-self: flex-start;
  background-color: #ffffff;
  border: 1px solid #e0e0e0;
}

/*
 * ----------------------------------------------------
 * --- Chat Footer & Input ---
 * ----------------------------------------------------
 */
.chat-footer {
  display: flex;
  padding: 10px;
  border-top: 1px solid #e0e0e0;
}

.chat-footer input {
  flex-grow: 1;
  padding: 8px 15px;
  border: 1px solid #ddd;
  border-radius: 20px;
  outline: none;
}

.chat-footer button {
  width: 40px;
  height: 40px;
  margin-left: 10px;
  background-color: #33ccff;
  color: white;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  transition: background-color 0.2s ease;
}
.chat-footer button:hover {
  background-color: #2497d5;
}

/*
 * ----------------------------------------------------
 * --- Other UI Components ---
 * ----------------------------------------------------
 */
.no-chat-selected {
  flex-grow: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  font-style: italic;
  color: #6c757d;
}

.error-message {
  padding: 10px;
  background-color: #ffe0e0;
  color: red;
  border-radius: 5px;
  margin-top: 10px;
  text-align: center;
}

/*
 * ----------------------------------------------------
 * --- Minimized Widget & Animations ---
 * ----------------------------------------------------
 */
.minimized-widget {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 1000;
}

.restore-btn {
  width: 60px;
  height: 60px;
  background-color: #33ccff;
  color: white;
  font-size: 1.5rem;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  transition: transform 0.3s ease, background-color 0.3s ease;
}

.restore-btn:hover {
  background-color: #2497d5;
  transform: scale(1.1);
}

.fa-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/*
 * ----------------------------------------------------
 * --- Media Queries (Responsive Design) ---
 * ----------------------------------------------------
 */
@media (max-width: 768px) {
  .admin-chat-container {
    width: 100vw;
    height: 100vh;
    margin: 0;
  }
  .chat-main-panel {
    width: 100%;
    height: 100%;
    border-radius: 0;
  }
  .conversations-list {
    width: 80px;
    padding: 10px 5px;
    border-right: none;
  }
  .conversations-list h4 {
    display: none;
  }
  .conversations-list li {
    padding: 8px;
    justify-content: center;
    text-align: center;
  }
  .user-info {
    flex-direction: column;
    align-items: center;
  }
  .user-details {
    display: none;
  }
  .user-avatar {
    width: 50px;
    height: 50px;
  }
  .chat-panel {
    min-width: unset;
    width: calc(100% - 80px);
  }
  .chat-header h4 {
    font-size: 1rem;
  }
}
</style>