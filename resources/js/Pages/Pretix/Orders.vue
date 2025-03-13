<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

// Définir les props que le composant recevra
const props = defineProps({
    orders: Array,
    event: Object,
    eventSlug: String,
    apiResponse: Object
});

// État pour la recherche
const searchQuery = ref('');

// Filtre des commandes par recherche
const filteredOrders = computed(() => {
    if (!props.orders || !props.orders.length) return [];
    if (!searchQuery.value) return props.orders;
    
    const query = searchQuery.value.toLowerCase();
    return props.orders.filter(order => 
        (order.invoice_address?.name || '').toLowerCase().includes(query) ||
        (order.email || '').toLowerCase().includes(query) ||
        (order.code || '').toLowerCase().includes(query)
    );
});

// État pour les détails visibles en vue mobile
const expandedOrderId = ref(null);

// Basculer l'affichage détaillé en mobile
function toggleOrderDetails(id) {
    expandedOrderId.value = expandedOrderId.value === id ? null : id;
}

// Fonction pour formater la date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    
    // Format plus compact pour mobile
    const dateOptions = { day: '2-digit', month: '2-digit', year: 'numeric' };
    const timeOptions = { hour: '2-digit', minute: '2-digit' };
    
    return new Date(dateString).toLocaleDateString(undefined, dateOptions) + ' ' + 
           new Date(dateString).toLocaleTimeString(undefined, timeOptions);
}

// Format de date court pour mobile
function formatShortDate(dateString) {
    if (!dateString) return 'N/A';
    const options = { day: '2-digit', month: '2-digit', year: '2-digit' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

// Fonction pour formater le prix
function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', { 
        style: 'currency', 
        currency: 'EUR' 
    }).format(price);
}

// Fonction pour obtenir le canal de vente
function getChannel(order) {
    return order.sales_channel === 'web' ? 'En ligne' : order.sales_channel || 'N/A';
}
</script>

<template>
    <!-- Titre de la section -->
    <div class="mb-4 sm:mb-6">
        <h2 class="text-xl sm:text-2xl font-semibold flex items-center">
            Commandes 
            <span class="inline-flex items-center justify-center px-2 py-1 ml-2 text-xs font-bold leading-none text-white bg-blue-500 rounded">
                {{ filteredOrders.length || 0 }}
            </span>
        </h2>
    </div>
    
    <!-- Barre de recherche et bouton d'export -->
    <div class="flex flex-col sm:flex-row sm:justify-between gap-3 sm:gap-0 mb-4 sm:mb-6">
        <div class="relative w-full sm:w-64">
            <input 
                v-model="searchQuery"
                type="text" 
                placeholder="Rechercher une commande..." 
                class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
        
        <button class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 w-full sm:w-auto">
            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            Exporter
        </button>
    </div>
    
    <!-- Vue desktop - Table traditionnelle -->
    <div class="hidden sm:block">
        <!-- En-têtes de colonnes -->
        <div class="grid grid-cols-5 gap-4 mb-2 font-medium text-sm text-gray-500 uppercase">
            <div>Client</div>
            <div>Référence</div>
            <div>Canal</div>
            <div>Prix</div>
            <div>Date</div>
        </div>
        
        <!-- Lignes de données -->
        <div v-if="filteredOrders.length > 0">
            <Link 
                v-for="order in filteredOrders" 
                :key="order.code" 
                :href="route('pretix.order.details', { eventSlug: eventSlug, orderCode: order.code })"
                class="grid grid-cols-5 gap-4 py-4 border-t border-gray-200 hover:bg-gray-50 transition-colors cursor-pointer text-gray-800 no-underline"
                as="div"
            >
                <div class="flex items-center">
                    <div class="w-2 h-2 mr-3 bg-green-500 rounded-full"></div>
                    <div>{{ order.invoice_address?.name || order.email || 'Client inconnu' }}</div>
                </div>
                <div>{{ order.code }}</div>
                <div>{{ getChannel(order) }}</div>
                <div>{{ formatPrice(order.total) }}</div>
                <div>{{ formatDate(order.datetime) }}</div>
            </Link>
        </div>
    </div>
    
    <!-- Vue mobile - Cards -->
    <div class="sm:hidden">
        <div v-if="filteredOrders.length > 0">
            <div 
                v-for="order in filteredOrders" 
                :key="order.code" 
                class="mb-3 border border-gray-200 rounded-lg overflow-hidden"
            >
                <!-- En-tête de la card - toujours visible -->
                <div 
                    class="flex justify-between items-center p-3 bg-gray-50 cursor-pointer"
                    @click="toggleOrderDetails(order.code)"
                >
                    <div class="flex items-center">
                        <div class="w-2 h-2 mr-2 bg-green-500 rounded-full"></div>
                        <div class="font-medium truncate max-w-[150px]">
                            {{ order.invoice_address?.name || order.email || 'Client inconnu' }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold">{{ formatPrice(order.total) }}</div>
                        <div class="text-xs text-gray-500">{{ formatShortDate(order.datetime) }}</div>
                    </div>
                </div>
                
                <!-- Détails - s'affichent quand on clique -->
                <div 
                    v-if="expandedOrderId === order.code"
                    class="p-3 bg-white border-t border-gray-200"
                >
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div class="text-gray-500">Référence:</div>
                        <div class="font-medium">{{ order.code }}</div>
                        
                        <div class="text-gray-500">Canal:</div>
                        <div>{{ getChannel(order) }}</div>
                        
                        <div class="text-gray-500">Date:</div>
                        <div>{{ formatDate(order.datetime) }}</div>
                    </div>
                    
                    <Link 
                        :href="route('pretix.order.details', { eventSlug: eventSlug, orderCode: order.code })"
                        class="block w-full mt-3 py-2 text-center text-sm font-medium text-blue-600 bg-blue-50 rounded hover:bg-blue-100"
                    >
                        Voir les détails
                    </Link>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Message si aucune commande -->
    <div v-if="filteredOrders.length === 0" class="py-6 text-center text-gray-500 bg-gray-50 rounded-lg">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p>Aucune commande trouvée pour cet événement.</p>
        <p v-if="searchQuery" class="text-sm mt-2">Essayez d'autres termes de recherche.</p>
    </div>
    
    <!-- Pagination -->
    <div class="flex flex-col sm:flex-row sm:justify-between items-center gap-2 mt-4 sm:mt-6">
        <div class="text-sm text-gray-500">Page 1 de 1</div>
        <div class="flex">
            <button class="flex items-center justify-center w-8 h-8 ml-1 text-blue-600 bg-blue-100 rounded">1</button>
        </div>
    </div>
</template>

<style scoped>
/* Suppression des styles par défaut des liens */
a {
    color: inherit;
    text-decoration: none;
}

/* Transition pour l'expansion des cartes */
.card-details-enter-active, .card-details-leave-active {
    transition: max-height 0.3s ease, opacity 0.3s ease;
    max-height: 500px;
    opacity: 1;
}
.card-details-enter-from, .card-details-leave-to {
    max-height: 0;
    opacity: 0;
}

/* Media queries pour des ajustements plus fins */
@media (max-width: 640px) {
    .truncate {
        max-width: 120px;
    }
}

@media (min-width: 768px) and (max-width: 1024px) {
    /* Ajustements spécifiques pour iPad */
}
</style>