<template>
  <div class="page-container">
    <header class="page-header">
      <div>
        <h1 class="page-title">Slide Management</h1>
        <p class="page-subtitle">Tạo và quản lý nội dung slideshow của bạn</p>
      </div>
      <button class="btn btn-primary" @click="showAddForm = !showAddForm">
        <span v-if="!showAddForm">＋ Thêm Slide mới</span>
        <span v-else>Đóng Form</span>
      </button>
    </header>

    <Transition name="form-fade">
      <div v-if="showAddForm" class="form-container">
        <form @submit.prevent="addSlide" class="form-card">
          <h3 class="form-title">Thêm Slide Mới</h3>
          <div class="form-group">
            <label for="new-slide-name">Tên slide</label>
            <input id="new-slide-name" v-model="newSlideName" type="text" placeholder="Ví dụ: Khuyến mãi mùa hè" class="input-control" required />
          </div>
          <div class="form-group">
            <label for="new-slide-images">Hình ảnh (có thể chọn nhiều)</label>
            <input id="new-slide-images" type="file" multiple @change="handleNewSlideImages" class="input-file" required />
          </div>
          <div class="form-actions">
            <button type="button" class="btn btn-secondary" @click="showAddForm = false">Hủy</button>
            <button type="submit" class="btn btn-primary">Tạo Slide</button>
          </div>
        </form>
      </div>
    </Transition>

    <div class="slide-list">
      <div v-for="slide in slides" :key="slide.slide_id" class="slide-item">
        <div class="drag-handle" title="Kéo để sắp xếp">⠿</div>
        <div class="slide-thumbnail">
          <img :src="getImageUrl(slide.hinh_anh[0]?.duong_dan)" :alt="slide.ten_slide" />
        </div>
        <div class="slide-content">
          <h4 class="slide-name">{{ slide.ten_slide }}</h4>
          <div class="slide-meta">
            <span class="status-badge" :class="slide.hien_thi ? 'Đã hiển thị' : 'Chưa hiển thị'">
              {{ slide.hien_thi ? 'Đã hiển thị' : 'Chưa hiển thị' }}
            </span>
          </div>
        </div>
        <div class="slide-actions">
            <button @click="openEditModal(slide)" class="action-icon" title="Sửa chi tiết">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
            </button>
            <button @click="deleteSlide(slide.slide_id)" class="action-icon danger" title="Xóa">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
            </button>
        </div>
      </div>
    </div>
    
    <Transition name="modal-fade">
      <div v-if="isModalOpen" class="modal-overlay" @click.self="closeModal">
        <div class="modal-panel">
          <div class="modal-header">
            <h2 class="modal-title">Chỉnh Sửa Slide</h2>
            <button @click="closeModal" class="modal-close-btn">×</button>
          </div>
          
          <div v-if="editableSlide" class="modal-body">
            <div class="form-column">
              <div class="form-group">
                <label for="slide-title">Tên slide</label>
                <input id="slide-title" v-model="editableSlide.ten_slide" type="text" class="input-control" />
              </div>
              <div class="form-group-checkbox">
                <input id="slide-active" v-model="editableSlide.hien_thi" type="checkbox" class="checkbox-control" />
                <label for="slide-active">Hiển thị (active)</label>
              </div>
              <div class="form-group">
                  <label>Thêm ảnh vào slide</label>
                  <form @submit.prevent="addImageToExistingSlide" class="form-add-image-inline">
                    <input v-model="addImageForm.loai_anh" type="text" placeholder="Loại ảnh (vd: banner-1)" class="input-control" required />
                    <input type="file" @change="handleAddImageFile" class="input-file" required />
                    <button type="submit" class="btn btn-primary btn-sm">Thêm</button>
                  </form>
              </div>
            </div>
            
            <div class="preview-column">
              <label>Quản lý hình ảnh</label>
              <div v-if="!editableSlide.hinh_anh || editableSlide.hinh_anh.length === 0" class="image-management-empty">
                  <p>Slide này chưa có ảnh.</p>
              </div>
              <div v-else class="image-management-grid" :key="imageCacheKey">
                <div v-for="img in editableSlide.hinh_anh" :key="img.id" class="image-card">
                    <img :src="getImageUrl(img.duong_dan) + '?t=' + imageCacheKey" class="image-card-thumb" />
                    <div class="image-card-actions">
                        <button @click="triggerImageEdit(img.id)" title="Đổi ảnh">🖼️</button>
                        <button @click="deleteImage(img.id, editableSlide.slide_id)" title="Xoá ảnh">🗑️</button>
                    </div>
                    <div class="image-card-link-form">
                            <input v-model="img.dieu_huong" class="input-control input-sm" placeholder="Link (VD: /san-pham)" @blur="updateLink(img)"/>
                    </div>
                </div>
            </div>
            </div>
          </div>
          
          <div class="modal-footer">
            <button @click="closeModal" class="btn btn-secondary">Hủy</button>
            <button @click="handleSave" class="btn btn-primary">Lưu thay đổi</button>
          </div>
        </div>
      </div>
    </Transition>
    
    <input type="file" ref="fileInput" @change="handleFileChange" style="display: none" />
    <div v-if="showSuccess" class="toast-success">{{ successMessage }}</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// --- State Management ---
