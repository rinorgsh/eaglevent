<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// État pour l'affichage du mot de passe
const showPassword = ref(false);
const rememberMe = ref(false);

// Définition du formulaire
const form = useForm({
    email: '',
    password: '',
    remember: false
});

// Soumission du formulaire
const submit = () => {
    form.remember = rememberMe.value;
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="login-page">
        <Head title="Connexion" />
        
        <!-- Fond avec image du concert -->
        <div class="background-image"></div>
        
        <!-- Overlay coloré pour améliorer la lisibilité -->
        <div class="overlay"></div>
        
        <!-- Contenu principal -->
        <div class="content-container">
            <!-- Logo et titre -->
            <div class="logo-container">
                <div class="logo">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 4V20C15 20 8 17.5 8 12C8 6.5 15 4 15 4Z" fill="currentColor"/>
                        <path d="M17 6V18C17 18 22 15.5 22 12C22 8.5 17 6 17 6Z" fill="currentColor" fill-opacity="0.5"/>
                    </svg>
                    <h1>EagleEvent</h1>
                </div>
                <p class="tagline">Créez des événements inoubliables</p>
            </div>
            
            <!-- Formulaire de connexion -->
            <div class="login-container">
                <h2>Connexion</h2>
                
                <form @submit.prevent="submit">
                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <div class="input-container">
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            <input 
                                id="email" 
                                type="email" 
                                v-model="form.email" 
                                placeholder="exemple@email.com"
                                required
                                autocomplete="username"
                            />
                        </div>
                        <div v-if="form.errors.email" class="form-error">{{ form.errors.email }}</div>
                    </div>
                    
                    <!-- Mot de passe -->
                    <div class="form-group">
                        <div class="password-header">
                            <label for="password">Mot de passe</label>
                            <button type="button" @click="showPassword = !showPassword" class="toggle-visibility">
                                {{ showPassword ? 'Masquer' : 'Afficher' }}
                            </button>
                        </div>
                        <div class="input-container">
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <input 
                                id="password" 
                                :type="showPassword ? 'text' : 'password'" 
                                v-model="form.password" 
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                            />
                        </div>
                        <div v-if="form.errors.password" class="form-error">{{ form.errors.password }}</div>
                    </div>
                    
                    <!-- Mémoriser et mot de passe oublié -->
                    <div class="form-options">
                        <label class="remember-label">
                            <input type="checkbox" v-model="rememberMe" />
                            <span>Se souvenir de moi</span>
                        </label>
                        <Link href="/forgot-password" class="forgot-password">Mot de passe oublié?</Link>
                    </div>
                    
                    <!-- Bouton de connexion -->
                    <button 
                        type="submit" 
                        class="login-button"
                        :class="{ 'processing': form.processing }"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">
                            <svg class="spinner" viewBox="0 0 24 24">
                                <circle class="spinner-path" cx="12" cy="12" r="10" fill="none" stroke-width="3"></circle>
                            </svg>
                            Connexion en cours...
                        </span>
                        <span v-else>Se connecter</span>
                    </button>
                </form>
                
                <!-- Séparateur -->
                
            </div>
        </div>
        
        <!-- Footer avec informations -->
        <footer class="footer">
            <div class="stats">
                <!--
                <div class="stat-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"></path>
                    </svg>
                    <span>4.7 étoiles</span>
                </div>
                
                <div class="stat-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span>6000+ organisations</span>
                </div>-->
                <div class="stat-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                        <line x1="1" y1="10" x2="23" y2="10"></line>
                    </svg>
                    <span>Paiements sécurisés</span>
                </div>
            </div>
            
            <div class="copyright">
                &copy; {{ new Date().getFullYear() }} EagleEvent. Tous droits réservés.
            </div>
        </footer>
    </div>
</template>

<style scoped>
.login-page {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
    font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    color: #fff;
}

/* Image de fond et overlay */
.background-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('/images/event.jpg');
    background-size: cover;
    background-position: center;
    z-index: -2;
}

.overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.85) 0%, rgba(0, 0, 0, 0.6) 100%);
    z-index: -1;
}

/* Conteneur principal */
.content-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
}

@media (min-width: 768px) {
    .content-container {
        flex-direction: row;
        align-items: center;
        gap: 4rem;
        padding: 2rem;
    }
}

