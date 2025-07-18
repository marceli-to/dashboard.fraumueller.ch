import { createRouter, createWebHistory } from 'vue-router';
import Home from '@/views/home/Index.vue';
import Orders from '@/views/orders/Index.vue';
import UpdateForm from '@/views/orders/UpdateForm.vue';
import CreateForm from '@/views/orders/CreateForm.vue';
import Import from '@/views/import/Index.vue';
import Logs from '@/views/log/Index.vue';

// Error pages
import Error401 from '@/views/error/401.vue';
import Error403 from '@/views/error/403.vue';
import Error404 from '@/views/error/404.vue';
import Error419 from '@/views/error/419.vue';

const routes = [
  { path: '/dashboard', name: 'home', component: Home },
  { path: '/dashboard/bestellungen', name: 'orders', component: Orders },
  { path: '/dashboard/bestellungen/erstellen', name: 'orders.create', component: CreateForm },
  { path: '/dashboard/bestellungen/bearbeiten/:id', name: 'orders.edit', component: UpdateForm },
  { path: '/dashboard/import', name: 'import', component: Import },
  { path: '/dashboard/log', name: 'logs', component: Logs },

  // Error pages
  { path: '/error/401', name: '401', component: Error401 },
  { path: '/error/403', name: '403', component: Error403 },
  { path: '/error/404', name: '404', component: Error404 },
  { path: '/error/419', name: '419', component: Error419 },
  { path: '/:catchAll(.*)', redirect: '/error/404' },

];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
});

export default router;