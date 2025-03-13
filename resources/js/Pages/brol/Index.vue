<template>
    <AppLayout title="Événements Pretix">
      <template #header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Événements Pretix
        </h2>
      </template>
  
      <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div v-if="apiResponse && apiResponse.error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <p><strong>Erreur API:</strong> {{ apiResponse.error }}</p>
          </div>
          
          <div v-if="debug" class="bg-gray-100 p-4 mb-4 rounded overflow-auto max-h-60">
            <h3 class="font-bold mb-2">Debug - Réponse API:</h3>
            <pre>{{ JSON.stringify(apiResponse, null, 2) }}</pre>
          </div>
          
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
              <div class="alert alert-info" v-if="events.length === 0">
                <p>Aucun événement trouvé.</p>
                <p class="mt-2 text-sm">Vérifiez votre configuration API dans le fichier .env</p>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" v-else>
                <div v-for="event in events" :key="event.slug" class="bg-white shadow rounded-lg overflow-hidden border">
                  <div class="p-4">
                    <h3 class="text-lg font-semibold mb-2">{{ getEventName(event) }}</h3>
                    <p class="text-sm text-gray-600 mb-2">
                      <span v-if="event.date_from">{{ formatDate(event.date_from) }}</span>
                      <span v-if="event.date_from && event.date_to"> - {{ formatDate(event.date_to) }}</span>
                      <span v-if="!event.date_from">Date non spécifiée</span>
                    </p>
                    <div class="mt-4 flex space-x-2">
                      <Link :href="route('pretix.show', event.slug)" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Détails
                      </Link>
                      <Link :href="route('pretix.orders', event.slug)" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Commandes
                      </Link>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  </template>
  
  <script>
  import { Link } from '@inertiajs/vue3';
  import AppLayout from '@/Layouts/GuestLayout.vue';
  
  export default {
    components: {
      AppLayout,
      Link,
    },
    props: {
      events: Array,
      apiResponse: Object,
    },
    data() {
      return {
        debug: true, // Mettre à false en production
      };
    },
    methods: {
      formatDate(dateString) {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString();
      },
      getEventName(event) {
        // Gestion des différentes structures possibles pour le nom
        if (typeof event.name === 'string') {
          return event.name;
        } else if (event.name && typeof event.name === 'object') {
          // Essai avec plusieurs langues possibles
          return event.name.fr || event.name.en || event.name[Object.keys(event.name)[0]] || 'Sans nom';
        }
        return event.slug || 'Sans nom';
      }
    },
  };
  </script>