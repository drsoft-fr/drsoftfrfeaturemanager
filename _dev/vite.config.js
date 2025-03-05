import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import {resolve} from "path";
import tailwindcss from '@tailwindcss/vite'

// https://vite.dev/config/
export default defineConfig({
  build: {
    manifest: true,
    rollupOptions: {
      external: ['prestashop', '$', 'jquery'],
      input: {
        'admin-main': resolve(__dirname, 'src/js/admin/main.js'),
      },
      output: {
        assetFileNames: (assetInfo) => {
          const info = assetInfo.names[0].split('.')
          let extType = info[info.length - 1].toLowerCase()

          if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(extType)) {
            extType = 'img'
          } else if (/woff|woff2/.test(extType)) {
            extType = 'css'
          }

          return `${extType}/[name]-[hash][extname]`
        },
        chunkFileNames: 'js/[name]-[hash].js',
        entryFileNames: 'js/[name]-[hash].js',
        globals: {
          prestashop: 'prestashop',
          $: '$',
          jquery: 'jQuery',
        },
      },
    },
    outDir: '../views',
  },
  plugins: [
    tailwindcss(),
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
})