const slides = ref([]);
const imageCacheKey = ref(Date.now());
const successMessage = ref('');
const showSuccess = ref(false);

// State cho form Thêm Mới
const showAddForm = ref(false);
const newSlideName = ref('');
const newImages = ref([]);

// State cho Modal Chỉnh Sửa
const isModalOpen = ref(false);
const editableSlide = ref(null);
const originalSlideStatus = ref(null);

// State cho các chức năng quản lý ảnh
const fileInput = ref(null);
const selectedImageId = ref(null);
const addImageForm = ref({ loai_anh: '', hinh_anh: null });
const addImageInputRef = ref(null);

// --- API & Data Fetching ---
const getImageUrl = (url) => url ? `http://localhost:8000/storage/${url}` : 'https://placehold.co/100x60?text=No+Image';

const fetchSlides = async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/admin/slide');
    slides.value = res.data;
  } catch (error) {
    console.error("Lỗi khi tải slide:", error);
    showToast("Không thể tải dữ liệu slide.", 'error');
  }
};

onMounted(fetchSlides);

// --- Logic cho Form Thêm Mới ---
const handleNewSlideImages = e => newImages.value = Array.from(e.target.files);

const addSlide = async () => {
  const fd = new FormData();
  fd.append('ten_slide', newSlideName.value);
  if (newImages.value.length === 0) {
      alert('Vui lòng chọn ít nhất một ảnh.');
      return;
  }
  newImages.value.forEach(f => fd.append('hinh_anh[]', f));
  
  try {
    await axios.post('http://localhost:8000/api/admin/slide', fd);
    newSlideName.value = '';
    newImages.value = [];
    document.getElementById('new-slide-images').value = '';
    showAddForm.value = false;
    fetchSlides();
    showToast('Thêm slide thành công');
  } catch(error) {
    console.error("Lỗi khi thêm slide:", error);
    showToast('Thêm slide thất bại.', 'error');
  }
};


// --- Logic cho Modal Chỉnh Sửa ---
const openEditModal = (slide) => {
  editableSlide.value = JSON.parse(JSON.stringify(slide));
  originalSlideStatus.value = slide.hien_thi;
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  editableSlide.value = null;
  originalSlideStatus.value = null;
};

