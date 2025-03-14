<script setup>
import { Head, Link } from '@inertiajs/vue3';
import TopNav from '@/Components/TopNav.vue';
import { ref ,onMounted} from 'vue';

// Définir les props que le composant recevra
const props = defineProps({
    order: Object,
    event: Object,
    eventSlug: String,
    apiResponse: Object
});

// Logging pour le débogage
onMounted(() => {
    console.log('OrderDetail props:', props);
    if (props.order && props.order.positions) {
        console.log('Positions:', props.order.positions);
    }
});

// Fonctions pour formater les données
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString() + ' ' + 
           new Date(dateString).toLocaleTimeString();
}

function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', { 
        style: 'currency', 
        currency: 'EUR' 
    }).format(price);
}

function getStatusClass(status) {
    const statusMap = {
        'p': 'bg-yellow-100 text-yellow-800',
        'n': 'bg-red-100 text-red-800',
        'c': 'bg-green-100 text-green-800',
        'r': 'bg-blue-100 text-blue-800',
    };
    return statusMap[status] || 'bg-gray-100 text-gray-800';
}

function getStatusLabel(status) {
    const statusLabels = {
        'p': 'En attente',
        'n': 'Annulée',
        'c': 'Confirmée',
        'r': 'Remboursée',
    };
    return statusLabels[status] || status;
}

// Fonction pour obtenir le nom de l'événement en toute sécurité
function getEventName(event) {
    if (!event) return 'Événement';
    
    if (typeof event.name === 'string') {
        return event.name;
    } else if (event.name && typeof event.name === 'object') {
        return event.name.fr || event.name.en || event.name[Object.keys(event.name)[0]] || 'Sans nom';
    }
    return event.slug || 'Sans nom';
}

// Fonction améliorée pour obtenir le nom de l'article
function getItemName(position) {
    // Cas 1: Si position.item existe et a un nom
    if (position && position.item && position.item.name) {
        // Si le nom est une chaîne simple
        if (typeof position.item.name === 'string') {
            return position.item.name;
        }
        
        // Si le nom est un objet avec différentes langues
        if (typeof position.item.name === 'object') {
            const name = position.item.name.fr || 
                        position.item.name.en || 
                        (Object.keys(position.item.name).length > 0 ? 
                            position.item.name[Object.keys(position.item.name)[0]] : 
                            null);
            
            if (name) {
                return name;
            }
        }
    }
    
    // Cas 2: Si la position a directement un nom (certaines API)
    if (position && position.name) {
        if (typeof position.name === 'string') {
            return position.name;
        }
        
        if (typeof position.name === 'object') {
            const name = position.name.fr || 
                        position.name.en || 
                        (Object.keys(position.name).length > 0 ? 
                            position.name[Object.keys(position.name)[0]] : 
                            null);
            
            if (name) {
                return name;
            }
        }
    }
    
    // Cas 3: Si on a une description mais pas de nom
    if (position && position.description) {
        if (typeof position.description === 'string') {
            return position.description;
        }
        
        if (typeof position.description === 'object') {
            const desc = position.description.fr || 
                        position.description.en || 
                        (Object.keys(position.description).length > 0 ? 
                            position.description[Object.keys(position.description)[0]] : 
                            null);
            
            if (desc) {
                return desc;
            }
        }
    }
    
    // Cas 4: Si on a un item_name (format spécial de certaines API)
    if (position && position.item_name) {
        return position.item_name;
    }
    
    // Cas 5: Si on a une subordered_item (format parfois utilisé par Pretix)
    if (position && position.subordered_item && position.subordered_item.name) {
        if (typeof position.subordered_item.name === 'string') {
            return position.subordered_item.name;
        }
        
        if (typeof position.subordered_item.name === 'object') {
            return position.subordered_item.name.fr || 
                  position.subordered_item.name.en || 
                  Object.values(position.subordered_item.name)[0] || 'Article sans nom';
        }
    }
    
    // Si on a un item_id, essayons d'afficher un nom plus informatif
    if (position && position.item_id) {
        return `Billet #${position.item_id}`;
    }
    
    // Si on n'a pas trouvé de nom
    return 'Article inconnu';
}

