<template>

  <template v-if="!isLoading">
    <h1 class="text-lg leading-[1.25]">
      Bestellungen
    </h1>
    <div class="absolute right-16 top-16 flex gap-x-16">
      <button
        v-if="selectedOrderIds.length > 0"
        @click="toggleMultiEditDialog"
        class="flex items-center gap-x-8 px-12 py-10 border border-black rounded-sm text-xs bg-white hover:bg-gray-50 focus:outline-none focus:!ring-0"
      >
        <IconCopy />
        Bearbeiten ({{ selectedOrderIds.length }})
      </button>
      <FilterSidebar
        :filters="filters"
        :result-count="filteredAndSortedOrders.length"
        @update:filters="updateFilters"
      />
      <router-link
        to="/dashboard/bestellungen/erstellen"
        class="flex items-center gap-x-8 px-12 py-10 border border-black rounded-sm text-xs bg-white hover:bg-gray-50 focus:outline-none focus:!ring-0 disabled:opacity-50 disabled:cursor-not-allowed">
        <IconPlus />
        Erfassen
      </router-link>
    </div>
    
    <SummaryStats :stats="summaryStats" />

    <DataTable
      :data="filteredAndSortedOrders"
      :columns="tableColumns"
      :actions="tableActions"
      :selectable="true"
      :selected-items="selectedOrderIds"
      :sort-key="sortKey"
      :sort-direction="sortDirection"
      @update:selected-items="selectedOrderIds = $event"
      @toggle-select-all="toggleSelectAll"
      @cell-click="handleCellClick"
      @action-click="handleActionClick"
      @sort="handleSort" />

  </template>
  
  <Dialog>
    <!-- Order Status Update Dialog -->
    <div v-if="selectedOrder && !showOrderDetail && !showMultiEditDialog" class="flex flex-col gap-y-20">
      <div>
        <label for="order-status-select" class="block text-xs mb-16">
          Bestellung Nr. {{ selectedOrder.order_id }}
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

    <!-- Multi-Edit Dialog -->
    <div v-if="showMultiEditDialog" class="flex flex-col gap-y-20">
      <template v-if="exportResult">
        <div class="text-xs">{{ exportResult.message }}</div>
        <a
          :href="exportResult.download_url"
          :download="exportResult.filename"
          target="_blank"
          class="inline-flex items-center justify-center px-20 py-10 border border-transparent rounded-sm text-xs font-medium text-white bg-black focus:outline-none focus:!ring-0 disabled:opacity-50 disabled:cursor-not-allowed">
          Datei herunterladen
        </a>
      </template>
      <template v-else>
        <MultiEditPanel
          :selected-count="selectedOrderIds.length"
          :actions="bulkActions"
          :selected-action="selectedAction"
          @action-selected="selectedAction = $event" />
        
        <div class="flex gap-x-16">
          <ButtonPrimary
            type="button"
            label="Anwenden"
            @click="applyBulkAction"
            :disabled="!selectedAction"
            class="w-full justify-center" />
          <ButtonSecondary
            type="button"
            label="Abbrechen"
            @click="closeMultiEditDialog"
            class="w-full justify-center" />
        </div>
      </template>
    </div>

    <!-- Order Detail Dialog -->
    <div v-if="showOrderDetail && selectedOrder" class="space-y-24 text-xs">
      <div class="space-y-16">
        <div>
          <Label for="product" label="Produkt" class="!mb-4" />
          <div>{{ selectedOrder.product_name || '-' }}</div>
        </div>
        <div>
          <Label for="email" label="E-Mail" class="!mb-4" />
          <div>{{ selectedOrder.email || '-' }}</div>
        </div>
        <div>
          <Label for="phone" label="Telefon" class="!mb-4" />
          <div>{{ selectedOrder.phone || '-' }}</div>
        </div>
        <div>
          <Label for="confirmed_at" label="Bestätigt am" class="!mb-4" />
          <div>{{ selectedOrder.confirmed_at || '-' }}</div>
        </div>
      </div>
      
      <div class="border-t pt-16">
        <div class="font-medium mb-4">Rechnungsadresse</div>
        <div class="leading-relaxed">
          <div>{{ selectedOrder.billing_name || '-' }}</div>
          <div v-if="selectedOrder.billing_address_1">{{ selectedOrder.billing_address_1 }}</div>
          <div v-if="selectedOrder.billing_address_2">{{ selectedOrder.billing_address_2 }}</div>
          <div>{{ [selectedOrder.billing_zip, selectedOrder.billing_city].filter(Boolean).join(' ') }}</div>
          <div v-if="selectedOrder.billing_country">{{ selectedOrder.billing_country }}</div>
        </div>
      </div>
      
      <div class="border-t pt-16">
        <div class="font-medium mb-4">Lieferadresse</div>
        <div class="leading-relaxed">
          <div>{{ selectedOrder.shipping_name || '-' }}</div>
          <div v-if="selectedOrder.shipping_address_1">{{ selectedOrder.shipping_address_1 }}</div>
          <div v-if="selectedOrder.shipping_address_2">{{ selectedOrder.shipping_address_2 }}</div>
          <div>{{ [selectedOrder.shipping_zip, selectedOrder.shipping_city].filter(Boolean).join(' ') }}</div>
          <div v-if="selectedOrder.shipping_province">{{ selectedOrder.shipping_province }}</div>
          <div v-if="selectedOrder.shipping_country">{{ selectedOrder.shipping_country }}</div>
        </div>
      </div>
    </div>
  </Dialog>