const handleSave = async () => {
    if (!editableSlide.value) return;
    try {
        await axios.post('http://localhost:8000/api/admin/slide/rename', { 
            slide_id: editableSlide.value.slide_id, 
            ten_slide: editableSlide.value.ten_slide 
        });
        
        if (editableSlide.value.hien_thi !== originalSlideStatus.value) {
            await axios.post('http://localhost:8000/api/admin/slide-hienthi', { slide_id: editableSlide.value.slide_id });
        }
        
        showToast('Đã cập nhật slide thành công!');
        closeModal();
        fetchSlides();
    } catch (error) {
        console.error("Lỗi khi lưu slide:", error);
        showToast('Lưu slide thất bại.', 'error');
    }
};


// --- Các chức năng quản lý ảnh (bên trong Modal) ---

const handleAddImageFile = e => {
    addImageForm.value.hinh_anh = e.target.files[0];
    addImageInputRef.value = e.target;
};

const addImageToExistingSlide = async () => {
    if (!addImageForm.value.hinh_anh || !editableSlide.value) return;
    const fd = new FormData();
    fd.append('slide_id', editableSlide.value.slide_id);
    fd.append('loai_anh', addImageForm.value.loai_anh);
    fd.append('hinh_anh', addImageForm.value.hinh_anh);

    try {
        await axios.post('http://localhost:8000/api/admin/slide/add-image', fd);
        
        const res = await axios.get(`http://localhost:8000/api/admin/slide/${editableSlide.value.slide_id}`);
        
        // [FIX] Dùng Object.assign để cập nhật đối tượng hiện tại một cách an toàn
        if(res.data) {
            Object.assign(editableSlide.value, res.data);
        }

        imageCacheKey.value = Date.now();
        fetchSlides();
        showToast('Thêm ảnh thành công');
        
        addImageForm.value = { loai_anh: '', hinh_anh: null };
        if (addImageInputRef.value) addImageInputRef.value.value = '';

    } catch(error) {
        console.error("Lỗi khi thêm ảnh vào slide:", error);
        showToast("Thêm ảnh thất bại.", 'error');
    }
};

const triggerImageEdit = (imageId) => {
  selectedImageId.value = imageId;
  fileInput.value.click();
};

