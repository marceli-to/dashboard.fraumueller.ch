<template>
  <div>
    <h1 class="text-lg leading-[1.25]">
      Produkt bearbeiten
    </h1>
    
    <div v-if="loading" class="mt-48">
      Lädt...
    </div>
    
    <form v-else @submit.prevent="submitForm" class="flex flex-col gap-y-48 mt-48 max-w-2xl">

      <div v-if="error" class="mt-48 text-red-600">
        {{ error }}
      </div>

      <!-- Product Information -->
      <section>
        <div class="flex flex-col gap-y-20">
          <div>
            <Label for="name" label="Produktname" :required="true" />
            <Input
              id="name"
              v-model="form.name"
              type="text"
              :required="true"
              placeholder="z.B. Erste Ausgabe, Jahresabo 2026"
            />
          </div>
          <div>
            <Label for="confirmation_text" label="Bestätigungstext" />
            <Textarea
              id="confirmation_text"
              v-model="form.confirmation_text"
              placeholder="Text für die Bestellbestätigung..."
              class="min-h-[240px]"
            />
          </div>
        </div>
      </section>

      <!-- Actions -->
      <div class="flex gap-x-16">
        <ButtonPrimary
          type="submit"
          label="Speichern"
          :disabled="isSubmitting"
          class="w-auto px-32"
        />
        <ButtonSecondary
          type="button"
          label="Abbrechen"
          @click="$router.push({ name: 'products' })"
          class="w-auto px-32"
        />
      </div>

    </form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { getProduct, updateProduct } from '@/services/api';
import { usePageTitle } from '@/composables/usePageTitle';

// Components
import Input from '@/components/input/Input.vue';
import Textarea from '@/components/input/Textarea.vue';
import Label from '@/components/input/Label.vue';
import ButtonPrimary from '@/components/buttons/Primary.vue';
import ButtonSecondary from '@/components/buttons/Secondary.vue';

// Page setup
const { setTitle } = usePageTitle();
setTitle('Produkt bearbeiten');

// Router
const router = useRouter();
const route = useRoute();

// Form state
const form = reactive({
  name: '',
  confirmation_text: ''
});

const error = ref('');
const loading = ref(true);
const isSubmitting = ref(false);

// Load product data
const loadProduct = async () => {
  try {
    loading.value = true;
    const product = await getProduct(route.params.id);
    
    // Populate form with product data
    form.name = product.name || '';
    form.confirmation_text = product.confirmation_text || '';
    
  } catch (err) {
    console.error('Error loading product:', err);
    error.value = 'Fehler beim Laden des Produkts';
  } finally {
    loading.value = false;
  }
};

// Form submission
const submitForm = async () => {
  if (isSubmitting.value) return;
  
  error.value = '';
  isSubmitting.value = true;

  try {
    await updateProduct(route.params.id, form);
    router.push({ name: 'products' });
  } catch (err) {
    console.error('Error updating product:', err);
    error.value = err.response?.data?.message || 'Fehler beim Speichern des Produkts';
  } finally {
    isSubmitting.value = false;
  }
};

// Load product on mount
onMounted(() => {
  loadProduct();
});
</script>