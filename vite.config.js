import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'path';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  resolve: {
    alias: {
      img: resolve('resources/img'),
      fonts: resolve('resources/fonts'),
      vue: 'vue/dist/vue.esm-bundler.js',
      '@': resolve('resources/js/spa'),
    }
  },
  server: {
    cors: true,
    headers: {
      'Access-Control-Allow-Origin': 'https://dashboard.fraumueller.ch.test'
    }
  },
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/spa.js'],
      refresh: true,
    }),
    vue({
      template: {
          transformAssetUrls: {
              base: null,
              includeAbsolute: false,
          },
      },
    }),
  ],
  define: {
    'process.env': {}
  }
});
