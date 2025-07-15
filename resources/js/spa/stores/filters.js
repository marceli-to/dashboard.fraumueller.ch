import { defineStore } from 'pinia';
import { ref, watch } from 'vue';

export const useFiltersStore = defineStore('filters', () => {
  const STORAGE_KEY = 'orders_filters';
  
  // Default filter values
  const defaultFilters = {
    order_status: 'open',
    merchant: '',
    product_id: ''
  };
  
  // Load filters from localStorage
  const loadFromStorage = () => {
    try {
      const stored = localStorage.getItem(STORAGE_KEY);
      if (stored) {
        const parsed = JSON.parse(stored);
        return {
          order_status: parsed.order_status || defaultFilters.order_status,
          merchant: parsed.merchant || defaultFilters.merchant,
          product_id: parsed.product_id || defaultFilters.product_id
        };
      }
    } catch (error) {
      console.warn('Error loading filters from localStorage:', error);
    }
    return { ...defaultFilters };
  };
  
  // Save filters to localStorage
  const saveToStorage = (filters) => {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(filters));
    } catch (error) {
      console.warn('Error saving filters to localStorage:', error);
    }
  };
  
  // Reactive filter state
  const filters = ref(loadFromStorage());
  
  // Watch for changes and persist to localStorage
  watch(
    filters,
    (newFilters) => {
      saveToStorage(newFilters);
    },
    { deep: true }
  );
  
  // Actions
  const updateFilters = (newFilters) => {
    filters.value = { ...newFilters };
  };
  
  const clearFilters = () => {
    filters.value = { ...defaultFilters };
  };
  
  const setFilter = (key, value) => {
    filters.value[key] = value;
  };
  
  return {
    filters,
    updateFilters,
    clearFilters,
    setFilter
  };
});