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

const defaultClasses = '';
const dangerClasses = '';

const computedClasses = computed(() => {
  if (props.variant === 'danger') {
    return defaultClasses + ' ' + dangerClasses;
  }
  return defaultClasses + ' ' + props.classes;
});

</script>