<template>
  <div class="relative">
    <!-- Filter Toggle Button -->
    <button
      @click="toggleSidebar"
      class="flex items-center gap-x-8 px-12 py-10 border border-black rounded-sm text-xs bg-white hover:bg-gray-50 focus:outline-none focus:!ring-0 disabled:opacity-50 disabled:cursor-not-allowed">
      <IconFunnel />
      Filter
    </button>

    <!-- Filter Sidebar -->
    <div
      v-if="isOpen"
      class="fixed inset-y-0 right-0 w-[320px] bg-white  shadow-lg z-50 overflow-y-auto"
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
              v-model="localFilters.order_status"
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
              v-model="localFilters.merchant"
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
              v-model="localFilters.product_id"
              :options="productOptions"
              placeholder="Produkt wählen..."
              @update:modelValue="updateFilters"
            />
          </div>

          <!-- Clear Filters Button -->
          <div class="!mt-32">
            <ButtonPrimary 
              label="Zurücksetzen"
              type="button"
              class="!w-full !flex !justify-center"
              @click="clearFilters" />
          </div>
        </div>
      </div>
    </div>

    <!-- Backdrop -->
    <div
      v-if="isOpen"
      @click="closeSidebar"
      class="fixed inset-0 bg-black bg-opacity-25 z-40"
    ></div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { getProducts } from '@/services/api'
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

const isOpen = ref(false)
const productOptions = ref([{ value: '', label: 'Alle' }])

const localFilters = reactive({
  order_status: props.filters.order_status || '',
  merchant: props.filters.merchant || '',
  product_id: props.filters.product_id || ''
})

const toggleSidebar = () => {
  isOpen.value = !isOpen.value
}

const closeSidebar = () => {
  // Check if filters are active and result in zero records
  const hasActiveFilters = localFilters.order_status || localFilters.merchant || localFilters.product_id
  
  if (hasActiveFilters && props.resultCount === 0) {
    clearFilters()
  }
  
  isOpen.value = false
}

const updateFilters = () => {
  emit('update:filters', { ...localFilters })
}

const clearFilters = () => {
  localFilters.order_status = ''
  localFilters.merchant = ''
  localFilters.product_id = ''
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