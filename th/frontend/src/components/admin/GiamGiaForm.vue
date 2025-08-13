<template>
  <div class="modal-overlay" @click.self="emit('close')">
    <div class="modal-content">
      <h3>{{ editingCoupon ? 'Chỉnh sửa mã giảm giá' : 'Tạo mã giảm giá mới' }}</h3>
      
      <form @submit.prevent="saveCoupon">
        <div class="form-row">
          <div class="form-group half-width">
            <label for="ma_giam_gia">Mã giảm giá *</label>
            <input id="ma_giam_gia" v-model="form.ma_giam_gia" placeholder="Ví dụ: SALEHE2025" required>
          </div>
          <div class="form-group half-width">
            <label for="ten_chuong_trinh">Tên chương trình *</label>
            <input id="ten_chuong_trinh" v-model="form.ten_chuong_trinh" placeholder="Ví dụ: Khuyến mãi hè" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group half-width">
            <label for="loai_giam_gia">Loại giảm giá *</label>
            <select id="loai_giam_gia" v-model="form.loai_giam_gia" required>
              <option value="fixed_amount">Số tiền cố định</option>
              <option value="percentage">Phần trăm (%)</option>
              <option value="free_ship">Miễn phí vận chuyển</option>
            </select>
          </div>
          <div class="form-group half-width" v-if="form.loai_giam_gia !== 'free_ship'">
            <label for="gia_tri">Giá trị *</label>
            <input id="gia_tri" v-model.number="form.gia_tri" type="number" min="0" required>
          </div>
        </div>
        
        <div class="form-row" v-if="form.loai_giam_gia === 'percentage'">
            <div class="form-group full-width">
                <label for="gia_tri_giam_toi_da">Giá trị giảm tối đa (VNĐ)</label>
                <input id="gia_tri_giam_toi_da" v-model.number="form.gia_tri_giam_toi_da" type="number" min="0" placeholder="Bỏ trống nếu không giới hạn">
            </div>
        </div>

        <div class="form-row">
          <div class="form-group half-width">
            <label for="gia_tri_don_hang_toi_thieu">Đơn hàng tối thiểu (VNĐ)</label>
            <input id="gia_tri_don_hang_toi_thieu" v-model.number="form.gia_tri_don_hang_toi_thieu" type="number" min="0" placeholder="Bỏ trống nếu không yêu cầu">
          </div>
          <div class="form-group half-width">
            <label for="so_luong">Tổng số lượng mã *</label>
            <input id="so_luong" v-model.number="form.so_luong" type="number" min="1" required>
          </div>
        </div>

        <div class="form-row">
            <div class="form-group half-width">
                <label for="ngay_bat_dau">Ngày bắt đầu *</label>
                <input id="ngay_bat_dau" v-model="form.ngay_bat_dau" type="datetime-local" required>
            </div>
            <div class="form-group half-width">
                <label for="ngay_ket_thuc">Ngày kết thúc *</label>
                <input id="ngay_ket_thuc" v-model="form.ngay_ket_thuc" type="datetime-local" required>
            </div>
        </div>

        <div class="form-row">
             <div class="form-group">
                <label for="trang_thai">Trạng thái</label>
                <select id="trang_thai" v-model="form.trang_thai">
                    <option :value="1">Hoạt động</option>
                    <option :value="0">Không hoạt động</option>
                </select>
            </div>
        </div>

        <hr class="form-divider">

        <div class="apply-section">
          <h4>Phạm vi áp dụng</h4>
          <div class="form-group"><label for="apply_type">Áp dụng cho</label><select id="apply_type" v-model="applyType"><option value="all">Toàn bộ đơn hàng</option><option value="product">Sản phẩm cụ thể</option><option value="category">Danh mục cụ thể</option></select></div>
          <div v-if="applyType === 'product'" class="form-group"><label for="products">Chọn sản phẩm</label><select id="products" v-model="selectedProducts" multiple size="5"><option v-for="product in productList" :key="product.san_pham_id" :value="product.san_pham_id">{{ product.ten_san_pham }}</option></select><small>Giữ Ctrl (hoặc Cmd trên Mac) để chọn nhiều sản phẩm.</small></div>
          <div v-if="applyType === 'category'" class="form-group"><label for="categories">Chọn danh mục</label><select id="categories" v-model="selectedCategories" multiple size="5"><option v-for="category in categoryList" :key="category.category_id" :value="category.category_id">{{ category.ten_danh_muc }}</option></select><small>Giữ Ctrl (hoặc Cmd trên Mac) để chọn nhiều danh mục.</small></div>
        </div>

        <div class="form-actions">
          <button type="button" class="btn-secondary" @click="emit('close')">Hủy</button>
          <button type="submit" class="btn-primary" :disabled="isSaving">{{ isSaving ? 'Đang lưu...' : 'Lưu' }}</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  couponToEdit: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'saved']);

// Hàm helper để định dạng ngày tháng cho input datetime-local
const formatDateTimeForInput = (dateStr) => {
    if (!dateStr) return '';
    // Cắt chuỗi ISO để có định dạng YYYY-MM-DDTHH:mm, tương thích với input
    return new Date(dateStr).toISOString().slice(0, 16);
};

