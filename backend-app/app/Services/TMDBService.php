<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class TMDBService
{
    private Client $client;
    private string $apiKey;
    private string $baseUrl = 'https://api.themoviedb.org/3';

    public function __construct()
    {
        $this->apiKey = env('TMDB_API_KEY', '17410523a8a9692121525379fe393593');
        
        $this->client = new Client([
            'base_uri' => 'https://api.themoviedb.org/3/',
            'timeout' => 15,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'MyMoviesApp/1.0'
            ],
            'verify' => false,
            'http_errors' => false // Para evitar exceções em respostas 4xx/5xx
        ]);
    }

    public function searchMovies(string $query, int $page = 1): array
    {
        $url = "search/movie?" . http_build_query([
            'api_key' => $this->apiKey,
            'query' => $query,
            'page' => $page,
            'language' => 'pt-BR',
            'include_adult' => false
        ]);

        try {
            $response = $this->client->get($url);
            $status = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            
            Log::debug("TMDB Response", [
                'status' => $status,
                'body' => $body
            ]);

            $data = json_decode($body, true) ?? [];
            
            return [
                'results' => $data['results'] ?? [],
                'total_pages' => $data['total_pages'] ?? 1,
                'status' => $status
            ];

        } catch (\Exception $e) {
            Log::error("TMDB Request Failed", [
                'error' => $e->getMessage(),
                'url' => $this->baseUrl . $url
            ]);
            
            return [
                'results' => [],
                'total_pages' => 1,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Método para debug que retorna a URL completa da requisição
     */
    public function debugRequest(string $query): string
    {
        return $this->baseUrl . '/search/movie?' . http_build_query([
            'api_key' => $this->apiKey,
            'query' => $query,
            'language' => 'pt-BR',
            'include_adult' => false
        ]);
    }

    public function getMovieDetails(int $movieId): ?array
    {
        try {
            $response = $this->client->get("movie/{$movieId}", [
                'query' => [
                    'api_key' => $this->apiKey,
                    'language' => 'pt-BR',
                    'append_to_response' => 'videos,credits,images'
                ]
            ]);

            return json_decode($response->getBody(), true);

        } catch (GuzzleException $e) {
            Log::error('TMDB Details Error', [
                'error' => $e->getMessage(),
                'movie_id' => $movieId
            ]);
            return null;
        }
    }

    public function getGenres(): array
    {
        try {
            $response = $this->client->get('genre/movie/list', [
                'query' => [
                    'api_key' => $this->apiKey,
                    'language' => 'pt-BR'
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['genres'] ?? [];

        } catch (GuzzleException $e) {
            Log::error('TMDB Genres Error', ['error' => $e->getMessage()]);
            return [];
        }
    }
}