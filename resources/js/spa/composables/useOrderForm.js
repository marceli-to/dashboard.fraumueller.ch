import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { defaultFormData, updateFormFields } from '@/constants/formConstants';

export const useOrderForm = (mode = 'create') => {
  const router = useRouter();
  
  // Form state
  const submitting = ref(false);
  const error = ref(null);
  
  // Initialize form with default data
  const form = ref({ ...defaultFormData });
  
  // Reset form to defaults
  const resetForm = () => {
    form.value = { ...defaultFormData };
    error.value = null;
  };
  
  // Populate form with existing data (for edit mode)
  const populateForm = (orderData) => {
    const fieldsToUpdate = mode === 'update' ? updateFormFields : Object.keys(defaultFormData);
    
    fieldsToUpdate.forEach(key => {
      if (orderData[key] !== undefined) {
        form.value[key] = orderData[key] || '';
      }
    });
  };
  
  // Clear error state
  const clearError = () => {
    error.value = null;
  };
  
  // Set error state
  const setError = (message) => {
    error.value = message;
  };
  
  // Set submitting state
  const setSubmitting = (isSubmitting) => {
    submitting.value = isSubmitting;
  };
  
  // Navigate back to orders list
  const navigateToOrders = () => {
    router.push('/dashboard/bestellungen');
  };
  
  // Form submission wrapper
  const handleSubmit = async (submitFunction) => {
    try {
      setSubmitting(true);
      clearError();
      
      await submitFunction(form.value);
      navigateToOrders();
    } catch (err) {
      const errorMessage = err.response?.data?.message || err.message;
      const prefix = mode === 'create' 
        ? 'Fehler beim Erstellen der Bestellung: ' 
        : 'Fehler beim Speichern: ';
      setError(prefix + errorMessage);
    } finally {
      setSubmitting(false);
    }
  };
  
  return {
    form,
    submitting,
    error,
    resetForm,
    populateForm,
    clearError,
    setError,
    setSubmitting,
    navigateToOrders,
    handleSubmit
  };
};