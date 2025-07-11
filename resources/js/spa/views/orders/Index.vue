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
              <th class="text-left !pr-24">ID</th>
              <th class="text-left">Produkt</th>
              <th class="text-left">E-Mail</th>
              <th class="text-left">Zahlung</th>
              <th class="text-center">Status</th>
              <th class="text-right !pr-0"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-black">
            <tr 
              v-for="order in orders" :key="order.id" class="hover:bg-gray-100">
              <td class="py-12 pr-24 tabular-nums">
                {{ order.order_id }}
              </td>
              <td class="py-12 pr-12 max-w-[360px]">
                {{ order.product_name }}
              </td>
              <td class="py-12 pr-12 max-w-[320px]">
                {{ order.email }}
              </td>
              <td class="py-12 pr-12 capitalize">
                {{ order.payment_method }}, {{ order.total }}, {{ formatDate(order.paid_at) }}
              </td>
               <td class="py-12 text-center pr-12">
                <button 
                  @click="openStatusDialog(order)"
                  class="px-8 py-4 text-tiny rounded-full leading-none capitalize transition-all hover:opacity-75 cursor-pointer" 
                  :class="getOrderStatusClass(order.order_status)"
                >
                  {{ order.order_status || 'open' }}
                </button>
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
  
  <Dialog>
    <div v-if="selectedOrder" class="flex flex-col gap-y-20">
      <div>
        <label for="order-status-select" class="block text-xs mb-16">
          Status für Bestellung #{{ selectedOrder.order_id }}
        </label>
        <select 
          id="order-status-select"
          v-model="newOrderStatus"
          class="appearance-none w-full px-8 py-6 border border-black focus:border-black !ring-0 focus:!ring-0 !outline-none"
        >
          <option value="open">Open</option>
          <option value="fulfilled">Fulfilled</option>
        </select>
      </div>
      
      <div class="flex gap-x-16">
        <ButtonPrimary
          type="button"
          label="Speichern"
          @click="updateOrderStatus" />
        <ButtonSecondary
          type="button"
          label="Abbrechen"
          @click="cancelStatusUpdate" />
      </div>
    </div>
  </Dialog>
</template>
<script setup>
import { ref, onMounted, computed } from 'vue';
import { getOrders, updateOrder } from '@/services/api';
import { usePageTitle } from '@/composables/usePageTitle';
import { useDialogStore } from '@/components/dialog/stores/dialog';
import IconEdit from '@/components/icons/Edit.vue';
import Dialog from '@/components/dialog/Dialog.vue';
import ButtonPrimary from '@/components/buttons/Primary.vue';
import ButtonSecondary from '@/components/buttons/Secondary.vue';

const { setTitle } = usePageTitle();
setTitle('Bestellungen');

const dialogStore = useDialogStore();
const orders = ref([]);
let isLoading = ref(true);
const selectedOrder = ref(null);
const newOrderStatus = ref('');

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

const getOrderStatusClass = (status) => {
  const classes = {
    'open': 'bg-blue-100 text-blue-800',
    'fulfilled': 'bg-green-100 text-green-800'
  };
  return classes[status] || 'bg-blue-100 text-blue-800';
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

const openStatusDialog = (order) => {
  selectedOrder.value = order;
  newOrderStatus.value = order.order_status || 'open';
  
  dialogStore.show({
    title: 'Status ändern',
    size: 'small',
    hideDefaultActions: true,
    component: null,
    message: null
  });
};

const updateOrderStatus = async () => {
  try {
    await updateOrder(selectedOrder.value.id, {
      order_status: newOrderStatus.value
    });
    
    // Update the order in the local array
    const orderIndex = orders.value.findIndex(o => o.id === selectedOrder.value.id);
    if (orderIndex !== -1) {
      orders.value[orderIndex].order_status = newOrderStatus.value;
    }
    
    dialogStore.hide();
  } catch (error) {
    console.error('Error updating order status:', error);
    alert('Fehler beim Aktualisieren des Status');
  }
};

const cancelStatusUpdate = () => {
  dialogStore.hide();
};
</script>
