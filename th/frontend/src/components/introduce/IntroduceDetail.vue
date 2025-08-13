<template>
  <section class="content">
    <div v-if="page" class="page-container">
      <div class="page-header">
        <h1>{{ page.Tieu_de_trang }}</h1>
        <button @click="isEditing = !isEditing" class="edit-btn">
          {{ isEditing ? 'Hủy' : 'Sửa nội dung' }}
        </button>
       </div>

       <div v-if="isEditing">
        <div id="editorjs" class="editor-box"></div>
        <button @click="save" class="save-btn">Lưu lại nội dung</button>
       </div>

      <div v-else v-html="renderedHtml" class="page-content" />
    </div>

    <div v-else class="loading">Đang tải nội dung...</div>
  </section>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import EditorJS from '@editorjs/editorjs'

import Header from '@editorjs/header'
import List from '@editorjs/list'
import Paragraph from '@editorjs/paragraph'
import Quote from '@editorjs/quote'
import Marker from '@editorjs/marker'
import Delimiter from '@editorjs/delimiter'
import InlineCode from '@editorjs/inline-code'
import LinkTool from '@editorjs/link'
import Swal from 'sweetalert2';

const page = ref(null)
const renderedHtml = ref('')
const isEditing = ref(false)
const route = useRoute()
let editor = null

const isJSON = (str) => {
  try {
    const parsed = JSON.parse(str)
    return parsed && typeof parsed === 'object'
  } catch {
    return false
  }
}

const initEditor = async (content = null) => {
  if (editor) {
    await editor.destroy()
    editor = null
  }

  await nextTick()

  const editorData = isJSON(content) ? JSON.parse(content) : undefined

  editor = new EditorJS({
    holder: 'editorjs',
    autofocus: true,
    placeholder: 'Nhập nội dung tại đây...',
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
        config: {
          endpoint: null
        }
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
  })

  await editor.isReady

  setTimeout(() => {
    const firstBlock = document.querySelector('.ce-block')
    if (firstBlock) firstBlock.click()
  }, 300)
}

onMounted(async () => {
  try {
    const res = await axios.get(`http://localhost:8000/api/admin/trang-tinh/${route.params.slug}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })
    page.value = res.data

    if (isJSON(page.value.Noi_dung_trang)) {
      const blockData = JSON.parse(page.value.Noi_dung_trang)
      renderedHtml.value = await convertBlocksToHtml(blockData)
    } else {
      renderedHtml.value = page.value.Noi_dung_trang || ''
    }
  } catch (error) {
    console.error('Không thể tải nội dung:', error)
  }
})

watch(isEditing, async (val) => {
  if (val && page.value) {
    await initEditor(page.value.Noi_dung_trang)
  }
})

const save = async () => {
  try {
    const output = await editor.save()
    await axios.post('http://localhost:8000/api/admin/trang-tinh/update', {
      Ten_trang: route.params.slug,
      Tieu_de_trang: page.value.Tieu_de_trang,
      Noi_dung_trang: JSON.stringify(output)
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })

 
    page.value.Noi_dung_trang = JSON.stringify(output)

    Swal.fire({
    toast: true,
    position: 'top-end',
    icon: 'success',
    title: `Cập nhật  thành công!`,
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
  });
    isEditing.value = false
    renderedHtml.value = await convertBlocksToHtml(output)
  } catch (error) {
    console.error('Lỗi khi lưu:', error)
    Swal.fire({
        icon: 'error',
        title: 'Thao tác thất bại',
        text: 'Đã có lỗi xảy ra, vui lòng thử lại.',
        confirmButtonColor: '#d33'
    });
  }
}
const convertBlocksToHtml = async (data) => {
  if (!data || !data.blocks) return ''
  return data.blocks.map(block => {
    switch (block.type) {
      case 'header':
        return `<h${block.data.level}>${block.data.text}</h${block.data.level}>`
      case 'paragraph':
        return `<p>${block.data.text}</p>`
      case 'list':
        const tag = block.data.style === 'ordered' ? 'ol' : 'ul'
        const renderList = (items = []) => {
          return items.map(i => {
            const content = typeof i === 'object' ? i.content : i
            const subItems = i.items && i.items.length
              ? `<ul>${renderList(i.items)}</ul>`
              : ''
            return `<li>${content}${subItems}</li>`
          }).join('')
        }
        return `<${tag}>${renderList(block.data.items)}</${tag}>`
      case 'quote':
        return `<blockquote>${block.data.text}</blockquote>`
      case 'delimiter':
        return `<hr />`
      case 'inlineCode':
        return `<code>${block.data.text}</code>`
      case 'linkTool':
        return `<a href="${block.data.link}" target="_blank">${block.data.meta?.title || block.data.link}</a>`
      case 'marker':
        return `<mark>${block.data.text}</mark>`
      case 'textColor':
        return `<span style="color: ${block.data.color};">${block.data.text}</span>`
      default:
        return ''
    }
  }).join('')
}

</script>

<style scoped>

</style>
