<template>
  <h1 class="text-lg leading-[1.25]">
    Downloads
  </h1>
  <template v-if="!isLoading">
    <div class="max-w-3xl mt-38 text-xs leading-none flex gap-x-16">
      <a 
        :href="'/export/comments?v='+randomString(8)"
        class="py-10 pr-16 pl-12 text-xs bg-black inline-flex gap-x-8 w-auto hover:bg-black/50 hover:text-white transition-colors duration-150 text-white">
        <IconDownload />
        Kommentare
      </a>
      <a
        :href="'/export/votes?v='+randomString(8)"
        class="py-10 pr-16 pl-12 text-xs bg-black inline-flex gap-x-8 w-auto hover:bg-black/50 hover:text-white transition-colors duration-150 text-white">
        <IconDownload />
        Stimmen
      </a>
    </div>
  </template>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { usePageTitle } from '@/composables/usePageTitle';
import IconDownload from '@/components/icons/Download.vue';
const { setTitle } = usePageTitle();
setTitle('Downloads');

const isLoading = ref(true);

onMounted(async () => {
  isLoading.value = false;
});

const randomString = (length) => {
  let result = [];
  const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  const charactersLength = characters.length;
  for (let i = 0; i < length; i++) {
    result.push(characters.charAt(Math.floor(Math.random() * charactersLength)));
  }
  return result.join('');
}
</script>