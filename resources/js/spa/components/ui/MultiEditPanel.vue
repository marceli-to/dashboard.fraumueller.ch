<template>
  <div v-if="selectedCount > 0" class="max-w-7xl absolute top-16 right-16">
    <div class="bg-blue-50 border border-blue-200 rounded-sm px-12 py-16 mb-16 max-w-[500px]">
      <div class="flex items-center justify-between gap-x-16">
        <span class="text-xs text-blue-800 shrink-0">
          <strong>{{ selectedCount }}</strong> {{ entityLabel }} ausgewählt
        </span>
        <Select
          :options="actions"
          :model-value="selectedAction"
          @update:model-value="handleActionChange"
          placeholder="Aktion wählen..."
          additional-classes="accent-blue-500 rounded-sm bg-transparent border-blue-300 text-xxs"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import Select from '@/components/input/Select.vue'

const props = defineProps({
  selectedCount: {
    type: Number,
    required: true
  },
  actions: {
    type: Array,
    required: true
  },
  selectedAction: {
    type: String,
    default: ''
  },
  entityName: {
    type: String,
    default: 'Bestellung'
  }
})

const emit = defineEmits(['action-selected'])

const entityLabel = computed(() => {
  return props.selectedCount !== 1 
    ? `${props.entityName}en` 
    : props.entityName
})

const handleActionChange = (action) => {
  if (action) {
    emit('action-selected', action)
  }
}
</script>