<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PretixAPIController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route pour activer/désactiver la visibilité de la billetterie
Route::post('/pretix/toggle-shop-status/{eventSlug}', [PretixAPIController::class, 'toggleShopStatus'])
    ->middleware(['auth'])
    ->name('api.pretix.toggle-shop-status');