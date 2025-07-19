import { computed } from 'vue';
import { useFiltersStore } from '@/stores/filters';

export const useOrdersFiltering = (orders, sortKey, sortDirection) => {
  const filtersStore = useFiltersStore();
  
  // Get filters from store
  const filters = computed(() => filtersStore.filters);

  // Filtered and sorted orders
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
    
    if (filters.value.search) {
      const searchTerm = filters.value.search.toLowerCase();
      filtered = filtered.filter(order => {
        const orderId = order.order_id?.toLowerCase() || '';
        const email = order.email?.toLowerCase() || '';
        const phone = order.phone?.toLowerCase() || '';
        const product = order.product_name?.toLowerCase() || '';
        const billingName = order.billing_name?.toLowerCase() || '';
        
        return orderId.includes(searchTerm) ||
               email.includes(searchTerm) || 
               phone.includes(searchTerm) || 
               product.includes(searchTerm) ||
               billingName.includes(searchTerm);
      });
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

  // Summary stats
  const summaryStats = computed(() => [{
    label: 'Total',
    value: filteredAndSortedOrders.value.length,
    formatter: (value) => `${value} Bestellungen`
  }]);

  // Filter operations
  const updateFilters = (newFilters) => {
    filtersStore.updateFilters(newFilters);
  };

  return {
    filters,
    filteredAndSortedOrders,
    summaryStats,
    updateFilters
  };
};