<template>
  <div class="edit-tintuc">
    <h2>Chỉnh sửa tin tức</h2>
    <form @submit.prevent="updateTintuc" enctype="multipart/form-data">
      <div>
        <label>Tiêu đề:</label>
        <input v-model="tintuc.tieude" required @input="userEditedSlug = false" />
      </div>
      <div>
        <label>Đường dẫn (slug):</label>
        <input
          v-model="tintuc.slug"
          placeholder="Tự động tạo từ tiêu đề hoặc tự nhập"
          @input="userEditedSlug = true"
        />
      </div>
      <div>
        <label>Danh mục:</label>
        <select v-model="tintuc.id_danh_muc_tin_tuc" required>
          <option value="">-- Chọn danh mục --</option>
          <option v-for="dm in danhMucs" :key="dm.id_danh_muc_tin_tuc" :value="dm.id_danh_muc_tin_tuc">
            {{ dm.ten_danh_muc }}
          </option>
        </select>
      </div>
      <div>
        <label>Ngày đăng:</label>
        <input type="date" v-model="tintuc.ngay_dang" />
      </div>
      <div>
        <label>Nội dung:</label>
        <div id="editorjs" class="editor-box"></div>
      </div>
      <div>
        <label>Hình ảnh:</label>
        <input type="file" @change="onFileChange" accept="image/*" />
        <div v-if="previewImage" style="margin-top: 10px;">
          <img :src="previewImage" alt="Preview" style="max-width: 120px; max-height: 80px;" />
        </div>
        <div v-else-if="tintuc.hinh_anh" style="margin-top: 10px;">
          <img
            :src="tintuc.hinh_anh.startsWith('http') ? tintuc.hinh_anh : `http://localhost:8000/storage/${tintuc.hinh_anh}`"
            alt="Hình ảnh"
            style="max-width: 120px; max-height: 80px;"
          />
        </div>
      </div>
      <button type="submit" style="margin-top: 1rem;">Cập nhật</button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import List from '@editorjs/list';
import Paragraph from '@editorjs/paragraph';
import Quote from '@editorjs/quote';
import Marker from '@editorjs/marker';
import Delimiter from '@editorjs/delimiter';
import InlineCode from '@editorjs/inline-code';
import LinkTool from '@editorjs/link';

const tintuc = ref({
  tieude: '',
  slug: '',
  id_danh_muc_tin_tuc: '',
  ngay_dang: '',
  noidung: '',
  hinh_anh: '',
});
const danhMucs = ref([]);
const file = ref(null);
const previewImage = ref('');
const userEditedSlug = ref(false);
const route = useRoute();
const router = useRouter();
let editor = null;

const generateSlug = (text) => {
  return text
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9\s-]/g, "")
    .replace(/\s+/g, "-")
    .replace(/-+/g, "-");
};

watch(
  () => tintuc.value.tieude,
  (newVal) => {
    if (!userEditedSlug.value) {
      tintuc.value.slug = generateSlug(newVal || "");
    }
  }
);

const getTintuc = async () => {
  try {
    const res = await axios.get(`http://localhost:8000/api/tintuc/${route.params.id}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    if (res.data.ngay_dang) {
      res.data.ngay_dang = res.data.ngay_dang.substring(0, 10);
    }
    res.data.id_danh_muc_tin_tuc = res.data.id_danh_muc_tin_tuc ? String(res.data.id_danh_muc_tin_tuc) : '';
    tintuc.value = res.data;
    await nextTick();
    await initEditor();
  } catch (err) {
    alert('Không thể lấy dữ liệu tin tức!');
  }
};

const getDanhMucs = async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/danh-muc-tin-tuc');
    danhMucs.value = res.data;
  } catch (err) {
    alert('Không thể lấy danh mục!');
  }
};

const onFileChange = (e) => {
  const f = e.target.files[0];
  if (f) {
    file.value = f;
    previewImage.value = URL.createObjectURL(f);
  }
};

const initEditor = async () => {
  if (editor) {
    await editor.destroy();
    editor = null;
  }
  let editorData = {};
  try {
    editorData = JSON.parse(tintuc.value.noidung || '{}');
  } catch {
    editorData = {};
  }
  editor = new EditorJS({
    holder: 'editorjs',
    autofocus: true,
    placeholder: 'Nhập nội dung tin tức...',
    tools: {
      header: Header,
      paragraph: {
        class: Paragraph,
        inlineToolbar: true
      },
      list: List,
      quote: Quote,
      marker: Marker,
      delimiter: Delimiter,
      inlineCode: InlineCode,
      linkTool: {
        class: LinkTool,
        config: { endpoint: null }
      },
    },
    i18n: {
      messages: {
        ui: {
          blockTunes: {
            toggler: {
              'Click to tune': 'Nhấn để chỉnh',
              'Click to untune': 'Nhấn để bỏ chỉnh'
            }
          },
          inlineToolbar: {
            converter: 'Chuyển định dạng'
          }
        },
        toolNames: {
          Text: 'Văn bản',
          Heading: 'Tiêu đề',
          List: 'Danh sách',
          Quote: 'Trích dẫn',
          Marker: 'Đánh dấu',
          Delimiter: 'Dấu phân cách',
          InlineCode: 'Mã nội tuyến',
          Link: 'Liên kết',
          Color: 'Màu'
        },
        tools: {
          link: {
            'Add a link': 'Thêm liên kết'
          }
        }
      }
    },
    data: editorData
  });
  await editor.isReady;
};

const updateTintuc = async () => {
  try {
    const token = localStorage.getItem('token');
    const output = await editor.save();
    const formData = new FormData();
    formData.append('tieude', tintuc.value.tieude);
    formData.append('slug', tintuc.value.slug);
    formData.append('id_danh_muc_tin_tuc', Number(tintuc.value.id_danh_muc_tin_tuc));
    formData.append('ngay_dang', tintuc.value.ngay_dang);
    formData.append('noidung', JSON.stringify(output));
    if (file.value) {
      formData.append('hinh_anh', file.value);
    }

    await axios.post(
      `http://localhost:8000/api/tintuc/${route.params.id}?_method=PUT`,
      formData,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "multipart/form-data",
        },
      }
    );
    alert('Cập nhật thành công!');
    router.push('/admin/tintuc');
  } catch (err) {
    alert('Cập nhật thất bại!');
  }
};

onMounted(() => {
  getTintuc();
  getDanhMucs();
});
</script>

<style scoped>
.editor-box {
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  min-height: 120px;
  padding: 8px 12px;
  background: #fafbfc;
  margin-bottom: 12px;
}
</style>