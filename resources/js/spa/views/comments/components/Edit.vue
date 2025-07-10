<template>
  <div 
    v-if="isOpen" 
    class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center">
    <div class="bg-white rounded-sm p-16 w-full max-w-xl shadow-xl">
      <h2 class="text-md mb-16">Kommentar bearbeiten</h2>

      <textarea
        v-model="editableComment.comment"
        rows="5"
        class="appearance-none w-full !ring-0 border-black focus:border-black p-8"
        maxlength="250"
      ></textarea>

      <div class="mt-2 text-[#777] text-xs flex justify-between items-center">
        <div>{{ formatDate(editableComment.date) }} • {{ editableComment.building }}</div>
        <div>{{ editableComment.comment.length }}/250</div>
      </div>

      <div class="mt-16 flex justify-end gap-16">
        <button 
          @click="close" 
          class="py-6 px-16 text-xs bg-gray-200 inline-block w-auto hover:bg-black/50 hover:text-white transition-colors duration-150 text-black">
          Abbrechen
        </button>
        <button 
          @click="submit" 
          class="py-6 px-16 text-xs bg-black inline-block w-auto hover:bg-black/50 hover:text-white transition-colors duration-150 text-white">
          Speichern
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { updateComment } from '@/services/api';
import { useToastStore } from '@/components/toast/stores/toast';
const toast = useToastStore();

const props = defineProps({
  comment: Object,
  isOpen: Boolean,
});

const emit = defineEmits(['close', 'save']);

// Create a local copy of the comment to edit
const editableComment = ref({ ...props.comment });

watch(() => props.comment, (newVal) => {
  editableComment.value = { ...newVal };
});

const close = () => emit('close');

const submit = async () => {
  const trimmed = editableComment.value.comment?.trim();
  if (!trimmed) {
    toast.show('Der Kommentar darf nicht leer sein.', 'error');
    // reset the textarea to the original value
    editableComment.value.comment = props.comment.comment;
    return;
  }
  
  try {
    const updated = await updateComment(props.comment.id, editableComment.value);
    emit('save', updated);
    close();
  } catch (e) {
    console.error('Update failed:', e);
  }
};


const formatDate = (iso) => {
  const date = new Date(iso);
  return date.toLocaleString('de-DE', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).replace(',', ' –');
};
</script>
