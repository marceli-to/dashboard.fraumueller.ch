import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { getOrder, getProducts, createOrder, updateOrder } from '@/services/api';

export const useOrderData = () => {
  const route = useRoute();
  
  // Data state
  const loading = ref(false);
  const productOptions = ref([]);
  
  // Load products for dropdown
  const loadProducts = async () => {
    try {
      const products = await getProducts();
      productOptions.value = products.data.map(product => ({
        value: product.id,
        label: product.name
      }));
    } catch (err) {
      throw new Error('Fehler beim Laden der Produkte: ' + (err.response?.data?.message || err.message));
    }
  };
  
  // Load single order for edit mode
  const loadOrder = async (orderId) => {
    try {
      loading.value = true;
      
      const [order, products] = await Promise.all([
        getOrder(orderId),
        getProducts()
      ]);
      
      productOptions.value = products.data.map(product => ({
        value: product.id,
        label: product.name
      }));
      
      return order;
    } catch (err) {
      throw new Error('Fehler beim Laden der Bestellung: ' + (err.response?.data?.message || err.message));
    } finally {
      loading.value = false;
    }
  };
  
  // Create new order
  const createNewOrder = async (formData) => {
    return await createOrder(formData);
  };
  
  // Update existing order
  const updateExistingOrder = async (orderId, formData) => {
    return await updateOrder(orderId, formData);
  };
  
  // Get order ID from route params
  const getOrderId = () => {
    return route.params.id;
  };
  
  return {
    loading,
    productOptions,
    loadProducts,
    loadOrder,
    createNewOrder,
    updateExistingOrder,
    getOrderId
  };
};