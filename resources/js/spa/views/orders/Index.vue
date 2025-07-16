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
        class="flex items-center gap-x-6 px-12 py-10 border border-black rounded-sm text-xs bg-white hover:bg-gray-50 focus:outline-none focus:!ring-0 disabled:opacity-50 disabled:cursor-not-allowed">
        <IconPlus class="size-18" />
        Erfassen
      </router-link>
    </div>
    
    <SummaryStats :stats="summaryStats" />

    <DataTable
      :data="paginatedOrders"
      :columns="tableColumns"
      :actions="tableActions"
      :selectable="true"
      :selected-items="selectedOrderIds"
      :sort-key="sortKey"
      :sort-direction="sortDirection"
      :pagination="pagination"
      @update:selected-items="selectedOrderIds = $event"
      @toggle-select-all="handleToggleSelectAll"
      @cell-click="handleCellClick"
      @action-click="handleActionClick"
      @sort="handleSort"
      @page-change="handlePageChange" />

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
          @action-selected="selectedAction = $event"
          @notes-changed="notesValue = $event" />
        
        <div class="flex gap-x-16">
          <ButtonPrimary
            type="button"
            label="Speichern"
            @click="handleApplyBulkAction"
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
      
      <div class="border-t pt-16 grid grid-cols-2 gap-x-20">
        <div>
          <div class="font-medium mb-4">Rechnungsadresse</div>
          <div class="leading-relaxed">
            <div>{{ selectedOrder.billing_name || '-' }}</div>
            <div v-if="selectedOrder.billing_address_1">{{ selectedOrder.billing_address_1 }}</div>
            <div v-if="selectedOrder.billing_address_2">{{ selectedOrder.billing_address_2 }}</div>
            <div>{{ [selectedOrder.billing_zip, selectedOrder.billing_city].filter(Boolean).join(' ') }}</div>
            <div v-if="selectedOrder.billing_country">{{ selectedOrder.billing_country }}</div>
          </div>
        </div>
        <div>
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
    </div>
  </Dialog>

</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { getOrders } from '@/services/api';
import { usePageTitle } from '@/composables/usePageTitle';
import { useFiltersStore } from '@/stores/filters';

// Composables
import { useOrdersTable } from '@/composables/useOrdersTable';
import { useOrdersFiltering } from '@/composables/useOrdersFiltering';
import { useOrdersMultiEdit } from '@/composables/useOrdersMultiEdit';
import { useOrdersDialogs } from '@/composables/useOrdersDialogs';

// Constants
import { bulkActions, statusOptions } from '@/constants/orderConstants';

// Components
import IconPlus from '@/components/icons/Plus.vue';
import IconCopy from '@/components/icons/Copy.vue';
import Dialog from '@/components/dialog/Dialog.vue';
import ButtonPrimary from '@/components/buttons/Primary.vue';
import ButtonSecondary from '@/components/buttons/Secondary.vue';
import SummaryStats from '@/components/ui/SummaryStats.vue';
import MultiEditPanel from '@/components/ui/MultiEditPanel.vue';
import DataTable from '@/components/ui/DataTable.vue';
import FilterSidebar from '@/components/ui/FilterSidebar.vue';
import Select from '@/components/input/Select.vue';
import Label from '@/components/input/Label.vue';

// Page setup
const { setTitle } = usePageTitle();
setTitle('Bestellungen');

// Core state
const orders = ref([]);
const currentPage = ref(1);
const perPage = 20;
const isLoading = ref(true);
const filtersStore = useFiltersStore();

// Initialize composables
const { 
  sortKey, 
  sortDirection, 
  tableColumns, 
  tableActions, 
  handleSort 
} = useOrdersTable();

const { 
  filters, 
  filteredAndSortedOrders, 
  summaryStats, 
  updateFilters 
} = useOrdersFiltering(orders, sortKey, sortDirection);

const { 
  selectedOrderIds, 
  selectedAction, 
  notesValue, 
  exportResult, 
  toggleSelectAll, 
  clearMultiEditState, 
  applyBulkAction 
} = useOrdersMultiEdit(orders);

const { 
  selectedOrder, 
  newOrderStatus, 
  showMultiEditDialog, 
  showOrderDetail, 
  openStatusDialog, 
  updateOrderStatus, 
  cancelStatusUpdate, 
  openMultiEditDialog, 
  closeMultiEditDialog, 
  toggleMultiEditDialog, 
  showOrderDetails, 
  handleDeleteOrder, 
  handleCellClick, 
  handleActionClick: dialogHandleActionClick 
} = useOrdersDialogs(orders);

// Load orders function
const loadOrders = async () => {
  try {
    isLoading.value = true;
    const response = await getOrders();
    orders.value = response;
  } catch (error) {
    console.error(error);
    orders.value = [];
  } finally {
    isLoading.value = false;
  }
};

// Client-side pagination computed properties
const paginatedOrders = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  const end = start + perPage;
  return filteredAndSortedOrders.value.slice(start, end);
});

const pagination = computed(() => {
  const total = filteredAndSortedOrders.value.length;
  const lastPage = Math.ceil(total / perPage);
  
  return lastPage > 1 ? {
    current_page: currentPage.value,
    last_page: lastPage,
    total: total,
    per_page: perPage
  } : null;
});

// Load orders on mount
onMounted(async () => {
  if (isLoading.value) {
    try {
      // Validate that saved product filter still exists
      await filtersStore.validateProductFilter();
      
      await loadOrders();
    } catch (error) {
      console.error(error);
    }
  }
});

// Event handlers
const handleToggleSelectAll = (checked) => {
  toggleSelectAll(checked, paginatedOrders.value);
};

const handleApplyBulkAction = async () => {
  try {
    const result = await applyBulkAction();
    if (result.success && result.type !== 'export') {
      closeMultiEditDialog();
    }
  } catch (error) {
    alert('Fehler beim Anwenden der Massenbearbeitung');
  }
};

// Enhanced action click handler that handles deletion
const handleActionClick = async ({ action, item }) => {
  const result = await dialogHandleActionClick({ action, item });
  
  // If order was deleted, remove it from selected items
  if (result?.success && result.deletedOrderId) {
    selectedOrderIds.value = selectedOrderIds.value.filter(id => id !== result.deletedOrderId);
  }
};

// Handle page change
const handlePageChange = (page) => {
  currentPage.value = page;
};

// Reset pagination when filters change
watch(filters, () => {
  currentPage.value = 1;
}, { deep: true });
</script>