</template>
<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { getOrders, updateOrder, deleteOrder, bulkUpdateOrders, exportOrdersCsv } from '@/services/api';
import { usePageTitle } from '@/composables/usePageTitle';
import { useDialogStore } from '@/components/dialog/stores/dialog';
import IconEdit from '@/components/icons/Edit.vue';
import IconTrash from '@/components/icons/Trash.vue';
import IconPlus from '@/components/icons/Plus.vue';
import IconCopy from '@/components/icons/Copy.vue';
import IconMagnifyingGlass from '@/components/icons/MagnifyingGlass.vue';
import Dialog from '@/components/dialog/Dialog.vue';
import ButtonPrimary from '@/components/buttons/Primary.vue';
import ButtonSecondary from '@/components/buttons/Secondary.vue';
import SummaryStats from '@/components/ui/SummaryStats.vue';
import MultiEditPanel from '@/components/ui/MultiEditPanel.vue';
import DataTable from '@/components/ui/DataTable.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import FilterSidebar from '@/components/ui/FilterSidebar.vue';
import Select from '@/components/input/Select.vue';
import Label from '@/components/input/Label.vue';

const { setTitle } = usePageTitle();
setTitle('Bestellungen');

const dialogStore = useDialogStore();
const orders = ref([]);
let isLoading = ref(true);
const selectedOrder = ref(null);
const newOrderStatus = ref('');
const showMultiEditDialog = ref(false);
const showOrderDetail = ref(false);
const exportResult = ref(null);

// Multi-edit state
const selectedOrderIds = ref([]);
const selectedAction = ref('');

// Sorting state
const sortKey = ref('paid_at');
const sortDirection = ref('desc');

// Filter state
const filters = ref({
  order_status: '',
  merchant: '',
  product_id: ''
});

// Component configuration
const summaryStats = computed(() => [
  {
    label: 'Total',
    value: filteredAndSortedOrders.value.length,
    formatter: (value) => `${value} Bestellungen`
  }
]);

const filteredAndSortedOrders = computed(() => {
  let filtered = [...orders.value];
  
  // Apply filters
  if (filters.value.order_status) {
    filtered = filtered.filter(order => order.order_status === filters.value.order_status);
  }
  
  if (filters.value.merchant) {
    filtered = filtered.filter(order => order.merchant === filters.value.merchant);
  }
  
  if (filters.value.product_id) {
    filtered = filtered.filter(order => order.product_id == filters.value.product_id);
  }
  
  // Apply sorting
  if (!sortKey.value) return filtered;
  
  const sorted = filtered.sort((a, b) => {
    let aValue, bValue;
    
    if (sortKey.value === 'paid_at') {
      aValue = a.paid_at ? new Date(a.paid_at) : new Date(0);
      bValue = b.paid_at ? new Date(b.paid_at) : new Date(0);
    } else if (sortKey.value === 'product_id') {
      aValue = a.product_name?.toLowerCase() || '';
      bValue = b.product_name?.toLowerCase() || '';
    } else if (sortKey.value === 'order_status') {
      // Sort order: open first, then fulfilled
      const statusOrder = { 'open': 0, 'fulfilled': 1 };
      aValue = statusOrder[a.order_status] ?? 999;
      bValue = statusOrder[b.order_status] ?? 999;
    } else {
      aValue = a[sortKey.value];
      bValue = b[sortKey.value];
    }
    
    if (aValue < bValue) return sortDirection.value === 'asc' ? -1 : 1;
    if (aValue > bValue) return sortDirection.value === 'asc' ? 1 : -1;
    return 0;
  });
  
  return sorted;
});

const bulkActions = [
  { value: 'status-open', label: 'Status offen' },
  { value: 'status-fulfilled', label: 'Status erledigt' },
  { value: 'export-csv', label: 'Export CSV' },
  // { value: 'generate-labels', label: 'Etiketten generieren' }
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
    cellClasses: 'pr-12 max-w-[360px]',
    sortable: true,
    sortKey: 'product_id'
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
    formatter: (value, item) => `${item.payment_method}, ${item.total}, ${formatDate(item.paid_at)}`,
    sortable: true,
    sortKey: 'paid_at'
  },
  {
    key: 'order_status',
    label: 'Status',
    align: 'center',
    cellClasses: 'text-center flex items-center',
    component: StatusBadge,
    componentProps: {
      statusType: 'order',
      clickable: true
    },
    clickable: true,
    sortable: true,
    sortKey: 'order_status'
  }
];

