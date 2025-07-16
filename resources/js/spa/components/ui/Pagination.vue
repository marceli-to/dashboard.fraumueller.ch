<template>
  <div v-if="totalPages > 1" 
      class="flex items-center justify-between py-12 border-t border-gray-200 text-xxs">
    
    <button
      @click="goToPage(currentPage - 1)"
      :disabled="currentPage === 1"
      class="flex items-center leading-none gap-x-4 hover:text-gray-500 disabled:hover:text-black disabled:opacity-50 disabled:cursor-not-allowed"
    >
      <IconArrowLeft />
      Zurück
    </button>
    
    <div class="flex items-center space-x-6">
      <template v-for="page in visiblePages" :key="page">
        <button
          v-if="page !== '...'"
          @click="goToPage(page)"
          :class="[
            'px-10 py-6 text-xs',
            page === currentPage 
              ? 'border-b border-blue-500 text-blue-500' 
              : 'border-b border-transparent hover:text-blue-500 hover:border-b-blue-500'
          ]"
        >
          {{ page }}
        </button>
        <span v-else class="px-8 py-6 text-xs text-gray-500">...</span>
      </template>
    </div>
    
    <button
      @click="goToPage(currentPage + 1)"
      :disabled="currentPage === totalPages"
      class="flex items-center leading-none gap-x-4 hover:text-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
    >
      Weiter
      <IconArrowRight />
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import IconArrowLeft from '../icons/ArrowLeft.vue'
import IconArrowRight from '../icons/ArrowRight.vue'

const props = defineProps({
  currentPage: {
    type: Number,
    required: true
  },
  totalPages: {
    type: Number,
    required: true
  },
  total: {
    type: Number,
    required: true
  },
  perPage: {
    type: Number,
    required: true
  }
})

const emit = defineEmits(['page-change'])

const visiblePages = computed(() => {
  const pages = []
  const { currentPage, totalPages } = props
  
  if (totalPages <= 7) {
    for (let i = 1; i <= totalPages; i++) {
      pages.push(i)
    }
  } else {
    if (currentPage <= 4) {
      for (let i = 1; i <= 5; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(totalPages)
    } else if (currentPage >= totalPages - 3) {
      pages.push(1)
      pages.push('...')
      for (let i = totalPages - 4; i <= totalPages; i++) {
        pages.push(i)
      }
    } else {
      pages.push(1)
      pages.push('...')
      for (let i = currentPage - 1; i <= currentPage + 1; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(totalPages)
    }
  }
  
  return pages
})

const goToPage = (page) => {
  if (page >= 1 && page <= props.totalPages && page !== props.currentPage) {
    emit('page-change', page)
  }
}
</script>