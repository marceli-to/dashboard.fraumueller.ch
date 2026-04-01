<template>
  <div>

    <h1 class="text-lg leading-[1.25]">
      Import Erneuerungen
    </h1>

    <div class="mt-48 max-w-2xl">

      <div class="mb-32">

        <p>Laden Sie eine CSV-Datei hoch, um Abo-Erneuerungen zu importieren:</p>

        <!-- Upload Area -->
        <div
          class="border border-dashed border-gray-400 p-48 text-center transition-colors"
          :class="{ 'border-blue-500 bg-blue-50': isDragging }"
          @dragover.prevent="toggleDrag(true)"
          @dragleave.prevent="toggleDrag(false)"
          @drop.prevent="handleDrop">

          <div v-if="!isUploading">
            <div class="text-xs">
              <span class="font-medium cursor-pointer" @click="triggerFileInput">
                Datei auswählen
              </span>
              oder per Drag & Drop hierher ziehen
            </div>
          </div>

          <div v-else class="flex items-center justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="ml-8 text-xs text-gray-600">Uploading... {{ uploadProgress }}%</span>
          </div>

          <input
            ref="fileInput"
            type="file"
            accept=".csv,.txt"
            @change="handleFileSelect"
            class="hidden"
          />
        </div>

        <!-- Error Display -->
        <div v-if="hasError" class="mt-16 p-12 bg-red-50 border border-red-200 rounded-lg">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="ml-12">
              <h3 class="text-xs font-medium text-red-800">Upload fehlgeschlagen</h3>
              <p class="text-xs text-red-700 mt-4">
                Bitte versuchen Sie es erneut oder wenden Sie sich an den Support.
              </p>
              <button @click="retryFailed" class="mt-8 text-xs text-red-600 hover:text-red-500 font-medium">
                Erneut versuchen
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Uploaded Files -->
      <div v-if="uploadedFiles.length > 0" class="mb-32">
        <div class="flex flex-col gap-y-12 border-t border-gray-200 pt-12">
          <div
            v-for="file in uploadedFiles"
            :key="file.name"
            class="flex items-center justify-between border-b border-gray-200 pb-12">
            <div class="inline-flex items-center w-auto">
              <div>
                <div class="text-xs font-medium">{{ file.original_name }}</div>
                <div class="text-xs">{{ formatFileSize(file.size) }}</div>
              </div>
            </div>
            <div class="inline-flex items-center justify-end space-x-8">
              <template v-if="file.status === 'pending'">
                <ButtonPrimary
                  type="button"
                  :label="isProcessing ? 'Verarbeitung...' : 'Verarbeiten'"
                  :disabled="isProcessing"
                  @click="processFile(file)"
                />
              </template>
            </div>
          </div>
        </div>
      </div>

      <!-- Processing Results -->
      <div v-if="processingResults" class="mb-32 mt-64">
        <h2 class="text-md mb-32">Verarbeitungsergebnisse</h2>
        <div class="bg-white">
          <div class="grid grid-cols-3 gap-16 mb-16">
            <div class="text-center">
              <div class="text-2xl font-bold text-green-600">{{ processingResults.imported }}</div>
              <div class="text-xs">Importiert</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-yellow-600">{{ processingResults.skipped }}</div>
              <div class="text-xs">Übersprungen</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-red-600">{{ processingResults.errors.length }}</div>
              <div class="text-xs">Fehler</div>
            </div>
          </div>

          <!-- Reset Button -->
          <div v-if="uploadedFiles.length > 0 || processingResults" class="my-48">
            <ButtonSecondary
              type="button"
              label="Zurücksetzen"
              @click="resetUpload"
            />
          </div>

          <!-- Skipped Rows Details -->
          <div v-if="processingResults.skipped_rows.length > 0">
            <h3 class="text-xs mb-16">Übersprungene Zeilen</h3>
            <div class="max-h-[360px] overflow-y-auto border-y border-gray-100">
              <div
                v-for="(row, index) in processingResults.skipped_rows"
                :key="index"
                class="text-xxs py-12 border-b border-gray-100 last:border-b-0">
                <span class="font-medium">{{ row.order_id }}</span> - {{ row.reason }}:
                <span v-if="row.email !== 'N/A'">{{ row.email }}</span>
              </div>
            </div>
            <button
              v-if="notFoundRows.length > 0"
              @click="downloadNotFound"
              class="mt-16 text-xs underline underline-offset-2 hover:no-underline">
              Nicht gefundene E-Mails herunterladen ({{ notFoundRows.length }})
            </button>
          </div>

          <!-- Errors Details -->
          <div v-if="processingResults.errors.length > 0" class="mt-16">
            <h3 class="text-xs mb-8">Fehlerdetails:</h3>
            <div class="max-h-48 overflow-y-auto">
              <div
                v-for="(error, index) in processingResults.errors"
                :key="index"
                class="text-xs text-red-600 py-2 border-b border-gray-100 last:border-b-0">
                {{ error }}
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useFileUpload } from '@/components/upload/composable/useFileUpload';
import { processRenewals } from '@/services/api';
import { usePageTitle } from '@/composables/usePageTitle';
import ButtonPrimary from '@/components/buttons/Primary.vue';
import ButtonSecondary from '@/components/buttons/Secondary.vue';

const { setTitle } = usePageTitle();
setTitle('Import Erneuerungen');

const isProcessing = ref(false);
const processingResults = ref(null);

const {
  fileInput,
  isDragging,
  isUploading,
  uploadProgress,
  hasError,
  uploadedFiles,
  handleDrop,
  handleFileSelect,
  triggerFileInput,
  retryFailed,
  reset
} = useFileUpload({
  maxSize: 50 * 1024 * 1024,
  allowedTypes: ['text/csv', 'text/plain', 'application/vnd.ms-excel'],
  uploadUrl: '/api/upload',
  multiple: false
});

const toggleDrag = (value) => {
  isDragging.value = value;
};

const processFile = async (file) => {
  isProcessing.value = true;
  processingResults.value = null;

  try {
    const response = await processRenewals(file.path);

    if (response.success) {
      processingResults.value = response.data;
      file.status = 'processed';
    } else {
      throw new Error(response.message);
    }
  } catch (error) {
    console.error('Processing error:', error);
    file.status = 'error';
    alert('Fehler beim Verarbeiten der Datei: ' + error.message);
  } finally {
    isProcessing.value = false;
  }
};

const notFoundRows = computed(() => {
  if (!processingResults.value) return [];
  return processingResults.value.skipped_rows.filter(row => row.reason === 'Keine Bestellung gefunden');
});

const downloadNotFound = () => {
  const lines = ['E-Mail'];
  notFoundRows.value.forEach(row => lines.push(row.email));
  const blob = new Blob([lines.join('\n')], { type: 'text/csv' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'nicht-gefundene-erneuerungen.csv';
  a.click();
  URL.revokeObjectURL(url);
};

const resetUpload = () => {
  reset();
  processingResults.value = null;
  isProcessing.value = false;
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>
