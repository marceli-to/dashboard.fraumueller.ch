import { ref, watch } from 'vue';
import { useDialogStore } from '@/components/dialog/stores/dialog';
import { updateOrder, deleteOrder } from '@/services/api';

export const useOrdersDialogs = (orders) => {
  const dialogStore = useDialogStore();
  
  // Dialog state
  const selectedOrder = ref(null);
  const newOrderStatus = ref('');
  const showMultiEditDialog = ref(false);
  const showOrderDetail = ref(false);

  // Single order status dialog
  const openStatusDialog = (order) => {
    selectedOrder.value = order;
    newOrderStatus.value = order.order_status || 'open';
    showMultiEditDialog.value = false;
    
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

  // Multi-edit dialog
  const openMultiEditDialog = () => {
    showMultiEditDialog.value = true;
    selectedOrder.value = null;
    
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
    selectedOrder.value = null;
    dialogStore.hide();
  };

  const toggleMultiEditDialog = () => {
    if (showMultiEditDialog.value) {
      closeMultiEditDialog();
    } else {
      openMultiEditDialog();
    }
  };

  // Order details dialog
  const showOrderDetails = (order) => {
    selectedOrder.value = order;
    showOrderDetail.value = true;
    showMultiEditDialog.value = false;
    
    dialogStore.show({
      title: `Bestellung Nr. ${order.order_id}`,
      size: 'large',
      hideDefaultActions: true,
      component: null,
      message: null
    });
  };

  // Delete order
  const handleDeleteOrder = async (order) => {
    if (confirm(`Möchten Sie die Bestellung #${order.order_id} wirklich löschen?`)) {
      try {
        await deleteOrder(order.id);
        
        // Remove the order from the local array
        const orderIndex = orders.value.findIndex(o => o.id === order.id);
        if (orderIndex !== -1) {
          orders.value.splice(orderIndex, 1);
        }
        
        return { success: true, deletedOrderId: order.id };
      } catch (error) {
        console.error('Error deleting order:', error);
        alert('Fehler beim Löschen der Bestellung');
        return { success: false };
      }
    }
    return { success: false };
  };

  // Table action handlers
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
      return handleDeleteOrder(item);
    }
  };

  // Watch dialog store visibility to sync local state
  watch(
    () => dialogStore.isVisible,
    (isVisible) => {
      if (!isVisible) {
        showMultiEditDialog.value = false;
        showOrderDetail.value = false;
        selectedOrder.value = null;
      }
    }
  );

  return {
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
    handleActionClick
  };
};