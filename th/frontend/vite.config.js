import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import mkcert from 'vite-plugin-mkcert'

export default defineConfig({
  plugins: [
    vue(),
    mkcert()
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    https: true,
    host: 'localhost',
    port: 5174
  },
  optimizeDeps: {
    include: ['@editorjs/editorjs', '@editorjs/header', '@editorjs/list']
  }
})
