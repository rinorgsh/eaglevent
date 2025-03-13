<script setup>
import { ref, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AddCheckinListModal from '@/Pages/Pretix/AddCheckinListModal.vue';

// Définir les props que le composant recevra
const props = defineProps({
    checkinLists: {
        type: Array,
        default: () => []
    },
    event: Object,
    eventSlug: String,
    tickets: {
        type: Array,
        default: () => []
    },
    apiResponse: Object
});

// État pour la recherche
const searchQuery = ref('');
// État pour le modal
const isModalOpen = ref(false);
// État pour l'affichage des détails en version mobile
const expandedListId = ref(null);

// Fonction pour basculer l'affichage des détails d'une liste en mobile
function toggleListDetails(id) {
    expandedListId.value = expandedListId.value === id ? null : id;
}

// Fonction pour ouvrir le modal
function openModal() {
    isModalOpen.value = true;
}

// Fonction pour fermer le modal
function closeModal() {
    isModalOpen.value = false;
}

// Fonction appelée après l'ajout d'une liste de check-in
function handleListAdded() {
    // Recharger la page pour voir la nouvelle liste
    window.location.reload();
}

// Listes de check-in filtrées en fonction de la recherche
const filteredLists = computed(() => {
    if (!searchQuery.value) return props.checkinLists;
    
    const query = searchQuery.value.toLowerCase();
    return props.checkinLists.filter(list => {
        return list.name.toLowerCase().includes(query);
    });
});

// Déterminer le nombre de listes
const listCount = computed(() => {
    return props.checkinLists.length;
});

// Fonction pour obtenir les noms des produits
function getProductNames(list) {
    if (list.all_products) {
        return 'Tous les billets';
    } else if (list.product_names && list.product_names.length > 0) {
        return list.product_names.join(', ');
    } else if (list.limit_products && list.limit_products.length > 0) {
        return `${list.limit_products.length} billet(s) sélectionné(s)`;
    } else {
        return 'Aucun billet sélectionné';
    }
}

// Fonction pour tronquer le texte si trop long (pour mobile)
function truncateText(text, maxLength = 20) {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}
</script>

<template>
    <!-- Titre de la section -->
    <div class="mb-4 sm:mb-6">
        <h2 class="text-xl sm:text-2xl font-semibold flex items-center">
            Listes de check-in 
            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-blue-500 rounded ml-2">
                {{ listCount }}
            </span>
        </h2>
    </div>
    
    <!-- Barre de recherche et bouton d'ajout -->
    <div class="flex flex-col sm:flex-row sm:justify-between gap-3 sm:gap-0 mb-4 sm:mb-6">
        <div class="relative w-full sm:w-64">
            <input 
                type="text" 
                v-model="searchQuery"
                placeholder="Rechercher une liste" 
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
            <span class="whitespace-nowrap">Créer une liste</span>
        </button>
    </div>
    
    <!-- Message d'erreur API -->
    <div v-if="apiResponse && apiResponse.error" class="p-3 sm:p-4 mb-4 text-red-700 bg-red-100 border-l-4 border-red-500 rounded text-sm sm:text-base">
        <p><strong>Erreur API:</strong> {{ apiResponse.error }}</p>
    </div>
    
    <!-- Vue Desktop - Table traditionnelle -->
    <div class="hidden sm:block">
        <!-- En-têtes -->
        <div v-if="checkinLists.length > 0" class="grid grid-cols-4 gap-4 mb-2 font-medium text-sm text-gray-500 uppercase">
            <div>Nom</div>
            <div>Billets</div>
            <div>Statut des paiements</div>
            <div>Actions</div>
        </div>
        
        <!-- Liste des listes de check-in -->
        <div v-if="filteredLists.length > 0" class="space-y-2">
            <div 
                v-for="list in filteredLists" 
                :key="list.id" 
                class="grid grid-cols-4 gap-4 p-4 border border-gray-200 rounded-lg bg-white hover:bg-gray-50"
            >
                <div>
                    <div class="font-medium">{{ list.name }}</div>
                </div>
                <div class="text-sm text-gray-600">
                    {{ getProductNames(list) }}
                </div>
                <div class="flex items-center">
                    <span class="px-2 py-1 text-xs font-medium rounded-full" 
                          :class="list.include_pending ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'">
                        {{ list.include_pending ? 'Inclut les paiements en attente' : 'Paiements confirmés uniquement' }}
                    </span>
                </div>
                <div class="flex items-center space-x-3">
                    <Link 
                        :href="route('pretix.checkin-list.details', { eventSlug: eventSlug, listId: list.id })"
                        class="text-blue-600 hover:text-blue-800 flex items-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Détails
                    </Link>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Vue Mobile - Cards -->
    <div class="sm:hidden">
        <div v-if="filteredLists.length > 0" class="space-y-3">
            <div 
                v-for="list in filteredLists" 
                :key="list.id" 
                class="border border-gray-200 rounded-lg bg-white overflow-hidden"
            >
                <!-- En-tête de la carte - toujours visible -->
                <div 
                    class="flex justify-between items-center p-3 bg-gray-50 cursor-pointer"
                    @click="toggleListDetails(list.id)"
                >
                    <div class="font-medium truncate pr-2">{{ list.name }}</div>
                    <div class="flex items-center">
                        <span 
                            class="px-2 py-1 text-xs font-medium rounded-full mr-2" 
                            :class="list.include_pending ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'"
                        >
                            {{ list.include_pending ? 'En attente' : 'Confirmés' }}
                        </span>
                        <svg 
                            xmlns="http://www.w3.org/2000/svg" 
                            class="h-5 w-5 text-gray-400 transition-transform" 
                            :class="{ 'rotate-180': expandedListId === list.id }"
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
                
                <!-- Détails et actions - s'affichent quand on clique -->
                <div v-if="expandedListId === list.id" class="p-3 border-t border-gray-200">
                    <!-- Informations sur les billets -->
                    <div class="mb-3">
                        <div class="text-sm text-gray-500 mb-1">Billets inclus:</div>
                        <div class="text-sm">{{ getProductNames(list) }}</div>
                    </div>
                    
                    <!-- Statut des paiements détaillé -->
                    <div class="mb-3">
                        <div class="text-sm text-gray-500 mb-1">Statut des paiements:</div>
                        <div class="text-sm">
                            {{ list.include_pending ? 'Inclut les paiements en attente' : 'Paiements confirmés uniquement' }}
                        </div>
                    </div>
                    
                    <!-- Lien vers les détails -->
                    <Link 
                        :href="route('pretix.checkin-list.details', { eventSlug: eventSlug, listId: list.id })"
                        class="flex items-center justify-center w-full py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded hover:bg-blue-100 transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Voir les détails
                    </Link>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Message si aucune liste - commun pour desktop et mobile -->
    <div v-if="filteredLists.length === 0" class="p-4 sm:p-6 text-center bg-blue-50 rounded-lg">
        <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <h3 class="mt-2 text-base sm:text-lg font-medium text-gray-900">
            {{ searchQuery ? 'Aucune liste trouvée' : 'Aucune liste de check-in disponible' }}
        </h3>
        <p class="mt-1 text-sm text-gray-500">
            {{ searchQuery ? 'Essayez d\'autres termes de recherche.' : 'Créez votre première liste de check-in pour cet événement.' }}
        </p>
        <div class="mt-4 sm:mt-6" v-if="!searchQuery">
            <button 
                @click="openModal"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
            >
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Créer une liste de check-in
            </button>
        </div>
    </div>
    
    <!-- Modal pour ajouter une liste de check-in -->
    <AddCheckinListModal 
        v-if="isModalOpen"
        :is-open="isModalOpen"
        :event-slug="eventSlug"
        :tickets="tickets"
        @close="closeModal"
        @list-added="handleListAdded"
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

/* Transitions pour les boutons */
.transition-colors {
    transition: background-color 0.2s ease-in-out;
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
    /* Ajustements spécifiques pour iPad */
}
</style>