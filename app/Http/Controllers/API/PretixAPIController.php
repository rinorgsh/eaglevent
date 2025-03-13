<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\PretixService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PretixAPIController extends Controller
{
    protected $pretixService;
    
    public function __construct(PretixService $pretixService)
    {
        $this->pretixService = $pretixService;
    }
    
    /**
     * Activer ou désactiver la billetterie en ligne (shop) pour un événement
     */
    public function toggleShopStatus(Request $request, $eventSlug)
    {
        // Valider la requête
        $validated = $request->validate([
            'active' => 'required|boolean',
        ]);
        
        try {
            // Récupérer l'événement actuel pour vérifier ses sales_channels
            $event = $this->pretixService->getEvent($eventSlug);
            
            // Vérifier les canaux de vente actuels
            $currentSalesChannels = $event['sales_channels'] ?? [];
            $hasWebChannel = in_array('web', $currentSalesChannels);
            
            // Si l'état demandé est déjà l'état actuel, on répond directement
            if ($hasWebChannel === $validated['active']) {
                return response()->json([
                    'success' => true,
                    'message' => 'La billetterie en ligne est déjà dans l\'état demandé',
                    'shop_active' => $hasWebChannel
                ]);
            }
            
            // Préparation des données de mise à jour
            $newSalesChannels = $validated['active']
                ? array_unique(array_merge($currentSalesChannels, ['web'])) // Ajouter web
                : array_diff($currentSalesChannels, ['web']); // Retirer web
            
            // Mise à jour de l'événement
            $updateData = [
                'sales_channels' => $newSalesChannels
            ];
            
            // Appel à l'API Pretix
            $response = $this->pretixService->updateEvent($eventSlug, $updateData);
            
            // Log pour débogage
            Log::info('Toggle shop status response', [
                'event_slug' => $eventSlug, 
                'active' => $validated['active'],
                'sales_channels' => $newSalesChannels,
                'response' => $response
            ]);
            
            // Vérifier si la mise à jour a réussi
            if (isset($response['error'])) {
                throw new \Exception($response['error']);
            }
            
            return response()->json([
                'success' => true,
                'message' => $validated['active'] 
                    ? 'La billetterie en ligne est maintenant active'
                    : 'La billetterie en ligne est maintenant désactivée',
                'shop_active' => $validated['active'],
                'sales_channels' => $newSalesChannels,
                'data' => $response
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error toggling shop status', [
                'event_slug' => $eventSlug,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ], 500);
        }
    }
}