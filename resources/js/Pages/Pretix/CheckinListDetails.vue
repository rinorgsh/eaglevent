<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import TopNav from '@/Components/TopNav.vue';
import axios from 'axios';

// Définir les props que le composant recevra
const props = defineProps({
    checkinList: Object,
    checkins: Array, // Ce sera la liste des positions (billets) en réalité
    event: Object,
    eventSlug: String,
    apiResponse: Object
});

// État du scanner
const scannerInput = ref('');
const scanning = ref(false);
const scanResult = ref(null);
const scanError = ref(null);

// État du filtre de recherche
const searchQuery = ref('');

// Filtre pour l'état des check-ins (tous, fait, pas fait)
const checkinStatusFilter = ref('all'); // 'all', 'checked', 'unchecked'

// Options pour ignorer les impayés et forcer le check-in
const ignoreUnpaid = ref(false);
const forceCheckin = ref(false);

// États pour la version mobile
const expandedPosition = ref(null);
const showStatistics = ref(false);
const showFilters = ref(false);

// Mapping des IDs de billets vers leurs noms (à améliorer avec des données réelles)
const ticketNames = {
    1: "Billet standard",
    2: "Billet VIP",
    3: "Billet Groupe",
    10: "Billet Enfant",
    16: "Billet Famille",
    24: "Billet Étudiant"
};

// Formater la date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString() + ' ' + 
           new Date(dateString).toLocaleTimeString();
}

// Format de date court pour mobile
function formatShortDate(dateString) {
    if (!dateString) return 'N/A';
    const options = { day: '2-digit', month: '2-digit', year: '2-digit', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleString(undefined, options);
}

// Fonction pour basculer l'affichage des détails d'une position
function togglePosition(id) {
    expandedPosition.value = expandedPosition.value === id ? null : id;
}

// Fonction pour copier un code dans l'entrée du scanner
function useCode(code) {
    scannerInput.value = code;
    
    // Faire défiler jusqu'au scanner sur mobile
    if (window.innerWidth < 1024) {
        const scannerSection = document.getElementById('scanner-section');
        if (scannerSection) {
            scannerSection.scrollIntoView({ behavior: 'smooth' });
        }
    }
}

// Convertir les données de positions en liste complète incluant les check-ins faits et non faits
const allPositions = computed(() => {
    if (!props.checkins || !Array.isArray(props.checkins)) return [];
    
    return props.checkins.map(position => {
        // Vérifier si cette position a des check-ins
        const hasCheckins = position.checkins && position.checkins.length > 0;
        
        // Obtenir le check-in le plus récent, s'il existe
        let latestCheckin = null;
        if (hasCheckins) {
            latestCheckin = position.checkins.sort((a, b) => 
                new Date(b.datetime) - new Date(a.datetime)
            )[0];
        }
        
        return {
            id: position.id,
            positionid: position.positionid,
            item: position.item,
            attendee_name: position.attendee_name,
            secret: position.secret,
            order: position.order,
            isCheckedIn: hasCheckins,
            checkin: latestCheckin,
            checkinDatetime: latestCheckin ? latestCheckin.datetime : null
        };
    });
});

// Filtrer les positions selon le statut sélectionné et la recherche
const filteredPositions = computed(() => {
    let result = [...allPositions.value];
    
    // Appliquer le filtre de statut de check-in
    if (checkinStatusFilter.value === 'checked') {
        result = result.filter(position => position.isCheckedIn);
    } else if (checkinStatusFilter.value === 'unchecked') {
        result = result.filter(position => !position.isCheckedIn);
    }
    
    // Appliquer la recherche textuelle
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(position => {
            return (
                (position.attendee_name && position.attendee_name.toLowerCase().includes(query)) ||
                (position.order && position.order.toLowerCase().includes(query)) ||
                (position.secret && position.secret.toLowerCase().includes(query))
            );
        });
    }
    
    // Trier: d'abord les check-ins récents, puis les billets non scannés
    return result.sort((a, b) => {
        // Si les deux ont été scannés, trier par date (plus récent en premier)
        if (a.checkinDatetime && b.checkinDatetime) {
            return new Date(b.checkinDatetime) - new Date(a.checkinDatetime);
        }
        
        // Si seulement a a été scanné, il vient en premier
        if (a.checkinDatetime) return -1;
        
        // Si seulement b a été scanné, il vient en premier
        if (b.checkinDatetime) return 1;
        
        // Si aucun n'a été scanné, trier par nom
        return (a.attendee_name || '').localeCompare(b.attendee_name || '');
    });
});