const handleFileChange = async e => {
    const file = e.target.files[0];
    if (!file || !selectedImageId.value) return;

    const fd = new FormData();
    fd.append('hinh_anh', file);
    try {
        await axios.post(`http://localhost:8000/api/admin/slide/image/update-image/${selectedImageId.value}`, fd, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        const res = await axios.get(`http://localhost:8000/api/admin/slide/${editableSlide.value.slide_id}`);

        // [FIX] Dùng Object.assign để đảm bảo tính nhất quán
        if(res.data) {
           Object.assign(editableSlide.value, res.data);
        }

        imageCacheKey.value = Date.now();
        fetchSlides();
        showToast('Cập nhật ảnh thành công');
    } catch(error) {
        console.error("Lỗi khi cập nhật ảnh:", error);
        showToast("Cập nhật ảnh thất bại.", 'error');
    } finally {
        selectedImageId.value = null;
        fileInput.value.value = '';
    }
};

const deleteImage = (imageId, slideId) => {
  Swal.fire({
    title: 'Xóa ảnh này?',
    text: "Hành động này sẽ xóa ảnh vĩnh viễn và không thể hoàn tác!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33', // Màu đỏ cho nút xóa
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Vâng, xóa đi!',
    cancelButtonText: 'Hủy'
  }).then(async (result) => {
    if (result.isConfirmed) {
      // ---- Bắt đầu logic xóa ----
      try {
        await axios.delete(`http://localhost:8000/api/admin/slide/image/${imageId}`);
        
        const res = await axios.get(`http://localhost:8000/api/admin/slide/${slideId}`);
        
        if (res.data) {
          Object.assign(editableSlide.value, res.data);
        } else {
          closeModal();
        }

        imageCacheKey.value = Date.now();
        fetchSlides();
        showToast('Xóa ảnh thành công');

      } catch (error) {
        console.error("Lỗi khi xóa ảnh:", error);
        showToast("Xóa ảnh thất bại.", 'error');
        if (error.response && error.response.status === 404) {
          closeModal();
        }
      }
      
    }
  });
};

const updateLink = async (image) => {
    const trimmed = image.dieu_huong ? image.dieu_huong.trim() : '';
    try {
        await axios.post('http://localhost:8000/api/admin/slide/update-link', {
            id: image.id,
            dieu_huong: trimmed
        });
        showToast('Đã cập nhật link điều hướng');
    } catch (error) {
        console.error("Lỗi khi cập nhật link:", error);
        showToast("Cập nhật link thất bại.", 'error');
    }
};

const deleteSlide = async id => {
  if (!confirm('Bạn có chắc chắn muốn xóa slide này? Hành động này không thể hoàn tác.')) return;
  try {
    await axios.delete(`http://localhost:8000/api/admin/slide/${id}`);
    fetchSlides();
    showToast('Đã xóa slide');
    
  } catch (error) {
    console.error("Lỗi khi xóa slide:", error);
    showToast("Xóa slide thất bại.", 'error');
  }
};

const showToast = (message) => {
  successMessage.value = message;
  showSuccess.value = true;
  setTimeout(() => {
    showSuccess.value = false;
    successMessage.value = '';
  }, 3000);
};
</script>

<style scoped>
/* === CSS Variables === */
:root {
  --color-primary: #4f46e5;
  --color-primary-hover: #4338ca;
  --color-danger: #ef4444;
  --color-danger-hover: #dc2626;
  --color-text-primary: #1f2937;
  --color-text-secondary: #6b7280;
  --color-bg-page: #f8f9fa;
  --color-bg-card: #ffffff;
  --color-border: #e5e7eb;
  --color-green: #10b981;
  --color-gray: #9ca3af;

  --font-sans: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  --radius-md: 8px;
  --radius-lg: 12px;
}

/* === Overall Layout === */
.page-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  font-family: var(--font-sans);
  background-color: var(--color-bg-page);
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--color-border);
}

.page-title {
  font-size: 1.875rem; /* 30px */
  font-weight: 700;
  color: var(--color-text-primary);
  line-height: 1.2;
}

.page-subtitle {
  font-size: 1rem;
  color: var(--color-text-secondary);
  margin-top: 0.25rem;
}

/* === Forms === */
.form-container {
  margin-bottom: 2rem;
}
.form-card {
  background-color: var(--color-bg-card);
  border-radius: var(--radius-lg);
  padding: 1.5rem;
  border: 1px solid var(--color-border);
  box-shadow: var(--shadow-sm);
}
.form-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
  color: var(--color-text-primary);
}
.form-group {
  margin-bottom: 1rem;
}
.form-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-text-primary);
  margin-bottom: 0.5rem;
}
.input-control {
  width: 100%;
  padding: 0.6rem 0.8rem;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  background-color: #fff;
  transition: border-color 0.2s, box-shadow 0.2s;
  box-sizing: border-box;
}
.input-control:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-primary) 20%, transparent);
}
.input-control.input-sm {
  padding: 0.4rem 0.6rem;
  font-size: 0.875rem;
}
.input-file {
  font-size: 0.9rem;
  color: var(--color-text-secondary);
}
.form-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
}
.form-fade-enter-active,
.form-fade-leave-active {
  transition: all 0.3s ease;
  max-height: 300px;
}
.form-fade-enter-from,
.form-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
  max-height: 0;
}


/* === Slide List === */
.slide-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
img {
  border-radius:0px !important;
}

.slide-item {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding: 1rem;
  border-radius: var(--radius-lg);
  border: 1px solid #4FC3F7;
  box-shadow: var(--shadow-sm);
  transition: box-shadow 0.2s ease, transform 0.2s ease;
  background-color:#9bdfff ;
}

.slide-item:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.drag-handle {
  cursor: grab;
  color: var(--color-text-secondary);
  font-size: 1.5rem;
  line-height: 1;
}

