<template>
  <template v-if="!isLoading">
    <div class="min-h-screen flex">
      <div class="w-[240px] bg-black">
        <Navigation />
      </div>
      <div class="w-full py-16 px-16 relative">
        <router-view v-slot="{ Component }">
          <component :is="Component" :key="route.fullPath" ref="pageComponent" />
        </router-view>
      </div>
    </div>
    <Toast />
  </template>
</template>
<script setup>
import { ref, watchEffect, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import Navigation from '@/layout/Navigation.vue';
import Toast from '@/components/toast/Toast.vue';

const route = useRoute();
const pageComponent = ref(null);
const pageTitle = ref('');
const isLoading = ref(true);

onMounted(async () => {
  isLoading.value = false;
});

watchEffect(() => {
  if (pageComponent.value?.title) {
    pageTitle.value = pageComponent.value.title;
  }
});
</script>