<template>
  <div v-if="selectedCount > 0">
    <div class="flex flex-col gap-y-16">
      <span class="text-sm">
        Aktion für <strong>{{ selectedCount }}</strong> {{ entityLabel }} wählen:
      </span>
      <Select
        :options="actions"
        :model-value="selectedAction"
        @update:model-value="handleActionChange"
        placeholder="Aktion wählen..." />
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