// Fonction pour obtenir la valeur de variation en toute sécurité
function getVariationValue(position) {
    // Cas 1: Si position.variation existe et a une valeur
    if (position && position.variation && position.variation.value) {
        // Si la valeur est une chaîne simple
        if (typeof position.variation.value === 'string') {
            return position.variation.value;
        }
        
        // Si la valeur est un objet avec différentes langues
        if (typeof position.variation.value === 'object') {
            return position.variation.value.fr || 
                   position.variation.value.en || 
                   (Object.keys(position.variation.value).length > 0 ? 
                    position.variation.value[Object.keys(position.variation.value)[0]] : 
                    '');
        }
    }
    
    // Cas 2: Si position a directement un variation_name
    if (position && position.variation_name) {
        return position.variation_name;
    }
    
    return '';
}

// Fonction pour obtenir l'URL de téléchargement du ticket
function getTicketDownloadUrl(position) {
    if (!position || !props.eventSlug || !props.order) {
        return '#';
    }
    // Cette URL est généralement fournie par l'API Pretix
    return position.downloads && position.downloads.pdf ? 
        position.downloads.pdf : 
        `${props.order.download_url || ''}`;
}
</script>

<template>
    <Head :title="`Commande ${order ? order.code : ''} - ${getEventName(event)}`" />

    <div class="min-h-screen bg-white text-gray-800">
        <!-- Header principal -->
        <TopNav />
        
        <!-- Section titre de la commande -->
        <div class="bg-white border-b border-gray-200 py-6">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">
                            Commande {{ order ? order.code : '' }} - {{ getEventName(event) }}
                        </h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-6">
            <!-- Bouton retour -->
            <div class="mb-6 flex space-x-2">
                <Link 
                :href="route('pretix.show', { eventSlug: eventSlug, tab: 'commandes' })" 
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    &larr; Retour aux commandes
                </Link>
                
                <!-- Bouton de téléchargement pour tous les tickets -->
                <a 
        v-if="order && order.download_url" 
        :href="route('pretix.download.ticket', { 
            eventSlug: eventSlug, 
            orderCode: order.code 
        })" 
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 border border-transparent rounded-lg"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Télécharger tous les tickets
    </a>
            </div>

            <!-- Message en cas d'erreur API -->
            <div v-if="apiResponse && apiResponse.error" class="p-4 mb-6 text-red-700 bg-red-100 border-l-4 border-red-500 rounded-lg">
                <p><strong>Erreur API:</strong> {{ apiResponse.error }}</p>
            </div>

            <!-- Détails de la commande -->
            <div v-if="order" class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <!-- Informations principales -->
                <div class="p-6 overflow-hidden bg-white shadow-sm md:col-span-2 rounded-lg border border-gray-200">
                    <h3 class="mb-4 text-lg font-semibold">Informations de commande</h3>
                    
                    
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <p class="mb-2"><span class="font-semibold">Code de commande:</span> {{ order.code }}</p>
                            <p class="mb-2"><span class="font-semibold">Date de commande:</span> {{ formatDate(order.datetime) }}</p>
                            <p class="mb-2"><span class="font-semibold">Email:</span> {{ order.email }}</p>
                        </div>
                        <div>
                            <p class="mb-2"><span class="font-semibold">Total:</span> {{ formatPrice(order.total) }}</p>
                            <p v-if="order.payment_date" class="mb-2">
                                <span class="font-semibold">Date de paiement:</span> {{ formatDate(order.payment_date) }}
                            </p>
                            <p v-if="order.payment_provider" class="mb-2">
                                <span class="font-semibold">Méthode de paiement:</span> {{ order.payment_provider }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Adresse de facturation -->
                <div class="p-6 overflow-hidden bg-white shadow-sm rounded-lg border border-gray-200">
                    <h3 class="mb-4 text-lg font-semibold">Adresse de facturation</h3>
                    
                    <div v-if="order.invoice_address">
                        <p v-if="order.invoice_address.company" class="mb-2">{{ order.invoice_address.company }}</p>
                        <p class="mb-2">{{ order.invoice_address.name }}</p>
                        <p v-if="order.invoice_address.street" class="mb-2">{{ order.invoice_address.street }}</p>
                        <p v-if="order.invoice_address.zipcode || order.invoice_address.city" class="mb-2">
                            {{ order.invoice_address.zipcode }} {{ order.invoice_address.city }}
                        </p>
                        <p v-if="order.invoice_address.country" class="mb-2">{{ order.invoice_address.country }}</p>
                        <p v-if="order.invoice_address.vat_id" class="mt-4 mb-2">
                            <span class="font-semibold">TVA:</span> {{ order.invoice_address.vat_id }}
                        </p>
                    </div>
                    <div v-else class="text-gray-500">
                        Aucune adresse de facturation
                    </div>
                </div>

                <!-- Positions de la commande (billets) -->
                <div class="p-6 overflow-hidden bg-white shadow-sm md:col-span-3 rounded-lg border border-gray-200">
                    <h3 class="mb-4 text-lg font-semibold">Billets / Articles</h3>
                    
                    <div v-if="order.positions && order.positions.length > 0" class="space-y-6">
                        <!-- Affichage de style carte pour chaque billet -->
                        <div v-for="(position, index) in order.positions" :key="position.id || position.positionid || index" 
                             class="p-4 border rounded-lg shadow-sm bg-gray-50">
                            <div class="flex flex-col md:flex-row md:justify-between md:items-start">
                                <!-- Informations sur le billet -->
                                <div class="flex-grow">
                                    <h4 class="text-lg font-medium">
                                        #{{ index + 1 }} – {{ getItemName(position) }}
                                        <span v-if="getVariationValue(position)" class="text-sm text-gray-500">
                                            ({{ getVariationValue(position) }})
                                        </span>
                                    </h4>
                                    
                                    <div class="mt-2 space-y-1">
                                        <!-- Code du billet -->
                                        <p v-if="position.secret" class="text-sm">
                                            <span class="font-semibold">Code du billet:</span> {{ position.secret }}
                                        </p>
                                        
                                        <!-- Informations sur le participant -->
                                        <div v-if="position.attendee_name" class="mt-3">
                                            <p class="text-sm font-semibold">Participant</p>
                                            <p class="text-base">{{ position.attendee_name }}</p>
                                            <p v-if="position.attendee_email" class="text-sm text-gray-600">{{ position.attendee_email }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Prix et boutons d'action -->
                                <div class="mt-4 md:mt-0 md:ml-4 flex flex-col items-start md:items-end space-y-2">
                                    <!-- Prix -->
                                    <p class="text-xl font-semibold">{{ formatPrice(position.price) }}</p>
                                    
                                    <!-- Boutons d'action -->
                                    <div class="flex space-x-2">
                                        <!-- Télécharger PDF -->
                                        <a 
                                        v-if="position.pdf_ticket" 
                                        :href="route('pretix.download.ticket', { 
                                            eventSlug: eventSlug, 
                                            orderCode: order.code, 
                                            positionId: position.id || position.positionid 
                                        })"
                                        class="inline-flex items-center px-3 py-1 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 border border-transparent rounded-md"
                                    >
                                        PDF
                                    </a>
                                        
                                        <!-- Lien vers la page du billet -->
                                        <a v-if="position.ticket_page_url" 
                                           :href="position.ticket_page_url" 
                                           target="_blank" 
                                           class="inline-flex items-center px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                            Page du billet
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-gray-500">
                        Aucun article dans cette commande
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Styles pour le cas où vous en auriez besoin */
.shadow-sm {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
</style>