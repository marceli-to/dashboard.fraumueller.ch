<template>
  <Message>
    <template #message>
      <h1 class="text-lg leading-[1.25] mb-32">
        419 – Sitzung abgelaufen
      </h1>
      <p>
        Sie werden in {{ countdown }} Sekunde<span v-if="countdown !== 1">n</span> weitergeleitet.
      </p>
    </template>
  </Message>
</template>
 <script setup>
import { ref, onMounted } from 'vue'
import Message from '@/views/error/ErrorMessage.vue'
const countdown = ref(5)

onMounted(() => {
  const interval = setInterval(() => {
    countdown.value--
    if (countdown.value === 0) {
      clearInterval(interval)
      document.location.href = '/login'
    }
  }, 1000)
})
</script>
