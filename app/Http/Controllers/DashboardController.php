<?php

namespace App\Http\Controllers;

use App\Services\PretixService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    protected $pretixService;
    
    public function __construct(PretixService $pretixService)
    {
        $this->pretixService = $pretixService;
    }
    
    public function index()
    {
        try {
            // Récupération des événements via le service Pretix
            $eventsResponse = $this->pretixService->getEvents();
            
            // Log pour debugging si nécessaire
            Log::info('Dashboard Events Response', [
                'user_id' => auth()->id(),
                'has_response' => !empty($eventsResponse)
            ]);
            
            // Extraction des événements de la réponse
            $events = $eventsResponse['results'] ?? [];
            
            return Inertia::render('Home', [
                'events' => $events,
                'apiResponse' => $eventsResponse, // Pour le debugging si nécessaire
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching Pretix events for dashboard', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return Inertia::render('Home', [
                'events' => [],
                'apiResponse' => ['error' => 'Impossible de récupérer les événements: ' . $e->getMessage()]
            ]);
        }
    }
}