<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

// Props du composant
const props = defineProps({
  isOpen: Boolean,
  eventSlug: String
});

// Émission d'événements
const emit = defineEmits(['close', 'ticketAdded']);

// Formulaire Inertia
const form = useForm({
  name: {
    fr: '',
    en: ''
  },
  description: {
    fr: '',
    en: ''
  },
  default_price: '',
  tax_rate: '20.00',
  admission: true,
  active: true,
  quota_size: '', // Nombre de billets disponibles (vide = illimité)
});

// Formatage du prix
const formattedPrice = computed(() => {
  if (!form.default_price) return '0,00 €';
  return new Intl.NumberFormat('fr-FR', { 
    style: 'currency', 
    currency: 'EUR' 
  }).format(parseFloat(form.default_price));
});

// Texte pour le quota
const quotaText = computed(() => {
  if (!form.quota_size) return "Illimité";
  return form.quota_size + " billets";
});

// Soumission du formulaire
function submitForm() {
  form.post(route('pretix.tickets.store', { eventSlug: props.eventSlug }), {
    onSuccess: () => {
      emit('ticketAdded');
      emit('close');
      form.reset();
    }
  });
}

// Fermeture du modal
function closeModal() {
  emit('close');
  form.reset();
}
</script>

<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-900">Ajouter un billet</h3>
        <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      
      <!-- Body -->
      <div class="p-6">
        <form @submit.prevent="submitForm">
          <!-- Erreurs de validation -->
          <div v-if="Object.keys(form.errors).length > 0" class="mb-4 p-4 bg-red-50 rounded-md border-l-4 border-red-500">
            <div v-for="(error, key) in form.errors" :key="key" class="text-sm text-red-700">
              {{ error }}
            </div>
          </div>
          
          <!-- Nom du billet -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom du billet (FR) <span class="text-red-500">*</span></label>
            <input 
              type="text" 
              v-model="form.name.fr" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Ex: Entrée générale"
              required
            />
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom du billet (EN)</label>
            <input 
              type="text" 
              v-model="form.name.en" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Ex: General admission"
            />
          </div>
          
          <!-- Prix et nombre de billets -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Prix (€) <span class="text-red-500">*</span></label>
              <div class="relative">
                <input 
                  type="number" 
                  v-model="form.default_price" 
                  step="0.01"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="0.00"
                  required
                />
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <span class="text-gray-500">€</span>
                </div>
              </div>
              <p class="text-xs text-gray-500 mt-1">Prix affiché: {{ formattedPrice }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de billets disponibles</label>
              <input 
                type="number" 
                v-model="form.quota_size"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Laissez vide pour illimité"
              />
              <p class="text-xs text-gray-500 mt-1">{{ quotaText }}</p>
            </div>
          </div>
          
          <!-- TVA -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Taux de TVA (%) <span class="text-red-500">*</span></label>
            <input 
              type="number" 
              v-model="form.tax_rate" 
              step="0.01"
              min="0"
              max="100"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
          </div>
          
          <!-- Description -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description (optionnelle)</label>
            <textarea 
              v-model="form.description.fr" 
              rows="2"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Description du billet"
            ></textarea>
          </div>
          
          <!-- Options supplémentaires -->
          <div class="mb-4">
            <div class="flex items-center">
              <input 
                type="checkbox" 
                id="admission" 
                v-model="form.admission"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="admission" class="ml-2 block text-sm text-gray-700">
                Ce billet donne droit à l'entrée à l'événement
              </label>
            </div>
          </div>
          
          <!-- Boutons d'action -->
          <div class="flex justify-end space-x-3 mt-6">
            <button 
              type="button" 
              @click="closeModal" 
              class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Annuler
            </button>
            <button 
              type="submit" 
              class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 flex items-center"
              :disabled="form.processing"
            >
              <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Créer le billet
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>