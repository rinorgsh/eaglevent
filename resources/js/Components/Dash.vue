<template>

    <!-- Première ligne -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- Résultat -->
                    <div class="bg-blue-500 text-white rounded-lg p-4 flex flex-col justify-between shadow-md">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium uppercase">RÉSULTAT</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z" />
                                <path d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h8a2 2 0 00-2-2H5z" />
                            </svg>
                        </div>
                        <div class="text-2xl font-bold mt-2">0,00 €</div>
                    </div>
                    
                    <!-- Revenus -->
                    <div class="bg-white rounded-lg p-4 flex flex-col justify-between shadow-md border border-gray-200">
                        <div class="text-sm font-medium uppercase text-gray-600">REVENUS</div>
                        <div class="text-2xl font-bold mt-2 text-gray-800">0,00 €</div>
                    </div>
                    
                    <!-- Remboursements -->
                    <div class="bg-white rounded-lg p-4 flex flex-col justify-between shadow-md border border-gray-200">
                        <div class="text-sm font-medium uppercase text-gray-600">REMBOURSEMENTS</div>
                        <div class="text-2xl font-bold mt-2 text-gray-800">0,00 €</div>
                    </div>
                </div>
                
                <!-- Deuxième ligne -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Billets -->
                    <div class="bg-white rounded-lg p-4 shadow-md border border-gray-200">
                        <div class="text-sm font-medium uppercase text-blue-600 mb-2">BILLETS</div>
                        <div class="text-2xl font-bold mb-4 text-gray-800">0</div>
                        <div class="flex justify-between border-t border-gray-200 pt-4">
                            <div>
                                <div class="text-sm text-gray-500 flex items-center">
                                    <span class="w-2 h-2 rounded-full bg-blue-500 mr-2"></span>
                                    PAYANT
                                </div>
                                <div class="text-lg font-bold mt-1 text-gray-800">0</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500 flex items-center">
                                    <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                                    GRATUIT
                                </div>
                                <div class="text-lg font-bold mt-1 text-gray-800">0</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Extras -->
                    <div class="bg-white rounded-lg p-4 shadow-md border border-gray-200">
                        <div class="text-sm font-medium uppercase text-blue-600 mb-2">EXTRAS</div>
                        <div class="text-2xl font-bold mb-4 text-gray-800">0</div>
                        <div class="flex justify-between border-t border-gray-200 pt-4">
                            <div>
                                <div class="text-sm text-gray-500 flex items-center">
                                    <span class="w-2 h-2 rounded-full bg-blue-500 mr-2"></span>
                                    PAYANT
                                </div>
                                <div class="text-lg font-bold mt-1 text-gray-800">0</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500 flex items-center">
                                    <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                                    GRATUIT
                                </div>
                                <div class="text-lg font-bold mt-1 text-gray-800">0</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Commandes -->
                    <div class="bg-white rounded-lg p-4 shadow-md border border-gray-200">
                        <div class="text-sm font-medium uppercase text-blue-600 mb-2">COMMANDES</div>
                        <div class="text-2xl font-bold text-gray-800">0</div>
                    </div>
                </div>
                
                <!-- Troisième ligne -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <!-- Participants -->
                    <div class="bg-white rounded-lg p-4 shadow-md border border-gray-200">
                        <div class="text-sm font-medium uppercase text-blue-600 mb-2">PARTICIPANTS</div>
                        <div class="text-2xl font-bold text-gray-800">0</div>
                    </div>
                    
                    <!-- Clients -->
                    <div class="bg-white rounded-lg p-4 shadow-md border border-gray-200">
                        <div class="text-sm font-medium uppercase text-blue-600 mb-2">CLIENTS</div>
                        <div class="text-2xl font-bold text-gray-800">0</div>
                    </div>
                    
                    <!-- En ligne -->
                    <div class="bg-white rounded-lg p-4 shadow-md border border-gray-200">
                        <div class="text-sm font-medium uppercase text-blue-600 mb-2">EN LIGNE</div>
                        <div class="text-2xl font-bold text-gray-800">0</div>
                    </div>
                </div>
</template>