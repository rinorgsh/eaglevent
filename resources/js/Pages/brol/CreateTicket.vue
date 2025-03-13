<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

// Définir les props que le composant recevra du contrôleur
const props = defineProps({
    event: Object,
    eventSlug: String,
    itemTypes: Array,
    quotas: Array,
    existingItems: Array
});

// État du formulaire avec Inertia
const form = useForm({
    name: {
        fr: '',
        en: ''
    },
    description: {
        fr: '',
        en: ''
    },
    price: '',
    tax_rate: '20.00',
    admission: true,
    position: 0,
    quota: null,
    itemType: 'default'
});

// États pour la gestion de l'interface utilisateur
const isCreating = ref(false);
const showSuccessMessage = ref(false);
const showErrorMessage = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const isLoading = ref(false);

// Formatages et utilitaires
function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', { 
        style: 'currency', 
        currency: 'EUR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(price);
}

// Prix avec formatage de la valeur numérique
const formattedPrice = computed(() => {
    if (!form.price) return '0,00 €';
    return formatPrice(parseFloat(form.price));
});

// Soumission du formulaire
function submitForm() {
    isLoading.value = true;
    showSuccessMessage.value = false;
    showErrorMessage.value = false;
    
    // Validation avant envoi
    if (!form.name.fr && !form.name.en) {
        errorMessage.value = "Le nom du ticket est requis dans au moins une langue";
        showErrorMessage.value = true;
        isLoading.value = false;
        return;
    }
    
    if (!form.price || isNaN(parseFloat(form.price))) {
        errorMessage.value = "Veuillez entrer un prix valide";
        showErrorMessage.value = true;
        isLoading.value = false;
        return;
    }
    
    if (!form.quota) {
        errorMessage.value = "Veuillez sélectionner un quota";
        showErrorMessage.value = true;
        isLoading.value = false;
        return;
    }
    
    // Préparation des données pour l'API Pretix
    form.post(route('pretix.tickets.store', { eventSlug: props.eventSlug }), {
        onSuccess: () => {
            // Réinitialiser le formulaire
            form.reset();
            isLoading.value = false;
            successMessage.value = "Le ticket a été créé avec succès";
            showSuccessMessage.value = true;
            
            // Masquer le message après 5 secondes
            setTimeout(() => {
                showSuccessMessage.value = false;
            }, 5000);
        },
        onError: (errors) => {
            isLoading.value = false;
            errorMessage.value = Object.values(errors).flat().join("\n");
            showErrorMessage.value = true;
        }
    });
}

// Annuler la création
function cancelCreation() {
    isCreating.value = false;
    form.reset();
    showErrorMessage.value = false;
    showSuccessMessage.value = false;
}
</script>

<template>
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold">
                Tickets
                <span v-if="existingItems && existingItems.length > 0" class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-blue-500 rounded ml-2">
                    {{ existingItems.length }}
                </span>
            </h2>
            <button 
                v-if="!isCreating" 
                @click="isCreating = true" 
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Créer un ticket
            </button>
        </div>
        
        <!-- Liste des tickets existants -->
        <div v-if="!isCreating && existingItems && existingItems.length > 0" class="mb-8">
            <div class="grid grid-cols-5 gap-4 py-2 text-xs font-medium text-gray-500 uppercase border-b border-gray-200 mb-2">
                <div>Nom</div>
                <div>Type</div>
                <div>Prix</div>
                <div>Quota</div>
                <div>Actions</div>
            </div>
            
            <div v-for="item in existingItems" :key="item.id" class="grid grid-cols-5 gap-4 py-3 border-b border-gray-100">
                <div class="font-medium">{{ item.name.fr || item.name.en }}</div>
                <div>{{ item.admission ? 'Entrée' : 'Produit' }}</div>
                <div>{{ formatPrice(item.default_price) }}</div>
                <div>{{ item.quota_name || 'Illimité' }}</div>
                <div class="flex space-x-2">
                    <button class="text-blue-600 hover:text-blue-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button class="text-red-600 hover:text-red-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Message si aucun ticket -->
        <div v-if="!isCreating && (!existingItems || existingItems.length === 0)" class="bg-blue-50 p-4 rounded-lg text-blue-800 mb-8">
            <p>Aucun ticket n'a été créé pour cet événement.</p>
            <p class="mt-2">Créez votre premier ticket en cliquant sur le bouton ci-dessus.</p>
        </div>
        
        <!-- Formulaire de création de ticket -->
        <div v-if="isCreating" class="bg-gray-50 p-6 rounded-lg border border-gray-200">
            <h3 class="text-lg font-medium mb-4">Création d'un nouveau ticket</h3>
            
            <!-- Messages de succès/erreur -->
            <div v-if="showSuccessMessage" class="bg-green-50 p-4 rounded-lg text-green-800 mb-4">
                {{ successMessage }}
            </div>
            
            <div v-if="showErrorMessage" class="bg-red-50 p-4 rounded-lg text-red-800 mb-4">
                {{ errorMessage }}
            </div>
            
            <form @submit.prevent="submitForm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <!-- Nom du ticket -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom du ticket (FR)</label>
                        <input 
                            type="text" 
                            v-model="form.name.fr" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ex: Entrée générale"
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom du ticket (EN)</label>
                        <input 
                            type="text" 
                            v-model="form.name.en" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ex: General admission"
                        >
                    </div>
                    
                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (FR)</label>
                        <textarea 
                            v-model="form.description.fr" 
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Description optionnelle"
                        ></textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (EN)</label>
                        <textarea 
                            v-model="form.description.en" 
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Optional description"
                        ></textarea>
                    </div>
                    
                    <!-- Prix et TVA -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prix (€)</label>
                        <div class="relative">
                            <input 
                                type="number" 
                                v-model="form.price" 
                                step="0.01"
                                min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="0.00"
                            >
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500">€</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Prix affiché: {{ formattedPrice }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Taux de TVA (%)</label>
                        <input 
                            type="number" 
                            v-model="form.tax_rate" 
                            step="0.01"
                            min="0"
                            max="100"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    
                    <!-- Type de ticket et Quota -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select 
                            v-model="form.itemType"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option v-for="type in itemTypes" :key="type.value" :value="type.value">
                                {{ type.label }}
                            </option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quota</label>
                        <select 
                            v-model="form.quota"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Sélectionnez un quota</option>
                            <option v-for="quota in quotas" :key="quota.id" :value="quota.id">
                                {{ quota.name }} ({{ quota.size ? quota.size : 'Illimité' }})
                            </option>
                        </select>
                    </div>
                    
                    <!-- Options supplémentaires -->
                    <div class="md:col-span-2">
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                id="admission" 
                                v-model="form.admission"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                            <label for="admission" class="ml-2 block text-sm text-gray-700">
                                Ce ticket donne droit à l'entrée à l'événement
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Boutons d'action -->
                <div class="flex justify-end space-x-3 mt-6">
                    <button 
                        type="button" 
                        @click="cancelCreation" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Annuler
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 flex items-center"
                        :disabled="isLoading"
                    >
                        <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Créer le ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>