/* Logo et tagline */
.logo-container {
    text-align: center;
    margin-bottom: 2rem;
}

.logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    color: #fff;
}

.logo h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
}

.tagline {
    font-size: 1.25rem;
    font-weight: 300;
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
}

@media (min-width: 768px) {
    .logo-container {
        margin-bottom: 0;
        text-align: left;
        max-width: 40%;
    }
    
    .logo {
        justify-content: flex-start;
    }
    
    .logo h1 {
        font-size: 3rem;
    }
    
    .tagline {
        font-size: 1.5rem;
    }
}

/* Formulaire de connexion */
.login-container {
    background-color: rgba(255, 255, 255, 0.95);
    border-radius: 1rem;
    padding: 2rem;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    color: #333;
}

.login-container h2 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    font-size: 1.75rem;
    font-weight: 600;
    color: #111827;
    text-align: center;
}

/* Groupes de formulaire */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #4B5563;
    font-size: 0.875rem;
}

.input-container {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    width: 1.25rem;
    height: 1.25rem;
    color: #9CA3AF;
}

.input-container input {
    width: 100%;
    padding: 0.875rem 1rem 0.875rem 3rem;
    border: 1px solid #D1D5DB;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: all 0.2s;
    box-sizing: border-box;
}

.input-container input:focus {
    outline: none;
    border-color: #4F46E5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.password-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.toggle-visibility {
    background: none;
    border: none;
    font-size: 0.75rem;
    font-weight: 500;
    color: #4F46E5;
    cursor: pointer;
}

.toggle-visibility:hover {
    text-decoration: underline;
}

.form-error {
    color: #DC2626;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

/* Options du formulaire */
.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.remember-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.remember-label input[type="checkbox"] {
    width: 1rem;
    height: 1rem;
    border-radius: 0.25rem;
    border: 1px solid #D1D5DB;
    accent-color: #4F46E5;
}

.remember-label span {
    font-size: 0.875rem;
    color: #6B7280;
}

.forgot-password {
    font-size: 0.875rem;
    color: #4F46E5;
    text-decoration: none;
}

.forgot-password:hover {
    text-decoration: underline;
}

/* Bouton de connexion */
.login-button {
    width: 100%;
    background-color: #4F46E5;
    color: white;
    border: none;
    border-radius: 0.5rem;
    padding: 0.875rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s;
    display: flex;
    justify-content: center;
    align-items: center;
}

.login-button:hover {
    background-color: #4338CA;
}

.login-button.processing {
    background-color: #818CF8;
    cursor: not-allowed;
}

.spinner {
    animation: spin 1s linear infinite;
    width: 1.25rem;
    height: 1.25rem;
    margin-right: 0.5rem;
}

.spinner-path {
    stroke: #ffffff;
    stroke-linecap: round;
    animation: dash 1.5s ease-in-out infinite;
}

@keyframes spin {
    100% {
        transform: rotate(360deg);
    }
}

@keyframes dash {
    0% {
        stroke-dasharray: 1, 150;
        stroke-dashoffset: 0;
    }
    50% {
        stroke-dasharray: 90, 150;
        stroke-dashoffset: -35;
    }
    100% {
        stroke-dasharray: 90, 150;
        stroke-dashoffset: -124;
    }
}

/* Séparateur */
.divider {
    position: relative;
    text-align: center;
    margin: 1.5rem 0;
}

.divider::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background-color: #E5E7EB;
}

.divider span {
    position: relative;
    padding: 0 1rem;
    background-color: white;
    color: #6B7280;
    font-size: 0.875rem;
}

/* Connexion sociale */
.social-login {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.social-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    background-color: white;
    border: 1px solid #D1D5DB;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: background-color 0.2s;
}

.social-button:hover {
    background-color: #F9FAFB;
}

.social-button svg {
    flex-shrink: 0;
}

/* Lien d'inscription */
.register-link {
    margin-top: 1.5rem;
    text-align: center;
    font-size: 0.875rem;
    color: #6B7280;
}

.register-link a {
    color: #4F46E5;
    font-weight: 500;
    text-decoration: none;
}

.register-link a:hover {
    text-decoration: underline;
}

/* Footer */
.footer {
    text-align: center;
    padding: 1.5rem;
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.875rem;
    margin-top: auto;
}

.stats {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-bottom: 1rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.copyright {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.6);
}
</style>