<template>
  <input
    :id="id"
    :type="type"
    :value="modelValue"
    :required="required"
    @input="$emit('update:modelValue', $event.target.value)"
    class="appearance-none w-full px-12 py-8 border border-gray-200 rounded-sm focus:border-black !ring-0 focus:!ring-0 !outline-none"
  />
</template>

<script setup>
defineProps({
  id: {
    type: String,
    required: true
  },
  type: {
    type: String,
    default: 'text'
  },
  modelValue: {
    type: [String, Date, Number],
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  }
})

defineEmits(['update:modelValue'])
</script>