// Statistiques sur les check-ins
const stats = computed(() => {
    const total = allPositions.value.length;
    const checked = allPositions.value.filter(p => p.isCheckedIn).length;
    const unchecked = total - checked;
    const percentage = total > 0 ? Math.round((checked / total) * 100) : 0;
    
    return { total, checked, unchecked, percentage };
});

// Obtenir le nom du billet à partir de son ID
function getTicketName(itemId) {
    return ticketNames[itemId] || `Billet #${itemId}`;
}

// Effectuer un check-in
async function performCheckin() {
    if (!scannerInput.value) return;
    
    scanning.value = true;
    scanResult.value = null;
    scanError.value = null;
    
    try {
        const response = await axios.post(
            route('pretix.checkin.perform', { 
                eventSlug: props.eventSlug, 
                listId: props.checkinList.id 
            }),
            {
                secret: scannerInput.value,
                ignore_unpaid: ignoreUnpaid.value,
                force: forceCheckin.value
            }
        );
        
        scanResult.value = response.data;
        // Réinitialiser l'input après un scan réussi
        scannerInput.value = '';
        
        // Recharger la page après quelques secondes pour rafraîchir la liste
        setTimeout(() => {
            window.location.reload();
        }, 3000);
    } catch (error) {
        scanError.value = error.response?.data || { error: 'Erreur lors du check-in' };
    } finally {
        scanning.value = false;
    }
}

