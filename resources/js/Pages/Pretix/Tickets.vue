<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AddTicketModal from '@/Pages/Pretix/AddTicketModal.vue';

// Définir les props que le composant recevra
const props = defineProps({
    event: Object,
    eventSlug: String,
    tickets: {
        type: [Array, Object],
        default: () => []
    },
    quotas: {
        type: [Array, Object],
        default: () => []
    },
    apiResponse: Object
});

// État pour la recherche
const searchQuery = ref('');
// État pour le modal
const isModalOpen = ref(false);
// État pour les détails du billet en vue mobile
const expandedTicketId = ref(null);

// Fonction pour ouvrir/fermer les détails d'un billet en vue mobile
function toggleTicketDetails(id) {
    expandedTicketId.value = expandedTicketId.value === id ? null : id;
}

// Fonction pour ouvrir le modal
function openModal() {
    isModalOpen.value = true;
}

// Fonction pour fermer le modal
function closeModal() {
    isModalOpen.value = false;
}

// Fonction appelée après l'ajout d'un billet
function handleTicketAdded() {
    // Recharger la page pour voir le nouveau billet
    window.location.reload();
}

// Fonction pour formater le prix
function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', { 
        style: 'currency', 
        currency: 'EUR' 
    }).format(price);
}

// Convertir les tickets en tableau si c'est un objet
const ticketsArray = computed(() => {
    if (!props.tickets) return [];
    if (Array.isArray(props.tickets)) return props.tickets;
    if (props.tickets.results) return props.tickets.results; // API Pretix renvoie souvent { results: [...] }
    return Object.values(props.tickets);
});

// Tickets filtrés en fonction de la recherche
const filteredTickets = computed(() => {
    if (!searchQuery.value) return ticketsArray.value;
    
    return ticketsArray.value.filter(ticket => {
        const name = ticket.name ? (ticket.name.fr || ticket.name.en || '') : '';
        return name.toLowerCase().includes(searchQuery.value.toLowerCase());
    });
});

// Déterminer le nombre de tickets
const ticketCount = computed(() => {
    return ticketsArray.value.length;
});
</script>

