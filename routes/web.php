<?php
// routes/web.php

use App\Http\Controllers\PretixController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect('/login');
    });
});



Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    // Création d'événement - Ces routes doivent être placées AVANT les routes avec paramètres
    Route::get('/pretix/events/create', [PretixController::class, 'create'])->name('pretix.events.create');
    Route::post('/pretix/events', [PretixController::class, 'store'])->name('pretix.events.store');
    
    // Détails d'un événement
    Route::get('/pretix/events/{eventSlug}', [PretixController::class, 'show'])->name('pretix.show');
    
    // Liste des commandes d'un événement
    Route::get('/pretix/events/{eventSlug}/orders', [PretixController::class, 'orders'])->name('pretix.orders');
    
    // Détails d'une commande
    Route::get('/pretix/events/{eventSlug}/orders/{orderCode}', [PretixController::class, 'orderDetails'])->name('pretix.order.details');
    
    // Gestion des tickets (billets)
    Route::post('/pretix/events/{eventSlug}/tickets', [PretixController::class, 'storeTicket'])->name('pretix.tickets.store');
    Route::put('/pretix/events/{eventSlug}/tickets/{ticketId}', [PretixController::class, 'updateTicket'])->name('pretix.tickets.update');
    Route::delete('/pretix/events/{eventSlug}/tickets/{ticketId}', [PretixController::class, 'destroyTicket'])->name('pretix.tickets.destroy');
    Route::post('/pretix/events/{eventSlug}/tickets/create', [PretixController::class, 'createTicket'])->name('pretix.tickets.create');
    
    // Toggle status de l'événement
    Route::post('/pretix/events/{eventSlug}/toggle-status', [PretixController::class, 'toggleEventStatus'])->name('pretix.toggle-status');
    Route::get('/pretix/events/{eventSlug}/checkin-lists', [PretixController::class, 'checkinLists'])
        ->name('pretix.checkin-lists');
    
    Route::get('/pretix/events/{eventSlug}/checkin-lists/{listId}', [PretixController::class, 'checkinListDetails'])
        ->name('pretix.checkin-list.details');
    
    Route::post('/pretix/events/{eventSlug}/checkin-lists', [PretixController::class, 'storeCheckinList'])
        ->name('pretix.checkin-list.store');
    
    Route::post('/pretix/events/{eventSlug}/checkin-lists/{listId}/checkin', [PretixController::class, 'performCheckin'])
        ->name('pretix.checkin.perform');
});

require __DIR__.'/auth.php';