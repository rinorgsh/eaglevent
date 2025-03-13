<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import TopNav from '@/Components/TopNav.vue';
import Dashboard from '@/Pages/Pretix/Dashboard.vue';
import Orders from '@/Pages/Pretix/Orders.vue';
import Tickets from '@/Pages/Pretix/Tickets.vue';
import CheckinLists from '@/Pages/Pretix/CheckinLists.vue';
import axios from 'axios';

// Définir les props que le composant recevra du contrôleur
const props = defineProps({
    orders: Object,       
    event: Object,
    eventSlug: String,
    tickets: Object,      
    quotas: Object,
    stats: Object,        // Nouvelle prop pour les statistiques du Dashboard       
    apiResponse: Object,
    checkinLists: Array,
    tab: {
        type: String,
        default: 'dashboard'
    }
});

// Récupérer le paramètre tab de l'URL si présent
const page = usePage();
const urlParams = new URLSearchParams(window.location.search);
const tabParam = urlParams.get('tab');

// Définir l'onglet actif en fonction du paramètre d'URL ou de la prop
const activeTab = ref(tabParam || props.tab || 'dashboard');

// État du toggle pour le shop 
const isShopActive = ref(false);
const isUpdating = ref(false);
const updateMessage = ref('');
const updateError = ref('');

// Calculer l'URL de partage
const shareUrl = computed(() => {
    return props.event?.public_url || `${props.event?.slug}.eventsquare.store`;
});

// État pour contrôler l'affichage du menu dropdown mobile pour les actions
const showActionsMenu = ref(false);

// Initialisation
onMounted(() => {
    console.log('Show Component Props:', props);
    
    // Initialiser l'état du toggle en fonction de l'état actuel du shop
    isShopActive.value = props.event?.live || false;
    
    // Fermer le menu dropdown quand on clique ailleurs sur la page
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.actions-dropdown')) {
            showActionsMenu.value = false;
        }
    });
});

// Fonction pour basculer l'état de la billetterie
function toggleShopStatus() {
    isUpdating.value = true;
    updateMessage.value = '';
    updateError.value = '';
    
    axios.post(`/pretix/events/${props.eventSlug}/toggle-status`, {
        live: !isShopActive.value
    })
    .then(response => {
        isShopActive.value = response.data.live;
        updateMessage.value = response.data.message || 'Statut de la billetterie mis à jour avec succès';
        isUpdating.value = false;
    })
    .catch(error => {
        updateError.value = error.response?.data?.message || 'Erreur lors de la mise à jour du statut';
        isUpdating.value = false;
    });
}

// Fonction pour ouvrir la billetterie dans un nouvel onglet
function openTicketShop() {
    if (props.event?.public_url) {
        window.open(props.event.public_url, '_blank');
    }
}

// Fonction pour basculer le menu d'actions mobile
function toggleActionsMenu() {
    showActionsMenu.value = !showActionsMenu.value;
}

// Fonction pour copier l'URL dans le presse-papiers
function copyShareUrl() {
    navigator.clipboard.writeText(shareUrl.value)
        .then(() => {
            updateMessage.value = 'URL copiée dans le presse-papiers';
            setTimeout(() => {
                updateMessage.value = '';
            }, 2000);
        })
        .catch(err => {
            updateError.value = 'Erreur lors de la copie de l\'URL';
            setTimeout(() => {
                updateError.value = '';
            }, 2000);
        });
}
</script>

