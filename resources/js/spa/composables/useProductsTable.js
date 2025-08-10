import { ref } from 'vue';
import IconEdit from '@/components/icons/Pencil.vue';
import IconTrash from '@/components/icons/Trash.vue';
import IconPaperPlane from '@/components/icons/PaperPlane.vue';
import ConfirmationTextIcon from '@/components/ui/ConfirmationTextIcon.vue';

export const useProductsTable = () => {
  // Sorting state
  const sortKey = ref('name');
  const sortDirection = ref('asc');

  // Table configuration
  const tableColumns = [
    {
      key: 'name',
      label: 'Name',
      component: 'router-link',
      componentProps: {
        class: 'hover:text-blue-500 transition-all'
      },
      to: (item) => ({ name: 'products.edit', params: { id: item.id } }),
      cellClasses: 'pr-12 max-w-[360px]',
      sortable: true,
      sortKey: 'name'
    },
    {
      key: 'confirmation_text',
      label: 'Bestätigungstext',
      align: 'center',
      cellClasses: 'flex items-center justify-center w-full',
      sortable: true,
      sortKey: 'confirmation_text',
      component: ConfirmationTextIcon,
      componentProps: {},
      cellClasses: 'flex items-center justify-center w-full'
    },
    {
      key: 'orders_count',
      label: 'Bestellungen',
      align: 'center',
      cellClasses: 'text-center tabular-nums',
      sortable: true,
      sortKey: 'orders_count'
    },
    {
      key: 'created_at',
      label: 'Erstellt am',
      cellClasses: 'pr-12',
      sortable: true,
      sortKey: 'created_at'
    }
  ];

  const tableActions = [
    {
      key: 'send-test-notification',
      component: 'button',
      componentProps: (item) => ({
        class: `inline-block text-right transition-all ${
          !item.confirmation_text 
            ? 'text-gray-400 cursor-not-allowed' 
            : 'hover:text-blue-500'
        }`,
        disabled: !item.confirmation_text,
        title: !item.confirmation_text 
          ? 'Kein Bestätigungstext vorhanden' 
          : 'Test-E-Mail senden'
      }),
      icon: IconPaperPlane,
      visible: (item) => !!item.confirmation_text
    },
    {
      key: 'edit',
      component: 'router-link',
      componentProps: {
        class: 'inline-block text-right hover:text-blue-500 transition-all'
      },
      to: (item) => ({ name: 'products.edit', params: { id: item.id } }),
      icon: IconEdit
    },
    {
      key: 'delete',
      component: 'button',
      componentProps: (item) => ({
        class: `inline-block text-right transition-all ${
          item.orders_count > 0 
            ? 'text-gray-400 cursor-not-allowed' 
            : 'hover:text-red-500'
        }`,
        disabled: item.orders_count > 0,
        title: item.orders_count > 0 
          ? 'Produkt kann nicht gelöscht werden, da Bestellungen vorhanden sind' 
          : 'Produkt löschen'
      }),
      icon: IconTrash
    }
  ];

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
    handleSort
  };
};