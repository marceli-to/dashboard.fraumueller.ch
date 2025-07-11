<template>
  <div v-if="images.length" :class="multiple ? 'mt-20' : ''">
    <draggable
      v-model="localImages"
      item-key="element.resized.name"
      :class="multiple ? 'grid grid-cols-12 gap-20' : ''"
      @end="onDragEnd">
      <template #item="{ element, index }">
        <ImageCard :class="multiple ? 'col-span-6 xl:col-span-4 cursor-move' : ''">
          <Image
            :src="element.resized?.url || element.url"
            :alt="element.resized?.original_name || element.original_name || element.name"
          />
           <button
            v-if="editable"
            type="button"
            @click="deleteImage((element.url), index)"
            class="absolute top-0 right-0 p-8 hover:bg-snow"
          >
            <IconCross variant="small" />
          </button>
        </ImageCard>
      </template>
    </draggable>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import draggable from 'vuedraggable';
import IconCross from '@/components/icons/Cross.vue';
import ImageCard from '@/components/media/Card.vue';
import Image from '@/components/media/Image.vue';
import { useToastStore } from '@/components/toast/stores/toast';
const toast = useToastStore();

const props = defineProps({
  images: {
    type: Array,
    required: true,
  },
  editable: {
    type: Boolean,
    default: true,
  },
  multiple: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['update:images', 'delete']);

// Create a local copy of the images for drag-and-drop
const localImages = ref([...props.images]);

// Watch for changes in the parent images and sync with localImages
watch(
  () => props.images,
  (newImages) => {
    localImages.value = [...newImages];
  }
);

// Emit the updated order when drag ends
const onDragEnd = () => {
  emit('update:images', localImages.value);
};

// Delete an image
const deleteImage = async (url, index) => {
  try {
    // Call the delete API
    await axios.delete(`/api/upload${url}`);

    // Remove the image from the local list
    localImages.value.splice(index, 1);

    // Emit the updated list to the parent
    emit('update:images', localImages.value);

    // Emit the delete event
    emit('delete', index);

    toast.show('Image deleted successfully!', 'success')
  } 
  catch (error) {
    console.error('Failed to delete image:', error);
  }
};
</script>

<style scoped>
.sortable-ghost {
  @apply bg-graphite/10
}
</style>