<template>
    <!-- Titre de la page -->
    <Head :title="event?.name && (event?.name.fr || event?.name.en) ? (event?.name.fr || event?.name.en) : 'Détail de l\'événement'" />

    <div class="min-h-screen bg-white text-gray-800">
        <!-- Header principal -->
        <TopNav/>
        
        <!-- Section titre de l'événement -->
        <div class="bg-white border-b border-gray-200 py-4 sm:py-6">
            <div class="container mx-auto px-4">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center">
                    <div class="w-full lg:w-auto">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 pr-10 sm:pr-0 truncate">
                            {{ event?.name?.fr || event?.name?.en || 'Événement' }}
                        </h1>
                        <div class="flex items-center mt-1">
                            <span class="text-xs sm:text-sm text-gray-500 truncate max-w-[200px] sm:max-w-xs">
                                {{ shareUrl }}
                            </span>
                            <button 
                                class="ml-2 text-gray-400 hover:text-gray-600" 
                                @click="copyShareUrl"
                                title="Copier l'URL"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
                                    <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Actions sur desktop -->
                    <div class="hidden md:flex md:space-x-3 mt-4 lg:mt-0">
                        <button 
                            @click="openTicketShop" 
                            class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded transition"
                            :disabled="!isShopActive"
                            :class="{ 'opacity-50 cursor-not-allowed': !isShopActive }"
                            title="Vous devez activer la billetterie en ligne pour y accéder"
                        >
                            Tester la billetterie
                        </button>
                        <Link 
                            :href="event?.public_url" 
                            class="border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white font-medium py-2 px-4 rounded flex items-center transition"
                        >
                            Partager
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                                <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                            </svg>
                        </Link>
                        
                        <!-- Toggle avec état de chargement et libellé -->
                        <div class="flex items-center">
                            <span class="mr-2 text-sm" :class="isShopActive ? 'text-green-600' : 'text-gray-500'">
                                {{ isShopActive ? 'Billetterie active' : 'Billetterie inactive' }}
                            </span>
                            <button 
                                @click="toggleShopStatus" 
                                class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none" 
                                :class="{ 'bg-gray-300': !isShopActive, 'bg-green-500': isShopActive, 'opacity-50': isUpdating }"
                                :disabled="isUpdating"
                            >
                                <span class="sr-only">Activer/désactiver la billetterie</span>
                                <span 
                                    class="inline-block w-4 h-4 transform transition-transform bg-white rounded-full" 
                                    :class="{ 'translate-x-6': isShopActive, 'translate-x-1': !isShopActive }"
                                >
                                    <!-- Indicateur de chargement -->
                                    <svg v-if="isUpdating" class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Actions sur mobile - Menu dropdown -->
                    <div class="md:hidden flex items-center mt-4 w-full justify-between">
                        <!-- Toggle de billetterie compact -->
                        <div class="flex items-center">
                            <span class="text-xs" :class="isShopActive ? 'text-green-600' : 'text-gray-500'">
                                {{ isShopActive ? 'Actif' : 'Inactif' }}
                            </span>
                            <button 
                                @click="toggleShopStatus" 
                                class="relative inline-flex items-center h-5 rounded-full w-10 ml-2 transition-colors focus:outline-none" 
                                :class="{ 'bg-gray-300': !isShopActive, 'bg-green-500': isShopActive, 'opacity-50': isUpdating }"
                                :disabled="isUpdating"
                            >
                                <span 
                                    class="inline-block w-3 h-3 transform transition-transform bg-white rounded-full" 
                                    :class="{ 'translate-x-6': isShopActive, 'translate-x-1': !isShopActive }"
                                >
                                    <svg v-if="isUpdating" class="animate-spin h-3 w-3 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                        
                        <!-- Bouton d'action mobile avec menu déroulant -->
                        <div class="relative actions-dropdown">
                            <button 
                                @click.stop="toggleActionsMenu"
                                class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded flex items-center transition"
                            >
                                Actions
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 011.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            
                            <!-- Menu déroulant d'actions -->
                            <div 
                                v-if="showActionsMenu"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 border border-gray-200"
                            >
                                <button 
                                    @click="openTicketShop" 
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    :disabled="!isShopActive"
                                    :class="{ 'opacity-50': !isShopActive }"
                                >
                                    Tester la billetterie
                                </button>
                                <Link 
                                    :href="event?.public_url" 
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                >
                                    Partager
                                </Link>
                                <button 
                                    @click="copyShareUrl"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                >
                                    Copier l'URL
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Messages de statut -->
                <div v-if="updateMessage" class="mt-2 text-xs sm:text-sm font-medium text-green-600">
                    {{ updateMessage }}
                </div>
                <div v-if="updateError" class="mt-2 text-xs sm:text-sm font-medium text-red-600">
                    {{ updateError }}
                </div>
            </div>
        </div>
        
        <!-- Navigation secondaire (tabs) -->
        <div class="bg-white border-b border-gray-200 shadow-sm">
            <div class="container mx-auto px-4">
                <nav class="flex overflow-x-auto hide-scrollbar">
                    <button 
                        @click="activeTab = 'dashboard'" 
                        class="px-3 sm:px-4 py-3 sm:py-4 font-medium text-xs sm:text-sm whitespace-nowrap border-b-2 focus:outline-none"
                        :class="activeTab === 'dashboard' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    >
                        Dashboard
                    </button>
                    <button 
                        @click="activeTab = 'commandes'" 
                        class="px-3 sm:px-4 py-3 sm:py-4 font-medium text-xs sm:text-sm whitespace-nowrap border-b-2 focus:outline-none"
                        :class="activeTab === 'commandes' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    >
                        Commandes
                    </button>
                    <button 
                        @click="activeTab = 'billets'" 
                        class="px-3 sm:px-4 py-3 sm:py-4 font-medium text-xs sm:text-sm whitespace-nowrap border-b-2 focus:outline-none"
                        :class="activeTab === 'billets' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    >
                        Billets
                    </button>
                    <button 
                        @click="activeTab = 'checkin'" 
                        class="px-3 sm:px-4 py-3 sm:py-4 font-medium text-xs sm:text-sm whitespace-nowrap border-b-2 focus:outline-none"
                        :class="activeTab === 'checkin' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    >
                        Check-in
                    </button>
                </nav>
            </div>
        </div>
        
        <!-- Contenu principal - Composants par onglet -->
        <main class="container mx-auto px-4 py-4 sm:py-6">
            <!-- Dashboard composant -->
            <div v-if="activeTab === 'dashboard'">
                <Dashboard
                    :event-slug="eventSlug"
                    :event="event"
                    :orders="orders"
                    :tickets="tickets"
                    :quotas="quotas"
                    :stats="stats"
                    :api-response="apiResponse"
                />
            </div>
            
            <!-- Commandes composant -->
            <div v-if="activeTab === 'commandes'">
                <Orders 
                    :event-slug="eventSlug" 
                    :event="event" 
                    :orders="orders" 
                    :api-response="apiResponse" 
                />
            </div>
            
            <!-- Billets composant -->
            <div v-if="activeTab === 'billets'">
                <Tickets 
                    :event-slug="eventSlug" 
                    :event="event" 
                    :tickets="tickets"
                    :quotas="quotas"
                    :api-response="apiResponse" 
                />
            </div>
            
            <!-- Check-in composant -->
            <div v-if="activeTab === 'checkin'">
                <CheckinLists
                    :event-slug="eventSlug"
                    :event="event"
                    :checkin-lists="checkinLists"
                    :tickets="tickets?.results"
                    :api-response="apiResponse"
                />
            </div>
        </main>
    </div>
</template>

<style scoped>
/* Styles pour la navigation à défilement horizontal */
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Ajustement des ombres et bordures */
.shadow-md {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Styles responsives pour différentes tailles d'écran */
@media (max-width: 640px) {
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}

@media (min-width: 641px) and (max-width: 1024px) {
    .container {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
}

/* Styles pour les transitions */
.transition {
    transition: all 0.2s ease-in-out;
}
</style>