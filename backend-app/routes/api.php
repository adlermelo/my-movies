<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MovieController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rota de saúde da API (para testes)
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Rotas públicas
Route::prefix('movies')->name('movies.')->group(function () {
    // Busca na API TMDB (pública ou autenticada, dependendo da necessidade)
    Route::get('/search', [MovieController::class, 'search'])
        ->name('search');

    // Rotas de favoritos SEM autenticação (simplificado)
    Route::get('/favorites', [MovieController::class, 'favorites'])
        ->name('favorites.list');

    Route::post('/favorites', [MovieController::class, 'addFavorite'])
        ->name('favorites.add');

    Route::delete('/favorites/{movie}', [MovieController::class, 'removeFavorite'])
        ->name('favorites.remove');
});

// Rotas autenticadas (se quiser manter outras rotas protegidas)
Route::middleware(['auth:sanctum', 'cors'])->group(function () {
    // Dados do usuário autenticado
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    // Outras rotas protegidas aqui (se houver)
});

// Fallback para rotas API não encontradas
Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint não encontrado'
    ], 404);
});
