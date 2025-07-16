<template>
  <div class="mt-24 w-full">
    <div class="overflow-x-auto" v-if="data.length > 0">
      <table class="w-full text-xxs">
        <TableHeader
          :columns="columns"
          :selectable="selectable"
          :is-all-selected="isAllSelected"
          :has-actions="actions.length > 0"
          :sort-key="sortKey"
          :sort-direction="sortDirection"
          @toggle-select-all="handleToggleSelectAll"
          @sort="handleSort"
        />
        <tbody class="divide-y divide-gray-200">
          <TableRow
            v-for="item in data"
            :key="item[itemKey]"
            :item="item"
            :columns="columns"
            :actions="actions"
            :selectable="selectable"
            :selectable-key="selectableKey"
            :selected-items="selectedItems"
            @update:selected-items="$emit('update:selectedItems', $event)"
            @cell-click="$emit('cell-click', $event)"
            @action-click="$emit('action-click', $event)"
          />
        </tbody>
      </table>
    </div>
    
    <Pagination
      v-if="pagination"
      :current-page="pagination.current_page"
      :total-pages="pagination.last_page"
      :total="pagination.total"
      :per-page="pagination.per_page"
      @page-change="handlePageChange"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import TableHeader from './TableHeader.vue'
import TableRow from './TableRow.vue'
import Pagination from './Pagination.vue'

const props = defineProps({
  data: {
    type: Array,
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
  selectedItems: {
    type: Array,
    default: () => []
  },
  itemKey: {
    type: String,
    default: 'id'
  },
  selectableKey: {
    type: String,
    default: 'id'
  },
  sortKey: {
    type: String,
    default: null
  },
  sortDirection: {
    type: String,
    default: 'asc',
    validator: (value) => ['asc', 'desc'].includes(value)
  },
  pagination: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['update:selectedItems', 'cell-click', 'action-click', 'toggle-select-all', 'sort', 'page-change'])

const isAllSelected = computed(() => {
  return props.data.length > 0 && props.selectedItems.length === props.data.length
})

const handleToggleSelectAll = (checked) => {
  if (checked) {
    const allIds = props.data.map(item => item[props.selectableKey])
    emit('update:selectedItems', allIds)
  } else {
    emit('update:selectedItems', [])
  }
  emit('toggle-select-all', checked)
}

const handleSort = (column) => {
  emit('sort', column)
}

const handlePageChange = (page) => {
  emit('page-change', page)
}
</script>