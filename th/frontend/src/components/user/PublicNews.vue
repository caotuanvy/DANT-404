<template>
  <div class="vohop-container">
    <div class="vohop-main">
      <h1 class="vohop-title">Tin tức mới nhất</h1>
      <div class="vohop-newsgrid">
        <div class="vohop-newsitem" v-for="item in newsList" :key="item.id">
          <img
            :src="item.hinh_anh
              ? (item.hinh_anh.startsWith('http')
                  ? item.hinh_anh
                  : `http://localhost:8000/storage/${item.hinh_anh}`)
              : 'https://via.placeholder.com/400x180'"
            alt="Hình ảnh"
          >
          <div class="vohop-newsitem-content">
            <h3>{{ item.tieude }}</h3>
            <div v-html="getNoiDungSnippet(item.noidung)"></div>
          </div>
          <div class="vohop-newsitem-footer">
            <span>
              <i class="fa-regular fa-calendar"></i>
              {{ item.ngay_dang }}
            </span>
            <button class="vohop-btn" @click="goToNewsDetail(item.id)">Xem chi tiết</button>
          </div>
        </div>
      </div>
    </div>
    <aside class="vohop-benphai">
      <div class="vohop-danhmuc">
        <h3><i class="fa-solid fa-list"></i> DANH MỤC</h3>
        <ul>
          <li
            v-for="(item,) in danhMucList"
            :key="item.id_danh_muc_tin_tuc"
            :class="{ active: activeDanhMuc === item.id_danh_muc_tin_tuc }"
            @click="handleSelectDanhMuc(item.id_danh_muc_tin_tuc)"
          >
            <i class="fa-solid fa-angle-right"></i> {{ item.ten_danh_muc }}
          </li>
        </ul>
      </div>
      <div class="vohop-tinnoibat">
        <h3><i class="fa-solid fa-eye"></i> TIN XEM NHIỀU NHẤT</h3>
        <div
          class="vohop-tinnoibat-item"
          v-for="tin in tinNoiBatList"
          :key="tin.id"
          @click="goToNewsDetail(tin.id)"
        >
          <img :src="tin.hinh_anh ? (tin.hinh_anh.startsWith('http') ? tin.hinh_anh : `http://localhost:8000/storage/${tin.hinh_anh}`) : 'https://via.placeholder.com/60x60'" alt="">
          <span>{{ tin.tieude }}</span>
        </div>
      </div>
      <div class="vohop-tags">
        <h3><i class="fa-solid fa-tags"></i> Tags</h3>
        <button>#burger</button>
        <button>#khuyến mãi</button>
        <button>#pizza</button>
        <button>#gà rán</button>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router' // Import useRouter để điều hướng

const newsList = ref([])
const activeDanhMuc = ref(null) // Sẽ lưu ID danh mục đang hoạt động
const danhMucList = ref([])
const tinNoiBatList = ref([])

const router = useRouter() // Khởi tạo router

onMounted(async () => {
  await fetchDanhMucList()
  await fetchAllNews()
  await fetchTinNoiBat()
})

async function fetchDanhMucList() {
  try {
    const res = await fetch('http://localhost:8000/api/danh-muc-tin-tuc')
    danhMucList.value = await res.json()
  } catch (err) {
    console.error("Lỗi khi tải danh mục:", err)
    danhMucList.value = []
  }
}

async function fetchAllNews() {
  try {
    const res = await fetch('http://localhost:8000/api/tintuc-cong-khai')
    newsList.value = await res.json()
  } catch (err) {
    console.error("Lỗi khi tải tất cả tin tức công khai:", err)
    newsList.value = []
  }
}

// Thay đổi handleSelectDanhMuc để truyền ID trực tiếp
async function handleSelectDanhMuc(id) {
  activeDanhMuc.value = id // Cập nhật activeDanhMuc bằng ID
  try {
    const res = await fetch(`http://localhost:8000/api/tintuc-cong-khai/danh-muc/${id}`)
    newsList.value = await res.json()
  } catch (err) {
    console.error("Lỗi khi tải tin tức theo danh mục:", err)
    newsList.value = []
  }
}

