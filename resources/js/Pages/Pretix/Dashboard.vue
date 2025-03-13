<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

// Définir les props que le composant recevra
const props = defineProps({
    event: Object,
    eventSlug: String,
    orders: Array,
    tickets: Object,
    quotas: Object,
    stats: Object,
    apiResponse: Object
});

// Statistiques calculées
const ticketStats = computed(() => {
    const ticketsArray = props.tickets?.results || [];
    const total = ticketsArray.length;
    const ticketsSold = (props.orders || []).reduce((count, order) => {
        return count + (order.positions?.length || 0);
    }, 0);
    
    return {
        total,
        ticketsSold,
        remaining: total > 0 ? total - ticketsSold : 0
    };
});

// Calculer le chiffre d'affaires total
const revenue = computed(() => {
    return (props.orders || []).reduce((total, order) => {
        return total + (parseFloat(order.total) || 0);
    }, 0);
});

// Formater les montants
function formatMoney(amount) {
    return new Intl.NumberFormat('fr-FR', { 
        style: 'currency', 
        currency: 'EUR' 
    }).format(amount);
}

// Formater les dates
function formatDate(dateString) {
    if (!dateString) return 'Non défini';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

// Obtenir les 5 dernières commandes
const recentOrders = computed(() => {
    const orders = [...(props.orders || [])];
    // Trier par date (la plus récente en premier)
    orders.sort((a, b) => {
        return new Date(b.datetime || 0) - new Date(a.datetime || 0);
    });
    // Prendre les 5 premières
    return orders.slice(0, 5);
});

onMounted(() => {
    console.log('Dashboard Component Props:', props);
});
</script>

<template>
    <div>
        <!-- Titre du Dashboard -->
        <div class="mb-6">
            <h2 class="text-2xl font-semibold">Dashboard</h2>
            <p class="text-gray-500">Vue d'ensemble de l'événement "{{ event?.name?.fr || event?.name?.en || 'Événement' }}"</p>
        </div>
        
        <!-- Message d'erreur API -->
        <div v-if="apiResponse && apiResponse.error" class="p-4 mb-6 text-red-700 bg-red-100 border-l-4 border-red-500 rounded-lg">
            <p><strong>Erreur API:</strong> {{ apiResponse.error }}</p>
        </div>
        
        <!-- Cartes de statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Revenus -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-500">Revenus</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="mt-2">
                    <p class="text-3xl font-bold">{{ formatMoney(revenue) }}</p>
                    <p class="text-sm text-gray-500 mt-1">Total des ventes</p>
                </div>
            </div>
            
            <!-- Nombre de commandes -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-500">Commandes</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <div class="mt-2">
                    <p class="text-3xl font-bold">{{ (orders || []).length }}</p>
                    <p class="text-sm text-gray-500 mt-1">Commandes totales</p>
                </div>
            </div>
            
            <!-- Billets vendus -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-500">Billets vendus</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                <div class="mt-2">
                    <p class="text-3xl font-bold">{{ ticketStats.ticketsSold }}</p>
                    <p class="text-sm text-gray-500 mt-1">Billets vendus</p>
                </div>
            </div>
            
            <!-- Statut de l'événement -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-500">Statut</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" :class="event?.live ? 'text-green-500' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="mt-2">
                    <p class="text-xl font-bold" :class="event?.live ? 'text-green-600' : 'text-gray-500'">
                        {{ event?.live ? 'En ligne' : 'Hors ligne' }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">{{ event?.live ? 'Billetterie active' : 'Billetterie inactive' }}</p>
                </div>
            </div>
        </div>
        
        <!-- Informations sur l'événement -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Détails de l'événement -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-medium mb-4">Détails de l'événement</h3>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Début de l'événement</p>
                            <p class="font-medium">{{ formatDate(event?.date_from) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Fin de l'événement</p>
                            <p class="font-medium">{{ formatDate(event?.date_to) }}</p>
                        </div>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Lieu</p>
                        <p class="font-medium">{{ event?.location?.fr || event?.location?.en || 'Non spécifié' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Devise</p>
                        <p class="font-medium">{{ event?.currency || 'EUR' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">URL publique</p>
                        <a :href="event?.public_url" target="_blank" class="text-blue-600 hover:underline font-medium">
                            {{ event?.public_url || 'Non disponible' }}
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Actions rapides -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-medium mb-4">Actions rapides</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <Link :href="`/pretix/events/${eventSlug}?tab=commandes`" 
                          class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-blue-50 transition-colors"
                    >
                        <div class="bg-blue-100 p-2 rounded-md mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium">Commandes</p>
                            <p class="text-sm text-gray-500">Gérer les commandes</p>
                        </div>
                    </Link>
                    
                    <Link :href="`/pretix/events/${eventSlug}?tab=billets`" 
                          class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-blue-50 transition-colors"
                    >
                        <div class="bg-purple-100 p-2 rounded-md mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium">Billets</p>
                            <p class="text-sm text-gray-500">Gérer les billets</p>
                        </div>
                    </Link>
                    
                    <Link :href="`/pretix/events/${eventSlug}?tab=checkin`" 
                          class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-blue-50 transition-colors"
                    >
                        <div class="bg-green-100 p-2 rounded-md mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium">Check-in</p>
                            <p class="text-sm text-gray-500">Gérer les check-ins</p>
                        </div>
                    </Link>
                    
                    <a :href="event?.public_url" target="_blank"
                       class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-blue-50 transition-colors"
                    >
                        <div class="bg-orange-100 p-2 rounded-md mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium">Billetterie</p>
                            <p class="text-sm text-gray-500">Voir la billetterie</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Dernières commandes -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Dernières commandes</h3>
                <Link :href="route('pretix.orders', eventSlug)" class="text-sm text-blue-600 hover:underline">
                    Voir toutes les commandes
                </Link>
            </div>
            
            <!-- Tableau des dernières commandes -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Référence
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Client
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Montant
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Statut
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="recentOrders.length === 0">
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                Aucune commande trouvée
                            </td>
                        </tr>
                        <tr v-for="order in recentOrders" :key="order.code" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ order.code }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ order.invoice_address?.name || order.email || 'Client inconnu' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(order.datetime) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatMoney(order.total) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                      :class="order.status === 'p' ? 'bg-yellow-100 text-yellow-800' : 
                                             order.status === 'c' ? 'bg-green-100 text-green-800' :
                                             order.status === 'n' ? 'bg-red-100 text-red-800' :
                                             'bg-gray-100 text-gray-800'">
                                    {{ order.status === 'p' ? 'En attente' : 
                                       order.status === 'c' ? 'Confirmée' :
                                       order.status === 'n' ? 'Annulée' :
                                       order.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <Link :href="route('pretix.order.details', { eventSlug: eventSlug, orderCode: order.code })" class="text-blue-600 hover:text-blue-900">
                                    Détails
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Types de billets -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mt-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Types de billets</h3>
                <Link :href="`/pretix/events/${eventSlug}?tab=billets`" class="text-sm text-blue-600 hover:underline">
                    Gérer les billets
                </Link>
            </div>
            
            <!-- Liste des billets -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nom
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Prix
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quota
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Statut
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="!(tickets?.results?.length)">
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                Aucun billet trouvé
                            </td>
                        </tr>
                        <tr v-for="ticket in tickets?.results" :key="ticket.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ ticket.name?.fr || ticket.name?.en || `Billet #${ticket.id}` }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatMoney(ticket.default_price) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ ticket.quota_type === 'limited' ? ticket.quota_size : 'Illimité' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                      :class="ticket.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ ticket.active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Styles pour les tableaux et cartes */
.rounded-lg {
    border-radius: 0.5rem;
}
.shadow-sm {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
</style>