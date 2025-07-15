import { ref } from 'vue';
import { bulkUpdateOrders, exportOrdersCsv } from '@/services/api';

export const useOrdersMultiEdit = (orders) => {
  // Multi-edit state
  const selectedOrderIds = ref([]);
  const selectedAction = ref('');
  const notesValue = ref('');
  const exportResult = ref(null);

  // Selection operations
  const toggleSelectAll = (checked, filteredOrders) => {
    if (checked) {
      selectedOrderIds.value = filteredOrders.map(order => order.id);
    } else {
      selectedOrderIds.value = [];
    }
  };

  // Clear all multi-edit state
  const clearMultiEditState = () => {
    selectedOrderIds.value = [];
    selectedAction.value = '';
    notesValue.value = '';
    exportResult.value = null;
  };

  // Update local order data after bulk operations
  const updateLocalOrderData = (orderIds, updateData) => {
    orderIds.forEach(orderId => {
      const orderIndex = orders.value.findIndex(o => o.id === orderId);
      if (orderIndex !== -1) {
        Object.assign(orders.value[orderIndex], updateData);
      }
    });
  };

  // Apply bulk actions
  const applyBulkAction = async () => {
    if (!selectedAction.value || selectedOrderIds.value.length === 0) return;
    
    try {
      if (selectedAction.value === 'export-csv') {
        // Handle CSV export
        const result = await exportOrdersCsv(selectedOrderIds.value);
        
        if (result.success) {
          exportResult.value = result;
          selectedAction.value = '';
          return { success: true, type: 'export' };
        } else {
          throw new Error('Fehler beim Erstellen der CSV-Datei');
        }
      } else if (selectedAction.value === 'notes') {
        // Handle notes update
        await bulkUpdateOrders(selectedOrderIds.value, { notes: notesValue.value });
        
        // Update local order data with appended notes
        selectedOrderIds.value.forEach(orderId => {
          const orderIndex = orders.value.findIndex(o => o.id === orderId);
          if (orderIndex !== -1) {
            const existingNotes = orders.value[orderIndex].notes;
            if (existingNotes) {
              orders.value[orderIndex].notes = notesValue.value + "\n" + existingNotes;
            } else {
              orders.value[orderIndex].notes = notesValue.value;
            }
          }
        });
        
        clearMultiEditState();
        return { success: true, type: 'notes' };
      } else {
        // Handle status updates
        let newStatus = '';
        
        if (selectedAction.value === 'status-open') {
          newStatus = 'open';
        } else if (selectedAction.value === 'status-fulfilled') {
          newStatus = 'fulfilled';
        }
        
        if (newStatus) {
          await bulkUpdateOrders(selectedOrderIds.value, { order_status: newStatus });
          
          // Update local order data
          updateLocalOrderData(selectedOrderIds.value, { order_status: newStatus });
          
          clearMultiEditState();
          return { success: true, type: 'status' };
        }
      }
    } catch (error) {
      console.error('Error applying bulk action:', error);
      throw error;
    }
  };

  return {
    selectedOrderIds,
    selectedAction,
    notesValue,
    exportResult,
    toggleSelectAll,
    clearMultiEditState,
    applyBulkAction
  };
};