.slide-thumbnail {
  width: 100px;
  height: 60px;
  border-radius: var(--radius-md);
  overflow: hidden;
  flex-shrink: 0;
  background-color: #f0f0f0;
}
.slide-thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.slide-content {
  flex-grow: 1;
}

.slide-name {
  font-size: 1rem;
  font-weight: 600;
  color: var(--color-text-primary);
  margin: 0;
}

.slide-meta {
  display: flex;
  align-items: center;
  gap: 1rem;
  font-size: 0.875rem;
  color: var(--color-text-secondary);
  margin-top: 0.25rem;
}

.status-badge {
  font-size: 0.75rem;
  font-weight: 500;
  padding: 0.15rem 0.6rem;
  border-radius: 9999px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.status-badge.active {
  color: var(--color-green);
  background-color: color-mix(in srgb, var(--color-green) 15%, transparent);
}
.status-badge.inactive {
  color: var(--color-gray);
  background-color: color-mix(in srgb, var(--color-gray) 15%, transparent);
}

/* === Action Buttons === */
.slide-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.action-icon {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: transparent;
  color: var(--color-text-secondary);
  border: none;
  border-radius: 50%;
  cursor: pointer;
  transition: background-color 0.2s, color 0.2s;
}
.action-icon:hover:not(:disabled) {
  background-color: #f3f4f6; /* gray-100 */
  color: var(--color-text-primary);
}
.action-icon.danger:hover {
  background-color: color-mix(in srgb, var(--color-danger) 10%, transparent);
  color: var(--color-danger);
}
.action-icon:disabled {
  color: #d1d5db; /* gray-300 */
  cursor: not-allowed;
}
.action-icon svg {
  width: 20px;
  height: 20px;
}


/* === General Buttons === */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem 1rem;
  border-radius: var(--radius-md);
  font-weight: 600;
  border: 1px solid transparent;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}
.btn-primary {
  background-color:#4FC3F7 !important;
  color: white;
  box-shadow: var(--shadow-sm);
  padding: 8px 16px;
  border: none;
}
.btn-primary:hover {
  background-color: var(--color-primary-hover);
  box-shadow: var(--shadow-md);
}
.btn-secondary {
  background-color: var(--color-bg-card);
  color: var(--color-text-secondary);
  border-color: var(--color-border);
}
.btn-secondary:hover {
  background-color: #f9fafb; /* gray-50 */
  border-color: #d1d5db; /* gray-300 */
  color: var(--color-text-primary);
}
.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
}


/* === Modal Styles === */
.modal-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(31, 41, 55, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-panel {
  width: 100%;
  max-width: 900px;
  background-color: var(--color-bg-card);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  display: flex;
  flex-direction: column;
  max-height: 90vh;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--color-border);
  flex-shrink: 0;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--color-text-primary);
}

.modal-close-btn {
  background: none;
  border: none;
  font-size: 1.75rem;
  line-height: 1;
  color: var(--color-text-secondary);
  cursor: pointer;
}

.modal-body {
  padding: 1.5rem;
  display: grid;
  grid-template-columns: 1fr 1.5fr; 
  gap: 2rem;
  overflow-y: auto;
  background-color: white;
}

.form-add-image-inline {
    display: grid;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.form-group-checkbox {
  display: flex;
  align-items: center;
  margin-top: 1rem;
}

.checkbox-control {
  height: 1rem;
  width: 1rem;
  border-radius: 4px;
  border: 1px solid var(--color-border);
  margin-right: 0.5rem;
  flex-shrink: 0;
}
.checkbox-control:checked {
  background-color: var(--color-primary);
}


.image-management-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
    background-color: white;
}

.image-management-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    min-height: 150px;
    border: 2px dashed var(--color-border);
    border-radius: var(--radius-md);
    color: var(--color-text-secondary);
}

.image-card {
    position: relative;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    overflow: hidden;
}

