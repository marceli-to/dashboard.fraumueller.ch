<template>
  <textarea
    :id="id"
    :value="modelValue"
    :rows="rows"
    :placeholder="placeholder"
    @input="$emit('update:modelValue', $event.target.value)"
    class="appearance-none w-full px-12 py-8 border border-gray-200 focus:border-black !ring-0 focus:!ring-0 !outline-none placeholder:text-gray-500"
  />
</template>

<script setup>
defineProps({
  id: {
    type: String,
    required: true
  },
  modelValue: {
    type: String,
    default: ''
  },
  rows: {
    type: [String, Number],
    default: 4
  },
  placeholder: {
    type: String,
    default: ''
  }
})

defineEmits(['update:modelValue'])
</script>