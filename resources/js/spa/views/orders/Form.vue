<template>
  <div>
    <h1 class="text-lg leading-[1.25]">
      Bestellung bearbeiten
    </h1>
    
    <div v-if="loading" class="mt-48">
      Lädt...
    </div>
    
    <div v-else-if="error" class="mt-48 text-red-600">
      {{ error }}
    </div>
    
    <form v-else @submit.prevent="submitForm" class="flex flex-col gap-y-32 mt-48 max-w-2xl">
      <!-- Contact Information -->
      <section>
        <h2 class="text-sm font-medium mb-12">
          Kontaktinformationen
        </h2>
        <div class="flex flex-col gap-y-16">
          <div>
            <label for="email" class="block text-xs mb-4">E-Mail *</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="appearance-none w-full px-12 py-8 border border-black focus:border-black !ring-0 focus:!ring-0 !outline-none "
            />
          </div>
          <div>
            <label for="phone" class="block text-xs mb-4">Telefon</label>
            <input
              id="phone"
              v-model="form.phone"
              type="text"
              class="appearance-none w-full px-12 py-8 border border-black focus:border-black !ring-0 focus:!ring-0 !outline-none "
            />
          </div>
        </div>
      </section>

      <!-- Billing Information -->
      <section>
        <h2 class="text-sm font-medium mb-12">
          Rechnungsadresse
        </h2>
        <div class="flex flex-col gap-y-16">
          <div>
            <label for="billing_name" class="block text-xs mb-4">Name *</label>
            <input
              id="billing_name"
              v-model="form.billing_name"
              type="text"
              required
              class="appearance-none w-full px-12 py-8 border border-black focus:border-black !ring-0 focus:!ring-0 !outline-none "
            />
          </div>
          <div>
            <label for="billing_address_1" class="block text-xs mb-4">Adresse 1 *</label>
            <input
              id="billing_address_1"
              v-model="form.billing_address_1"
              type="text"
              required
              class="appearance-none w-full px-12 py-8 border border-black focus:border-black !ring-0 focus:!ring-0 !outline-none "
            />
          </div>
          <div>
            <label for="billing_address_2" class="block text-xs mb-4">Adresse 2</label>
            <input
              id="billing_address_2"
              v-model="form.billing_address_2"
              type="text"
              class="appearance-none w-full px-12 py-8 border border-black focus:border-black !ring-0 focus:!ring-0 !outline-none "
            />
          </div>
          <div class="grid grid-cols-2 gap-x-16">
            <div>
              <label for="billing_zip" class="block text-xs mb-4">PLZ *</label>
              <input
                id="billing_zip"
                v-model="form.billing_zip"
                type="text"
                required
                class="appearance-none w-full px-12 py-8 border border-black focus:border-black !ring-0 focus:!ring-0 !outline-none "
              />
            </div>
            <div>
              <label for="billing_city" class="block text-xs mb-4">Stadt *</label>
              <input
                id="billing_city"
                v-model="form.billing_city"
                type="text"
                required
                class="appearance-none w-full px-12 py-8 border border-black focus:border-black !ring-0 focus:!ring-0 !outline-none "
              />
            </div>
          </div>
          <div>
            <label for="billing_country" class="block text-xs mb-4">Land</label>
            <input
              id="billing_country"
              v-model="form.billing_country"
              type="text"
              class="appearance-none w-full px-12 py-8 border border-black focus:border-black !ring-0 focus:!ring-0 !outline-none "
            />
          </div>
        </div>
      </section>

      <!-- Shipping Information -->
      <section>
        <h2 class="text-sm font-medium mb-12">
          Lieferadresse
        </h2>
        <div class="flex flex-col gap-y-16">
          <div>
            <label for="shipping_name" class="block text-xs mb-4">Name</label>
            <input
              id="shipping_name"
              v-model="form.shipping_name"
              type="text"
              class="appearance-none w-full px-12 py-8 border border-black focus:border-black !ring-0 focus:!ring-0 !outline-none "
            />
          </div>
          <div>
            <label for="shipping_address_1" class="block text-xs mb-4">Adresse 1</label>
            <input
              id="shipping_address_1"
              v-model="form.shipping_address_1"
              type="text"
              class="appearance-none w-full px-12 py-8 border border-black focus:border-black !ring-0 focus:!ring-0 !outline-none "
            />
          </div>
          <div>
            <label for="shipping_address_2" class="block text-xs mb-4">Adresse 2</label>
            <input
              id="shipping_address_2"
              v-model="form.shipping_address_2"
              type="text"
              class="appearance-none w-full px-12 py-8 border border-black focus:border-black !ring-0 focus:!ring-0 !outline-none "
            />
          </div>
          <div class="grid grid-cols-2 gap-x-16">
            <div>
              <label for="shipping_zip" class="block text-xs mb-4">PLZ</label>
              <input
                id="shipping_zip"
                v-model="form.shipping_zip"
                type="text"
                class="appearance-none w-full px-12 py-8 border border-black focus:border-black !ring-0 focus:!ring-0 !outline-none "
              />
            </div>
            <div>
              <label for="shipping_city" class="block text-xs mb-4">Stadt</label>
              <input
                id="shipping_city"
                v-model="form.shipping_city"
                type="text"
                class="appearance-none w-full px-12 py-8 border border-black focus:border-black !ring-0 focus:!ring-0 !outline-none "
              />
            </div>
          </div>
          <div>
            <label for="shipping_province" class="block text-xs mb-4">Kanton/Provinz</label>
            <input
              id="shipping_province"
              v-model="form.shipping_province"
              type="text"
              class="appearance-none w-full px-12 py-8 border border-black focus:border-black !ring-0 focus:!ring-0 !outline-none "
            />
          </div>
          <div>
            <label for="shipping_country" class="block text-xs mb-4">Land</label>
            <input
              id="shipping_country"
              v-model="form.shipping_country"
              type="text"
              class="appearance-none w-full px-12 py-8 border border-black focus:border-black !ring-0 focus:!ring-0 !outline-none "
            />
          </div>
        </div>
      </section>

      <!-- Notes -->
      <section>
        <div>
          <label for="notes" class="block text-xs mb-4">Notizen</label>
          <textarea
            id="notes"
            v-model="form.notes"
            rows="4"
            class="appearance-none w-full px-12 py-8 border border-black focus:border-black !ring-0 focus:!ring-0 !outline-none placeholder:text-gray-500"
            placeholder="Zusätzliche Notizen zur Bestellung..."
          />
        </div>
      </section>

      <div class="flex gap-x-16">
        <ButtonPrimary
          type="submit"
          :label="submitting ? 'Speichert...' : 'Speichern'"
          :disabled="submitting"
        />
        <router-link
          to="/dashboard/bestellungen"
          class="inline-flex items-center px-16 py-8 border border-black text-xs bg-white hover:bg-gray-50 focus:outline-none focus:!ring-0"
        >
          Abbrechen
        </router-link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getOrder, updateOrder } from '@/services/api'
