<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FavoriteMovie;
use App\Services\TMDBService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MovieController extends Controller
{
    public function __construct(protected TMDBService $tmdbService)
    {
        // Middleware auth removido para facilitar testes públicos
        // $this->middleware('auth:sanctum')->except(['search']);
    }

    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'page' => 'sometimes|integer|min:1|max:500'
        ]);

        if ($validator->fails()) {
            Log::warning('Validation errors on addFavorite:', $validator->errors()->toArray());
            
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $results = $this->tmdbService->searchMovies(
                $validator->validated()['query'],
                $validator->validated()['page'] ?? 1
            );

            return response()->json([
                'results' => $results['results'] ?? [],
                'total_pages' => $results['total_pages'] ?? 1,
                'current_page' => $validator->validated()['page'] ?? 1
            ]);

        } catch (\Exception $e) {
            Log::error('TMDB Search Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to search movies',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function favorites(): JsonResponse
    {
        try {
            // Retorna todos os filmes favoritos cadastrados, sem filtro por usuário
            $favorites = FavoriteMovie::orderBy('created_at', 'desc')->get();

            return response()->json($favorites);

        } catch (\Exception $e) {
            Log::error('Get Favorites Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to get favorites'
            ], 500);
        }
    }

    public function addFavorite(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tmdb_id' => [
                'required',
                'integer',
                Rule::unique('favorite_movies', 'tmdb_id')
            ],
            'title' => 'required|string|max:255',
            'poster_path' => 'nullable|string|max:255',
            'genres' => 'required|array',
            'overview' => 'nullable|string',
            'vote_average' => 'nullable|numeric',
            'release_date' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Cria o favorito (sem user_id)
            $movie = FavoriteMovie::create(
                $validator->validated()
            );

            return response()->json($movie, 201);

        } catch (\Exception $e) {
            Log::error('Add Favorite Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to add movie to favorites',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function removeFavorite($movieId): JsonResponse
    {
        try {
            $movie = FavoriteMovie::findOrFail($movieId);
            $movie->delete();

            return response()->json(null, 204);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Movie not found in favorites'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Remove Favorite Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to remove movie from favorites'
            ], 500);
        }
    }
}
