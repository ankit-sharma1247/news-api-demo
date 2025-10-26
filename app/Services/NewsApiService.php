<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\News;
use App\Models\NewsSource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsApiService
{
    public function fetchNewsFromNewsApi(): array
    {
        $apiKey = ApiKey::where('source', 'newsapi.org')->first();

        if (! $apiKey) {
            throw new \Exception('NewsAPI.org API key not found');
        }

        try {
            // Parse the URL to check if it already has query parameters
            $urlParts = parse_url($apiKey->api_url);

            // If the URL doesn't have query parameters, add default ones
            if (! isset($urlParts['query'])) {
                $urlWithParams = $apiKey->api_url.'?q=technology&language=en&sortBy=publishedAt';
            } else {
                $urlWithParams = $apiKey->api_url;
            }

            $response = Http::withoutVerifying()->withHeaders([
                'X-Api-Key' => $apiKey->api_key,
            ])->get($urlWithParams);

            if (! $response->successful()) {
                Log::error('NewsAPI request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \Exception('Failed to fetch news from NewsAPI.org');
            }

            $data = $response->json();

            if (! isset($data['articles']) || ! is_array($data['articles'])) {
                throw new \Exception('Invalid response from NewsAPI.org');
            }

            return $this->storeNews($data['articles']);
        } catch (\Exception $e) {
            Log::error('Error fetching news from NewsAPI.org', [
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function storeNews(array $articles): array
    {
        $storedCount = 0;
        $errors = [];

        foreach ($articles as $article) {
            try {
                // Find or create news source
                $newsSource = NewsSource::firstOrCreate(
                    ['source_id' => $article['source']['id'] ?? null, 'name' => $article['source']['name']],
                    ['source_id' => $article['source']['id'] ?? null, 'name' => $article['source']['name']]
                );

                // Check if news already exists (by URL)
                $existingNews = News::where('url', $article['url'])->first();

                if ($existingNews) {
                    continue;
                }

                // Store news article with HTML content if available
                // Note: NewsAPI.org free tier provides limited plain text content
                // For HTML content, consider upgrading or fetching from article URL
                News::create([
                    'source_id' => $newsSource->id,
                    'title' => $article['title'] ?? '',
                    'description' => $article['description'] ?? '',
                    'author' => $article['author'] ?? '',
                    'url' => $article['url'] ?? '',
                    'image_url' => $article['urlToImage'] ?? null,
                    'published_at' => $article['publishedAt'] ? \Carbon\Carbon::parse($article['publishedAt']) : null,
                    'content' => $article['content'] ?? '',
                ]);

                $storedCount++;
            } catch (\Exception $e) {
                $errors[] = [
                    'title' => $article['title'] ?? 'Unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => true,
            'stored_count' => $storedCount,
            'errors' => $errors,
        ];
    }
}