// State cho form
const isSaving = ref(false);
const editingCoupon = ref(null);
const defaultFormState = {
  ma_giam_gia: '',
  ten_chuong_trinh: '',
  loai_giam_gia: 'fixed_amount',
  gia_tri: 0,
  gia_tri_don_hang_toi_thieu: null,
  gia_tri_giam_toi_da: null,
  so_luong: 100,
  ngay_bat_dau: new Date().toISOString().slice(0, 16),
  ngay_ket_thuc: new Date(new Date().setDate(new Date().getDate() + 7)).toISOString().slice(0, 16),
  trang_thai: 1,
  ap_dung_cho: null,
};
const form = ref({ ...defaultFormState });

// State cho phần "Áp dụng cho"
const applyType = ref('all');
const selectedProducts = ref([]);
const selectedCategories = ref([]);
const productList = ref([]);
const categoryList = ref([]);

// Hàm lấy danh sách sản phẩm và danh mục từ API
const fetchProductsAndCategories = async () => {
  try {
    const [productsRes, categoriesRes] = await Promise.all([
      axios.get('/admin/products-list'),
      axios.get('/admin/categories-list')
    ]);
    productList.value = productsRes.data;
    categoryList.value = categoriesRes.data;
  } catch (error) {
    console.error("Lỗi khi tải danh sách sản phẩm/danh mục:", error);
  }
};

// Hàm lưu (tạo mới hoặc cập nhật)
const saveCoupon = async () => {
    isSaving.value = true;
    
    // Xây dựng giá trị cho cột `ap_dung_cho` dựa trên lựa chọn
    let apDungChoValue = null;
    if (applyType.value === 'product' && selectedProducts.value.length > 0) {
        apDungChoValue = JSON.stringify({ type: 'product', ids: selectedProducts.value });
    } else if (applyType.value === 'category' && selectedCategories.value.length > 0) {
        apDungChoValue = JSON.stringify({ type: 'category', ids: selectedCategories.value });
    }

    const payload = { ...form.value, ap_dung_cho: apDungChoValue };

    try {
        if (editingCoupon.value) { // Chế độ sửa
            await axios.put(`/admin/giam-gia/${editingCoupon.value.giam_gia_id}`, payload);
        } else { // Chế độ tạo mới
            await axios.post('/admin/giam-gia', payload);
        }
        emit('saved');
        emit('close');
    } catch (error) {
        console.error("Lỗi khi lưu mã:", error);
        if (error.response && error.response.status === 422) {
            const errors = error.response.data.errors;
            let errorMessage = "Dữ liệu không hợp lệ:\n";
            for (const key in errors) {
                errorMessage += `- ${errors[key][0]}\n`;
            }
            alert(errorMessage);
        } else {
            alert('Có lỗi xảy ra, vui lòng thử lại.');
        }
    } finally {
        isSaving.value = false;
    }
};

// Theo dõi prop `couponToEdit` để tự động điền/reset form
watch(() => props.couponToEdit, (newVal) => {
  if (newVal) {
    // Chế độ SỬA
    editingCoupon.value = newVal;
    form.value = { 
        ...newVal,
        ngay_bat_dau: formatDateTimeForInput(newVal.ngay_bat_dau),
        ngay_ket_thuc: formatDateTimeForInput(newVal.ngay_ket_thuc),
     };
    
    // Phân tích `ap_dung_cho`
    if (form.value.ap_dung_cho) {
      try {
        const condition = JSON.parse(form.value.ap_dung_cho);
        applyType.value = condition.type || 'all';
        if (condition.type === 'product') selectedProducts.value = condition.ids || [];
        else if (condition.type === 'category') selectedCategories.value = condition.ids || [];
      } catch (e) {
        applyType.value = 'all';
      }
    } else {
        applyType.value = 'all';
    }
  } else {
    // Chế độ TẠO MỚI
    editingCoupon.value = null;
    form.value = { ...defaultFormState };
    applyType.value = 'all';
    selectedProducts.value = [];
    selectedCategories.value = [];
  }
}, { immediate: true });

// Lấy dữ liệu cần thiết khi component được tạo
onMounted(() => {
  fetchProductsAndCategories();
});
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}
.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
  width: 100%;
  max-width: 700px; /* Tăng chiều rộng cho form lớn hơn */
  max-height: 90vh; /* Giới hạn chiều cao */
  overflow-y: auto; /* Cho phép cuộn nếu form quá dài */
}
.form-divider {
  border: none;
  border-top: 1px solid #eee;
  margin: 2rem 0;
}
.apply-section h4 {
  margin-bottom: 1rem;
  color: #333;
}
.form-group select[multiple] {
  min-height: 120px;
}
.form-group small {
    margin-top: 0.5rem;
    color: #6c757d;
    font-size: 0.875em;
}
h3 {
  margin-top: 0;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid #eee;
  padding-bottom: 1rem;
}
.form-row {
  display: flex;
  flex-wrap: wrap; /* Cho phép xuống hàng trên màn hình nhỏ */
  gap: 1rem;
  margin-bottom: 1rem;
}
.form-group {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 200px; /* Đảm bảo các ô không bị quá hẹp */
}
.form-group.half-width {
  flex-basis: calc(50% - 0.5rem);
}
.form-group.full-width {
  flex-basis: 100%;
}
.form-group label {
  margin-bottom: 0.5rem;
  font-weight: 500;
}
.form-group input, .form-group select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
  font-size: 1rem;
  font-family: inherit;
}
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
}
button {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
  transition: background-color 0.2s;
}
button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}
.btn-primary {
  background-color: #007bff;
  color: white;
}
.btn-primary:hover {
  background-color: #0056b3;
}
.btn-secondary {
  background-color: #f1f1f1;
  color: #333;
}
.btn-secondary:hover {
  background-color: #ddd;
}
</style>