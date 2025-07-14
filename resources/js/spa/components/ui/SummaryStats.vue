<template>
  <div class="mt-16">
    <span v-for="(stat, index) in stats" :key="stat.label">
      <span v-if="index > 0"> • </span>
      {{ stat.label }} <strong>{{ formatValue(stat) }}</strong>
    </span>
  </div>
</template>

<script setup>
const props = defineProps({
  stats: {
    type: Array,
    required: true,
    validator: (stats) => {
      return stats.every(stat => 
        typeof stat === 'object' && 
        'label' in stat && 
        'value' in stat
      )
    }
  }
})

const formatValue = (stat) => {
  if (stat.formatter && typeof stat.formatter === 'function') {
    return stat.formatter(stat.value)
  }
  return stat.value
}
</script>