<template>
    <!-- Titre de la section -->
    <div class="mb-4 sm:mb-6">
        <h2 class="text-xl sm:text-2xl font-semibold flex items-center">
            Billets 
            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-blue-500 rounded ml-2">
                {{ ticketCount }}
            </span>
        </h2>
    </div>
    
    <!-- Barre de recherche et bouton d'ajout -->
    <div class="flex flex-col sm:flex-row sm:justify-between gap-3 sm:gap-0 mb-4 sm:mb-6">
        <div class="relative w-full sm:w-64">
            <input 
                type="text" 
                v-model="searchQuery"
                placeholder="Rechercher un billet" 
                class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
        
        <button 
            @click="openModal"
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Ajouter un billet
        </button>
    </div>
    
    <!-- Message d'erreur API -->
    <div v-if="apiResponse && apiResponse.error" class="p-3 sm:p-4 mb-4 text-red-700 bg-red-100 border-l-4 border-red-500 rounded text-sm sm:text-base">
        <p><strong>Erreur API:</strong> {{ apiResponse.error }}</p>
    </div>
    
    <!-- Vue Desktop - Table traditionnelle -->
    <div class="hidden sm:block">
        <!-- En-têtes -->
        <div v-if="ticketsArray.length > 0" class="grid grid-cols-4 gap-4 mb-2 font-medium text-sm text-gray-500 uppercase">
            <div>Nom</div>
            <div>Prix</div>
            <div>Quota</div>
            <div>Actions</div>
        </div>
        
        <!-- Liste des billets -->
        <div v-if="ticketsArray.length > 0" class="space-y-2">
            <div 
                v-for="ticket in filteredTickets" 
                :key="ticket.id" 
                class="grid grid-cols-4 gap-4 p-4 border border-gray-200 rounded-lg bg-white hover:bg-gray-50"
            >
                <div>
                    <div class="font-medium">
                        {{ ticket.name && (ticket.name.fr || ticket.name.en) ? 
                           (ticket.name.fr || ticket.name.en) : 
                           'Billet sans nom' }}
                    </div>
                    <div class="text-sm text-gray-500" 
                         v-if="ticket.description && (ticket.description.fr || ticket.description.en)">
                        {{ ticket.description.fr || ticket.description.en }}
                    </div>
                </div>
                <div class="flex items-center">{{ formatPrice(ticket.default_price || 0) }}</div>
                <div class="flex items-center">
                    <span class="px-2 py-1 text-xs font-medium rounded-full" 
                          :class="!ticket.quota_size || ticket.quota_size === 'unlimited' ? 
                               'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'">
                        {{ !ticket.quota_size || ticket.quota_size === 'unlimited' ? 
                            'Illimité' : ticket.quota_size }}
                    </span>
                </div>
                <div class="flex items-center space-x-3">
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
    </div>
    
    <!-- Vue Mobile - Cards -->
    <div class="sm:hidden">
        <div v-if="ticketsArray.length > 0" class="space-y-3">
            <div 
                v-for="ticket in filteredTickets" 
                :key="ticket.id" 
                class="border border-gray-200 rounded-lg bg-white overflow-hidden"
            >
                <!-- En-tête de carte - toujours visible -->
                <div 
                    class="flex justify-between items-center p-3 bg-gray-50 cursor-pointer"
                    @click="toggleTicketDetails(ticket.id)"
                >
                    <div class="flex-1">
                        <div class="font-medium truncate pr-2">
                            {{ ticket.name && (ticket.name.fr || ticket.name.en) ? 
                               (ticket.name.fr || ticket.name.en) : 
                               'Billet sans nom' }}
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="font-bold mr-3">{{ formatPrice(ticket.default_price || 0) }}</div>
                        <svg 
                            xmlns="http://www.w3.org/2000/svg" 
                            class="h-5 w-5 text-gray-400 transition-transform" 
                            :class="{ 'rotate-180': expandedTicketId === ticket.id }"
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
                
                <!-- Détails et actions - s'affichent quand on clique -->
                <div v-if="expandedTicketId === ticket.id" class="p-3 border-t border-gray-200">
                    <!-- Description si disponible -->
                    <div 
                        v-if="ticket.description && (ticket.description.fr || ticket.description.en)"
                        class="mb-3 text-sm text-gray-600"
                    >
                        {{ ticket.description.fr || ticket.description.en }}
                    </div>
                    
                    <!-- Informations additionnelles -->
                    <div class="flex justify-between items-center mb-3">
                        <div class="text-sm text-gray-500">Quota :</div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full" 
                              :class="!ticket.quota_size || ticket.quota_size === 'unlimited' ? 
                                   'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'">
                            {{ !ticket.quota_size || ticket.quota_size === 'unlimited' ? 
                                'Illimité' : ticket.quota_size }}
                        </span>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="flex justify-end space-x-2">
                        <button class="flex items-center justify-center p-2 rounded bg-blue-100 text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button class="flex items-center justify-center p-2 rounded bg-red-100 text-red-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Message si aucun billet (commun aux deux vues) -->
    <div v-if="filteredTickets.length === 0" class="p-4 sm:p-6 text-center bg-blue-50 rounded-lg">
        <svg class="mx-auto h-12 w-12 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-lg font-medium text-gray-900">
            {{ searchQuery ? 'Aucun billet trouvé' : 'Aucun billet disponible' }}
        </h3>
        <p class="mt-1 text-sm text-gray-500">
            {{ searchQuery ? 'Essayez d\'autres termes de recherche.' : 'Créez votre premier billet pour cet événement.' }}
        </p>
        <div class="mt-4 sm:mt-6" v-if="!searchQuery">
            <button 
                @click="openModal"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
            >
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Créer un billet
            </button>
        </div>
    </div>
    
    <!-- Modal pour ajouter un billet -->
    <AddTicketModal 
        :is-open="isModalOpen"
        :event-slug="eventSlug"
        :quotas="quotas"
        @close="closeModal"
        @ticket-added="handleTicketAdded"
    />
</template>

<style scoped>
.rounded-lg {
    border-radius: 0.5rem;
}

/* Transition pour la rotation de l'icône */
.transition-transform {
    transition: transform 0.2s ease-in-out;
}

.rotate-180 {
    transform: rotate(180deg);
}

/* Media queries pour ajustements spécifiques */
@media (max-width: 640px) {
    .truncate {
        max-width: 200px;
    }
}

@media (min-width: 640px) and (max-width: 768px) {
    /* Ajustements pour petites tablettes */
}

@media (min-width: 768px) and (max-width: 1024px) {
    /* Ajustements pour iPad */
}
</style>