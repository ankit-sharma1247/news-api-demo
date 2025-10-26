<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\News;
use App\Models\NewsSource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NYTimesService
{
    public function fetchNewsFromNYTimes(): array
    {
        $apiKey = ApiKey::where('source', 'nytimes.com')->first();

        if (! $apiKey) {
            throw new \Exception('NY Times API key not found');
        }

        try {
            // Parse the URL to check if it already has query parameters
            $urlParts = parse_url($apiKey->api_url);

            // If the URL doesn't have query parameters, add default ones
            if (! isset($urlParts['query'])) {
                $urlWithParams = $apiKey->api_url.'?sort=newest&page=0';
            } else {
                $urlWithParams = $apiKey->api_url;
            }

            $response = Http::withoutVerifying()->get($urlWithParams, [
                'api-key' => $apiKey->api_key,
            ]);

            if (! $response->successful()) {
                Log::error('NY Times API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \Exception('Failed to fetch news from NY Times');
            }

            $data = $response->json();

            if (! isset($data['response']['docs']) || ! is_array($data['response']['docs'])) {
                throw new \Exception('Invalid response from NY Times');
            }

            return $this->storeNews($data['response']['docs']);
        } catch (\Exception $e) {
            Log::error('Error fetching news from NY Times', [
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
                    ['source_id' => null, 'name' => 'The New York Times'],
                    ['source_id' => null, 'name' => 'The New York Times']
                );

                // Check if news already exists (by URL)
                $existingNews = News::where('url', $article['web_url'] ?? '')->first();

                if ($existingNews) {
                    continue;
                }

                // Extract image URL from multimedia object
                $imageUrl = null;
                if (isset($article['multimedia'])) {
                    // Try to get the default image URL first, fallback to thumbnail
                    if (isset($article['multimedia']['default']['url'])) {
                        $imageUrl = $article['multimedia']['default']['url'];
                    } elseif (isset($article['multimedia']['thumbnail']['url'])) {
                        $imageUrl = $article['multimedia']['thumbnail']['url'];
                    }
                }

                // Extract author from byline
                $author = '';
                if (isset($article['byline']['original'])) {
                    $author = $article['byline']['original'];
                } elseif (isset($article['byline']['person']) && is_array($article['byline']['person']) && count($article['byline']['person']) > 0) {
                    $authors = array_map(fn ($person) => $person['firstname'].' '.$person['lastname'], $article['byline']['person']);
                    $author = implode(', ', $authors);
                }

                // Extract title from headline
                $title = $article['headline']['main'] ?? '';

                // Extract description from abstract or lead_paragraph
                $description = $article['abstract'] ?? '';
                if (empty($description) && isset($article['lead_paragraph'])) {
                    $description = $article['lead_paragraph'];
                }

                // Extract content from lead_paragraph or snippet
                // Note: NY Times API provides plain text in standard response
                // For HTML content with images, you would need to fetch from article URL
                $content = $article['lead_paragraph'] ?? '';
                if (empty($content) && isset($article['snippet'])) {
                    $content = $article['snippet'];
                }

                // Optionally wrap content in HTML paragraph tags for consistency
                if (! empty($content) && ! str_starts_with(trim($content), '<')) {
                    $content = '<p>'.nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8')).'</p>';
                }

                // Store news article
                News::create([
                    'source_id' => $newsSource->id,
                    'title' => $title,
                    'description' => $description,
                    'author' => $author,
                    'url' => $article['web_url'] ?? '',
                    'image_url' => $imageUrl,
                    'published_at' => isset($article['pub_date']) ? \Carbon\Carbon::parse($article['pub_date']) : null,
                    'content' => $content,
                ]);

                $storedCount++;
            } catch (\Exception $e) {
                $errors[] = [
                    'title' => $article['headline']['main'] ?? 'Unknown',
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