async function fetchTinNoiBat() {
  try {
    const res = await fetch('http://localhost:8000/api/tin-noi-bat')
    tinNoiBatList.value = await res.json()
  } catch (err) {
    console.error("Lỗi khi tải tin nổi bật:", err)
    tinNoiBatList.value = []
  }
}

// Chức năng chuyển hướng đến trang chi tiết tin tức
function goToNewsDetail(newsId) {
  router.push({ name: 'ChiTietTinTucCongKhai', params: { id: newsId } })
  // Lưu ý: 'NewsDetail' là tên route bạn cần định nghĩa trong router của Vue
  // Ví dụ trong router/index.js (hoặc router.js) của bạn:
  // {
  //   path: '/tin-tuc/:id',
  //   name: 'NewsDetail',
  //   component: () => import('../views/NewsDetail.vue') // Đường dẫn đến component chi tiết tin tức
  // }
}

// Hàm kiểm tra và xử lý nội dung để hiển thị snippet
function getNoiDungSnippet(noidung) {
  // Ưu tiên hiển thị HTML nếu nó là kết quả từ AI (không phải JSON EditorJS)
  // Nếu nội dung là HTML (từ API generateSeoContent), nó sẽ không phải JSON.
  // Chúng ta sẽ hiển thị một đoạn nhỏ của nội dung đó.
  if (typeof noidung === 'string' && noidung.startsWith('<')) { // Giả định HTML sẽ bắt đầu bằng thẻ
    // Loại bỏ các thẻ HTML để lấy văn bản thuần túy và cắt ngắn
    const doc = new DOMParser().parseFromString(noidung, 'text/html')
    const textContent = doc.body.textContent || ""
    return textContent.length > 150 ? textContent.slice(0, 150) + '...' : textContent
  }

  // Nếu là JSON từ EditorJS (nếu bạn vẫn dùng ở một số nơi)
  try {
    const parsed = JSON.parse(noidung)
    if (parsed && typeof parsed === 'object' && parsed.blocks) {
      // Chuyển đổi một phần nhỏ của các khối thành HTML để làm snippet
      let snippet = ''
      for (const block of parsed.blocks) {
        if (block.type === 'paragraph' || block.type === 'header') {
          snippet += block.data.text + ' '
          if (snippet.length > 150) break
        }
      }
      return snippet.slice(0, 150) + (snippet.length > 150 ? '...' : '')
    }
  } catch (e) {
    // Không phải JSON hợp lệ, xử lý như văn bản thuần
  }

  // Nếu không phải JSON và không phải HTML rõ ràng, xử lý như văn bản thuần túy
  return noidung ? (noidung.length > 150 ? noidung.slice(0, 150) + '...' : noidung) : ''
}
</script>

<style scoped>
/* Giữ nguyên phần CSS */
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css');

:root {
  --main-blue: #33ccff;
  --main-bg: #fff;
  --main-bg-light: #f4fbfd;
  --main-border: #b3eaff;
  --main-shadow: 0 2px 12px rgba(51,204,255,0.08);
}

