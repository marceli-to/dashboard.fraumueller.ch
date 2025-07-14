<template>
  <input
    type="checkbox"
    :checked="isChecked"
    :value="value"
    @change="handleChange"
    class="appearance-none size-14 border-black text-black focus:!ring-0 !ring-0 !outline-none !shadow-none"
    :class="additionalClasses"
  />
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: [Boolean, Array],
    default: false
  },
  value: {
    type: [String, Number, Boolean],
    default: null
  },
  additionalClasses: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue'])

const isChecked = computed(() => {
  if (Array.isArray(props.modelValue)) {
    return props.modelValue.includes(props.value)
  }
  return props.modelValue
})

const handleChange = (event) => {
  if (Array.isArray(props.modelValue)) {
    const newValue = [...props.modelValue]
    if (event.target.checked) {
      newValue.push(props.value)
    } else {
      const index = newValue.indexOf(props.value)
      if (index > -1) {
        newValue.splice(index, 1)
      }
    }
    emit('update:modelValue', newValue)
  } else {
    emit('update:modelValue', event.target.checked)
  }
}
</script>