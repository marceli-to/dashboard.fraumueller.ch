<template>
  <thead>
    <tr class="border-b border-gray-200 [&_th]:font-medium [&_th]:py-12 [&_th]:pr-12">
      <th v-if="selectable" class="text-left pl-4 !pr-12">
        <Checkbox
          :model-value="isAllSelected"
          @update:model-value="$emit('toggle-select-all', $event)"
        />
      </th>
      <th 
        v-for="column in columns" 
        :key="column.key"
        :class="getColumnClasses(column)"
      >
        <button
          v-if="column.sortable"
          @click="handleSort(column)"
          class="flex items-center gap-x-4 hover:text-gray-600 transition-colors group"
        >
          {{ column.label }}
          <CaretUpDown class="text-gray-400 group-hover:text-gray-600" />
        </button>
        <span v-else>{{ column.label }}</span>
      </th>
      <th v-if="hasActions" class="text-right !pr-0"></th>
    </tr>
  </thead>
</template>

<script setup>
import { computed } from 'vue'
import Checkbox from '@/components/input/Checkbox.vue'
import CaretUpDown from '@/components/icons/CaretUpDown.vue'

const props = defineProps({
  columns: {
    type: Array,
    required: true,
    validator: (columns) => {
      return columns.every(column => 
        typeof column === 'object' && 
        'key' in column && 
        'label' in column
      )
    }
  },
  selectable: {
    type: Boolean,
    default: false
  },
  isAllSelected: {
    type: Boolean,
    default: false
  },
  hasActions: {
    type: Boolean,
    default: false
  },
  sortKey: {
    type: String,
    default: null
  },
  sortDirection: {
    type: String,
    default: 'asc'
  }
})

const emit = defineEmits(['toggle-select-all', 'sort'])

const getColumnClasses = (column) => {
  const baseClasses = ['text-left']
  
  if (column.align) {
    baseClasses[0] = `text-${column.align}`
  }
  
  if (column.classes) {
    baseClasses.push(column.classes)
  }
  
  return baseClasses.join(' ')
}

const handleSort = (column) => {
  emit('sort', column)
}
</script>