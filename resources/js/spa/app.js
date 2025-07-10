import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from '@/App.vue';
import router from '@/router';
import { registerApiErrorRouter } from '@/services/error';

const app = createApp(App);
const pinia = createPinia();

// proceed only if #app is present
if (document.querySelector('#app')) {
  app.use(router)
  app.use(pinia)
  registerApiErrorRouter(router)
  app.mount('#app')
}