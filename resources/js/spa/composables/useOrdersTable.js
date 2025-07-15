import { ref } from 'vue';
import IconEdit from '@/components/icons/Edit.vue';
import IconTrash from '@/components/icons/Trash.vue';
import IconMagnifyingGlass from '@/components/icons/MagnifyingGlass.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import { defaultSortKey, defaultSortDirection } from '@/constants/orderConstants';

export const useOrdersTable = () => {
  // Sorting state
  const sortKey = ref(defaultSortKey);
  const sortDirection = ref(defaultSortDirection);

  // Table configuration
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

  // Utility functions
  const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('de-CH', {
      year: '2-digit',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  // Sorting
  const handleSort = (column) => {
    if (sortKey.value === column.sortKey) {
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
      sortKey.value = column.sortKey;
      sortDirection.value = 'asc';
    }
  };

  return {
    sortKey,
    sortDirection,
    tableColumns,
    tableActions,
    handleSort,
    formatDate
  };
};