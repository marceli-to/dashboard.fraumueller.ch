<template>
  <select
    :id="id"
    :value="modelValue"
    @change="$emit('update:modelValue', $event.target.value)"
    class="appearance-none w-full px-8 py-6 border border-gray-200 focus:border-black !ring-0 focus:!ring-0 !outline-none"
    :class="additionalClasses"
  >
    <option v-if="placeholder" value="">{{ placeholder }}</option>
    <option 
      v-for="option in options" 
      :key="option.value" 
      :value="option.value"
    >
      {{ option.label }}
    </option>
  </select>
</template>

<script setup>
defineProps({
  id: {
    type: String,
    default: ''
  },
  modelValue: {
    type: [String, Number],
    default: ''
  },
  options: {
    type: Array,
    required: true,
    validator: (options) => {
      return options.every(option => 
        typeof option === 'object' && 
        'value' in option && 
        'label' in option
      )
    }
  },
  placeholder: {
    type: String,
    default: ''
  },
  additionalClasses: {
    type: String,
    default: ''
  }
})

defineEmits(['update:modelValue'])
</script>