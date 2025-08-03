<template>
  <div>
    <h1 class="text-lg leading-[1.25]">
      Neues Produkt erstellen
    </h1>
    
    <div v-if="error" class="mt-48 text-red-600">
      {{ error }}
    </div>
    
    <form @submit.prevent="submitForm" class="flex flex-col gap-y-48 mt-48 max-w-2xl">

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
              rows="4"
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
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { createProduct } from '@/services/api';
import { usePageTitle } from '@/composables/usePageTitle';

// Components
import Input from '@/components/input/Input.vue';
import Textarea from '@/components/input/Textarea.vue';
import Label from '@/components/input/Label.vue';
import ButtonPrimary from '@/components/buttons/Primary.vue';
import ButtonSecondary from '@/components/buttons/Secondary.vue';

// Page setup
const { setTitle } = usePageTitle();
setTitle('Neues Produkt erstellen');

// Router
const router = useRouter();

// Form state
const form = reactive({
  name: '',
  confirmation_text: ''
});

const error = ref('');
const isSubmitting = ref(false);

// Form submission
const submitForm = async () => {
  if (isSubmitting.value) return;
  
  error.value = '';
  isSubmitting.value = true;

  try {
    await createProduct(form);
    router.push({ name: 'products' });
  } catch (err) {
    console.error('Error creating product:', err);
    error.value = err.response?.data?.message || 'Fehler beim Erstellen des Produkts';
  } finally {
    isSubmitting.value = false;
  }
};
</script>