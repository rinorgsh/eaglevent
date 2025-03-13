<!-- Layout.vue (composant parent) -->
<template>
    <div class="app-container">
      <!-- Bouton hamburger pour mobile -->
      <button class="hamburger-menu" @click="toggleMobileSidebar" aria-label="Menu">
        <i class="bi bi-list"></i>
      </button>
      
      <!-- Overlay pour mobile -->
      <div 
        v-if="mobileSidebarOpen" 
        class="sidebar-overlay" 
        @click="closeMobileSidebar"
      ></div>
  
      <!-- Sidebar -->
      <div 
        class="sidebar" 
        :class="{ 
          'sidebar-collapsed': sidebarCollapsed && !mobileSidebarOpen, 
          'sidebar-mobile-open': mobileSidebarOpen 
        }"
      >
        <div class="logo-container">
          <div class="logo" v-if="!sidebarCollapsed || mobileSidebarOpen">Admin Panel</div>
          <button class="toggle-btn" @click="toggleSidebar">
            <i class="bi" :class="sidebarCollapsed && !mobileSidebarOpen ? 'bi-chevron-right' : 'bi-chevron-left'"></i>
          </button>
          <!-- Bouton pour fermer sur mobile -->
          <button class="close-mobile-menu" @click="closeMobileSidebar">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        
        <div class="sidebar-menu">
          <a href="#" class="menu-item" :class="{ active: activeMenu === 'dashboard' }" @click.prevent="setActiveMenu('dashboard')">
            <span class="menu-icon"><i class="bi bi-speedometer2"></i></span>
            <span class="menu-text" v-if="!sidebarCollapsed || mobileSidebarOpen">Tableau de Bord</span>
          </a>
          <a href="#" class="menu-item" :class="{ active: activeMenu === 'users' }" @click.prevent="setActiveMenu('users')">
            <span class="menu-icon"><i class="bi bi-people"></i></span>
            <span class="menu-text" v-if="!sidebarCollapsed || mobileSidebarOpen">Utilisateurs</span>
          </a>
          <a href="#" class="menu-item" :class="{ active: activeMenu === 'products' }" @click.prevent="setActiveMenu('products')">
            <span class="menu-icon"><i class="bi bi-box"></i></span>
            <span class="menu-text" v-if="!sidebarCollapsed || mobileSidebarOpen">Produits</span>
          </a>
          <a href="#" class="menu-item" :class="{ active: activeMenu === 'orders' }" @click.prevent="setActiveMenu('orders')">
            <span class="menu-icon"><i class="bi bi-cart"></i></span>
            <span class="menu-text" v-if="!sidebarCollapsed || mobileSidebarOpen">Commandes</span>
          </a>
          <a href="#" class="menu-item" :class="{ active: activeMenu === 'settings' }" @click.prevent="setActiveMenu('settings')">
            <span class="menu-icon"><i class="bi bi-gear"></i></span>
            <span class="menu-text" v-if="!sidebarCollapsed || mobileSidebarOpen">Paramètres</span>
          </a>
        </div>
      </div>
  
      <!-- Avatar/Profile Menu -->
      <div class="floating-profile" @click="toggleProfileMenu">
        <div class="avatar">AD</div>
        
        <!-- Menu profil déroulant -->
        <div v-show="showProfileMenu" class="profile-menu">
          <div class="profile-info">
            <div class="profile-circle large">AD</div>
            <p class="profile-name">Admin Demo</p>
            <p class="profile-role">Administrateur</p>
          </div>
          <hr>
          <a href="#" class="menu-link">
            <i class="bi bi-person"></i>
            <span>Mon profil</span>
          </a>
          <a href="#" class="menu-link">
            <i class="bi bi-gear"></i>
            <span>Paramètres</span>
          </a>
          <button class="menu-link logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Déconnexion</span>
          </button>
        </div>
      </div>
  
      <!-- Contenu principal -->
      <div class="content-wrapper" :class="{ 'content-collapsed': sidebarCollapsed && !mobileSidebarOpen }">
        <div class="main-content">
          <!-- Dashboard Content -->
          <div v-if="activeMenu === 'dashboard'">
            <h1 class="page-title">Tableau de Bord</h1>
            
            <!-- Stats Cards -->
            <div class="stats-container">
              <div class="stat-card" v-for="(stat, index) in stats" :key="index">
                <div class="stat-header">
                  <div class="stat-title">{{ stat.title }}</div>
                  <div class="stat-icon" :class="stat.bgClass">
                    <i :class="stat.icon"></i>
                  </div>
                </div>
                <div class="stat-value">{{ stat.value }}</div>
                <div class="stat-change" :class="stat.change > 0 ? 'positive-change' : 'negative-change'">
                  <i class="bi" :class="stat.change > 0 ? 'bi-arrow-up' : 'bi-arrow-down'"></i>
                  {{ Math.abs(stat.change) }}% depuis le mois dernier
                </div>
              </div>
            </div>
            
            <!-- Charts -->
            <div class="chart-container">
              <div class="chart-card">
                <div class="chart-header">
                  <div class="chart-title">Évolution des Ventes</div>
                  <div class="chart-actions">
                    <button class="chart-btn">Semaine</button>
                    <button class="chart-btn">Mois</button>
                    <button class="chart-btn active">Année</button>
                  </div>
                </div>
                <div class="chart-content">
                  <LineChart :data="revenueData" :options="lineChartOptions" />
                </div>
              </div>
              
              <div class="chart-card">
                <div class="chart-header">
                  <div class="chart-title">Répartition des Produits</div>
                  <div class="chart-actions">
                    <button class="chart-btn">Exporter</button>
                  </div>
                </div>
                <div class="chart-content">
                  <DoughnutChart :data="statusData" :options="doughnutChartOptions" />
                </div>
              </div>
            </div>
            
            <!-- Table -->
            <div class="table-container">
              <div class="table-header">
                <div class="chart-title">Commandes Récentes</div>
                <div class="chart-actions">
                  <a href="#" class="chart-btn">Voir tout</a>
                </div>
              </div>
              
              <div class="table-responsive">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Référence</th>
                      <th>Client</th>
                      <th>Date</th>
                      <th>Montant</th>
                      <th>Statut</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(order, index) in recentOrders" :key="index">
                      <td>{{ order.reference }}</td>
                      <td>{{ order.client }}</td>
                      <td>{{ order.date }}</td>
                      <td>{{ order.amount }}€</td>
                      <td>
                        <div class="status" :class="order.statusClass">
                          {{ order.status }}
                        </div>
                      </td>
                      <td>
                        <button class="action-btn"><i class="bi bi-eye"></i></button>
                        <button class="action-btn"><i class="bi bi-pencil"></i></button>
                        <button class="action-btn"><i class="bi bi-download"></i></button>
                        <button class="action-btn"><i class="bi bi-envelope"></i></button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              
              <div class="pagination">
                <div class="page-btn"><i class="bi bi-chevron-left"></i></div>
                <div class="page-btn active">1</div>
                <div class="page-btn">2</div>
                <div class="page-btn">3</div>
                <div class="page-btn"><i class="bi bi-chevron-right"></i></div>
              </div>
            </div>
          </div>
          
          <!-- Placeholder pour les autres menus -->
          <div v-else class="placeholder-content">
            <h2 class="page-title">{{ getMenuTitle() }}</h2>
            <div class="placeholder-center">
              <i class="bi bi-code-slash"></i>
              <p>Contenu de {{ getMenuTitle() }} à implémenter</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted, onUnmounted } from 'vue';
  import { Chart as ChartJS, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement, Title } from 'chart.js';
  import { Line as LineChart, Doughnut as DoughnutChart } from 'vue-chartjs';
  
  // État du menu
  const activeMenu = ref('dashboard');
  const sidebarCollapsed = ref(false);
  const mobileSidebarOpen = ref(false);
  const showProfileMenu = ref(false);
  
  // Fonctions pour le menu
  const setActiveMenu = (menu) => {
    activeMenu.value = menu;
    if (window.innerWidth <= 1024) {
      closeMobileSidebar();
    }
  };
  
  const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
  };
  
  const toggleMobileSidebar = () => {
    mobileSidebarOpen.value = !mobileSidebarOpen.value;
    if (mobileSidebarOpen.value) {
      document.body.classList.add('no-scroll');
    } else {
      document.body.classList.remove('no-scroll');
    }
  };
  
  const closeMobileSidebar = () => {
    mobileSidebarOpen.value = false;
    document.body.classList.remove('no-scroll');
  };
  
  const toggleProfileMenu = () => {
    showProfileMenu.value = !showProfileMenu.value;
  };
  
  const closeProfileMenu = (e) => {
    const profileElement = document.querySelector('.floating-profile');
    if (profileElement && !profileElement.contains(e.target)) {
      showProfileMenu.value = false;
    }
  };
  
  const getMenuTitle = () => {
    switch(activeMenu.value) {
      case 'dashboard': return 'Tableau de Bord';
      case 'users': return 'Utilisateurs';
      case 'products': return 'Produits';
      case 'orders': return 'Commandes';
      case 'settings': return 'Paramètres';
      default: return '';
    }
  };
  
  // Gestion des événements
  const handleResize = () => {
    if (window.innerWidth > 1024 && mobileSidebarOpen.value) {
      closeMobileSidebar();
    }
  };
  
  onMounted(() => {
    document.addEventListener('click', closeProfileMenu);
    window.addEventListener('resize', handleResize);
  });
  
  onUnmounted(() => {
    document.removeEventListener('click', closeProfileMenu);
    window.removeEventListener('resize', handleResize);
  });
  
  // Enregistrer les composants Chart.js nécessaires
  ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement, Title);
  
  // Données pour les statistiques
  const stats = [
    { 
      title: 'Total Commandes', 
      value: '856', 
      change: 12.5,
      icon: 'bi bi-cart',
      bgClass: 'bg-primary' 
    },
    { 
      title: 'Montant Total', 
      value: '57 893 €', 
      change: 8.2, 
      icon: 'bi bi-currency-euro', 
      bgClass: 'bg-success' 
    },
    { 
      title: 'Nouveaux Clients', 
      value: '385', 
      change: 5.7, 
      icon: 'bi bi-people', 
      bgClass: 'bg-warning' 
    },
    { 
      title: 'Commandes en Attente', 
      value: '45', 
      change: -3.8, 
      icon: 'bi bi-clock', 
      bgClass: 'bg-danger' 
    },
  ];
  
  // Données pour les graphiques
  const revenueData = ref({
    labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
    datasets: [
      {
        label: 'Montant total (€)',
        data: [4500, 5200, 6800, 7300, 6500, 7800, 8200, 8500, 9100, 9800, 10500, 11200],
        borderColor: '#4a90e2',
        backgroundColor: 'rgba(74, 144, 226, 0.2)',
        tension: 0.4,
        fill: true
      }
    ]
  });
  
  // Données pour le graphique en anneau (répartition des statuts)
  const statusData = ref({
    labels: ['Livrés', 'En cours', 'Annulés'],
    datasets: [
      {
        data: [65, 25, 10],
        backgroundColor: ['#42b983', '#f1c40f', '#f44336'],
        borderWidth: 0
      }
    ]
  });
  
  // Options pour les graphiques
  const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: false
      }
    },
    scales: {
      y: {
        beginAtZero: true
      }
    }
  };
  
  const doughnutChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '70%'
  };
  
  // Données pour les commandes récentes
  const recentOrders = [
    {
      reference: 'CMD-2023-001',
      client: 'Jean Dupont',
      date: '12/03/2023',
      amount: '1 299,99',
      status: 'Livré',
      statusClass: 'status-completed'
    },
    {
      reference: 'CMD-2023-002',
      client: 'Marie Martin',
      date: '10/03/2023',
      amount: '899,50',
      status: 'En cours',
      statusClass: 'status-pending'
    },
    {
      reference: 'CMD-2023-003',
      client: 'Thomas Bernard',
      date: '08/03/2023',
      amount: '249,99',
      status: 'Livré',
      statusClass: 'status-completed'
    },
    {
      reference: 'CMD-2023-004',
      client: 'Sophie Dubois',
      date: '05/03/2023',
      amount: '1 799,00',
      status: 'Annulé',
      statusClass: 'status-cancelled'
    },
    {
      reference: 'CMD-2023-005',
      client: 'Lucas Petit',
      date: '03/03/2023',
      amount: '349,90',
      status: 'Livré',
      statusClass: 'status-completed'
    }
  ];
  </script>
  
  <style>
  :root {
    --primary-color: #1976d2;
    --secondary-color: #e0e0e0;
    --accent-color: #42b983;
    --danger-color: #f44336;
    --warning-color: #ff9800;
    --text-color: #333;
    --light-text: #757575;
    --border-color: #e0e0e0;
    --bg-color: #f8fafc;
    --card-bg: #ffffff;
    --sidebar-width: 280px;
    --sidebar-collapsed-width: 80px;
    --gradient-sidebar: linear-gradient(135deg, #2d65ff 0%, #2020da 100%);
    --gradient-profile: linear-gradient(135deg, #040083 0%, #6f6f96 100%);
    --main-padding: 1.5rem;
    --border-radius: 16px;
    --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  }
  
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  
  body {
    background-color: var(--bg-color);
    color: var(--text-color);
  }
  
  body.no-scroll {
    overflow: hidden;
  }
  
  .app-container {
    display: flex;
    min-height: 100vh;
    padding: var(--main-padding);
    gap: var(--main-padding);
    position: relative;
  }
  
  /* Hamburger Menu */
  .hamburger-menu {
    display: none;
    position: fixed;
    top: 1rem;
    left: 1rem;
    z-index: 1000;
    background: var(--gradient-sidebar);
    color: white;
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    font-size: 1.5rem;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease;
  }
  
  .hamburger-menu:hover {
    transform: scale(1.05);
  }
  
  .sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 90;
    animation: fadeIn 0.3s ease;
  }
  
  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }
  
  /* Close mobile menu button */
  .close-mobile-menu {
    display: none;
    background: none;
    border: none;
    color: white;
    font-size: 1.25rem;
    cursor: pointer;
    padding: 5px;
  }
  
  /* Sidebar */
  .sidebar {
    width: var(--sidebar-width);
    background: var(--gradient-sidebar);
    border-radius: var(--border-radius);
    height: calc(100vh - 3rem);
    position: sticky;
    top: var(--main-padding);
    box-shadow: var(--shadow);
    z-index: 100;
    transition: all 0.3s ease;
    overflow: hidden;
    display: flex;
    flex-direction: column;
  }
  
  .sidebar-collapsed {
    width: var(--sidebar-collapsed-width);
  }
  
  .logo-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    height: 70px;
  }
  
  .logo {
    font-size: 22px;
    font-weight: bold;
    color: white;
  }
  
  .toggle-btn {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    font-size: 18px;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    transition: all 0.2s ease;
  }
  
  .toggle-btn:hover {
    background-color: rgba(255, 255, 255, 0.2);
  }
  
  .sidebar-menu {
    padding: 20px 15px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex-grow: 1;
    overflow-y: auto;
  }
  
  .menu-item {
    padding: 14px 18px;
    display: flex;
    align-items: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: white;
    text-decoration: none;
    border-radius: 12px;
  }
  
  .menu-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
  }
  
  .menu-item.active {
    background-color: rgba(255, 255, 255, 0.2);
    font-weight: 600;
  }
  
  .menu-icon {
    margin-right: 15px;
    width: 22px;
    text-align: center;
    font-size: 18px;
  }
  
  .menu-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 15px;
  }
  
  /* Content wrapper */
  .content-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: var(--main-padding);
    margin-left: 0;
    transition: all 0.3s ease;
    margin-top: 20px;
  }
  
  .content-collapsed {
    margin-left: 0;
  }
  
  /* Avatar flottant */
  .floating-profile {
    position: fixed;
    top: 1.5rem;
    right: 1.5rem;
    z-index: 1000;
    cursor: pointer;
  }
  
  .floating-profile .avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: var(--gradient-profile);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    transition: transform 0.2s ease;
  }
  
  .floating-profile .avatar:hover {
    transform: scale(1.05);
  }
  
  /* Main content */
  .main-content {
    background-color: var(--card-bg);
    border-radius: var(--border-radius);
    padding: 25px;
    box-shadow: var(--shadow);
    overflow-y: auto;
    flex: 1;
  }
  
  /* Profile menu */
  .profile-menu {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 260px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    animation: menuFade 0.2s ease;
    overflow: hidden;
  }
  
  @keyframes menuFade {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  .profile-info {
    padding: 1.8rem;
    text-align: center;
    background: linear-gradient(to bottom, #f8fafc, white);
  }
  
  .profile-circle {
    width: 55px;
    height: 55px;
    background: var(--gradient-profile);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1rem;
    border: 2px solid transparent;
    transition: all 0.3s ease;
  }
  
  .profile-circle.large {
    width: 70px;
    height: 70px;
    font-size: 1.6rem;
    margin: 0 auto 1.2rem;
  }
  
  .profile-name {
    font-weight: 600;
    margin: 0;
    color: #1e293b;
    font-size: 1.1rem;
  }
  
  .profile-role {
    color: #040083;
    font-size: 0.9rem;
    margin: 0.25rem 0 0 0;
    font-weight: 500;
  }
  
  .menu-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.9rem 1.5rem;
    color: #1e293b;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    font-size: 1rem;
  }
  
  .menu-link:hover {
    background: #f8fafc;
    color: #040083;
  }
  
  .menu-link.logout {
    color: #dc2626;
  }
  
  .menu-link.logout:hover {
    background: #fef2f2;
    color: #dc2626;
  }
  
  .menu-link i {
    width: 22px;
    text-align: center;
    font-size: 1.1rem;
  }
  
  hr {
    border: none;
    border-top: 1px solid #e2e8f0;
    margin: 0;
  }
  
  /* Dashboard Styles */
  .page-title {
    margin-bottom: 20px;
    font-size: 24px;
    font-weight: 500;
  }
  
  .stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
  }
  
  .stat-card {
    background-color: var(--card-bg);
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    display: flex;
    flex-direction: column;
  }
  
  .stat-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
  }
  
  .stat-title {
    color: var(--light-text);
    font-size: 14px;
  }
  
  .stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
  }
  
  .bg-primary {
    background-color: var(--primary-color);
  }
  
  .bg-success {
    background-color: var(--accent-color);
  }
  
  .bg-warning {
    background-color: var(--warning-color);
  }
  
  .bg-danger {
    background-color: var(--danger-color);
  }
  
  .stat-value {
    font-size: 24px;
    font-weight: bold;
    margin: 5px 0;
  }
  
  .stat-change {
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 5px;
  }
  
  .positive-change {
    color: var(--accent-color);
  }
  
  .negative-change {
    color: var(--danger-color);
  }
  
  .chart-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
  }
  
  .chart-card {
    background-color: var(--card-bg);
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    height: 350px;
  }
  
  .chart-content {
    height: 280px;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  
  .chart-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
  }
  
  .chart-title {
    font-size: 16px;
    font-weight: 500;
  }
  
  .chart-actions {
    display: flex;
    gap: 10px;
  }
  
  .chart-btn {
    background: none;
    border: none;
    color: var(--light-text);
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
  }
  
  .chart-btn.active {
    color: var(--primary-color);
    font-weight: 500;
  }
  
  .chart-btn:hover {
    color: var(--primary-color);
  }
  
  .table-container {
    background-color: var(--card-bg);
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  }
  
  .table-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
  }
  
  .table-responsive {
    overflow-x: auto;
  }
  
  .data-table {
    width: 100%;
    border-collapse: collapse;
  }
  
  .data-table th,
  .data-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
  }
  
  .data-table th {
    font-weight: 500;
    color: var(--light-text);
  }
  
  .data-table tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.01);
  }
  
  .status {
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 500;
    display: inline-block;
  }
  
  .status-completed {
    background-color: rgba(66, 185, 131, 0.1);
    color: var(--accent-color);
  }
  
  .status-pending {
    background-color: rgba(255, 152, 0, 0.1);
    color: var(--warning-color);
  }
  
  .status-cancelled {
    background-color: rgba(244, 67, 54, 0.1);
    color: var(--danger-color);
  }
  
  .action-btn {
    background: none;
    border: none;
    color: var(--light-text);
    cursor: pointer;
    margin-right: 5px;
  }
  
  .action-btn:hover {
    color: var(--primary-color);
  }
  
  .pagination {
    display: flex;
    justify-content: flex-end;
    margin-top: 15px;
    gap: 5px;
  }
  
  .page-btn {
    width: 30px;
    height: 30px;
    border: 1px solid var(--border-color);
    background: var(--card-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border-radius: 4px;
  }
  
  .page-btn.active {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
  }
  
  .chart-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--light-text);
    background-color: rgba(0, 0, 0, 0.02);
    border-radius: 8px;
  }
  
  .chart-placeholder i {
    font-size: 3rem;
    margin-bottom: 1rem;
  }
  
</style>