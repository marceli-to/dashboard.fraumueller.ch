import { ref, computed } from 'vue'
import axios from 'axios'

export function useFileUpload(options = {}) {
  const {
    maxSize = 50 * 1024 * 1024, // 50MB
    allowedTypes = ['text/csv', 'text/plain', 'application/vnd.ms-excel'],
    uploadUrl = '/api/upload',
    multiple = true
  } = options;

  const fileInput = ref(null)
  const isDragging = ref(false)
  const isUploading = ref(false)
  const uploadProgress = ref(0)
  const hasError = ref(false)
  const retryQueue = ref([])
  const uploadedFiles = ref([])

  const isUploaded = computed(() => uploadedFiles.value.length > 0)

  const toggleDrag = (value) => {
    isDragging.value = value
  }

  const validateFile = (file) => {
    const errors = []

    if (file.size > maxSize) {
      errors.push(`File "${file.name}" exceeds maximum size of ${maxSize / (1024 * 1024)}MB`)
    }

    const isTypeAllowed = allowedTypes.some(type => {
      if (type.includes('*')) {
        const baseType = type.split('/')[0]
        return file.type.startsWith(baseType)
      }
      return file.type === type
    })

    if (!isTypeAllowed) {
      errors.push(`File type "${file.type}" is not allowed`)
    }

    return {
      isValid: errors.length === 0,
      errors
    }
  }

  const processFiles = (fileList) => {
    let files = Array.from(fileList)
    if (!multiple) {
      files = files.slice(0, 1)
    }
    const validFiles = []
    const errors = []

    files.forEach(file => {
      const { isValid, errors: fileErrors } = validateFile(file)
      if (isValid) {
        validFiles.push(file)
      } else {
        errors.push(...fileErrors)
      }
    })

    return { validFiles, errors }
  }

  const uploadFiles = async (files) => {
    if (!files.length) return

    isUploading.value = true
    uploadProgress.value = 0
    hasError.value = false

    const formData = new FormData()
    files.forEach(file => formData.append('files[]', file))

    try {
      const response = await axios.post(uploadUrl, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        },
        onUploadProgress: (event) => {
          uploadProgress.value = Math.round((event.loaded * 100) / event.total)
        }
      });

      const normalized = response.data.files.map(normalizeCsvEntry)
      uploadedFiles.value = multiple ? [...normalized, ...uploadedFiles.value] : [...normalized]

      return response.data;
    } 
    catch (error) {
      hasError.value = true
      retryQueue.value = [...retryQueue.value, ...files]
      console.error(error);
    } 
    finally {
      isUploading.value = false
    }
  }

  const handleDrop = async (event) => {
    toggleDrag(false)
    const { validFiles, errors } = processFiles(event.dataTransfer.files)
    
    if (errors.length) {
      // Handle validation errors
      console.error('Validation errors:', errors)
    }

    if (validFiles.length) {
      await uploadFiles(validFiles)
    }
  }

  const handleFileSelect = async (event) => {
    const { validFiles, errors } = processFiles(event.target.files)
    
    if (errors.length) {
      console.error('Validation errors:', errors)
    }

    if (validFiles.length) {
      await uploadFiles(validFiles)
    }

    // Reset input value to allow uploading the same file again
    if (fileInput.value) {
      fileInput.value.value = ''
    }
  }

  const triggerFileInput = () => {
    fileInput.value?.click()
  }

  const retryFailed = async () => {
    const files = [...retryQueue.value]
    retryQueue.value = []
    hasError.value = false
    await uploadFiles(files)
  }

  const reset = () => {
    uploadedFiles.value = []
    retryQueue.value = []
    hasError.value = false
    isUploading.value = false
    uploadProgress.value = 0
    if (fileInput.value) {
      fileInput.value.value = ''
    }
  }
  
  return {
    fileInput,
    isDragging,
    isUploading,
    uploadProgress,
    hasError,
    isUploaded,
    uploadedFiles,
    handleDrop,
    handleFileSelect,
    triggerFileInput,
    retryFailed,
    reset,
    normalizeCsvEntry
  }
}

export function normalizeCsvEntry(entry) {
  return {
    name: entry.name,
    original_name: entry.original_name,
    url: entry.url,
    path: entry.path,
    mime_type: entry.mime_type,
    size: entry.size,
    status: entry.status || 'pending'
  }
}