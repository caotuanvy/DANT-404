<template>
  <section class="content">
    <h2>Quản lý Slide</h2>
    <div v-if="showSuccess" class="toast-success">
      {{ successMessage }}
    </div>
    <button class="btn-toggle" @click="showForm = !showForm">
      {{ showForm ? 'Đóng form' : 'Thêm Slide Mới' }}
    </button>
    <form v-if="showForm" @submit.prevent="addSlide" class="form-slide">
      <h3 class="form-title">Thêm Slide Mới</h3>
      <input v-model="newSlideName" type="text" placeholder="Tên slide" class="input-text" required />
      <input type="file" multiple @change="handleNewSlideImages" class="input-file" />
      <button type="submit" class="btn-submit">Lưu slide</button>
    </form>

    <div v-for="slide in slides" :key="slide.slide_id" class="slide-card">
      <div v-if="editingSlideId === slide.slide_id" class="flex gap-2 items-center mb-2">
        <input v-model="editedSlideName" class="input-text" />
        <button class="btn-submit" @click="saveEditedSlideName(slide.slide_id)">Lưu</button>
        <button class="btn-cancel" @click="cancelEdit">Hủy</button>
      </div>
      <div v-else class="flex justify-between items-center mb-2">
        <h3 class="slide-title">{{ slide.ten_slide }}</h3>
        <div class="dropdown-container">
          <button class="dropdown-toggle" @click="toggleDropdown(slide.slide_id)">Tuỳ chọn </button>
          <div v-if="activeDropdown === slide.slide_id" class="dropdown-menu">
            <button @click="startEdit(slide.slide_id, slide.ten_slide)"> Sửa tên</button>
            <button @click="deleteSlide(slide.slide_id)"> Xóa</button>
            <button @click="chonHienThi(slide.slide_id)" :disabled="slide.hien_thi">
              {{ slide.hien_thi ? ' Đang hiển thị' : ' Hiển thị trang chủ' }}
            </button>
            <button @click="openAddImage(slide.slide_id)"> Thêm ảnh</button>
          </div>
        </div>
      </div>

      <form
        v-if="addImageSlideId === slide.slide_id"
        @submit.prevent="addImageToExistingSlide"
        class="form-slide form-sm"
      >
        <h4 class="form-title-sm">Thêm ảnh cho slide</h4>
        <input
          v-model="addImageForm.loai_anh"
          type="text"
          placeholder="Loại ảnh"
          class="input-text"
          required
        />
        <input type="file" @change="handleAddImageFile" class="input-file" required />
        <button type="submit" class="btn-submit">Thêm ảnh</button>
        <button type="button" class="btn-cancel" @click="cancelAddImage">Hủy</button>
      </form>

      <div class="image-grid">
        <div v-for="img in slide.hinh_anh" :key="img.loai_anh" class="image-item">
          <img :src="getImageUrl(img.duong_dan)" :alt="img.loai_anh" class="slide-img" />
          <p class="image-label">{{ img.loai_anh }}</p>
                <div class="setting-wrapper">
          <button class="setting-btn" @click="toggleSetting(slide.slide_id, img.loai_anh)">
            <i class="fas fa-cog"></i>
          </button>
          <div v-if="isSettingOpen(slide.slide_id, img.loai_anh)" class="setting-actions">
            <button class="delete-btn" @click="deleteImage(slide.slide_id, img.loai_anh)">
              <i class="fas fa-trash"></i> Xóa
            </button>
            <button class="edit-btn" @click="editImage(slide.slide_id, img.loai_anh)">
              <i class="fas fa-wrench"></i> Đổi ảnh
            </button>
            <button class="btn-cancel" @click="editLink(slide.slide_id, img.loai_anh, img.dieu_huong)">
              <i class="fas fa-link"></i> Điều hướng
            </button>
          </div>
        </div>


          <div v-if="isEditingLink(slide.slide_id, img.loai_anh)">
            <input v-model="linkForm.dieu_huong" class="input-text" placeholder="Nhập link điều hướng" @input="linkError = ''"/>
            <p v-if="linkError" class="text-danger" style="margin-top: 5px;">{{ linkError }}</p>
            <button class="btn-submit" @click="saveLinkUpdate">Lưu link</button>
            <button class="btn-cancel" @click="cancelEditLink">Hủy</button>
          </div>
        </div>
      </div>
    </div>

    <input type="file" ref="fileInput" @change="uploadImage" style="display: none" />
  </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
