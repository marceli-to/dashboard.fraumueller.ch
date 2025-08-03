<template>
  <IconCheckmark 
    v-if="hasConfirmationText" 
    class="size-16 text-green-600" 
  />
  <IconShield 
    v-else 
    class="size-16 text-red-600" 
  />
</template>

<script setup>
import { computed } from 'vue';
import IconCheckmark from '@/components/icons/Checkmark.vue';
import IconShield from '@/components/icons/Shield.vue';

const props = defineProps({
  // The DataTable passes the column value as 'status' prop!
  status: {
    type: [String, null],
    default: null
  }
});

const hasConfirmationText = computed(() => {
  const text = props.status;
  
  if (!text) return false;
  if (typeof text !== 'string') return false;
  
  return text.trim().length > 0;
});
</script>