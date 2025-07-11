<template>
  <h1 class="text-lg leading-[1.25]">
    Bestellungen
  </h1>
  <template v-if="!isLoading">
    <div class="mt-48 max-w-6xl">
      <div class="overflow-x-auto">
        <table class="w-auto text-xxs">
          <thead>
            <tr class="border-b border-black">
              <th class="py-12 pr-16 text-left">ID</th>
              <th class="py-12 pr-16 text-left">Produkt</th>
              <th class="py-12 pr-16 text-left">E-Mail</th>
              <th class="py-12 pr-16 text-left">Zahlungsart</th>
              <th class="py-12 pr-16 text-right">Betrag</th>
              <th class="py-12 text-center pr-16 text-left">Status</th>
              <th class="py-12 pr-16 text-right">Bezahlt am</th>
              <th class="py-12 text-right">Aktionen</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-black">
            <tr v-for="order in orders" :key="order.id">
              <td class="py-12 pr-16">
                {{ order.order_id }}
              </td>
              <td class="py-12 pr-16 max-w-[400px]">
                {{ order.product_name }}
              </td>
              <td class="py-12 pr-16 max-w-[320px]">
                {{ order.email }}
              </td>
              <td class="py-12 pr-16 capitalize">
                {{ order.payment_method }}
              </td>
              <td class="py-12 pr-16 text-right">
                {{ order.total }}
              </td>
              <td class="py-12 text-center pr-16">
                <span class="px-8 py-4 text-xxs rounded-full leading-none capitalize" :class="getStatusClass(order.financial_status)">
                  {{ order.financial_status }}
                </span>
              </td>
              <td class="py-12 pr-16 text-right tabular-nums">
                {{ formatDate(order.paid_at) }}
              </td>
              <td class="py-12 text-right">
                <router-link 
                  :to="{ name: 'orders.edit', params: { id: order.id } }"
                  class="text-blue-600 hover:text-blue-800 text-xxs"
                >
                  Bearbeiten
                </router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- <div class="mt-16 p-4 bg-gray-50 rounded">
        <div class="flex justify-between items-center">
          <span class="font-bold">Gesamtanzahl Bestellungen:</span>
          <span class="font-bold">{{ orders.length }}</span>
        </div>
        <div class="flex justify-between items-center mt-2">
          <span class="font-bold">Gesamtumsatz:</span>
          <span class="font-bold">{{ totalRevenue }} CHF</span>
        </div>
      </div> -->
    </div>
  </template>
</template>
<script setup>
import { ref, onMounted, computed } from 'vue';
import { getOrders } from '@/services/api';
import { usePageTitle } from '@/composables/usePageTitle';
const { setTitle } = usePageTitle();
setTitle('Bestellungen');

const orders = ref([]);
let isLoading = ref(true);

onMounted(async () => {
  if (isLoading.value) {
    try {
      orders.value = await getOrders();
    } catch (error) {
      console.error(error);
    } finally {
      isLoading.value = false;
    }
  }
});

const totalRevenue = computed(() => {
  return orders.value.reduce((sum, order) => sum + parseFloat(order.total), 0).toFixed(2);
});

const getStatusClass = (status) => {
  const classes = {
    'paid': 'bg-green-100 text-green-800',
    'pending': 'bg-yellow-100 text-yellow-800',
    'failed': 'bg-red-100 text-red-800',
    'cancelled': 'bg-gray-100 text-gray-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('de-CH', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>
