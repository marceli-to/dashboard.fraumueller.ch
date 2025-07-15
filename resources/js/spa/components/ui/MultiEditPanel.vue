<template>
  <div v-if="selectedCount > 0">
    <div class="flex flex-col gap-y-24">
      <div>
        <span class="text-xs block mb-12">
          Aktion für <strong>{{ selectedCount }}</strong> {{ entityLabel }} wählen:
        </span>
        <Select
          :options="actions"
          :model-value="selectedAction"
          @update:model-value="handleActionChange"
          placeholder="Aktion wählen..." />
      </div>
      <!-- Show textarea for notes when notes action is selected -->
      <div v-if="selectedAction === 'notes'">
        <Label for="bulk-notes" label="Bemerkungen" />
        <Textarea
          id="bulk-notes"
          v-model="notesValue"
          placeholder="Bemerkungen eingeben..."
          rows="4"
          @update:model-value="handleNotesChange" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import Select from '@/components/input/Select.vue'
import Label from '@/components/input/Label.vue'
import Textarea from '@/components/input/Textarea.vue'

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

const emit = defineEmits(['action-selected', 'notes-changed'])

const notesValue = ref('')

const entityLabel = computed(() => {
  return props.selectedCount !== 1 
    ? `${props.entityName}en` 
    : props.entityName
})

const handleActionChange = (action) => {
  if (action) {
    emit('action-selected', action)
    // Clear notes when switching away from notes action
    if (action !== 'notes') {
      notesValue.value = ''
    }
  }
}

const handleNotesChange = (value) => {
  emit('notes-changed', value)
}
</script>