import ButtonPrimary from '@/components/buttons/Primary.vue'

const route = useRoute()
const router = useRouter()
const loading = ref(true)
const submitting = ref(false)
const error = ref(null)

const form = ref({
  email: '',
  phone: '',
  billing_name: '',
  billing_address_1: '',
  billing_address_2: '',
  billing_city: '',
  billing_zip: '',
  billing_country: '',
  shipping_name: '',
  shipping_address_1: '',
  shipping_address_2: '',
  shipping_city: '',
  shipping_zip: '',
  shipping_province: '',
  shipping_country: '',
  notes: ''
})

const loadOrder = async () => {
  try {
    loading.value = true
    error.value = null
    
    const orderId = route.params.id
    const order = await getOrder(orderId)
    
    // Populate form with order data
    Object.keys(form.value).forEach(key => {
      if (order[key] !== undefined) {
        form.value[key] = order[key] || ''
      }
    })
    
  } catch (err) {
    error.value = 'Fehler beim Laden der Bestellung: ' + (err.response?.data?.message || err.message)
  } finally {
    loading.value = false
  }
}

const submitForm = async () => {
  try {
    submitting.value = true
    error.value = null
    
    const orderId = route.params.id
    await updateOrder(orderId, form.value)
    
    router.push('/dashboard/bestellungen')
  } catch (err) {
    error.value = 'Fehler beim Speichern: ' + (err.response?.data?.message || err.message)
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  loadOrder()
})
</script>