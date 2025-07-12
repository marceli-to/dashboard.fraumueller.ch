<template>
  <label 
    :for="for" 
    class="block text-xs mb-8 font-medium">
    {{ label }}
    <span v-if="required" class="text-xxs ml-1">*</span>
  </label>
</template>

<script setup>
defineProps({
  for: {
    type: String,
    required: true
  },
  label: {
    type: String,
    required: true
  },
  required: {
    type: Boolean,
    default: false
  }
})
</script>