.image-card-thumb {
    width: 100%;
    aspect-ratio: 16 / 10;
    object-fit: cover;
    display: block;
}

.image-card-actions {
    position: absolute;
    top: 4px;
    right: 4px;
    display: flex;
    gap: 4px;
    background: rgba(0,0,0,0.5);
    border-radius: var(--radius-md);
    padding: 4px;
    opacity: 0;
    transition: opacity 0.2s;
}
.image-card:hover .image-card-actions {
    opacity: 1;
}
.image-card-actions button {
    background: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
}

.image-card-link-form {
    padding: 0.5rem;
    background-color: #f9fafb;
}

.modal-footer {
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  border-top: 1px solid var(--color-border);
  background-color: #f9fafb;
  border-bottom-left-radius: var(--radius-lg);
  border-bottom-right-radius: var(--radius-lg);
  flex-shrink: 0;
}

/* Modal Transition */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}
.modal-fade-enter-active .modal-panel,
.modal-fade-leave-active .modal-panel {
  transition: transform 0.3s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
.modal-fade-enter-from .modal-panel,
.modal-fade-leave-to .modal-panel {
  transform: scale(0.95);
}
.btn-primary {
  background-color: var(--color-primary);
  color: white;
  box-shadow: var(--shadow-sm);
}

/* === Toast Notification === */
.toast-success {
  position: fixed;
  top: 20px;
  right: 20px;
  background-color: #22c55e;
  color: white;
  padding: 1rem 1.5rem;
  border-radius: var(--radius-md);
  font-weight: 500;
  box-shadow: var(--shadow-md);
  z-index: 9999;
}
@media (max-width: 768px) {
    /* --- Bố cục chung & Header --- */
    .page-container {
        padding: 1rem;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .page-title {
        font-size: 1.5rem;
    }

    .page-header .btn {
        width: 100%;
    }

    /* --- Form Thêm Mới --- */
    .form-card {
        padding: 1rem;
    }
    .form-actions {
        flex-direction: column-reverse; /* Đảo ngược thứ tự nút */
        gap: 0.5rem;
    }
    .form-actions .btn {
        width: 100%;
    }

    /* --- Danh sách Slide Items --- */
    .slide-item {
        flex-direction: column; /* Xếp các thành phần theo chiều dọc */
        align-items: flex-start; /* Căn các thành phần về bên trái */
        gap: 0.75rem;
        padding: 0.75rem;
    }
    
    .drag-handle {
        display: none; /* Ẩn icon kéo thả trên mobile */
    }

    .slide-thumbnail {
        width: 100%; /* Thumbnail chiếm toàn bộ chiều rộng card */
        height: auto;
        aspect-ratio: 16 / 9; /* Giữ tỉ lệ khung hình */
    }

    .slide-content {
        width: 100%;
    }
    
    .slide-actions {
        width: 100%;
        border-top: 1px solid var(--color-border);
        padding-top: 0.75rem;
        justify-content: flex-end; /* Đẩy các nút hành động về bên phải */
    }

    /* --- Cửa sổ Modal Chỉnh sửa --- */
    .modal-panel {
        /* Cho modal chiếm gần hết màn hình */
        width: 95%;
        max-width: 95%;
        max-height: 90vh;
    }

    .modal-body {
        /* Quan trọng: Chuyển layout từ 2 cột thành 1 cột */
        grid-template-columns: 1fr;
        padding: 1rem;
        gap: 1.5rem;
    }
    
    .modal-footer {
        flex-direction: column-reverse; /* Đảo ngược thứ tự nút */
    }
    .modal-footer .btn {
        width: 100%;
    }

    /* --- Các form nhỏ bên trong modal --- */
    .form-add-image-inline {
        /* Sắp xếp lại form thêm ảnh cho gọn */
        grid-template-columns: 1fr;
    }
     .form-add-image-inline .btn {
        width: 100%;
    }
}

</style>