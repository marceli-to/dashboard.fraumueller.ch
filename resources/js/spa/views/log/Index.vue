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
      :actions="tableActions"
      :selectable="false"
      :sort-key="sortKey"
      :sort-direction="sortDirection"
      :pagination="pagination"
      @sort="handleSort"
      @action-click="handleActionClick"
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
import IconPencil from '@/components/icons/Pencil.vue';

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
    <div class="flex justify-center">
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
    sortable: false,
    component: 'router-link',
    componentProps: (item) => ({
      class: item.status === 'error' ? 'hover:text-red-400 text-red-500 transition-all' : 'hover:text-blue-500 transition-all'
    }),
    to: (item) => ({ name: 'orders.edit', params: { id: item.order?.id } }),
    cellClasses: (item) => item.status === 'error' ? 'pr-24 tabular-nums !text-red-500' : 'pr-24 tabular-nums'
  },
  {
    key: 'email',
    label: 'E-Mail',
    sortable: false,
    cellClasses: (item) => item.status === 'error' ? 'text-red-500' : ''
  },
  {
    key: 'info',
    label: 'Info',
    sortable: false,
    cellClasses: (item) => item.status === 'error' ? 'text-red-500' : ''
  },
  {
    key: 'updated_at',
    label: 'Erstellt am',
    sortable: false,
    formatter: (value) => {
      if (!value) return '-';
      return new Date(value).toLocaleString('de-CH', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    cellClasses: (item) => item.status === 'error' ? 'text-red-500' : ''
  },
  {
    key: 'status',
    label: 'Status',
    sortable: false,
    align: 'center',
    cellClasses: '!text-center',
    component: StatusIcon,
    componentProps: (item) => ({
      status: item.status
    })
  }
];

// Table actions
const tableActions = [
  {
    key: 'edit',
    label: 'Bearbeiten',
    component: 'router-link',
    icon: IconPencil,
    componentProps: {
      class: 'inline-block text-right hover:text-blue-500 transition-all'
    },
    to: (item) => ({ name: 'orders.edit', params: { id: item.order?.id } }),
    visible: (item) => item.status !== 'success'
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


// Client-side pagination computed properties
const paginatedLogs = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  const end = start + perPage;
  return logs.value.slice(start, end);
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

const handleActionClick = ({ action, item }) => {
  // Handle action clicks if needed
  // console.log('Action clicked:', action, item);
};

// Load logs on mount
onMounted(async () => {
  await loadLogs();
});
</script>