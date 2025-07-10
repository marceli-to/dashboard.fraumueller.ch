<template>
  <div class="border-t border-black last-of-type:border-b py-10 flex justify-between items-start">
    <div>
      <div>{{ comment.comment.length > 75 ? comment.comment.slice(0, 75) + '...' : comment.comment }}</div>
      <div class="mt-2 text-[#777] text-xxs">
        {{ formatDate(comment.date) }} • {{ comment.building }}
      </div>
    </div>
    <div class="flex gap-x-8">

      <button  
        class="hover:text-gray-400 transition-colors"
        :title="comment.published ? 'Verbergen' : 'Veröffentlichen'"
        @click="toggle(comment.id)"
        v-if="can.includes('toggle')">
        <IconLightbulb :class="comment.published ? 'text-yellow-400 hover:text-gray-400 transition-colors' : 'text-gray-400 hover:text-yellow-400 transition-colors'" />
      </button>
      <button 
        title="Bearbeiten"
        class="hover:text-gray-400 transition-colors"
        @click="editComment(comment)"
        v-if="can.includes('edit')">
        <IconEdit />
      </button>
      <button 
        title="Löschen" 
        class="hover:text-gray-400 transition-colors"
        @click="destroy(comment.id)"
        v-if="can.includes('delete')">
        <IconTrash />
      </button>
      <button 
        title="Wiederherstellen" 
        class="hover:text-gray-400 transition-colors"
        @click="restore(comment.id)"
        v-if="can.includes('restore')">
        <IconRestore />
      </button>
    </div>
    <CommentEdit
      :comment="selectedComment"
      :isOpen="isEditing"
      @close="isEditing = false"
      @save="updateComment" />

  </div>
</template>
<script setup>
import { ref } from 'vue';
import IconTrash from '@/components/icons/Trash.vue';
import IconEdit from '@/components/icons/Edit.vue';
import IconRestore from '@/components/icons/Restore.vue';
import IconLightbulb from '@/components/icons/Lightbulb.vue';
import CommentEdit from './Edit.vue';
import { deleteComment, toggleComment, restoreComment } from '@/services/api';
import { useToastStore } from '@/components/toast/stores/toast';
const toast = useToastStore();

defineProps({
  comment: Object,
  can: Array
});

const emit = defineEmits(['deleted', 'toggled', 'restored', 'edited']);

const isEditing = ref(false);
const selectedComment = ref(null);

const editComment = (comment) => {
  selectedComment.value = comment;
  isEditing.value = true;
};

const updateComment = (updatedComment) => {
  emit('edited', updatedComment);
  isEditing.value = false;
  toast.show('Der Kommentar wurde erfolgreich gespeichert.', 'success');
};

const destroy = async (id) => {
  const updated = await deleteComment(id);
  emit('deleted', updated);
  toast.show('Der Kommentar wurde erfolgreich gelöscht.', 'success');
}

const toggle = async (id) => {
  const updated = await toggleComment(id);
  emit('toggled', updated);
  toast.show('Der Kommentar wurde erfolgreich verändert.', 'success');
}

const restore = async (id) => {
  const updated = await restoreComment(id);
  emit('restored', updated);
  toast.show('Der Kommentar wurde erfolgreich verändert.', 'success');
}

const formatDate = (isoString) => {
  const date = new Date(isoString);
  return date.toLocaleString('de-DE', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).replace(',', ' –'); // match your previous format: 07.05.2025 – 03:26
};
</script>