const successMessage = ref('')
const showSuccess = ref(false)
const linkError = ref('');
const slides = ref([])
const newSlideName = ref('')
const newImages = ref([])
const showForm = ref(false)
const editingSlideId = ref(null)
const editedSlideName = ref('')
const addImageSlideId = ref(null)
const addImageForm = ref({ slide_id: null, loai_anh: '', hinh_anh: null })
const selectedSlide = ref(null)
const selectedLoai = ref(null)
const fileInput = ref(null)
const activeDropdown = ref(null)
const linkForm = ref({ slide_id: null, loai_anh: '', dieu_huong: '' })
const openSettings = ref({})

const toggleDropdown = (id) => {
  activeDropdown.value = activeDropdown.value === id ? null : id
}
const closeDropdown = () => {
  activeDropdown.value = null
}

const handleClickOutside = (e) => {
  const dropdowns = document.querySelectorAll('.dropdown-container')
  let isInside = false
  dropdowns.forEach(dropdown => {
    if (dropdown.contains(e.target)) {
      isInside = true
    }
  })
  if (!isInside) closeDropdown()
}

const toggleSetting = (slideId, loaiAnh) => {
  const key = `${slideId}_${loaiAnh}`
  openSettings.value[key] = !openSettings.value[key]
}

const isSettingOpen = (slideId, loaiAnh) => {
  return openSettings.value[`${slideId}_${loaiAnh}`]
}
onMounted(() => {
  fetchSlides()
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

const getImageUrl = url => url ? `http://localhost:8000/storage/${url}` : 'https://placehold.co/100x66?text=No+Image'

const fetchSlides = async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/admin/slide')
    slides.value = res.data
  } catch (err) {
    console.error('Lỗi khi tải slide:', err)
  }
}

const handleNewSlideImages = e => newImages.value = Array.from(e.target.files)
const addSlide = async () => {
  if (!newSlideName.value || !newImages.value.length) return alert('Nhập tên slide và chọn ảnh!')
  const fd = new FormData()
  fd.append('ten_slide', newSlideName.value)
  newImages.value.forEach(f => fd.append('hinh_anh[]', f))
  await axios.post('http://localhost:8000/api/admin/slide', fd)
  newSlideName.value = ''
  newImages.value = []
  showForm.value = false
  fetchSlides()
}
const deleteImage = async (slide_id, loai_anh) => {
  if (!confirm('Bạn có chắc chắn muốn xóa ảnh này?')) return
  try {
    await axios.delete(`http://localhost:8000/api/admin/slide/${slide_id}/${loai_anh}`)
    alert('Xóa ảnh thành công')
    fetchSlides()
  } catch (err) {
    console.error('Lỗi xóa ảnh:', err)
    alert('Xóa ảnh thất bại')
  }
}
const showSuccessToast = (message) => {
  successMessage.value = message
  showSuccess.value = true

  setTimeout(() => {
    showSuccess.value = false
    successMessage.value = ''
  }, 3000) 
}
const startEdit = (id, name) => {
  editingSlideId.value = id
  editedSlideName.value = name
}
const cancelEdit = () => {
  editingSlideId.value = null; editedSlideName.value = ''
}
const saveEditedSlideName = async id => {
  await axios.post('http://localhost:8000/api/admin/slide/rename', { slide_id: id, ten_slide: editedSlideName.value })
  cancelEdit(); fetchSlides()
}

