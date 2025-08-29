<template>
  <div class="chat-container">
    <div v-if="!isChatOpen" class="minimized-widget">
      <button @click="toggleChat" class="restore-btn">
        <i class="fas fa-comment-alt"></i>
      </button>
    </div>

    <div v-if="isChatOpen" class="chat-widget">
      <div class="chat-header">
        <h4>Liên Hệ Hỗ Trợ</h4>
        <button @click="toggleChat">
          <i class="fas fa-minus"></i>
        </button>
      </div>

      <div class="chat-body">
        <div class="messages" ref="messagesContainer">
          <div
            v-for="msg in messages"
            :key="msg.id"
            :class="{
              'message-sent': isMyMessage(msg),
              'message-received': !isMyMessage(msg)
            }"
          >
            {{ msg.content }}
          </div>
        </div>

        <div class="chat-footer">
          <input
            v-model="newMessage"
            @keyup.enter="sendMessage"
            placeholder="Nhập tin nhắn..."
            :disabled="isSending"
          />
          <button @click="sendMessage" :disabled="isSending">
            <i v-if="!isSending" class="fas fa-paper-plane"></i>
            <i v-else class="fas fa-spinner fa-spin"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';

// --- State Management ---
const isChatOpen = ref(false);
const messages = ref([]);
const newMessage = ref('');
const userId = ref(null);
const isAdmin = ref(false);
const conversationId = ref(null);
const isSending = ref(false); // Thêm biến trạng thái này

// --- Polling Intervals & DOM Refs ---
let pollingInterval = null;
const messagesContainer = ref(null);

// --- User Interaction & UI Functions ---
const toggleChat = () => {
  isChatOpen.value = !isChatOpen.value;
  if (isChatOpen.value) {
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

// --- API & Data Fetching Functions ---
const getOrCreateConversation = async (nguoiDungId, adminId) => {
  try {
    const response = await axios.post('/chat/get-or-create-conversation', {
      nguoi_dung_id: nguoiDungId,
      admin_id: adminId
    });
    conversationId.value = response.data.conversation_id;
  } catch (error) {
    console.error('Lỗi khi lấy hoặc tạo conversation:', error);
  }
};

const fetchMessages = async () => {
  if (!conversationId.value) return;

  try {
    const response = await axios.get('/chat/get-messages', {
      params: {
        conversation_id: conversationId.value
      }
    });
    messages.value = response.data;
    nextTick(() => {
      scrollToBottom();
    });
  } catch (error) {
    console.error('Lỗi khi lấy tin nhắn:', error);
  }
};

const sendMessage = async () => {
  if (newMessage.value.trim() === '' || !conversationId.value || !userId.value || isSending.value) {
    console.error('Thiếu thông tin hoặc đang gửi tin nhắn.');
    return;
  }

  isSending.value = true;

  const tempMessage = {
    id: Date.now(),
    content: newMessage.value,
    sender_id: userId.value
  };

  messages.value.push(tempMessage);
  await nextTick();
  scrollToBottom();

  try {
    await axios.post('/chat/send-message', {
      conversation_id: conversationId.value,
      sender_id: userId.value,
      content: tempMessage.content
    });
    newMessage.value = '';
    await fetchMessages();
  } catch (error) {
    console.error('Lỗi khi gửi tin nhắn:', error);
    messages.value = messages.value.filter(msg => msg.id !== tempMessage.id);
  } finally {
    isSending.value = false; // Đảm bảo trạng thái trở về false
  }
};

// --- Utility Functions ---
const isMyMessage = (message) => {
  return parseInt(message.sender_id) === parseInt(userId.value);
};

// --- Lifecycle Hooks ---
onMounted(async () => {
  const user = JSON.parse(localStorage.getItem('user'));
  if (user && user.nguoi_dung_id) {
    userId.value = user.nguoi_dung_id;
    isAdmin.value = user.vai_tro_id === 1;

    const nguoiDungId = isAdmin.value ? 2 : userId.value;
    const adminId = isAdmin.value ? userId.value : 1;

    await getOrCreateConversation(nguoiDungId, adminId);

    if (conversationId.value) {
      await fetchMessages();
      pollingInterval = setInterval(fetchMessages, 3000);
    }
  } else {
    console.warn('Không tìm thấy thông tin người dùng trong localStorage.');
  }
});

onUnmounted(() => {
  clearInterval(pollingInterval);
});
</script>

<style scoped>
/* --- Chat Container & Widget --- */
.chat-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 1000;
}

.chat-widget {
  width: 400px; /* Tăng chiều rộng lên 400px */
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  overflow: hidden;
}

/* --- Chat Header --- */
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

.chat-header button {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  font-size: 1.2rem;
}

/* --- Chat Body & Messages --- */
.chat-body {
  height: 450px; /* Tăng chiều cao lên 450px */
  display: flex;
  flex-direction: column;
}

.messages {
  flex-grow: 1;
  padding: 10px;
  overflow-y: auto;
  background-color: #f8f9fa;
  display: flex;
  flex-direction: column;
}

.message-sent {
  align-self: flex-end;
  background-color: #a8e8fd;
  border-radius: 10px;
  padding: 8px;
  margin-bottom: 5px;
  width: fit-content;
  max-width: 70%;
  word-wrap: break-word;
}

.message-received {
  align-self: flex-start;
  background-color: #ffffff;
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  padding: 8px;
  margin-bottom: 5px;
  width: fit-content;
  max-width: 70%;
  word-wrap: break-word;
}

/* --- Chat Footer & Input --- */
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

.chat-footer button:disabled {
  cursor: not-allowed;
  opacity: 0.7;
}

.chat-footer button:hover:not(:disabled) {
  background-color: #2497d5;
}

/* --- Minimized Widget & Animations --- */
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
  transition: background-color 0.3s;
}

.restore-btn:hover {
  background-color: #2497d5;
}

/* Hiệu ứng xoay */
.fa-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
@media (max-width: 768px) {
  /* Center the chat container horizontally */
  

  /* Adjust the chat widget size for mobile screens */
  .chat-widget {
    width: 90vw; /* Use viewport width for a more fluid layout */
    max-width: 400px; /* Keep the max-width to prevent it from getting too wide on tablets */
  }
}
</style>