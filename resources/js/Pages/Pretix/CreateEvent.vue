<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// Formulaire pour créer un événement
const form = useForm({
    name: {
        fr: '',  // Nom en français
        en: ''   // Nom en anglais
    },
    slug: '',    // Identifiant unique pour l'URL
    live: false, // Si l'événement est immédiatement disponible
    currency: 'EUR', // Devise par défaut
    date_from: '',   // Date de début
    date_to: '',     // Date de fin
    presale_start: '', // Début des préventes
    presale_end: '',   // Fin des préventes
    location: '',    // Lieu
    has_subevents: false, // Si l'événement a des sous-événements
    testmode: false,  // Mode test
    plugins: [], // Plugins actifs
});

const isSubmitting = ref(false);
const showAdvancedOptions = ref(false);

// Méthode pour soumettre le formulaire
function submit() {
    isSubmitting.value = true;
    
    form.post(route('pretix.events.store'), {
        onSuccess: () => {
            isSubmitting.value = false;
            // Redirection effectuée automatiquement par Inertia
        },
        onError: () => {
            isSubmitting.value = false;
        }
    });
}

// Génération automatique du slug à partir du nom
function generateSlug() {
    if (form.name.fr) {
        form.slug = form.name.fr.toLowerCase()
            .replace(/\s+/g, '-')        // Remplacer les espaces par des tirets
            .replace(/[^\w\-]+/g, '')    // Supprimer les caractères spéciaux
            .replace(/\-\-+/g, '-')      // Remplacer les doubles tirets par un seul
            .replace(/^-+/, '')          // Supprimer les tirets au début
            .replace(/-+$/, '');         // Supprimer les tirets à la fin
    }
}
</script>

<template>
    <Head title="Créer un événement" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Créer un nouvel événement
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <!-- Bouton de retour -->
                            <div class="mb-6">
                                <Link :href="route('dashboard')" class="text-sm text-indigo-600 hover:text-indigo-900">
                                    &larr; Retour au dashboard
                                </Link>
                            </div>

                            <!-- Nom de l'événement -->
                            <div class="mb-6">
                                <label for="name_fr" class="block text-sm font-medium text-gray-700">Nom de l'événement (français) <span class="text-red-600">*</span></label>
                                <input
                                    id="name_fr"
                                    v-model="form.name.fr"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                    @blur="generateSlug"
                                />
                                <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                            </div>

                            <div class="mb-6">
                                <label for="name_en" class="block text-sm font-medium text-gray-700">Nom de l'événement (anglais)</label>
                                <input
                                    id="name_en"
                                    v-model="form.name.en"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

                            <!-- Slug (identifiant URL) -->
                            <div class="mb-6">
                                <label for="slug" class="block text-sm font-medium text-gray-700">Identifiant URL <span class="text-red-600">*</span></label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                        votre-url.com/
                                    </span>
                                    <input
                                        id="slug"
                                        v-model="form.slug"
                                        type="text"
                                        class="flex-1 block w-full rounded-none rounded-r-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    />
                                </div>
                                <div v-if="form.errors.slug" class="text-red-500 text-xs mt-1">{{ form.errors.slug }}</div>
                                <p class="text-xs text-gray-500 mt-1">
                                    L'identifiant doit être unique et ne peut contenir que des lettres, chiffres et tirets.
                                </p>
                            </div>

                            <!-- Dates -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="date_from" class="block text-sm font-medium text-gray-700">Date de début <span class="text-red-600">*</span></label>
                                    <input
                                        id="date_from"
                                        v-model="form.date_from"
                                        type="datetime-local"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    />
                                    <div v-if="form.errors.date_from" class="text-red-500 text-xs mt-1">{{ form.errors.date_from }}</div>
                                </div>

                                <div>
                                    <label for="date_to" class="block text-sm font-medium text-gray-700">Date de fin</label>
                                    <input
                                        id="date_to"
                                        v-model="form.date_to"
                                        type="datetime-local"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <div v-if="form.errors.date_to" class="text-red-500 text-xs mt-1">{{ form.errors.date_to }}</div>
                                </div>
                            </div>

                            <!-- Localisation -->
                            <div class="mb-6">
                                <label for="location" class="block text-sm font-medium text-gray-700">Lieu</label>
                                <input
                                    id="location"
                                    v-model="form.location"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <div v-if="form.errors.location" class="text-red-500 text-xs mt-1">{{ form.errors.location }}</div>
                            </div>

                            <!-- Options avancées (toggle) -->
                            <div class="mb-6">
                                <button
                                    type="button"
                                    class="text-sm text-indigo-600 hover:text-indigo-900 flex items-center"
                                    @click="showAdvancedOptions = !showAdvancedOptions"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 mr-1"
                                        :class="{ 'transform rotate-90': showAdvancedOptions }"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Options avancées
                                </button>
                            </div>

                            <!-- Options avancées (contenu) -->
                            <div v-if="showAdvancedOptions" class="mb-6 border-t border-gray-200 pt-6">
                                <!-- Dates de prévente -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="presale_start" class="block text-sm font-medium text-gray-700">Début des préventes</label>
                                        <input
                                            id="presale_start"
                                            v-model="form.presale_start"
                                            type="datetime-local"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <div v-if="form.errors.presale_start" class="text-red-500 text-xs mt-1">{{ form.errors.presale_start }}</div>
                                    </div>

                                    <div>
                                        <label for="presale_end" class="block text-sm font-medium text-gray-700">Fin des préventes</label>
                                        <input
                                            id="presale_end"
                                            v-model="form.presale_end"
                                            type="datetime-local"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <div v-if="form.errors.presale_end" class="text-red-500 text-xs mt-1">{{ form.errors.presale_end }}</div>
                                    </div>
                                </div>

                                <!-- Devise -->
                                <div class="mb-6">
                                    <label for="currency" class="block text-sm font-medium text-gray-700">Devise</label>
                                    <select
                                        id="currency"
                                        v-model="form.currency"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="EUR">Euro (€)</option>
                                        <option value="USD">Dollar américain ($)</option>
                                        <option value="GBP">Livre sterling (£)</option>
                                        <option value="CHF">Franc suisse</option>
                                    </select>
                                    <div v-if="form.errors.currency" class="text-red-500 text-xs mt-1">{{ form.errors.currency }}</div>
                                </div>

                                <!-- Options booléennes -->
                                <div class="flex flex-col space-y-4 mb-6">
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input
                                                id="live"
                                                v-model="form.live"
                                                type="checkbox"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            />
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="live" class="font-medium text-gray-700">Événement actif</label>
                                            <p class="text-gray-500">L'événement sera immédiatement disponible pour les clients.</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input
                                                id="testmode"
                                                v-model="form.testmode"
                                                type="checkbox"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            />
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="testmode" class="font-medium text-gray-700">Mode test</label>
                                            <p class="text-gray-500">Les paiements seront effectués en mode test.</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input
                                                id="has_subevents"
                                                v-model="form.has_subevents"
                                                type="checkbox"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            />
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="has_subevents" class="font-medium text-gray-700">Événement sériel</label>
                                            <p class="text-gray-500">L'événement est une série avec plusieurs dates ou lieux (sous-événements).</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Boutons d'action -->
                            <div class="flex justify-end">
                                <Link
                                    :href="route('dashboard')"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Annuler
                                </Link>
                                <button
                                    type="submit"
                                    class="ml-3 px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    :disabled="isSubmitting"
                                >
                                    <span v-if="isSubmitting">Création en cours...</span>
                                    <span v-else>Créer l'événement</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>