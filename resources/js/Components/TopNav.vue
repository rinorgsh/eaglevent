<script setup>
import { Link } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

// Définition des props
const props = defineProps({
    // Utilisateur actuel
    user: {
        type: Object,
        default: () => ({
            initials: 'RG',
            name: 'User'
        })
    },
    // Solde utilisateur
    balance: {
        type: Object,
        default: () => ({
            amount: '0,00',
            currency: '€'
        })
    },
    // Route active pour le surlignage du menu
    currentRoute: {
        type: String,
        default: 'dashboard'
    }
});

// État pour contrôler l'affichage du menu déroulant
const dropdownOpen = ref(false);

// Fonction pour basculer l'état du menu déroulant
const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
};

// Fermer le menu si on clique en dehors
const closeDropdown = (event) => {
    if (!event.target.closest('.profile-dropdown')) {
        dropdownOpen.value = false;
    }
};

// Ajouter un écouteur de clic global lorsque le composant est monté
onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

// Nettoyer l'écouteur lorsque le composant est démonté
onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});

// Calculer les initiales de l'utilisateur
const userInitials = computed(() => {
    if (props.user.initials) return props.user.initials;
    if (!props.user.name) return 'U';
    
    const nameParts = props.user.name.split(' ');
    if (nameParts.length === 1) {
        return nameParts[0].substring(0, 2).toUpperCase();
    }
    
    return (nameParts[0].charAt(0) + nameParts[1].charAt(0)).toUpperCase();
});

// Définition des éléments du menu
const menuItems = [
    { name: 'Événements', route: 'dashboard', href: '/dashboard' },
    { name: 'Clients', route: 'clients', href: '/clients' },
    { name: 'Statistiques', route: 'statistics', href: '/statistics' }
];
</script>

<template>
    <header class="bg-white border-b border-gray-200 shadow-sm">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <Link href="/" class="flex items-center">
                    <img src="/Images/logo2.png" alt="">
                    <span class="ml-2 text-gray-800 font-semibold">EagleEvent</span>
                </Link>
                
                <!-- Navigation principale 
                <nav class="hidden md:flex space-x-8">
                    <Link
                        v-for="item in menuItems"
                        :key="item.route"
                        :href="item.href"
                        class="font-medium"
                        :class="currentRoute === item.route ? 'text-gray-800' : 'text-gray-500 hover:text-gray-800'"
                    >
                        {{ item.name }}
                    </Link>
                </nav>-->
                
                <!-- Solde et profil utilisateur -->
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div class="text-xs text-gray-500">RINOR</div>
                        
                    </div>
                    
                    <!-- Menu déroulant du profil -->
                    <div class="relative profile-dropdown">
                        <div 
                            class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-medium cursor-pointer hover:bg-blue-600 transition-colors"
                            @click.stop="toggleDropdown"
                        >
                            {{ userInitials }}
                        </div>
                        
                        <!-- Menu déroulant -->
                        <div 
                            v-if="dropdownOpen" 
                            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 border border-gray-200"
                        >
                            <div class="px-4 py-2 text-sm text-gray-700 border-b border-gray-100">
                                <div class="font-medium">{{ user.name }}</div>
                                <div class="text-gray-500 text-xs truncate">{{ user.email || 'utilisateur@example.com' }}</div>
                            </div>
                            
                            <Link href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Mon profil
                            </Link>
                            <Link href="/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Paramètres
                            </Link>
                            <Link 
                                href="/logout" 
                                method="post" 
                                as="button" 
                                class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 border-t border-gray-100"
                            >
                                Déconnexion
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<style scoped>
/* Styles spécifiques au menu si nécessaire */
.shadow-sm {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
</style>