const tableActions = [
  {
    key: 'show',
    component: 'button',
    componentProps: {
      class: 'inline-block text-right hover:text-blue-500 transition-all'
    },
    icon: IconMagnifyingGlass
  },
  {
    key: 'edit',
    component: 'router-link',
    componentProps: {
      class: 'inline-block text-right hover:text-blue-500 transition-all'
    },
    to: (item) => ({ name: 'orders.edit', params: { id: item.id } }),
    icon: IconEdit
  },
  {
    key: 'delete',
    component: 'button',
    componentProps: {
      class: 'inline-block text-right hover:text-red-500 transition-all'
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
  showMultiEditDialog.value = false; // Close multi-edit dialog if open
  
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
    selectedOrderIds.value = filteredAndSortedOrders.value.map(order => order.id);
  } else {
    selectedOrderIds.value = [];
  }
};

const applyBulkAction = async () => {
  if (!selectedAction.value || selectedOrderIds.value.length === 0) return;
  
  try {
    if (selectedAction.value === 'export-csv') {
      // Handle CSV export
      const result = await exportOrdersCsv(selectedOrderIds.value);
      
      if (result.success) {
        // Show download link instead of action buttons
        exportResult.value = result;
        selectedAction.value = '';
      } else {
        alert('Fehler beim Erstellen der CSV-Datei');
      }
    } else {
      let newStatus = '';
      
      // Map action to status
      if (selectedAction.value === 'status-open') {
        newStatus = 'open';
      } else if (selectedAction.value === 'status-fulfilled') {
        newStatus = 'fulfilled';
      }
      
      if (newStatus) {
        // Use bulk update API
        await bulkUpdateOrders(selectedOrderIds.value, newStatus);
        
        // Update the local order data
        selectedOrderIds.value.forEach(orderId => {
          const orderIndex = orders.value.findIndex(o => o.id === orderId);
          if (orderIndex !== -1) {
            orders.value[orderIndex].order_status = newStatus;
          }
        });
        
        // Clear selections and close dialog
        selectedOrderIds.value = [];
        selectedAction.value = '';
        closeMultiEditDialog();
      }
    }
  } catch (error) {
    console.error('Error applying bulk action:', error);
    alert('Fehler beim Anwenden der Massenbearbeitung');
  }
};

const handleCellClick = ({ column, item }) => {
  if (column.key === 'order_status') {
    openStatusDialog(item);
  }
};

const handleActionClick = ({ action, item }) => {
  if (action.key === 'show') {
    showOrderDetails(item);
  } else if (action.key === 'edit') {
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

const handleSort = (column) => {
  if (sortKey.value === column.sortKey) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = column.sortKey;
    sortDirection.value = 'asc';
  }
};

const updateFilters = (newFilters) => {
  filters.value = { ...newFilters };
};

// Watch dialog store visibility to sync our local state
watch(
  () => dialogStore.isVisible,
  (isVisible) => {
    if (!isVisible) {
      // When dialog is closed, reset our local states
      showMultiEditDialog.value = false;
      showOrderDetail.value = false;
      selectedOrder.value = null;
      exportResult.value = null;
    }
  }
);

// Manual toggle for multi-edit dialog
const toggleMultiEditDialog = () => {
  if (showMultiEditDialog.value) {
    closeMultiEditDialog();
  } else {
    openMultiEditDialog();
  }
};

const openMultiEditDialog = () => {
  showMultiEditDialog.value = true;
  selectedOrder.value = null; // Clear any selected order for status update
  
  dialogStore.show({
    title: 'Bearbeiten',
    size: 'medium',
    hideDefaultActions: true,
    component: null,
    message: null
  });
};

const closeMultiEditDialog = () => {
  showMultiEditDialog.value = false;
  selectedOrder.value = null; // Ensure status dialog state is also cleared
  exportResult.value = null; // Clear export result
  selectedOrderIds.value = []; // Clear selections
  selectedAction.value = ''; // Clear action
  dialogStore.hide();
};

const showOrderDetails = (order) => {
  selectedOrder.value = order;
  showOrderDetail.value = true;
  showMultiEditDialog.value = false; // Close multi-edit dialog if open
  
  dialogStore.show({
    title: `Bestellung Nr. ${order.order_id}`,
    size: 'medium',
    hideDefaultActions: true,
    component: null,
    message: null
  });
};
</script>
