import { defineStore } from 'pinia';
import { ref, markRaw } from 'vue';

export const useDialogStore = defineStore('dialog', () => {
  const isVisible = ref(false);
  const content = ref({
    title: '',
    confirmLabel: 'Confirm',
    cancelLabel: 'Cancel',
    onConfirm: null,
    onCancel: null,
    component: null,
    props: null,
    size: 'medium' // Default size
  });

  const show = (dialogContent) => {
    content.value = {
      ...content.value,
      ...dialogContent,
      component: dialogContent.component ? markRaw(dialogContent.component) : null
    };
    isVisible.value = true;
  };

  const hide = () => {
    isVisible.value = false;
    setTimeout(() => {
      content.value = {
        title: '',
        confirmLabel: 'Confirm',
        cancelLabel: 'Cancel',
        onConfirm: null,
        onCancel: null,
        component: null,
        props: null,
        size: 'medium' // Reset to default size
      };
    }, 300);
  };

  return {
    isVisible,
    content,
    show,
    hide
  };
});