const deleteSlide = async id => {
  if (!confirm('Xóa slide?')) return;
  await axios.delete(`http://localhost:8000/api/admin/slide/${id}`)
  fetchSlides()
}

const chonHienThi = async id => {
  await axios.post('http://localhost:8000/api/admin/slide-hienthi', { slide_id: id })
  fetchSlides()
}

const openAddImage = slideId => {
  addImageSlideId.value = slideId
  addImageForm.value = { slide_id: slideId, loai_anh: '', hinh_anh: null }
}
const cancelAddImage = () => addImageSlideId.value = null
const handleAddImageFile = e => addImageForm.value.hinh_anh = e.target.files[0]
const addImageToExistingSlide = async () => {
  if (!addImageForm.value.loai_anh || !addImageForm.value.hinh_anh) return alert('Vui lòng nhập loại và chọn ảnh')
  const fd = new FormData()
  fd.append('slide_id', addImageForm.value.slide_id)
  fd.append('loai_anh', addImageForm.value.loai_anh)
  fd.append('hinh_anh', addImageForm.value.hinh_anh)
  try {
    await axios.post('http://localhost:8000/api/admin/slide/add-image', fd, { headers: {'Content-Type':'multipart/form-data'} })
    alert('Thêm ảnh thành công')
    cancelAddImage()
    fetchSlides()
  } catch (err) {
    console.error('Lỗi thêm ảnh:', err)
    alert('Thêm ảnh thất bại')
  }
}

const editImage = (slideId, loaiAnh) => {
  selectedSlide.value = slideId
  selectedLoai.value = loaiAnh
  fileInput.value.click()
}
const uploadImage = async e => {
  const file = e.target.files[0]
  if (!file) return
  const fd = new FormData()
  fd.append('slide_id', selectedSlide.value)
  fd.append('loai_anh', selectedLoai.value)
  fd.append('hinh_anh', file)
  try {
    await axios.post('http://localhost:8000/api/admin/slide/update', fd, { headers: {'Content-Type':'multipart/form-data'} })
    fetchSlides()
  } catch (err) {
    console.error('Lỗi khi cập nhật ảnh:', err)
    alert('Cập nhật ảnh thất bại')
  }
}

const editLink = (slideId, loaiAnh, dieuHuongCu) => {
  linkForm.value = {
    slide_id: slideId,
    loai_anh: loaiAnh,
    dieu_huong: dieuHuongCu || ''
  }
}

const cancelEditLink = () => {
  linkForm.value = { slide_id: null, loai_anh: '', dieu_huong: '' }
}

const isEditingLink = (slideId, loaiAnh) =>
  linkForm.value.slide_id === slideId && linkForm.value.loai_anh === loaiAnh

const isValidPathOrURL = (path) => {
  const pathRegex = /^\/[a-zA-Z0-9\-\/]*$/; // đường dẫn nội bộ kiểu: /slide/okha
  const urlRegex = /^(https?:\/\/)[^\s]+$/; // đường dẫn đầy đủ kiểu: http://localhost:5173/
  return pathRegex.test(path) || urlRegex.test(path);
}

const saveLinkUpdate = async () => {
  const trimmedLink = linkForm.value.dieu_huong?.trim() || ''
  if (!isValidPathOrURL(trimmedLink)) {
    linkError.value = 'Vui lòng nhập URL hợp lệ (http://...) hoặc đường dẫn bắt đầu bằng "/"';
    return
  }

  try {
    const payload = {
      slide_id: Number(linkForm.value.slide_id),
      loai_anh: linkForm.value.loai_anh,
      dieu_huong: trimmedLink
    }

    console.log('Dữ liệu gửi:', payload)

    await axios.post('http://localhost:8000/api/admin/slide/update-link', payload)

    fetchSlides()
    cancelEditLink()
    linkError.value = '' 
    showSuccessToast('Cập nhật thành công!')
  } catch (err) {
    console.error('Lỗi cập nhật link:', err.response?.data || err.message)
    alert('Không thể cập nhật link')
  }
}

