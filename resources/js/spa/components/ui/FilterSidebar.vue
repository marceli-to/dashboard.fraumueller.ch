<template>
  <div class="relative">
    <!-- Filter Toggle Button -->
    <button
      @click="toggleSidebar"
      class="relative flex items-center gap-x-6 px-12 py-10 border border-black rounded-sm text-xs bg-white hover:bg-gray-50 focus:outline-none focus:!ring-0 disabled:opacity-50 disabled:cursor-not-allowed"
      :class="hasActiveFilters ? '!bg-blue-200 border-blue-600 text-blue-900' : ''">
      <IconFunnel class="size-18" />
      Filter
    </button>

    <!-- Filter Sidebar -->
    <div
      v-if="isOpen"
      class="fixed inset-y-0 right-0 w-[320px] bg-white shadow-lg z-50 overflow-y-auto"
    >
      <div class="p-16">
        <div class="flex items-center justify-between mb-32">
          <h3 class="text-sm font-medium">Filter</h3>
          <button
            @click="closeSidebar">
            <IconCross />
          </button>
        </div>

        <div class="space-y-24">
          <!-- Order Status Filter -->
          <div>
            <Label for="filter-status" label="Status" />
            <Select
              id="filter-status"
              v-model="filters.order_status"
              :options="[
                { value: '', label: 'Alle' },
                { value: 'open', label: 'Offen' },
                { value: 'fulfilled', label: 'Erledigt' }
              ]"
              placeholder="Status wählen..."
              @update:modelValue="updateFilters"
            />
          </div>

          <!-- Merchant Filter -->
          <div>
            <Label for="filter-merchant" label="Anbieter" />
            <Select
              id="filter-merchant"
              v-model="filters.merchant"
              :options="[
                { value: '', label: 'Alle' },
                { value: 'twint', label: 'TWINT' },
                { value: 'squarespace', label: 'Squarespace' },
                { value: 'other', label: 'Andere' }
              ]"
              placeholder="Anbieter wählen..."
              @update:modelValue="updateFilters"
            />
          </div>

          <!-- Product Filter -->
          <div>
            <Label for="filter-product" label="Produkt" />
            <Select
              id="filter-product"
              v-model="filters.product_id"
              :options="productOptions"
              placeholder="Produkt wählen..."
              @update:modelValue="updateFilters"
            />
          </div>

          <!-- Clear Filters Button -->
          <div class="!mt-32">
            <ButtonPrimary 
              label="Anzeigen"
              type="button"
              class="!w-full !flex !justify-center"
              @click="closeSidebar" />
              <a 
                href="javascript:;" 
                class="text-xs mt-16 block underline underline-offset-2 text-center hover:no-underline" 
                @click="clearFilters">
                Zurücksetzen
              </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Backdrop -->
    <div
      v-if="isOpen"
      @click="closeSidebar"
      class="fixed inset-0 bg-black/25 z-40"
    ></div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { getProducts } from '@/services/api'
import { useFiltersStore } from '@/stores/filters'
import Label from '@/components/input/Label.vue'
import Select from '@/components/input/Select.vue'
import IconCross from '@/components/icons/Cross.vue'
import IconFunnel from '@/components/icons/Funnel.vue'
import ButtonPrimary from '@/components/buttons/Primary.vue'

const props = defineProps({
  filters: {
    type: Object,
    default: () => ({})
  },
  resultCount: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['update:filters'])

const filtersStore = useFiltersStore()
const isOpen = ref(false)
const productOptions = ref([{ value: '', label: 'Alle' }])

// Use store filters directly instead of local reactive copy
const filters = computed(() => filtersStore.filters)

// Computed property to check if any filters are active
const hasActiveFilters = computed(() => {
  return filters.value.order_status || filters.value.merchant || filters.value.product_id
})

const toggleSidebar = () => {
  isOpen.value = !isOpen.value
}

const closeSidebar = () => {
  // Check if filters are active and result in zero records
  if (hasActiveFilters.value && props.resultCount === 0) {
    clearFilters()
  }
  
  isOpen.value = false
}

const updateFilters = () => {
  emit('update:filters', { ...filters.value })
}

const clearFilters = () => {
  filtersStore.clearFilters()
  updateFilters()
}

const loadProducts = async () => {
  try {
    const products = await getProducts()
    productOptions.value = [
      { value: '', label: 'Alle' },
      ...products.data
    ]
  } catch (err) {
    console.error('Error loading products:', err)
  }
}

onMounted(() => {
  loadProducts()
})
</script>