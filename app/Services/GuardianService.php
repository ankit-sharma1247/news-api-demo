<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\News;
use App\Models\NewsSource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuardianService
{
    public function fetchNewsFromGuardian(): array
    {
        $apiKey = ApiKey::where('source', 'theguardian.com')
            ->first();

        if (! $apiKey) {
            throw new \Exception('The Guardian API key not found');
        }

        try {
            // Parse the URL to check if it already has query parameters
            $urlParts = parse_url($apiKey->api_url);

            // If the URL doesn't have query parameters, add default ones
            if (! isset($urlParts['query'])) {
                $urlWithParams = $apiKey->api_url.'?show-fields=headline,byline,body,thumbnail,trailText&page-size=20&order-by=newest';
            } else {
                $urlWithParams = $apiKey->api_url;
            }

            $response = Http::withoutVerifying()->get($urlWithParams, [
                'api-key' => $apiKey->api_key,
            ]);

            if (! $response->successful()) {
                Log::error('Guardian API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \Exception('Failed to fetch news from The Guardian');
            }

            $data = $response->json();

            if (! isset($data['response']['results']) || ! is_array($data['response']['results'])) {
                throw new \Exception('Invalid response from The Guardian');
            }

            return $this->storeNews($data['response']['results']);
        } catch (\Exception $e) {
            Log::error('Error fetching news from The Guardian', [
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
                // Extract fields from Guardian response
                $fields = $article['fields'] ?? [];

                // Find or create news source
                $newsSource = NewsSource::firstOrCreate(
                    ['source_id' => null, 'name' => 'The Guardian'],
                    ['source_id' => null, 'name' => 'The Guardian']
                );

                // Check if news already exists (by URL)
                $existingNews = News::where('url', $article['webUrl'] ?? '')->first();

                if ($existingNews) {
                    continue;
                }

                // Extract thumbnail URL
                $thumbnailUrl = null;
                if (isset($fields['thumbnail'])) {
                    $thumbnailUrl = $fields['thumbnail'];
                }

                // Extract description
                $description = $fields['trailText'] ?? '';
                if (empty($description) && isset($fields['body'])) {
                    $description = mb_substr(strip_tags($fields['body']), 0, 200);
                }

                // Extract content with HTML tags preserved
                $content = $fields['body'] ?? '';

                // Store news article
                News::create([
                    'source_id' => $newsSource->id,
                    'title' => $fields['headline'] ?? $article['webTitle'] ?? '',
                    'description' => $description,
                    'author' => $fields['byline'] ?? '',
                    'url' => $article['webUrl'] ?? '',
                    'image_url' => $thumbnailUrl,
                    'published_at' => isset($article['webPublicationDate']) ? \Carbon\Carbon::parse($article['webPublicationDate']) : null,
                    'content' => $content,
                ]);

                $storedCount++;
            } catch (\Exception $e) {
                $errors[] = [
                    'title' => $fields['headline'] ?? $article['webTitle'] ?? 'Unknown',
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