</script>

<style scoped>
.content {
  max-width: 1000px;
  margin: 0 auto;
  padding: 2rem;
  background-color: #f9fafb;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}
.btn-active {
  background-color: #10b981 !important;
  color: white;
  cursor: default;
}
.btn-toggle,
.btn-submit,
.edit-btn,
.delete-btn,
.btn-cancel {
  padding: 4px 18px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.70rem;
  font-weight: 500;
  border: none;
  transition: 0.2s ease-in-out;
}
.btn-toggle,
.btn-submit,
.edit-btn {
  background-color: #3b82f6;
  color: white;
}
.btn-toggle:hover,
.btn-submit:hover,
.edit-btn:hover {
  background-color: #2563eb;
}
.btn-cancel {
  background-color: #6b7280;
  color: white;
}
.btn-cancel:hover {
  background-color: #4b5563;
}
.delete-btn {
  background-color: #ef4444;
  color: white;
}
.delete-btn:hover {
  background-color: #dc2626;
}
.form-slide {
  background-color: #fff;
  padding: 1.5rem;
  border-radius: 12px;
  margin-bottom: 2rem;
  box-shadow: 0 1px 6px rgba(0, 0, 0, 0.08);
}
.form-title {
  font-weight: bold;
  margin-bottom: 12px;
  font-size: 1.1rem;
}
.input-text {
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  width: 100%;
  margin-bottom: 12px;
}
.input-file {
  margin-top: 8px;
}
.slide-card {
  background: #fff;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 6px rgba(0, 0, 0, 0.08);
  margin-bottom: 2rem;
}
.slide-title {
  font-weight: 600;
  font-size: 1.1rem;
}
.image-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 20px;
  margin-top: 1rem;
  text-align: center;
}
.image-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}
.slide-img {
  width: 140px;
  height: 90px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  transition: transform 0.2s, box-shadow 0.2s;
}
.slide-img:hover {
  border-color: #3b82f6;
  transform: scale(1.05);
  box-shadow: 0 0 6px rgba(59, 130, 246, 0.3);
}
.image-label {
  font-size: 0.85rem;
  color: #444;
  margin-top: 6px;
  margin-bottom: 6px;
}
button {
  margin: 10px;
}
.dropdown-container {
  position: relative;
  display: inline-block;
}
.dropdown-toggle {
  background-color: #3b82f6;
  color: white;
  padding: 8px 14px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  border: none;
}
.dropdown-menu {
  position: absolute;
  top: 110%;
  left: 0;
  z-index: 100;
  background-color: white;
  border: 1px solid #ccc;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  min-width: 180px;
  padding: 0.5rem 0;
  display: flex;
  flex-direction: column;
}
.dropdown-menu button {
  background: none;
  border: none;
  padding: 8px 14px;
  text-align: left;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background 0.2s;
}
.dropdown-menu button:hover {
  background-color: #f3f4f6;
}
.image-actions {
  display: flex;
  gap: 10px;
}
.setting-wrapper {
  margin-top: 8px;
  text-align: center;
}

.setting-btn {
  background: #000000;
  border: none;
  border-radius: 50%;
  width: 45px;
  padding: 10px;
  font-size: 16px;
  cursor: pointer;
  color:#ffffff;
  transition: background 0.2s, color 0.2s;
}
.setting-btn:hover {
  background: #dbdbdb;
  color: #000000;
  
}

.setting-actions {
  margin-top: 8px;
  display: flex;
  justify-content: center;
  gap: 10px;
}
.toast-success {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  background-color: #4caf50;
  color: white;
  padding: 12px 24px;
  border-radius: 6px;
  font-weight: bold;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
  z-index: 9999;
  transition: opacity 0.3s ease-in-out;
}

</style>
