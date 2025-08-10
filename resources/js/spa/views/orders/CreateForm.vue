<template>
  <div>
    <h1 class="text-lg leading-[1.25]">
      Neue Bestellung erstellen
    </h1>
    
    <div v-if="error" class="mt-48 text-red-600">
      {{ error }}
    </div>
    
    <form @submit.prevent="submitForm" class="flex flex-col gap-y-48 mt-48 max-w-2xl">

      <!-- Product -->
      <section>
        <h2 class="text-sm font-medium mb-20">
          Produkt
        </h2>
        <div class="flex flex-col gap-y-20">
          <div>
            <Label for="product_id" label="Produkt" :required="true" />
            <Select
              id="product_id"
              v-model="form.product_id"
              :options="productOptions"
              placeholder="Produkt wählen..."
            />
          </div>
          <div class="grid grid-cols-2 gap-x-16">
            <div>
              <Label for="quantity" label="Anzahl" :required="true" />
              <Input
                id="quantity"
                v-model="form.quantity"
                type="number"
                min="1"
                :required="true"
              />
            </div>
            <div>
              <Label for="size" label="Grösse" />
              <Input
                id="size"
                v-model="form.size"
                placeholder="z.B. 36-40, L, XL"
              />
            </div>
          </div>
        </div>
      </section>

      <!-- Contact Information -->
      <section>
        <h2 class="text-sm font-medium mb-20">
          Kontaktinformationen
        </h2>
        <div class="flex flex-col gap-y-20">
          <div>
            <Label for="email" label="E-Mail" :required="true" />
            <Input
              id="email"
              v-model="form.email"
              type="email"
              :required="true"
            />
          </div>
          <div>
            <Label for="phone" label="Telefon" />
            <Input
              id="phone"
              v-model="form.phone"
              type="tel"
            />
          </div>
        </div>
      </section>

      <!-- Payment Information -->
      <section>
        <h2 class="text-sm font-medium mb-20">
          Zahlungsinformationen
        </h2>
        <div class="flex flex-col gap-y-20">
          <div>
            <Label for="payment_method" label="Zahlungsmethode" :required="true" />
            <Select
              id="payment_method"
              v-model="form.payment_method"
              :options="paymentMethodOptions"
              placeholder="Zahlungsmethode wählen..." />
          </div>
          <div>
            <Label for="merchant" label="Anbieter" :required="true" />
            <Select
              id="merchant"
              v-model="form.merchant"
              :options="merchantOptions"
              placeholder="Anbieter wählen..." />
          </div>
          <div>
            <Label for="total" label="Gesamtbetrag" :required="true" />
            <Input
              id="total"
              v-model="form.total"
              type="number"
              step="0.01"
              :required="true"
            />
          </div>
          <div>
            <Label for="paid_at" label="Bezahlt am" />
            <Input
              id="paid_at"
              v-model="form.paid_at"
              type="datetime-local"
            />
          </div>
        </div>
      </section>

      <!-- Billing Information -->
      <section>
        <h2 class="text-sm font-medium mb-20">
          Rechnungsadresse
        </h2>
        <div class="flex flex-col gap-y-20">
          <div>
            <Label for="billing_name" label="Name" :required="true" />
            <Input
              id="billing_name"
              v-model="form.billing_name"
              :required="true"
            />
          </div>
          <div>
            <Label for="billing_address_1" label="Adresse 1" :required="true" />
            <Input
              id="billing_address_1"
              v-model="form.billing_address_1"
              :required="true"
            />
          </div>
          <div>
            <Label for="billing_address_2" label="Adresse 2" />
            <Input
              id="billing_address_2"
              v-model="form.billing_address_2"
            />
          </div>
          <div class="grid grid-cols-2 gap-x-16">
            <div>
              <Label for="billing_zip" label="PLZ" :required="true" />
              <Input
                id="billing_zip"
                v-model="form.billing_zip"
                :required="true"
              />
            </div>
            <div>
              <Label for="billing_city" label="Stadt" :required="true" />
              <Input
                id="billing_city"
                v-model="form.billing_city"
                :required="true"
              />
            </div>
          </div>
          <div>
            <Label for="billing_country" label="Land" />
            <Input
              id="billing_country"
              v-model="form.billing_country"
            />
          </div>
        </div>
      </section>

      <!-- Shipping Information -->
      <section>
        <h2 class="text-sm font-medium mb-20">
          Lieferadresse
        </h2>
        <div class="flex flex-col gap-y-20">
          <div>
            <Label for="shipping_name" label="Name" />
            <Input
              id="shipping_name"
              v-model="form.shipping_name"
            />
          </div>
          <div>
            <Label for="shipping_address_1" label="Adresse 1" />
            <Input
              id="shipping_address_1"
              v-model="form.shipping_address_1"
            />
          </div>
          <div>
            <Label for="shipping_address_2" label="Adresse 2" />
            <Input
              id="shipping_address_2"
              v-model="form.shipping_address_2"
            />
          </div>
          <div class="grid grid-cols-2 gap-x-16">
            <div>
              <Label for="shipping_zip" label="PLZ" />
              <Input
                id="shipping_zip"
                v-model="form.shipping_zip"
              />
            </div>
            <div>
              <Label for="shipping_city" label="Stadt" />
              <Input
                id="shipping_city"
                v-model="form.shipping_city"
              />
            </div>
          </div>
          <div>
            <Label for="shipping_province" label="Kanton/Provinz" />
            <Input
              id="shipping_province"
              v-model="form.shipping_province"
            />
          </div>
          <div>
            <Label for="shipping_country" label="Land" />
            <Input
              id="shipping_country"
              v-model="form.shipping_country"
            />
          </div>
        </div>
      </section>

      <!-- Notes -->
      <section>
        <div>
          <Label for="notes" label="Notizen" />
          <Textarea
            id="notes"
            v-model="form.notes"
            :rows="4"
            placeholder="Zusätzliche Notizen zur Bestellung..."
          />
        </div>
      </section>

      <div class="flex gap-x-16">
        <ButtonPrimary
          type="submit"
          :label="submitting ? 'Gespeichert...' : 'Speichern'"
          :disabled="submitting"
        />
        <router-link
          to="/dashboard/bestellungen"
          class="inline-flex items-center px-16 py-8 border border-gray-200 text-xs bg-white hover:bg-gray-50 focus:outline-none focus:!ring-0"
        >
          Abbrechen
        </router-link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { usePageTitle } from '@/composables/usePageTitle';

// Composables
import { useOrderForm } from '@/composables/useOrderForm';
import { useOrderData } from '@/composables/useOrderData';

// Constants
import { paymentMethodOptions, merchantOptions } from '@/constants/formConstants';

// Components
import ButtonPrimary from '@/components/buttons/Primary.vue';
import Label from '@/components/input/Label.vue';
import Input from '@/components/input/Input.vue';
import Select from '@/components/input/Select.vue';
import Textarea from '@/components/input/Textarea.vue';

// Page setup
const { setTitle } = usePageTitle();
setTitle('Neue Bestellung erstellen');

// Initialize composables
const { 
  form, 
  submitting, 
  error, 
  setError, 
  handleSubmit 
} = useOrderForm('create');

const { 
  productOptions, 
  loadProducts, 
  createNewOrder 
} = useOrderData();

// Load initial data
onMounted(async () => {
  try {
    await loadProducts();
  } catch (err) {
    setError(err.message);
  }
});

// Form submission
const submitForm = async () => {
  await handleSubmit(createNewOrder);
};
</script>