<template>
  <h1 class="text-lg leading-[1.25]">
    Kommentare
  </h1>

  <template v-if="!isLoading">
    <div class="max-w-3xl mt-38 text-xs">
      <!-- Tabs -->
      <div class="flex gap-x-32">
        <button
          v-for="(label, key) in tabs"
          :key="key"
          @click="activeTab = key"
          class="inline-flex items-center gap-x-8">
          <span 
            :class="[
              'gap-x-8 pt-4 pb-1 border-b text-left hover:border-black transition-colors',
              activeTab === key ? 'border-black' : 'border-transparent'
            ]">
          {{ label }}
          </span>
          <span class="rounded-full bg-black text-white w-auto min-w-20 h-16 px-4 py-2 text-tiny leading-none inline-flex items-center justify-center"
          v-if="key == 'drafts' && comments[key]?.length">
          {{ comments[key]?.length }}
          </span>
        </button>
      </div>

      <!-- Comments -->
      <div class="mt-32">
        <CommentCard
          v-for="comment in currentComments"
          :key="comment.id"
          :comment="comment"
          :can="getPermissions()"
          @edited="editComment"
          @deleted="deleteComment"
          @toggled="toggleComment"
          @restored="restoreComment"
          v-if="currentComments.length > 0" />

        <p class="pt-8" v-else>
          {{ emptyMessages[activeTab] }}
        </p>

        <!-- Pagination -->
        <div class="mt-24 flex justify-between items-center" v-if="totalPages > 1">
          <button
            :disabled="currentPages[activeTab] === 1"
            @click="currentPages[activeTab]--"
            class="disabled:text-gray-400 disabled:hover:!text-gray-400 hover:underline underline-offset-4 decoration-black disabled:no-underline">
            Vorherige Seite
          </button>
          <span>Seite {{ currentPages[activeTab] }} von {{ totalPages }}</span>
          <button
            :disabled="currentPages[activeTab] === totalPages"
            @click="currentPages[activeTab]++"
            class="disabled:text-gray-400 disabled:hover:!text-gray-400 hover:underline underline-offset-4 decoration-black disabled:no-underline">
            Nächste Seite
          </button>
        </div>
      </div>
    </div>
  </template>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { getComments } from '@/services/api';
import CommentCard from './components/Comment.vue';

import { usePageTitle } from '@/composables/usePageTitle';
const { setTitle } = usePageTitle();
setTitle('Kommentare');

const tabs = {
  drafts: 'Neue Kommentare',
  published: 'Veröffentlichte Kommentare',
  deleted: 'Gelöschte Kommentare'
};

const emptyMessages = {
  drafts: 'Es sind keine neuen Kommentare vorhanden',
  published: 'Es sind keine veröffentlichten Kommentare vorhanden',
  deleted: 'Es sind keine gelöschten Kommentare vorhanden'
};

const activeTab = ref('drafts');
const isLoading = ref(true);

const comments = ref({
  drafts: [],
  published: [],
  deleted: []
});

const perPage = 12;

const currentPages = ref({
  drafts: 1,
  published: 1,
  deleted: 1
});

const currentComments = computed(() => {
  const tab = activeTab.value;
  const start = (currentPages.value[tab] - 1) * perPage;
  return comments.value[tab].slice(start, start + perPage);
});

const totalPages = computed(() => {
  const total = comments.value[activeTab.value].length;
  return Math.max(1, Math.ceil(total / perPage));
});

watch(activeTab, (newTab) => {
  if (currentPages.value[newTab] !== 1) {
    currentPages.value[newTab] = 1;
  }
});

onMounted(async () => {
  try {
    comments.value = await getComments();
  } catch (error) {
    console.error(error);
  } finally {
    isLoading.value = false;
  }
});

const getPermissions = () => {
  return activeTab.value === 'deleted'
    ? ['restore']
    : ['toggle', 'edit', 'delete'];
};

function moveComment(updatedComment, targetTab) {
  for (const tab in comments.value) {
    comments.value[tab] = comments.value[tab].filter(c => c.id !== updatedComment.id);
  }
  comments.value[targetTab].push(updatedComment);
  comments.value[targetTab].sort((a, b) => new Date(b.date) - new Date(a.date));
}

const editComment = (comment) => {
  const targetTab = comment.published ? 'published' : 'drafts';
  moveComment(comment, targetTab);
};

const toggleComment = (comment) => {
  const targetTab = comment.published ? 'published' : 'drafts';
  moveComment(comment, targetTab);
};

const deleteComment = (comment) => {
  if (comment.deleted_at) {
    moveComment(comment, 'deleted');
  }
};

const restoreComment = (comment) => {
  const targetTab = comment.published ? 'published' : 'drafts';
  moveComment(comment, targetTab);
};
</script>
