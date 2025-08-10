<template>
  <template v-if="!isLoading">
    <h1 class="text-lg leading-[1.25]">
      Produkte
    </h1>
    <div class="absolute right-16 top-16 flex gap-x-16">
      <router-link
        to="/dashboard/produkte/erstellen"
        class="flex items-center gap-x-6 px-12 py-10 border border-black rounded-sm text-xs bg-white hover:bg-gray-50 focus:outline-none focus:!ring-0 disabled:opacity-50 disabled:cursor-not-allowed">
        <IconPlus class="size-18" />
        Erstellen
      </router-link>
    </div>

    <DataTable
      :data="paginatedProducts"
      :filtered-data="sortedProducts"
      :columns="tableColumns"
      :actions="tableActions"
      :selectable="false"
      :sort-key="sortKey"
      :sort-direction="sortDirection"
      :pagination="pagination"
      @cell-click="handleCellClick"
      @action-click="handleActionClick"
      @sort="handleSort"
      @page-change="handlePageChange" />

  </template>


</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { getProducts, deleteProduct, sendTestNotification } from '@/services/api';
import { usePageTitle } from '@/composables/usePageTitle';

// Composables
import { useProductsTable } from '@/composables/useProductsTable';

// Components
import IconPlus from '@/components/icons/Plus.vue';
import DataTable from '@/components/ui/DataTable.vue';

// Page setup
const { setTitle } = usePageTitle();
setTitle('Produkte');

// Core state
const products = ref([]);
const currentPage = ref(1);
const perPage = 20;
const isLoading = ref(true);


// Initialize table composable
const { 
  sortKey, 
  sortDirection, 
  tableColumns, 
  tableActions, 
  handleSort 
} = useProductsTable();

// Load products function
const loadProducts = async () => {
  try {
    isLoading.value = true;
    const response = await getProducts();
    products.value = response.data;
  } catch (error) {
    console.error(error);
    products.value = [];
  } finally {
    isLoading.value = false;
  }
};

// Sorting computed property
const sortedProducts = computed(() => {
  if (!products.value.length) return [];
  
  const sorted = [...products.value].sort((a, b) => {
    let aVal = a[sortKey.value];
    let bVal = b[sortKey.value];
    
    // Handle numeric sorting for orders_count
    if (sortKey.value === 'orders_count') {
      aVal = parseInt(aVal) || 0;
      bVal = parseInt(bVal) || 0;
    } else {
      // String sorting
      aVal = String(aVal || '').toLowerCase();
      bVal = String(bVal || '').toLowerCase();
    }
    
    if (sortDirection.value === 'asc') {
      return aVal < bVal ? -1 : aVal > bVal ? 1 : 0;
    } else {
      return aVal > bVal ? -1 : aVal < bVal ? 1 : 0;
    }
  });
  
  return sorted;
});

// Client-side pagination computed properties
const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  const end = start + perPage;
  return sortedProducts.value.slice(start, end);
});

const pagination = computed(() => {
  const total = sortedProducts.value.length;
  const lastPage = Math.ceil(total / perPage);
  
  return lastPage > 1 ? {
    current_page: currentPage.value,
    last_page: lastPage,
    total: total,
    per_page: perPage
  } : null;
});

// Load products on mount
onMounted(async () => {
  await loadProducts();
});

// Event handlers
const handleCellClick = ({ column, item }) => {
  if (column.clickable) {
    // Handle clickable columns if needed
  }
};

const handleActionClick = async ({ action, item }) => {
  if (action.key === 'edit') {
    // Router link handles this
  } else if (action.key === 'send-test-notification') {
    try {
      const result = await sendTestNotification(item.id);
      if (result.success) {
        alert(result.message);
      } else {
        alert('Fehler beim Senden der Test-E-Mail: ' + result.message);
      }
    } catch (error) {
      console.error('Error sending test notification:', error);
      alert(error.response?.data?.message || 'Fehler beim Senden der Test-E-Mail');
    }
  } else if (action.key === 'delete') {
    // Check if product has orders (frontend check)
    if (item.orders_count > 0) {
      alert('Produkt kann nicht gelöscht werden, da Bestellungen vorhanden sind.');
      return;
    }
    
    if (confirm('Sind Sie sicher, dass Sie dieses Produkt löschen möchten?')) {
      try {
        await deleteProduct(item.id);
        // Remove product from local list
        products.value = products.value.filter(p => p.id !== item.id);
      } catch (error) {
        console.error('Error deleting product:', error);
        alert(error.response?.data?.message || 'Fehler beim Löschen des Produkts');
      }
    }
  }
};

// Handle page change
const handlePageChange = (page) => {
  currentPage.value = page;
};
</script>