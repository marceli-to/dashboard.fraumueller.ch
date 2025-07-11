<template>
  <button 
    :type="type" 
    :class="computedClasses"
    :disabled="disabled"
    :aria-label="label">
    {{ label }}
    <slot />
  </button>
</template>
<script setup>
import { computed } from 'vue';

const props = defineProps({
  type: {
    type: String,
    default: 'submit'
  },
  label: {
    type: String,
    required: true
  },
  classes: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  submitting: {
    type: Boolean,
    default: true
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'danger'].includes(value)
  }
});

const defaultClasses = 'inline-flex items-center px-16 py-8 border border-black text-xs bg-white hover:bg-gray-50 focus:outline-none focus:!ring-0';
const dangerClasses = '';

const computedClasses = computed(() => {
  if (props.variant === 'danger') {
    return defaultClasses + ' ' + dangerClasses;
  }
  return defaultClasses + ' ' + props.classes;
});

</script>