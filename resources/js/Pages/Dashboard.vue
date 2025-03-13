<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import TopNav from '@/Components/TopNav.vue';
// Définir les props que le composant recevra du contrôleur
defineProps({
    events: Array,
    apiResponse: Object
});

// Fonctions pour formater les données
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    }).format(date);
}

function getEventName(event) {
    // Gestion des différentes structures possibles pour le nom
    if (typeof event.name === 'string') {
        return event.name;
    } else if (event.name && typeof event.name === 'object') {
        // Essai avec plusieurs langues possibles
        return event.name.fr || event.name.en || event.name[Object.keys(event.name)[0]] || 'Sans nom';
    }
    return event.slug || 'Sans nom';
}
</script>

<template>
    <Head title="Événements" />

    
        
        <TopNav/>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Grille d'événements -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Carte pour créer un nouvel événement -->
                    <Link 
                        :href="route('pretix.events.create')"
                        class="bg-blue-500 text-white rounded-lg p-6 flex flex-col items-center justify-center hover:bg-blue-600 transition h-64"
                    >
                        <div class="w-16 h-16 rounded-full border-2 border-white flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <span class="text-xl font-medium">Nouvel événement</span>
                    </Link>
                    
                    <!-- Cartes des événements existants -->
                    <Link 
                        v-for="event in events" 
                        :key="event.slug"
                        :href="route('pretix.show', event.slug)"
                        class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition h-64"
                    >
                        <!-- Image/Icône de l'événement -->
                        <div class="h-32 bg-gray-100 flex items-center justify-center">
                            <div v-if="event.icon" class="w-16 h-16">
                                <img :src="event.icon" :alt="getEventName(event)" class="w-full h-full object-contain" />
                            </div>
                            <div v-else class="text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Informations de l'événement -->
                        <div class="p-4">
                            <h3 class="text-lg font-medium text-blue-500 truncate">{{ getEventName(event) }}</h3>
                            <p v-if="event.date_from" class="text-sm text-gray-600 mt-1">
                                {{ formatDate(event.date_from) }}
                            </p>
                        </div>
                    </Link>
                    
                    <!-- Message si aucun événement -->
                    <div v-if="events && events.length === 0 && !apiResponse?.error" 
                         class="col-span-full text-center py-12">
                        <p class="text-gray-500">Aucun événement disponible pour le moment.</p>
                    </div>
                    
                    <!-- Message en cas d'erreur API -->
                    <div v-if="apiResponse && apiResponse.error" 
                         class="col-span-full bg-red-50 p-4 rounded-lg border border-red-200">
                        <p class="text-red-600">{{ apiResponse.error }}</p>
                    </div>
                </div>
            </div>
        </div>
    
</template>

<style scoped>
/* Les cartes doivent avoir une apparence uniforme */
.rounded-lg {
    border-radius: 0.75rem;
}

/* Effet d'ombre au survol */
.hover\:shadow-lg:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style>