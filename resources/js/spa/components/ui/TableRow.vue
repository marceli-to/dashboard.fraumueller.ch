<template>
  <tr class="hover:bg-gray-100">
    <td v-if="selectable" class="py-12 pr-12 pl-4">
      <Checkbox
        :value="item[selectableKey]"
        :model-value="selectedItems"
        @update:model-value="$emit('update:selectedItems', $event)"
      />
    </td>
    <td 
      v-for="column in columns" 
      :key="column.key"
      :class="getCellClasses(column)"
    >
      <component 
        v-if="column.component"
        :is="column.component"
        v-bind="getComponentProps(column)"
        :status="item[column.key]"
        @click="handleCellClick(column)"
      >
        {{ getCellContent(column) }}
      </component>
      <template v-else>
        {{ getCellContent(column) }}
      </template>
    </td>
    <td v-if="actions.length > 0" class="py-12 text-right space-x-6 whitespace-nowrap">
      <component
        v-for="action in actions"
        :key="action.key"
        :is="action.component"
        v-bind="getActionProps(action)"
        @click="handleActionClick(action)"
      >
        <component v-if="action.icon" :is="action.icon" />
        <span v-else>{{ action.label }}</span>
      </component>
    </td>
  </tr>
</template>

<script setup>
import { computed } from 'vue'
import Checkbox from '@/components/input/Checkbox.vue'

const props = defineProps({
  item: {
    type: Object,
    required: true
  },
  columns: {
    type: Array,
    required: true
  },
  actions: {
    type: Array,
    default: () => []
  },
  selectable: {
    type: Boolean,
    default: false
  },
  selectableKey: {
    type: String,
    default: 'id'
  },
  selectedItems: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:selectedItems', 'cell-click', 'action-click'])

const getCellClasses = (column) => {
  const baseClasses = ['py-12']
  
  if (column.cellClasses) {
    baseClasses.push(column.cellClasses)
  }
  
  return baseClasses.join(' ')
}

const getCellContent = (column) => {
  if (column.formatter && typeof column.formatter === 'function') {
    return column.formatter(props.item[column.key], props.item)
  }
  return props.item[column.key]
}

const getComponentProps = (column) => {
  const baseProps = column.componentProps || {}
  
  if (column.component === 'router-link') {
    return {
      ...baseProps,
      to: column.to ? column.to(props.item) : baseProps.to
    }
  }
  
  return baseProps
}

const getActionProps = (action) => {
  const baseProps = action.componentProps || {}
  
  if (action.component === 'router-link') {
    return {
      ...baseProps,
      to: action.to ? action.to(props.item) : baseProps.to
    }
  }
  
  return baseProps
}

const handleCellClick = (column) => {
  if (column.clickable) {
    emit('cell-click', { column, item: props.item })
  }
}

const handleActionClick = (action) => {
  emit('action-click', { action, item: props.item })
}
</script>