.vohop-container {
  display: flex;
  max-width: 1200px;
  margin: 0 auto;
  padding: 32px 64px;
  gap: 36px;
  background: var(--main-bg-light);
  min-height: 100vh;
}
.vohop-main {
  flex: 3;
}
.vohop-title {
  font-size: 2.2rem;
  font-weight: 700;
  color: var(--main-blue);
  margin-bottom: 28px;
  letter-spacing: 1px;
  text-shadow: 0 2px 8px rgba(51,204,255,0.10);
}
.vohop-newsgrid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 28px;
}
.vohop-newsitem {
  background: var(--main-bg);
  border-radius: 14px;
  overflow: hidden;
  box-shadow: var(--main-shadow);
  display: flex;
  flex-direction: column;
  transition: box-shadow 0.2s, transform 0.2s;
  cursor: pointer;
  border: 1.5px solid var(--main-border);
}
.vohop-newsitem:hover {
  box-shadow: 0 6px 24px rgba(51,204,255,0.18);
  transform: translateY(-4px) scale(1.02);
  border-color: var(--main-blue);
}
.vohop-newsitem img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  border-bottom: 1px solid #e3f7fb;
}
.vohop-newsitem-content {
  padding: 18px 16px 10px 16px;
  flex: 1;
}
.vohop-newsitem h3 {
  font-size: 1.1rem;
  margin: 0 0 8px;
  color: #222;
  font-weight: 600;
}
.vohop-newsitem p {
  font-size: 14px;
  color: #666;
  flex-grow: 1;
  margin: 0;
}
.vohop-newsitem-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  border-top: 1px solid #e3f7fb;
  background: #eafaff;
}
.vohop-newsitem-footer span {
  font-size: 13px;
  color: var(--main-blue);
  display: flex;
  align-items: center;
  gap: 5px;
}
.vohop-btn {
  background: var(--main-bg);
  border: 1.5px solid var(--main-blue);
  color: var(--main-blue);
  padding: 6px 16px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 500;
  transition: background 0.2s, color 0.2s, border 0.2s;
}
.vohop-btn:hover {
  background: #1ba6d9;
  color: #fff;
  border-color: #1ba6d9;
}
.vohop-benphai {
  flex: 1;
  min-width: 270px;
}
.vohop-benphai h3 {
  margin-top: 0;
  font-size: 1.08rem;
  border-bottom: 1.5px solid var(--main-border);
  padding-bottom: 7px;
  color: var(--main-blue);
  font-weight: 700;
  letter-spacing: 0.5px;
  display: flex;
  align-items: center;
  gap: 7px;
}
.vohop-danhmuc, .vohop-tinnoibat, .vohop-tags {
  background: var(--main-bg);
  padding: 18px 16px;
  margin-bottom: 22px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(51,204,255,0.04);
  border: 1.5px solid var(--main-border);
}
.vohop-danhmuc ul {
  list-style: none;
  padding: 0;
  margin: 0;
}
.vohop-danhmuc li {
  margin-bottom: 10px;
  color: #333;
  font-size: 15px;
  display: flex;
  align-items: center;
  gap: 7px;
  transition: color 0.2s, background 0.2s;
  cursor: pointer;
  padding: 5px 8px; /* Thêm padding để dễ click */
}
.vohop-danhmuc li:last-child {
    margin-bottom: 0;
}
.vohop-danhmuc li:hover {
  color: var(--main-blue);
  background: #eafaff; /* Thêm hover background */
  border-radius: 6px;
}
.vohop-danhmuc li.active {
  background: #eafaff;
  color: var(--main-blue);
  border-radius: 6px;
  font-weight: 700;
}
.vohop-tinnoibat-item img {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 7px;
  margin-bottom: 0;
  border: 1.5px solid var(--main-border);
  background: #eafaff;
  flex-shrink: 0; /* Đảm bảo ảnh không bị co lại */
}
.vohop-tinnoibat-item {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
  cursor: pointer;
  transition: background 0.2s, color 0.2s, box-shadow 0.2s;
  padding: 8px 0; /* Thêm padding để dễ click */
}
.vohop-tinnoibat-item:last-child {
    margin-bottom: 0;
}
.vohop-tinnoibat-item:hover,
.vohop-tinnoibat-item:focus {
  background: #eafaff;
  color: var(--main-blue);
  box-shadow: 0 2px 8px #33ccff44;
  border-radius: 8px; /* Bo tròn khi hover */
}
.vohop-tinnoibat-item:active {
  background: #33ccff;
  color: #fff;
  box-shadow: 0 2px 12px #33ccff55;
}
.vohop-tinnoibat-item span {
  font-size: 14px;
  color: #333;
  font-weight: 500;
  line-height: 1.4; /* Tăng line-height cho dễ đọc */
  flex-grow: 1; /* Cho phép text mở rộng */
}
.vohop-tags button {
  border: 1.5px solid var(--main-blue);
  background: #eafaff;
  color: var(--main-blue);
  border-radius: 4px;
  padding: 5px 14px;
  font-size: 13px;
  margin: 4px 6px 4px 0;
  cursor: pointer;
  transition: background 0.2s, color 0.2s, border 0.2s, box-shadow 0.2s;
}
.vohop-tags button:active {
  background: #1ba6d9;
  color: #fff;
  border-color: #1ba6d9;
  box-shadow: 0 2px 12px #33ccff55;
}
</style>