<template>
  <template v-if="!isLoading">
    <h1 class="text-lg leading-[1.25]">
      Log
    </h1>
    
    <SummaryStats :stats="summaryStats" />

    <DataTable
      :data="paginatedLogs"
      :filtered-data="logs"
      :columns="tableColumns"
      :selectable="false"
      :sort-key="sortKey"
      :sort-direction="sortDirection"
      :pagination="pagination"
      @sort="handleSort"
      @page-change="handlePageChange" />

  </template>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { getOrderLogs } from '@/services/api';
import { usePageTitle } from '@/composables/usePageTitle';

// Components
import SummaryStats from '@/components/ui/SummaryStats.vue';
import DataTable from '@/components/ui/DataTable.vue';
import IconCheckmark from '@/components/icons/Checkmark.vue';
import IconShield from '@/components/icons/Shield.vue';

// Page setup
const { setTitle } = usePageTitle();
setTitle('Log');

// Core state
const logs = ref([]);
const currentPage = ref(1);
const perPage = 20;
const isLoading = ref(true);
const sortKey = ref('created_at');
const sortDirection = ref('desc');

// Status icon component
const StatusIcon = {
  props: ['status'],
  template: `
    <div class="flex justify-start">
      <IconShield v-if="status === 'error'" class="text-red-500" />
      <IconCheckmark v-else class="text-green-500" />
    </div>
  `,
  components: {
    IconShield,
    IconCheckmark
  }
};

// Table configuration
const tableColumns = [
  {
    key: 'order_id',
    label: 'Bestellungs-ID',
    sortable: true,
    component: 'router-link',
    componentProps: {
      class: 'hover:text-blue-500 transition-all'
    },
    to: (item) => ({ name: 'orders.edit', params: { id: item.order?.id } }),
    cellClasses: 'pr-24 tabular-nums'
  },
  {
    key: 'email',
    label: 'E-Mail',
    sortable: true
  },
  {
    key: 'info',
    label: 'Info',
    sortable: false
  },
  {
    key: 'created_at',
    label: 'Erstellt am',
    sortable: true,
    formatter: (value) => {
      if (!value) return '-';
      return new Date(value).toLocaleString('de-CH', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      });
    }
  },
  {
    key: 'status',
    label: 'Status',
    sortable: true,
    cellClasses: '!text-right',
    component: StatusIcon,
    componentProps: (item) => ({
      status: item.status
    })
  }
];

// Load logs function
const loadLogs = async () => {
  try {
    isLoading.value = true;
    const response = await getOrderLogs();
    logs.value = response;
  } catch (error) {
    console.error(error);
    logs.value = [];
  } finally {
    isLoading.value = false;
  }
};

// Sorted logs computed property
const sortedLogs = computed(() => {
  const sorted = [...logs.value].sort((a, b) => {
    // First sort by status: errors first, then success
    if (a.status !== b.status) {
      return a.status === 'error' ? -1 : 1;
    }
    
    // Then sort by the selected sort key
    if (sortKey.value) {
      let aValue = a[sortKey.value];
      let bValue = b[sortKey.value];
      
      // Handle date sorting
      if (sortKey.value === 'created_at') {
        aValue = new Date(aValue);
        bValue = new Date(bValue);
      }
      
      if (aValue < bValue) return sortDirection.value === 'asc' ? -1 : 1;
      if (aValue > bValue) return sortDirection.value === 'asc' ? 1 : -1;
    }
    
    return 0;
  });
  
  return sorted;
});

// Client-side pagination computed properties
const paginatedLogs = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  const end = start + perPage;
  return sortedLogs.value.slice(start, end);
});

const pagination = computed(() => {
  const total = logs.value.length;
  const lastPage = Math.ceil(total / perPage);
  
  return lastPage > 1 ? {
    current_page: currentPage.value,
    last_page: lastPage,
    total: total,
    per_page: perPage
  } : null;
});

// Summary stats
const summaryStats = computed(() => [{
  label: 'Total',
  value: logs.value.length,
  formatter: (value) => `${value} Log-Einträge`
}]);

// Event handlers
const handleSort = ({ key, direction }) => {
  sortKey.value = key;
  sortDirection.value = direction;
  currentPage.value = 1; // Reset to first page when sorting
};

const handlePageChange = (page) => {
  currentPage.value = page;
};


// Load logs on mount
onMounted(async () => {
  await loadLogs();
});
</script>