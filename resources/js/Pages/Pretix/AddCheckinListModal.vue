<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    isOpen: Boolean,
    eventSlug: String,
    tickets: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close', 'list-added']);

// Formulaire pour créer une liste de check-in
const form = useForm({
    name: '',
    all_products: true,
    limit_products: [],
    include_pending: false
});

// Référence pour la zone du modal
const modalRef = ref(null);

// Tickets disponibles pour la sélection
const availableTickets = computed(() => {
    return props.tickets.map(ticket => {
        const name = typeof ticket.name === 'object' 
            ? (ticket.name.fr || ticket.name.en || 'Billet sans nom') 
            : (ticket.name || 'Billet sans nom');
        
        return {
            id: ticket.id,
            name: name
        };
    });
});

// Gérer la sélection/désélection de tous les produits
watch(() => form.all_products, (newValue) => {
    if (newValue) {
        form.limit_products = [];
    }
});

// Fonction pour soumettre le formulaire
function submitForm() {
    form.post(route('pretix.checkin-list.store', props.eventSlug), {
        onSuccess: () => {
            emit('list-added');
            emit('close');
        }
    });
}

// Fonction pour fermer le modal
function closeModal() {
    emit('close');
}

// Gérer les clics en dehors du modal pour le fermer
function handleOutsideClick(event) {
    if (modalRef.value && !modalRef.value.contains(event.target)) {
        closeModal();
    }
}

// Ajouter/retirer les écouteurs d'événements quand le modal est ouvert/fermé
onMounted(() => {
    document.addEventListener('mousedown', handleOutsideClick);
});

watch(() => props.isOpen, (newValue) => {
    if (newValue) {
        document.addEventListener('mousedown', handleOutsideClick);
    } else {
        document.removeEventListener('mousedown', handleOutsideClick);
    }
});
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 overflow-y-auto z-50 flex items-center justify-center">
        <!-- Fond semi-transparent -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <!-- Contenu du modal -->
        <div 
            ref="modalRef"
            class="bg-white rounded-lg max-w-md w-full mx-4 p-6 relative z-10 shadow-xl transform transition-all"
        >
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Nouvelle liste de check-in</h3>
                <button 
                    @click="closeModal" 
                    class="text-gray-400 hover:text-gray-500 focus:outline-none"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <form @submit.prevent="submitForm">
                <!-- Nom de la liste -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nom de la liste
                    </label>
                    <input 
                        type="text" 
                        id="name"
                        v-model="form.name"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    />
                    <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">
                        {{ form.errors.name }}
                    </div>
                </div>
                
                <!-- Sélection des billets -->
                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <input 
                            type="checkbox" 
                            id="all_products"
                            v-model="form.all_products"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <label for="all_products" class="ml-2 block text-sm font-medium text-gray-700">
                            Tous les billets
                        </label>
                    </div>
                    
                    <div v-if="!form.all_products">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Sélectionnez les billets à inclure
                        </label>
                        <div class="max-h-48 overflow-y-auto border border-gray-300 rounded-md p-2">
                            <div 
                                v-for="ticket in availableTickets" 
                                :key="ticket.id" 
                                class="flex items-center py-1"
                            >
                                <input 
                                    type="checkbox" 
                                    :id="`ticket-${ticket.id}`"
                                    v-model="form.limit_products"
                                    :value="ticket.id"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <label :for="`ticket-${ticket.id}`" class="ml-2 block text-sm text-gray-700">
                                    {{ ticket.name }}
                                </label>
                            </div>
                        </div>
                        <div v-if="form.errors.limit_products" class="text-sm text-red-600 mt-1">
                            {{ form.errors.limit_products }}
                        </div>
                    </div>
                </div>
                
                <!-- Option pour les paiements en attente -->
                <div class="mb-6">
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="include_pending"
                            v-model="form.include_pending"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <label for="include_pending" class="ml-2 block text-sm font-medium text-gray-700">
                            Inclure les commandes avec paiement en attente
                        </label>
                    </div>
                </div>
                
                <!-- Boutons d'action -->
                <div class="flex justify-end space-x-3">
                    <button 
                        type="button" 
                        @click="closeModal"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Annuler
                    </button>
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Création en cours...</span>
                        <span v-else>Créer la liste</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>