// Déterminer la classe CSS en fonction du statut
function getStatusClass(status) {
    switch (status) {
        case 'valid':
            return 'bg-green-100 text-green-800';
        case 'invalid':
            return 'bg-red-100 text-red-800';
        case 'already_redeemed':
            return 'bg-yellow-100 text-yellow-800';
        case 'error':
            return 'bg-red-100 text-red-800';
        case true:
            return 'bg-green-100 text-green-800';
        case false:
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

// Déterminer le libellé du statut
function getStatusLabel(status) {
    switch (status) {
        case 'valid':
            return 'Valide';
        case 'invalid':
            return 'Invalide';
        case 'already_redeemed':
            return 'Déjà utilisé';
        case 'error':
            return 'Erreur';
        case true:
            return 'Validé';
        case false:
            return 'Non validé';
        default:
            return status;
    }
}

// Focus sur l'input au chargement
onMounted(() => {
    console.log("Positions reçues:", props.checkins);
    console.log("Positions traitées:", allPositions.value);
    
    const inputElement = document.getElementById('scanner-input');
    if (inputElement) {
        inputElement.focus();
    }
});
</script>

<template>
    <Head :title="`Check-in: ${checkinList ? checkinList.name : 'Liste'} - ${event?.name?.fr || event?.name?.en || 'Événement'}`" />

    <div class="min-h-screen bg-gray-50 text-gray-800">
        <!-- Header principal -->
        <TopNav />
        
        <!-- Section titre -->
        <div class="bg-white border-b border-gray-200 py-4 sm:py-6">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 truncate">
                            Liste: {{ checkinList ? checkinList.name : 'Chargement...' }}
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1 truncate">
                            {{ event?.name?.fr || event?.name?.en || 'Événement' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-4 sm:py-6">
            <!-- Bouton retour -->
            <div class="mb-4 sm:mb-6">
                <Link 
                    :href="route('pretix.show', { eventSlug: eventSlug, tab: 'checkin' })" 
                    class="inline-flex items-center px-3 sm:px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    &larr; Retour aux listes
                </Link>
            </div>

            <!-- Message en cas d'erreur API -->
            <div v-if="apiResponse && apiResponse.error" class="p-3 sm:p-4 mb-4 sm:mb-6 text-red-700 bg-red-100 border-l-4 border-red-500 rounded-lg text-sm">
                <p><strong>Erreur API:</strong> {{ apiResponse.error }}</p>
            </div>

            <!-- Interface mobile seulement - Scanner en premier -->
            <div id="scanner-section" class="lg:hidden mb-4">
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <h2 class="text-lg font-semibold mb-4">Scanner un billet</h2>
                    
                    <div class="mb-4">
                        <label for="scanner-input-mobile" class="block text-sm font-medium text-gray-700 mb-1">
                            Code du billet
                        </label>
                        <input 
                            type="text" 
                            id="scanner-input-mobile"
                            v-model="scannerInput"
                            @keyup.enter="performCheckin"
                            placeholder="Scanner ou saisir le code"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            :disabled="scanning"
                            inputmode="text"
                        />
                    </div>
                    
                    <div class="mb-4 grid grid-cols-2 gap-2">
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                id="ignore-unpaid-mobile"
                                v-model="ignoreUnpaid"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <label for="ignore-unpaid-mobile" class="ml-2 block text-xs sm:text-sm font-medium text-gray-700">
                                Ignorer impayés
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                id="force-checkin-mobile"
                                v-model="forceCheckin"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <label for="force-checkin-mobile" class="ml-2 block text-xs sm:text-sm font-medium text-gray-700">
                                Forcer check-in
                            </label>
                        </div>
                    </div>
                    
                    <button 
                        @click="performCheckin"
                        class="w-full px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!scannerInput || scanning"
                    >
                        <span v-if="scanning">Vérification en cours...</span>
                        <span v-else>Valider le billet</span>
                    </button>
                    
                    <!-- Résultat du scan (mobile) -->
                    <div v-if="scanResult" class="mt-4 p-3 rounded-md" :class="getStatusClass(scanResult.status)">
                        <div class="font-semibold">{{ getStatusLabel(scanResult.status) }}</div>
                        <div v-if="scanResult.position">
                            <div class="text-sm mt-1">
                                <span class="font-medium">Billet:</span> 
                                {{ getTicketName(scanResult.position.item) }}
                            </div>
                            <div class="text-sm" v-if="scanResult.position.attendee_name">
                                <span class="font-medium">Nom:</span> {{ scanResult.position.attendee_name }}
                            </div>
                            <div class="text-sm">
                                <span class="font-medium">Code:</span> {{ scanResult.position.secret }}
                            </div>
                        </div>
                        <div v-if="scanResult.message" class="text-sm mt-1">
                            {{ scanResult.message }}
                        </div>
                    </div>
                    
                    <!-- Erreur de scan (mobile) -->
                    <div v-if="scanError" class="mt-4 p-3 bg-red-100 text-red-800 rounded-md">
                        <div class="font-semibold">Erreur</div>
                        <div v-if="scanError.message" class="text-sm mt-1">
                            {{ scanError.message }}
                        </div>
                        <div v-else-if="scanError.error" class="text-sm mt-1">
                            {{ scanError.error }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistiques en accordéon sur mobile -->
            <div class="lg:hidden mb-4">
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                    <div 
                        class="flex justify-between items-center p-4 cursor-pointer"
                        @click="showStatistics = !showStatistics"
                    >
                        <h2 class="text-lg font-semibold">Statistiques</h2>
                        <div class="flex items-center">
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ stats.percentage }}%
                            </span>
                            <svg 
                                xmlns="http://www.w3.org/2000/svg" 
                                class="h-5 w-5 ml-2 text-gray-400 transition-transform duration-200" 
                                :class="{ 'rotate-180': showStatistics }"
                                fill="none" 
                                viewBox="0 0 24 24" 
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                    
                    <div v-if="showStatistics" class="p-4 border-t border-gray-200">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600 text-sm">Total:</span>
                            <span class="font-semibold">{{ stats.total }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600 text-sm">Scannés:</span>
                            <span class="font-semibold text-green-600">{{ stats.checked }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-gray-600 text-sm">Non scannés:</span>
                            <span class="font-semibold text-red-600">{{ stats.unchecked }}</span>
                        </div>
                        
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" :style="`width: ${stats.percentage}%`"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Interface de Check-in principale -->
            <div class="grid grid-cols-1 gap-4 lg:gap-6 lg:grid-cols-3">
                <!-- Panneau de gauche (desktop uniquement): Scanner et stats -->
                <div class="lg:col-span-1 hidden lg:block">
                    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                        <h2 class="text-lg font-semibold mb-4">Scanner un billet</h2>
                        
                        <div class="mb-4">
                            <label for="scanner-input" class="block text-sm font-medium text-gray-700 mb-1">
                                Code du billet (secret)
                            </label>
                            <input 
                                type="text" 
                                id="scanner-input"
                                v-model="scannerInput"
                                @keyup.enter="performCheckin"
                                placeholder="Scanner ou saisir le code"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                :disabled="scanning"
                            />
                        </div>
                        
                        <div class="mb-4">
                            <div class="flex items-center mb-2">
                                <input 
                                    type="checkbox" 
                                    id="ignore-unpaid"
                                    v-model="ignoreUnpaid"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <label for="ignore-unpaid" class="ml-2 block text-sm font-medium text-gray-700">
                                    Ignorer les paiements en attente
                                </label>
                            </div>
                            
                            <div class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    id="force-checkin"
                                    v-model="forceCheckin"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <label for="force-checkin" class="ml-2 block text-sm font-medium text-gray-700">
                                    Forcer le check-in (même si déjà utilisé)
                                </label>
                            </div>
                        </div>
                        
                        <button 
                            @click="performCheckin"
                            class="w-full px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="!scannerInput || scanning"
                        >
                            <span v-if="scanning">Vérification en cours...</span>
                            <span v-else>Valider le billet</span>
                        </button>
                        
                        <!-- Résultat du scan (desktop) -->
                        <div v-if="scanResult" class="mt-4 p-3 rounded-md" :class="getStatusClass(scanResult.status)">
                            <div class="font-semibold">{{ getStatusLabel(scanResult.status) }}</div>
                            <div v-if="scanResult.position">
                                <div class="text-sm mt-1">
                                    <span class="font-medium">Billet:</span> 
                                    {{ getTicketName(scanResult.position.item) }}
                                </div>
                                <div class="text-sm" v-if="scanResult.position.attendee_name">
                                    <span class="font-medium">Nom:</span> {{ scanResult.position.attendee_name }}
                                </div>
                                <div class="text-sm">
                                    <span class="font-medium">Code:</span> {{ scanResult.position.secret }}
                                </div>
                            </div>
                            <div v-if="scanResult.message" class="text-sm mt-1">
                                {{ scanResult.message }}
                            </div>
                        </div>
                        
                        <!-- Erreur de scan (desktop) -->
                        <div v-if="scanError" class="mt-4 p-3 bg-red-100 text-red-800 rounded-md">
                            <div class="font-semibold">Erreur</div>
                            <div v-if="scanError.message" class="text-sm mt-1">
                                {{ scanError.message }}
                            </div>
                            <div v-else-if="scanError.error" class="text-sm mt-1">
                                {{ scanError.error }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Récapitulatif des statistiques (desktop) -->
                    <div class="mt-6 bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                        <h2 class="text-lg font-semibold mb-4">Statistiques</h2>
                        
                        <div class="flex flex-col space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total des billets:</span>
                                <span class="font-semibold">{{ stats.total }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Billets scannés:</span>
                                <span class="font-semibold text-green-600">{{ stats.checked }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Billets non scannés:</span>
                                <span class="font-semibold text-red-600">{{ stats.unchecked }}</span>
                            </div>
                            <div class="mt-2 pt-2 border-t border-gray-200">
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-blue-600 h-2.5 rounded-full" :style="`width: ${stats.percentage}%`"></div>
                                </div>
                                <div class="text-center mt-1 text-sm font-medium text-gray-500">
                                    {{ stats.percentage }}% scannés
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Liste des billets - Panneau de droite -->
                <div class="lg:col-span-2">
                    <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 shadow-sm">
                        <!-- En-tête avec filtres -->
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4">
                            <div class="flex justify-between items-center w-full sm:w-auto">
                                <h2 class="text-lg font-semibold">Liste des billets</h2>
                                
                                <!-- Bouton pour afficher/masquer les filtres sur mobile -->
                                <button 
                                    class="sm:hidden flex items-center text-sm text-blue-600"
                                    @click="showFilters = !showFilters"
                                >
                                    <span>Filtres</span>
                                    <svg 
                                        xmlns="http://www.w3.org/2000/svg" 
                                        class="h-5 w-5 ml-1 transition-transform duration-200" 
                                        :class="{ 'rotate-180': showFilters }"
                                        fill="none" 
                                        viewBox="0 0 24 24" 
                                        stroke="currentColor"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- Filtres - toujours visibles sur desktop, conditionnels sur mobile -->
                            <div 
                                class="sm:flex flex-wrap gap-2"
                                :class="{ 'hidden': !showFilters, 'flex': showFilters }"
                            >
                                <button 
                                    @click="checkinStatusFilter = 'all'" 
                                    class="flex-1 sm:flex-none px-3 py-1 text-xs sm:text-sm rounded-md"
                                    :class="checkinStatusFilter === 'all' ? 
                                        'bg-blue-100 text-blue-800 font-medium' : 
                                        'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                >
                                    Tous ({{ stats.total }})
                                </button>
                                <button 
                                    @click="checkinStatusFilter = 'checked'" 
                                    class="flex-1 sm:flex-none px-3 py-1 text-xs sm:text-sm rounded-md"
                                    :class="checkinStatusFilter === 'checked' ? 
                                        'bg-green-100 text-green-800 font-medium' : 
                                        'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                >
                                    Scannés ({{ stats.checked }})
                                </button>
                                <button 
                                    @click="checkinStatusFilter = 'unchecked'" 
                                    class="flex-1 sm:flex-none px-3 py-1 text-xs sm:text-sm rounded-md"
                                    :class="checkinStatusFilter === 'unchecked' ? 
                                        'bg-red-100 text-red-800 font-medium' : 
                                        'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                >
                                    Non scannés ({{ stats.unchecked }})
                                </button>
                            </div>
                        </div>
                        
                        <!-- Barre de recherche -->
                        <div class="relative w-full mb-4">
                            <input 
                                type="text" 
                                v-model="searchQuery"
                                placeholder="Rechercher par nom, code..." 
                                class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Tableau des billets - version desktop -->
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Participant
                                        </th>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Billet
                                        </th>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Code
                                        </th>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Statut
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="filteredPositions.length === 0">
                                        <td colspan="4" class="px-3 py-4 text-center text-sm text-gray-500">
                                            Aucun billet trouvé avec les filtres actuels
                                        </td>
                                    </tr>
                                    <tr v-for="position in filteredPositions" :key="`position-${position.id}`" 
                                        :class="position.isCheckedIn ? 'bg-green-50' : ''"
                                    >
                                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ position.attendee_name || 'Non spécifié' }}
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ getTicketName(position.item) }}
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ position.secret || '' }}
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                                      :class="position.isCheckedIn ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                                    {{ position.isCheckedIn ? 'Scanné' : 'Non scanné' }}
                                                </span>
                                                <span v-if="position.checkinDatetime" class="mt-1 text-xs text-gray-500">
                                                    {{ formatDate(position.checkinDatetime) }}
                                                </span></div></td>
                                                </tr></tbody></table></div>

                                            
                        <!-- Liste des billets - version mobile -->
                        <div class="sm:hidden">
                            <div v-if="filteredPositions.length === 0" class="p-4 text-center text-sm text-gray-500 bg-gray-50 rounded-lg">
                                Aucun billet trouvé avec les filtres actuels
                            </div>
                            
                            <div v-else class="space-y-3">
                                <div 
                                    v-for="position in filteredPositions" 
                                    :key="`mobile-position-${position.id}`" 
                                    class="border rounded-lg overflow-hidden"
                                    :class="position.isCheckedIn ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-white'"
                                >
                                    <!-- En-tête de la carte (toujours visible) -->
                                    <div 
                                        class="p-3 flex justify-between items-center cursor-pointer"
                                        @click="togglePosition(position.id)"
                                    >
                                        <div class="flex-1">
                                            <div class="font-medium truncate">
                                                {{ position.attendee_name || 'Participant non spécifié' }}
                                            </div>
                                            <div class="text-xs text-gray-500 truncate">
                                                {{ getTicketName(position.item) }}
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center">
                                            <span 
                                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full mr-2" 
                                                :class="position.isCheckedIn ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                            >
                                                {{ position.isCheckedIn ? 'Scanné' : 'Non scanné' }}
                                            </span>
                                            <svg 
                                                xmlns="http://www.w3.org/2000/svg" 
                                                class="h-5 w-5 text-gray-400 transition-transform duration-200" 
                                                :class="{ 'rotate-180': expandedPosition === position.id }"
                                                fill="none" 
                                                viewBox="0 0 24 24" 
                                                stroke="currentColor"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <!-- Détails étendus (visibles uniquement lorsque déplié) -->
                                    <div v-if="expandedPosition === position.id" class="p-3 border-t border-gray-200 bg-white">
                                        <div class="grid grid-cols-2 gap-2 text-sm">
                                            <div class="text-gray-500">Code :</div>
                                            <div class="font-medium break-all">{{ position.secret || 'N/A' }}</div>
                                            
                                            <div class="text-gray-500" v-if="position.order">Commande :</div>
                                            <div v-if="position.order">{{ position.order }}</div>
                                            
                                            <div class="text-gray-500" v-if="position.checkinDatetime">Date de scan :</div>
                                            <div v-if="position.checkinDatetime">{{ formatShortDate(position.checkinDatetime) }}</div>
                                        </div>
                                        
                                        <!-- Bouton pour utiliser ce code -->
                                        <button 
                                            v-if="!position.isCheckedIn"
                                            @click="useCode(position.secret); $event.stopPropagation()"
                                            class="mt-3 w-full px-3 py-2 bg-blue-50 text-blue-600 rounded text-sm font-medium hover:bg-blue-100 flex items-center justify-center"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Scanner ce billet
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</template>

<style scoped>
.shadow-sm {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

/* Transitions pour animations fluides */
.transition-transform {
    transition: transform 0.2s ease;
}

.rotate-180 {
    transform: rotate(180deg);
}

/* Optimisations pour différentes tailles d'écran */
@media (max-width: 640px) {
    /* Styles spécifiques aux iPhones */
    .container {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    
    /* Pour éviter le zoom automatique sur iOS */
    input, select, textarea {
        font-size: 16px;
    }
    
    /* Améliorer l'accessibilité pour les cibles tactiles */
    button, .cursor-pointer {
        min-height: 44px;
    }
}

@media (min-width: 641px) and (max-width: 1024px) {
    /* Styles spécifiques aux iPads */
    .container {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
}

/* Amélioration pour le mode sombre (si activé par l'utilisateur) */
@media (prefers-color-scheme: dark) {
    /* Vous pouvez ajouter des styles pour le mode sombre ici si nécessaire */
}
</style>
