<?php

namespace App\Http\Controllers;

use App\Services\PretixService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class PretixController extends Controller
{
    protected $pretixService;
    
    public function __construct(PretixService $pretixService)
    {
        $this->pretixService = $pretixService;
    }
    
    /**
     * Afficher la liste des événements
     */
    public function index()
    {
        try {
            $eventsResponse = $this->pretixService->getEvents();
            
            Log::info('Events Response', ['response' => $eventsResponse]);
            
            $events = $eventsResponse['results'] ?? [];
            
            return Inertia::render('Pretix/Index', [
                'events' => $events,
                'apiResponse' => $eventsResponse,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching events', ['error' => $e->getMessage()]);
            
            return Inertia::render('Pretix/Index', [
                'events' => [],
                'apiResponse' => ['error' => $e->getMessage()],
            ]);
        }
    }
    
/**
 * Afficher les détails d'un événement
 */
public function show($eventSlug, Request $request)
{
    try {
        $event = $this->pretixService->getEvent($eventSlug);
        $tickets = $this->pretixService->getItems($eventSlug);
        $quotas = $this->pretixService->getQuotas($eventSlug);
        $orders = $this->pretixService->getOrders($eventSlug);
        
        // Récupérer les listes de check-in
        $checkinListsResponse = $this->pretixService->getCheckinLists($eventSlug);
        $checkinLists = $checkinListsResponse['results'] ?? [];
        
        // Extraire les tableaux des résultats
        $ticketsArray = $tickets['results'] ?? [];
        $quotasArray = $quotas['results'] ?? [];
        $ordersArray = $orders['results'] ?? [];
        
        // Ajouter le quota à chaque ticket
        foreach ($ticketsArray as &$ticket) {
            // Trouver les quotas qui contiennent cet item
            $ticketQuotas = [];
            foreach ($quotasArray as $quota) {
                if (isset($quota['items']) && in_array($ticket['id'], $quota['items'])) {
                    $ticketQuotas[] = [
                        'id' => $quota['id'],
                        'name' => $quota['name'],
                        'size' => $quota['size']
                    ];
                }
            }
            
            $ticket['quotas'] = $ticketQuotas;
            
            // Déterminer la taille du quota (la plus restrictive si plusieurs)
            $ticket['quota_size'] = 'unlimited';
            $ticket['quota_type'] = 'unlimited'; // unlimited, limited
            
            if (!empty($ticketQuotas)) {
                $hasUnlimited = false;
                $minSize = PHP_INT_MAX;
                
                foreach ($ticketQuotas as $quota) {
                    if ($quota['size'] === null) {
                        $hasUnlimited = true;
                    } elseif (is_numeric($quota['size']) && $quota['size'] < $minSize) {
                        $minSize = $quota['size'];
                    }
                }
                
                if (!$hasUnlimited && $minSize < PHP_INT_MAX) {
                    $ticket['quota_size'] = $minSize;
                    $ticket['quota_type'] = 'limited';
                }
            }
        }
        
        // Reconstruire les tableaux
        $tickets['results'] = $ticketsArray;
        
        // Récupérer l'onglet actif depuis la requête
        $tab = $request->query('tab', 'dashboard');
        
        return Inertia::render('Pretix/Show', [
            'event' => $event,
            'eventSlug' => $eventSlug,
            'orders' => $ordersArray,
            'tickets' => $tickets,
            'quotas' => $quotas,
            'checkinLists' => $checkinLists, // Ajout des listes de check-in
            'tab' => $tab, // Ajout du paramètre d'onglet actif
        ]);
    } catch (\Exception $e) {
        Log::error('Error in show method', [
            'error' => $e->getMessage(),
            'eventSlug' => $eventSlug
        ]);
        
        return Inertia::render('Pretix/Show', [
            'event' => null,
            'eventSlug' => $eventSlug,
            'orders' => [],
            'tickets' => [],
            'quotas' => [],
            'checkinLists' => [], // Ajout d'un tableau vide pour les listes de check-in
            'apiResponse' => ['error' => $e->getMessage()],
        ]);
    }
}
    
    /**
     * Afficher les commandes d'un événement
     */
    public function orders($eventSlug)
    {
        try {
            $event = $this->pretixService->getEvent($eventSlug);
            $ordersResponse = $this->pretixService->getOrders($eventSlug);
            
            $orders = $ordersResponse['results'] ?? [];
            
            return Inertia::render('Pretix/Orders', [
                'orders' => $orders,
                'event' => $event,
                'eventSlug' => $eventSlug,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching orders', [
                'error' => $e->getMessage(),
                'eventSlug' => $eventSlug
            ]);
            
            return Inertia::render('Pretix/Orders', [
                'orders' => [],
                'event' => null,
                'eventSlug' => $eventSlug,
                'apiResponse' => ['error' => $e->getMessage()],
            ]);
        }
    }
    
   /**
 * Affiche les détails d'une commande spécifique.
 *
 * @param string $eventSlug Slug de l'événement
 * @param string $orderCode Code de la commande
 * @return \Inertia\Response
 */
public function orderDetails($eventSlug, $orderCode)
{
    try {
        // Récupérer les détails de l'événement
        $event = $this->pretixService->getEvent($eventSlug);
        
        // Récupérer les détails de la commande
        $order = $this->pretixService->getOrderDetails($eventSlug, $orderCode);
        
        // Récupérer les positions si elles ne sont pas incluses dans la réponse de la commande
        if (!isset($order['positions']) || empty($order['positions'])) {
            $positionsResponse = $this->pretixService->getOrderPositions($eventSlug, $orderCode);
            $order['positions'] = $positionsResponse['results'] ?? [];
        }
        
        // Récupérer tous les articles (tickets) disponibles pour cet événement
        // afin d'enrichir les positions avec leurs noms
        $allItems = $this->pretixService->getItems($eventSlug);
        $itemsById = [];
        
        // Créer un tableau associatif d'items indexé par ID
        if (isset($allItems['results']) && !empty($allItems['results'])) {
            foreach ($allItems['results'] as $item) {
                $itemsById[$item['id']] = $item;
            }
        }
        
        // Enrichir chaque position avec les détails de son article
        foreach ($order['positions'] as &$position) {
            $itemId = $position['item'] ?? $position['item_id'] ?? null;
            
            // Si on a un ID d'article et qu'il existe dans notre liste
            if ($itemId && isset($itemsById[$itemId])) {
                // Ajouter les détails de l'article à la position
                $position['item'] = $itemsById[$itemId];
            }
        }

        // URL pour télécharger tous les tickets
        $apiBaseUrl = $this->pretixService->getApiUrl();
        $order['download_url'] = $apiBaseUrl . 'organizers/' . $this->pretixService->getOrganizer() . 
                               '/events/' . $eventSlug . '/orders/' . $orderCode . '/download/pdf/';

        // Pour chaque position/ticket
        foreach ($order['positions'] as &$position) {
            $positionId = $position['id'] ?? $position['positionid'] ?? null;

            if ($positionId) {
                // URL pour télécharger le ticket individuel en PDF
                $position['pdf_ticket'] = $apiBaseUrl . 'organizers/' . $this->pretixService->getOrganizer() . 
                    '/events/' . $eventSlug . '/orderpositions/' . $positionId . '/download/pdf/';
            }

            // Ticket page URL (si secret est disponible)
            if (isset($position['secret'])) {
                $pretixBaseUrl = str_replace('/api/v1/', '', $apiBaseUrl);
                $position['ticket_page_url'] = $pretixBaseUrl . $this->pretixService->getOrganizer() . 
                    '/' . $eventSlug . '/tickets/' . $position['secret'] . '/';
            }
        }
        
        return Inertia::render('Pretix/OrderDetail', [
            'eventSlug' => $eventSlug,
            'event' => $event,
            'order' => $order,
            'apiResponse' => null // Pour stocker les erreurs API si nécessaire
        ]);
    } catch (\Exception $e) {
        // Gérer les erreurs API
        Log::error('Error fetching order details', [
            'error' => $e->getMessage(),
            'eventSlug' => $eventSlug,
            'orderCode' => $orderCode
        ]);
        
        return Inertia::render('Pretix/OrderDetail', [
            'eventSlug' => $eventSlug,
            'event' => null,
            'order' => null,
            'apiResponse' => [
                'error' => 'Erreur lors de la récupération des données: ' . $e->getMessage()
            ]
        ]);
    }
}

    public function create()
    {
        return Inertia::render('Pretix/CreateEvent');
    }

    /**
 * Mise à jour de la méthode de création d'événement
 * Implémentez cette méthode dans votre PretixController
 */
public function store(Request $request)
{
    // Validation des données du formulaire
    $validated = $request->validate([
        'name.fr' => 'required|string|max:255',
        'name.en' => 'nullable|string|max:255',
        'slug' => 'required|string|max:255|regex:/^[a-zA-Z0-9\-]+$/',
        'date_from' => 'required|date',
        'date_to' => 'nullable|date|after_or_equal:date_from',
        'location' => 'nullable|string|max:255',
        'live' => 'boolean',
        'currency' => 'required|string|size:3',
    ]);
    
    try {
        // Préparation des données pour l'API
        $eventData = [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'live' => $validated['live'] ?? false,
            'testmode' => false,
            'currency' => $validated['currency'],
            'date_from' => $validated['date_from'],
            'has_subevents' => false,
            
            // Inclure les plugins nécessaires
            'plugins' => [
                "pretix.plugins.sendmail",
                "pretix.plugins.statistics",
                "pretix.plugins.ticketoutputpdf"
            ]
        ];
        
        // Ajout des champs optionnels
        if (!empty($validated['date_to'])) {
            $eventData['date_to'] = $validated['date_to'];
        }
        
        if (!empty($validated['location'])) {
            $eventData['location'] = ['fr' => $validated['location']];
        }
        
        // Créer l'événement
        $response = $this->pretixService->createEvent($eventData);
        
        if (isset($response['error'])) {
            throw new \Exception($response['error']);
        }
        
        // IMPORTANT: Configurer les settings après création
        $eventSettings = [
            'ticket_download' => true,                // Activer le téléchargement des tickets
            'ticket_download_date' => null,           // Disponible immédiatement
            'ticket_download_addons' => false,
            'ticket_download_pending' => false,
            'mail_attach_tickets' => true,            // Joindre les tickets aux emails
            'waiting_list_enabled' => true,           // Activer la liste d'attente
            'waiting_list_auto' => true,
            'waiting_list_hours' => 48,
            'locales' => ['fr', 'en']                 // Langues disponibles
        ];
        
        $settingsResponse = $this->pretixService->updateEventSettings($response['slug'], $eventSettings);
        
        Log::info('Event settings update', ['response' => $settingsResponse]);
        
        return redirect()->route('pretix.show', $response['slug'])
            ->with('message', 'Événement créé avec succès !');
            
    } catch (\Exception $e) {
        Log::error('Error creating event', [
            'error' => $e->getMessage()
        ]);
        
        return redirect()->back()
            ->withInput()
            ->withErrors(['api_error' => 'Erreur lors de la création de l\'événement : ' . $e->getMessage()]);
    }
}

/**
 * Mise à jour de la méthode de création de ticket
 * Implémentez cette méthode dans votre PretixController
 */
public function createTicket(Request $request, $eventSlug)
{
    // Validation simplifiée
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'quota_size' => 'nullable|numeric|min:0',
        'is_admission' => 'boolean',
    ]);

    try {
        // 1. Créer le ticket avec generate_tickets explicitement à true
        $ticketData = [
            'name' => ['fr' => $validated['name']],
            'default_price' => (float) $validated['price'],
            'admission' => $validated['is_admission'] ?? true,
            'active' => true,
            'sales_channels' => ['web'],
            'tax_rate' => 0,
            'generate_tickets' => true,            // IMPORTANT: Activer explicitement
            'show_quota_left' => true,            // Afficher le nombre de tickets restants
            'allow_waitinglist' => true           // Permettre la liste d'attente
        ];
        
        $ticketResponse = $this->pretixService->createTicket($eventSlug, $ticketData);
        
        Log::info('Ticket creation response', ['response' => $ticketResponse]);
        
        if (isset($ticketResponse['error'])) {
            return back()->withErrors(['api_error' => 'Erreur lors de la création du ticket: ' . $ticketResponse['error']]);
        }
        
        // Récupérer l'ID du ticket
        $ticketId = $ticketResponse['id'] ?? null;
        
        if (!$ticketId) {
            return back()->withErrors(['api_error' => 'Impossible de déterminer l\'ID du ticket créé']);
        }
        
        // 2. Créer un quota et l'associer au ticket
        $quotaData = [
            'name' => 'Quota pour ' . $validated['name'],
            'size' => !empty($validated['quota_size']) ? (int)$validated['quota_size'] : null,
            'items' => [$ticketId]
        ];
        
        $quotaResponse = $this->pretixService->createQuota($eventSlug, $quotaData);
        
        Log::info('Quota creation response', ['response' => $quotaResponse]);
        
        if (isset($quotaResponse['error'])) {
            return back()->with('warning', 'Ticket créé, mais erreur lors de la création du quota: ' . $quotaResponse['error']);
        }
        
        return back()->with('success', 'Billet créé avec succès');
    } catch (\Exception $e) {
        Log::error('Error creating ticket', [
            'error' => $e->getMessage(),
            'eventSlug' => $eventSlug
        ]);
        
        return back()->withErrors(['api_error' => $e->getMessage()]);
    }
}

/**
 * Méthode pour activer les PDF sur un événement existant
 * Ajoutez cette méthode à votre PretixController
 */
public function enablePdfTickets($eventSlug)
{
    try {
        // Configurer les paramètres nécessaires pour les tickets PDF
        $settings = [
            'ticket_download' => true,
            'ticket_download_date' => null,
            'mail_attach_tickets' => true,
            'waiting_list_enabled' => true
        ];
        
        $response = $this->pretixService->updateEventSettings($eventSlug, $settings);
        
        // Vérifier aussi les plugins de l'événement
        $event = $this->pretixService->getEvent($eventSlug);
        
        $requiredPlugins = [
            "pretix.plugins.sendmail",
            "pretix.plugins.statistics",
            "pretix.plugins.ticketoutputpdf"
        ];
        
        $missingPlugins = array_diff($requiredPlugins, $event['plugins'] ?? []);
        
        if (!empty($missingPlugins)) {
            $eventUpdate = [
                'plugins' => array_merge($event['plugins'] ?? [], $missingPlugins)
            ];
            
            $pluginResponse = $this->pretixService->updateEvent($eventSlug, $eventUpdate);
            Log::info('Plugin update response', ['response' => $pluginResponse]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'PDF tickets enabled successfully',
            'settings_response' => $response
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Toggle le statut "live" de l'événement (activer/désactiver la billetterie)
 */
public function toggleEventStatus(Request $request, $eventSlug)
{
    try {
        // Valider la requête
        $validated = $request->validate([
            'live' => 'required|boolean',
        ]);
        
        // Préparer les données à mettre à jour
        $eventData = [
            'live' => $validated['live']
        ];
        
        // Appel au service pour mettre à jour l'événement
        $response = $this->pretixService->updateEvent($eventSlug, $eventData);
        
        if (isset($response['error'])) {
            throw new \Exception($response['error']);
        }
        
        return response()->json([
            'success' => true,
            'message' => $validated['live'] ? 'Billetterie activée avec succès' : 'Billetterie désactivée avec succès',
            'live' => $validated['live']
        ]);
    } catch (\Exception $e) {
        Log::error('Error toggling event status', [
            'error' => $e->getMessage(),
            'eventSlug' => $eventSlug
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour du statut : ' . $e->getMessage()
        ], 500);
    }
}

public function showOrderDetails($eventSlug, $orderCode)
{
    try {
        // Récupérer les détails de l'événement
        $event = $this->pretixService->getEvent($eventSlug);
        
        // Récupérer les détails de la commande
        $order = $this->pretixService->getOrderDetails($eventSlug, $orderCode);
        
        // Récupérer les positions si elles ne sont pas incluses dans la réponse de la commande
        if (!isset($order['positions']) || empty($order['positions'])) {
            $positionsResponse = $this->pretixService->getOrderPositions($eventSlug, $orderCode);
            $order['positions'] = $positionsResponse['results'] ?? [];
        }

        // URL pour télécharger tous les tickets
        $apiBaseUrl = $this->pretixService->getApiUrl();
        $order['download_url'] = $apiBaseUrl . 'organizers/' . $this->pretixService->getOrganizer() . 
                               '/events/' . $eventSlug . '/orders/' . $orderCode . '/download/pdf/';

        // Pour chaque position/ticket
        foreach ($order['positions'] as &$position) {
            $positionId = $position['id'] ?? $position['positionid'] ?? null;

            if ($positionId) {
                // URL pour télécharger le ticket individuel en PDF
                $position['pdf_ticket'] = $apiBaseUrl . 'organizers/' . $this->pretixService->getOrganizer() . 
                    '/events/' . $eventSlug . '/orderpositions/' . $positionId . '/download/pdf/';
            }

            // Ticket page URL (si secret est disponible)
            if (isset($position['secret'])) {
                $pretixBaseUrl = str_replace('/api/v1/', '', $apiBaseUrl);
                $position['ticket_page_url'] = $pretixBaseUrl . $this->pretixService->getOrganizer() . 
                    '/' . $eventSlug . '/tickets/' . $position['secret'] . '/';
            }
        }
        
        return Inertia::render('Pretix/OrderDetail', [
            'eventSlug' => $eventSlug,
            'event' => $event,
            'order' => $order,
            'apiResponse' => null // Pour stocker les erreurs API si nécessaire
        ]);
    } catch (\Exception $e) {
        // Gérer les erreurs API
        return Inertia::render('Pretix/OrderDetail', [
            'eventSlug' => $eventSlug,
            'event' => null,
            'order' => null,
            'apiResponse' => [
                'error' => 'Erreur lors de la récupération des données: ' . $e->getMessage()
            ]
        ]);
    }
}
/**
 * Récupérer et afficher les listes de check-in pour un événement
 */
public function checkinLists($eventSlug)
{
    try {
        $event = $this->pretixService->getEvent($eventSlug);
        $checkinListsResponse = $this->pretixService->getCheckinLists($eventSlug);
        
        $checkinLists = $checkinListsResponse['results'] ?? [];
        
        // Récupérer les tickets (items) pour les associer aux listes de check-in
        $tickets = $this->pretixService->getItems($eventSlug);
        $ticketsArray = $tickets['results'] ?? [];
        
        // Créer un tableau associatif pour recherche rapide par ID
        $ticketsById = [];
        foreach ($ticketsArray as $ticket) {
            $ticketsById[$ticket['id']] = $ticket;
        }
        
        // Enrichir les listes de check-in avec des informations sur les tickets
        foreach ($checkinLists as &$list) {
            if (isset($list['limit_products']) && is_array($list['limit_products'])) {
                $productNames = [];
                foreach ($list['limit_products'] as $productId) {
                    if (isset($ticketsById[$productId])) {
                        $name = $ticketsById[$productId]['name'] ?? null;
                        if (is_array($name)) {
                            $productNames[] = $name['fr'] ?? $name['en'] ?? 'Billet sans nom';
                        } else {
                            $productNames[] = $name ?? 'Billet sans nom';
                        }
                    }
                }
                $list['product_names'] = $productNames;
            }
        }
        
        return Inertia::render('Pretix/CheckinLists', [
            'checkinLists' => $checkinLists,
            'event' => $event,
            'eventSlug' => $eventSlug,
            'tickets' => $ticketsArray
        ]);
    } catch (\Exception $e) {
        Log::error('Error fetching check-in lists', [
            'error' => $e->getMessage(),
            'eventSlug' => $eventSlug
        ]);
        
        return Inertia::render('Pretix/CheckinLists', [
            'checkinLists' => [],
            'event' => null,
            'eventSlug' => $eventSlug,
            'tickets' => [],
            'apiResponse' => ['error' => $e->getMessage()]
        ]);
    }
}

/**
 * Afficher les détails d'une liste de check-in spécifique
 */
public function checkinListDetails($eventSlug, $listId)
{
    try {
        $event = $this->pretixService->getEvent($eventSlug);
        $list = $this->pretixService->getCheckinListDetails($eventSlug, $listId);
        $checkinsResponse = $this->pretixService->getCheckins($eventSlug, $listId);
        
        $checkins = $checkinsResponse['results'] ?? [];
        
        return Inertia::render('Pretix/CheckinListDetails', [
            'checkinList' => $list,
            'checkins' => $checkins,
            'event' => $event,
            'eventSlug' => $eventSlug
        ]);
    } catch (\Exception $e) {
        Log::error('Error fetching check-in list details', [
            'error' => $e->getMessage(),
            'eventSlug' => $eventSlug,
            'listId' => $listId
        ]);
        
        return Inertia::render('Pretix/CheckinListDetails', [
            'checkinList' => null,
            'checkins' => [],
            'event' => null,
            'eventSlug' => $eventSlug,
            'apiResponse' => ['error' => $e->getMessage()]
        ]);
    }
}

/**
 * Créer une nouvelle liste de check-in
 */
public function storeCheckinList(Request $request, $eventSlug)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'all_products' => 'boolean',
        'limit_products' => 'required_if:all_products,false|array',
        'include_pending' => 'boolean',
    ]);
    
    try {
        $listData = [
            'name' => $validated['name'],
            'all_products' => $validated['all_products'] ?? false,
            'include_pending' => $validated['include_pending'] ?? false
        ];
        
        // Si on ne sélectionne pas tous les produits, on spécifie ceux qui sont sélectionnés
        if (!($validated['all_products'] ?? false) && isset($validated['limit_products'])) {
            $listData['limit_products'] = $validated['limit_products'];
        }
        
        $response = $this->pretixService->createCheckinList($eventSlug, $listData);
        
        if (isset($response['error'])) {
            throw new \Exception($response['error']);
        }
        
        return redirect()->route('pretix.show', ['eventSlug' => $eventSlug, 'tab' => 'checkin'])
        ->with('message', 'Liste de check-in créée avec succès !');
    } catch (\Exception $e) {
        Log::error('Error creating check-in list', [
            'error' => $e->getMessage(),
            'eventSlug' => $eventSlug
        ]);
        
        return redirect()->back()
            ->withInput()
            ->withErrors(['api_error' => 'Erreur lors de la création de la liste de check-in : ' . $e->getMessage()]);
    }
}

/**
 * Effectuer un check-in
 */
public function performCheckin(Request $request, $eventSlug, $listId)
{
    $validated = $request->validate([
        'secret' => 'required|string',
        'ignore_unpaid' => 'boolean',
        'force' => 'boolean',
        'nonce' => 'nullable|string'
    ]);
    
    try {
        $checkinData = [
            'secret' => $validated['secret'],
            'ignore_unpaid' => $validated['ignore_unpaid'] ?? false,
            'force' => $validated['force'] ?? false
        ];
        
        if (isset($validated['nonce'])) {
            $checkinData['nonce'] = $validated['nonce'];
        }
        
        $response = $this->pretixService->performCheckin($eventSlug, $listId, $checkinData);
        
        return response()->json($response);
    } catch (\Exception $e) {
        Log::error('Error performing check-in', [
            'error' => $e->getMessage(),
            'eventSlug' => $eventSlug,
            'listId' => $listId
        ]);
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}
public function downloadTicket($eventSlug, $orderCode, $positionId = null)
{
    try {
        // Récupérer le contenu PDF
        $pdfContent = $this->pretixService->downloadTicketPdf($eventSlug, $orderCode, $positionId);
        
        // Générer un nom de fichier
        $filename = "ticket-{$eventSlug}-{$orderCode}" . 
                    ($positionId ? "-{$positionId}" : "") . 
                    ".pdf";
        
        // Retourner la réponse avec le PDF
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\""
        ]);
    } catch (\Exception $e) {
        Log::error('Erreur de téléchargement du ticket', [
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Impossible de télécharger le ticket: ' . $e->getMessage()
        ], 500);
    }
}
}