<template>
  <h1 class="text-lg leading-[1.25]">
    Bestellungen
  </h1>
  <template v-if="!isLoading">
    <div class="mt-16">
      Total Bestellungen: {{ orders.length }}
    </div>
    <div class="mt-24 max-w-7xl">
      <div class="overflow-x-auto">
        <table class="w-auto text-xxs">
          <thead>
            <tr class="border-b border-black [&_th]:font-medium [&_th]:py-12 [&_th]:pr-12">
              <th class="text-left">ID</th>
              <th class="text-left">Produkt</th>
              <th class="text-left">E-Mail</th>
              <th class="text-left">Zahlungsart</th>
              <th class="text-right">Betrag</th>
              <th class="text-center">Status</th>
              <th class="text-left">Bezahlt am</th>
              <th class="text-right !pr-0"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-black">
            <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-100">
              <td class="py-12 pr-12 tabular-nums">
                {{ order.order_id }}
              </td>
              <td class="py-12 pr-12 max-w-[360px]">
                {{ order.product_name }}
              </td>
              <td class="py-12 pr-12 max-w-[320px]">
                {{ order.email }}
              </td>
              <td class="py-12 pr-12 capitalize">
                {{ order.payment_method }}
              </td>
              <td class="py-12 pr-12 text-right">
                {{ order.total }}
              </td>
              <td class="py-12 text-center pr-12">
                <span class="px-8 py-4 text-xxs rounded-full leading-none capitalize" :class="getStatusClass(order.financial_status)">
                  {{ order.financial_status }}
                </span>
              </td>
              <td class="py-12 pr-12 text-right tabular-nums">
                {{ formatDate(order.paid_at) }}
              </td>
              <td class="py-12 text-right">
                <router-link 
                  :to="{ name: 'orders.edit', params: { id: order.id } }"
                  class="inline-block text-right hover:text-gray-500 transition-all">
                  <IconEdit />
                </router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </template>
</template>
<script setup>
import { ref, onMounted, computed } from 'vue';
import { getOrders } from '@/services/api';
import { usePageTitle } from '@/composables/usePageTitle';
import IconEdit from '@/components/icons/Edit.vue';

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
    year: '2-digit',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>
