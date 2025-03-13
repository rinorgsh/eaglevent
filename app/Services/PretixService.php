<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PretixService
{
    protected $apiUrl;
    protected $apiKey;
    protected $organizer;
    
    public function __construct()
    {
        $user = Auth::user();
        
        if ($user && $user->pretixConfiguration && $user->pretixConfiguration->active) {
            // Utiliser les identifiants configurés pour cet utilisateur
            $this->apiUrl = $user->pretixConfiguration->api_url;
            $this->apiKey = $user->pretixConfiguration->api_key;
            $this->organizer = $user->pretixConfiguration->organizer;
        } else {
            // Fallback sur la configuration par défaut (pour admin ou tests)
            $this->apiUrl = config('services.pretix.url');
            $this->apiKey = config('services.pretix.key');
            $this->organizer = config('services.pretix.organizer');
        }
    }
    
    /**
     * Méthode générique pour faire des requêtes vers l'API Pretix
     */
    private function request($method, $endpoint, $data = [])
    {
        $url = $this->apiUrl . $endpoint;
        
        Log::info('Pretix API Request', [
            'url' => $url,
            'method' => $method,
            'user_id' => Auth::id()
        ]);
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])
            ->$method($url, $data);
            
            Log::info('Pretix API Response', [
                'status' => $response->status(),
                'user_id' => Auth::id()
            ]);
            
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Pretix API Error', [
                'message' => $e->getMessage(),
                'url' => $url,
                'method' => $method,
                'user_id' => Auth::id()
            ]);
            
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Récupérer la liste des événements
     */
    public function getEvents()
    {
        return $this->request('get', 'organizers/' . $this->organizer . '/events/');
    }
    
    /**
     * Récupérer les détails d'un événement
     */
    public function getEvent($eventSlug)
    {
        return $this->request('get', 'organizers/' . $this->organizer . '/events/' . $eventSlug . '/');
    }
    
    /**
     * Récupérer les commandes d'un événement
     */
    public function getOrders($eventSlug)
    {
        return $this->request('get', 'organizers/' . $this->organizer . '/events/' . $eventSlug . '/orders/');
    }
    
    /**
     * Récupérer les détails d'une commande spécifique
     */
    public function getOrderDetails($eventSlug, $orderCode)
    {
        return $this->request('get', 'organizers/' . $this->organizer . '/events/' . $eventSlug . '/orders/' . $orderCode . '/');
    }
    
    /**
     * Récupérer les positions (billets) d'une commande
     */
    public function getOrderPositions($eventSlug, $orderCode)
    {
        return $this->request('get', 'organizers/' . $this->organizer . '/events/' . $eventSlug . '/orders/' . $orderCode . '/positions/');
    }
    public function getOrganizer()
    {
        return $this->organizer;
    }
    public function getApiUrl()
    {
        return $this->apiUrl;
    }
    public function createEvent(array $eventData)
    {
        return $this->request('post', 'organizers/' . $this->organizer . '/events/', $eventData);
    }




/**
     * Récupérer tous les tickets (items) d'un événement
     */
    public function getItems($eventSlug)
    {
        return $this->request('get', 'organizers/' . $this->organizer . '/events/' . $eventSlug . '/items/');
    }
    
    /**
     * Récupérer les quotas disponibles pour un événement
     */
    public function getQuotas($eventSlug)
    {
        return $this->request('get', 'organizers/' . $this->organizer . '/events/' . $eventSlug . '/quotas/');
    }
    
    /**
     * Créer un nouveau ticket (item) pour un événement
     */
    public function createTicket($eventSlug, array $ticketData)
    {
        return $this->request('post', 'organizers/' . $this->organizer . '/events/' . $eventSlug . '/items/', $ticketData);
    }
    
    /**
     * Mettre à jour un ticket existant
     */
    public function updateTicket($eventSlug, $ticketId, array $ticketData)
    {
        return $this->request('patch', 'organizers/' . $this->organizer . '/events/' . $eventSlug . '/items/' . $ticketId . '/', $ticketData);
    }
    
    /**
     * Supprimer un ticket
     */
    public function deleteTicket($eventSlug, $ticketId)
    {
        return $this->request('delete', 'organizers/' . $this->organizer . '/events/' . $eventSlug . '/items/' . $ticketId . '/');
    }
    /**
 * Créer un nouveau quota pour un événement
 */
public function createQuota($eventSlug, array $quotaData)
{
    return $this->request('post', 'organizers/' . $this->organizer . '/events/' . $eventSlug . '/quotas/', $quotaData);
}

/**
 * Mettre à jour un quota existant
 */
public function updateQuota($eventSlug, $quotaId, array $quotaData)
{
    return $this->request('patch', 'organizers/' . $this->organizer . '/events/' . $eventSlug . '/quotas/' . $quotaId . '/', $quotaData);
}

/**
 * Supprimer un quota
 */
public function deleteQuota($eventSlug, $quotaId)
{
    return $this->request('delete', 'organizers/' . $this->organizer . '/events/' . $eventSlug . '/quotas/' . $quotaId . '/');
}
/**
 * Ajoutez cette méthode à votre PretixService
 */
public function updateEventSettings($eventSlug, array $settings)
{
    return $this->request('patch', 'organizers/' . $this->organizer . '/events/' . $eventSlug . '/settings/', $settings);
}


/**
 * Mettre à jour un événement existant
 */
public function updateEvent($eventSlug, array $eventData)
{
    return $this->request('patch', 'organizers/' . $this->organizer . '/events/' . $eventSlug . '/', $eventData);
}

/**
 * Récupérer les listes de check-in d'un événement
 */
public function getCheckinLists($eventSlug)
{
    return $this->request('get', 'organizers/' . $this->organizer . '/events/' . $eventSlug . '/checkinlists/');
}

/**
 * Récupérer les détails d'une liste de check-in spécifique
 */
public function getCheckinListDetails($eventSlug, $listId)
{
    return $this->request('get', 'organizers/' . $this->organizer . '/events/' . $eventSlug . '/checkinlists/' . $listId . '/');
}

/**
 * Récupérer les check-ins effectués pour une liste de check-in
 */
public function getCheckins($eventSlug, $listId)
{
    return $this->request('get', 'organizers/' . $this->organizer . '/events/' . $eventSlug . '/checkinlists/' . $listId . '/positions/');
}

/**
 * Créer une nouvelle liste de check-in
 */
public function createCheckinList($eventSlug, array $listData)
{
    return $this->request('post', 'organizers/' . $this->organizer . '/events/' . $eventSlug . '/checkinlists/', $listData);
}

/**
 * Effectuer un check-in
 */
public function performCheckin($eventSlug, $listId, array $checkinData)
{
    return $this->request('post', 'organizers/' . $this->organizer . '/events/' . $eventSlug . '/checkinlists/' . $listId . '/checkins/', $checkinData);
}

}