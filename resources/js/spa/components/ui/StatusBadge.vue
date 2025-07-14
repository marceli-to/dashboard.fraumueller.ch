<template>
  <button 
    v-if="clickable"
    @click="$emit('click')"
    class="px-8 py-4 text-tiny rounded-full leading-none transition-all hover:opacity-75 cursor-pointer" 
    :class="statusClasses"
  >
    {{ displayStatus }}
  </button>
  <span 
    v-else
    class="px-8 py-4 text-tiny rounded-full leading-none" 
    :class="statusClasses"
  >
    {{ displayStatus }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true
  },
  statusType: {
    type: String,
    default: 'order',
    validator: (value) => ['order', 'payment'].includes(value)
  },
  clickable: {
    type: Boolean,
    default: false
  }
})

defineEmits(['click'])

const displayStatus = computed(() => {
  const status = props.status || (props.statusType === 'order' ? 'open' : 'pending')
  
  if (props.statusType === 'order') {
    const orderStatusLabels = {
      'open': 'offen',
      'fulfilled': 'erledigt'
    }
    return orderStatusLabels[status] || status
  } else {
    const paymentStatusLabels = {
      'paid': 'bezahlt',
      'pending': 'ausstehend',
      'failed': 'fehlgeschlagen',
      'cancelled': 'storniert'
    }
    return paymentStatusLabels[status] || status
  }
})

const statusClasses = computed(() => {
  if (props.statusType === 'order') {
    const classes = {
      'open': 'bg-blue-100 text-blue-800',
      'fulfilled': 'bg-green-100 text-green-800'
    }
    return classes[props.status] || 'bg-blue-100 text-blue-800'
  } else {
    const classes = {
      'paid': 'bg-green-100 text-green-800',
      'pending': 'bg-yellow-100 text-yellow-800',
      'failed': 'bg-red-100 text-red-800',
      'cancelled': 'bg-gray-100 text-gray-800'
    }
    return classes[props.status] || 'bg-gray-100 text-gray-800'
  }
})
</script>