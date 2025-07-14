<template>
  <h1 class="text-lg leading-[1.25]">
    Bestellungen
  </h1>

  <template v-if="!isLoading">
    <SummaryStats :stats="summaryStats" />

    <MultiEditPanel
      :selected-count="selectedOrderIds.length"
      :actions="bulkActions"
      :selected-action="selectedAction"
      @action-selected="handleActionChange" />

    <DataTable
      :data="orders"
      :columns="tableColumns"
      :actions="tableActions"
      :selectable="true"
      :selected-items="selectedOrderIds"
      @update:selected-items="selectedOrderIds = $event"
      @toggle-select-all="toggleSelectAll"
      @cell-click="handleCellClick"
      @action-click="handleActionClick" />

  </template>
  
  <Dialog>
    <div v-if="selectedOrder" class="flex flex-col gap-y-20">
      <div>
        <label for="order-status-select" class="block text-xs mb-16">
          Status für Bestellung #{{ selectedOrder.order_id }}
        </label>
        <Select
          id="order-status-select"
          v-model="newOrderStatus"
          :options="statusOptions"
        />
      </div>
      
      <div class="flex gap-x-16">
        <ButtonPrimary
          type="button"
          label="Speichern"
          @click="updateOrderStatus"
          class="w-full justify-center" />
        <ButtonSecondary
          type="button"
          label="Abbrechen"
          @click="cancelStatusUpdate"
          class="w-full justify-center" />
      </div>
    </div>
  </Dialog>

</template>
<script setup>
import { ref, onMounted, computed } from 'vue';
import { getOrders, updateOrder, deleteOrder } from '@/services/api';
import { usePageTitle } from '@/composables/usePageTitle';
import { useDialogStore } from '@/components/dialog/stores/dialog';
import IconEdit from '@/components/icons/Edit.vue';
import IconTrash from '@/components/icons/Trash.vue';
import Dialog from '@/components/dialog/Dialog.vue';
import ButtonPrimary from '@/components/buttons/Primary.vue';
import ButtonSecondary from '@/components/buttons/Secondary.vue';
import SummaryStats from '@/components/ui/SummaryStats.vue';
import MultiEditPanel from '@/components/ui/MultiEditPanel.vue';
import DataTable from '@/components/ui/DataTable.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import Select from '@/components/input/Select.vue';

const { setTitle } = usePageTitle();
setTitle('Bestellungen');

const dialogStore = useDialogStore();
const orders = ref([]);
let isLoading = ref(true);
const selectedOrder = ref(null);
const newOrderStatus = ref('');

// Multi-edit state
const selectedOrderIds = ref([]);
const selectedAction = ref('');

// Component configuration
const summaryStats = computed(() => [
  {
    label: 'Total',
    value: orders.value.length,
    formatter: (value) => `${value} Bestellungen`
  }
]);

const bulkActions = [
  { value: 'status-open', label: 'Status offen' },
  { value: 'status-fulfilled', label: 'Status erledigt' },
  { value: 'generate-labels', label: 'Etiketten generieren' }
];

const statusOptions = [
  { value: 'open', label: 'offen' },
  { value: 'fulfilled', label: 'erledigt' }
];

const tableColumns = [
  {
    key: 'order_id',
    label: 'ID',
    component: 'router-link',
    componentProps: {
      class: 'hover:text-blue-500 transition-all'
    },
    to: (item) => ({ name: 'orders.edit', params: { id: item.id } }),
    cellClasses: 'pr-24 tabular-nums'
  },
  {
    key: 'product_name',
    label: 'Produkt',
    cellClasses: 'pr-12 max-w-[360px]'
  },
  {
    key: 'email',
    label: 'E-Mail',
    cellClasses: 'pr-12 max-w-[320px]'
  },
  {
    key: 'payment_info',
    label: 'Zahlung',
    cellClasses: 'pr-12 capitalize',
    formatter: (value, item) => `${item.payment_method}, ${item.total}, ${formatDate(item.paid_at)}`
  },
  {
    key: 'order_status',
    label: 'Status',
    align: 'center',
    cellClasses: 'text-center pr-12',
    component: StatusBadge,
    componentProps: {
      statusType: 'order',
      clickable: true
    },
    clickable: true
  }
];

const tableActions = [
  {
    key: 'edit',
    component: 'router-link',
    componentProps: {
      class: 'inline-block text-right hover:text-gray-500 transition-all'
    },
    to: (item) => ({ name: 'orders.edit', params: { id: item.id } }),
    icon: IconEdit
  },
  {
    key: 'delete',
    component: 'button',
    componentProps: {
      class: 'inline-block text-right hover:text-red-500 transition-all ml-8'
    },
    icon: IconTrash
  }
];

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

// Multi-edit handlers
const toggleSelectAll = (checked) => {
  if (checked) {
    selectedOrderIds.value = orders.value.map(order => order.id);
  } else {
    selectedOrderIds.value = [];
  }
};

const handleActionChange = (action) => {
  if (action) {
    console.log('Selected action:', action, 'for orders:', selectedOrderIds.value);
    // Reset the dropdown
    selectedAction.value = '';
  }
};

const handleCellClick = ({ column, item }) => {
  if (column.key === 'order_status') {
    openStatusDialog(item);
  }
};

const handleActionClick = ({ action, item }) => {
  if (action.key === 'edit') {
    // Router link will handle navigation
  } else if (action.key === 'delete') {
    handleDeleteOrder(item);
  }
};

const handleDeleteOrder = async (order) => {
  if (confirm(`Möchten Sie die Bestellung #${order.order_id} wirklich löschen?`)) {
    try {
      await deleteOrder(order.id);
      
      // Remove the order from the local array
      const orderIndex = orders.value.findIndex(o => o.id === order.id);
      if (orderIndex !== -1) {
        orders.value.splice(orderIndex, 1);
      }
      
      // Remove from selected items if it was selected
      selectedOrderIds.value = selectedOrderIds.value.filter(id => id !== order.id);
      
    } catch (error) {
      console.error('Error deleting order:', error);
      alert('Fehler beim Löschen der Bestellung');
    }
  }
};
</script>
