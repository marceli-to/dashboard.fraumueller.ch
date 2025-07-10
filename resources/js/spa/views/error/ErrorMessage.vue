<template>
  <slot name="message"></slot>
</template>
<script setup>
import { usePageTitle } from '@/composables/usePageTitle';
const { setTitle } = usePageTitle();
setTitle('');
</script>

  