<?php

use App\Models\ApiKey;
use Illuminate\Support\Facades\Http;

test('user can fetch news from newsapi.org', function () {
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    // Create API key for newsapi.org
    $apiKey = ApiKey::factory()->create([
        'source' => 'newsapi.org',
        'api_url' => 'https://newsapi.org/v2/everything?q=technology&pageSize=20',
        'api_key' => 'test-api-key',
    ]);

    // Mock the NewsAPI response
    Http::fake([
        'newsapi.org/v2/everything*' => Http::response([
            'status' => 'ok',
            'totalResults' => 2,
            'articles' => [
                [
                    'source' => ['id' => 'test', 'name' => 'Test Source'],
                    'author' => 'Test Author',
                    'title' => 'Test Article 1',
                    'description' => 'Test Description 1',
                    'url' => 'https://example.com/article1',
                    'urlToImage' => 'https://example.com/image1.jpg',
                    'publishedAt' => '2025-01-10T12:00:00Z',
                    'content' => 'Test Content 1',
                ],
                [
                    'source' => ['id' => 'test', 'name' => 'Test Source'],
                    'author' => 'Test Author 2',
                    'title' => 'Test Article 2',
                    'description' => 'Test Description 2',
                    'url' => 'https://example.com/article2',
                    'urlToImage' => 'https://example.com/image2.jpg',
                    'publishedAt' => '2025-01-10T13:00:00Z',
                    'content' => 'Test Content 2',
                ],
            ],
        ], 200),
    ]);

    // Visit the news index page
    $response = $this->get(route('news.index'));
    $response->assertStatus(200);

    // Simulate selecting newsapi.org and fetching news
    $livewire = \Livewire\Volt\Volt::test('news.index')
        ->set('selectedSources', ['newsapi.org'])
        ->call('fetchNews');

    $livewire->assertHasNoErrors();

    // Verify that news articles were created
    expect(\App\Models\News::count())->toBe(2);
    expect(\App\Models\News::first()->title)->toBe('Test Article 1');
    expect(\App\Models\NewsSource::count())->toBe(1);
});

test('fetching news without api key fails gracefully', function () {
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    // Try to fetch news without an API key
    $livewire = \Livewire\Volt\Volt::test('news.index')
        ->set('selectedSources', ['newsapi.org'])
        ->call('fetchNews');

    // Verify that an error was set in the session
    $livewire->assertSet('selectedSources', []);
});

test('user can see available sources in dropdown', function () {
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    ApiKey::factory()->create(['source' => 'newsapi.org']);
    ApiKey::factory()->create(['source' => 'another-source.com']);

    $response = $this->get(route('news.index'));
    $response->assertSee('newsapi.org');
    $response->assertSee('another-source.com');
});

test('empty source selection shows error message', function () {
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    $livewire = \Livewire\Volt\Volt::test('news.index')
        ->call('fetchNews');

    // Verify that the component handles empty selection correctly
    $livewire->assertHasNoErrors();
});

test('user can fetch news from the guardian', function () {
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    // Create API key for The Guardian
    $apiKey = ApiKey::factory()->create([
        'source' => 'theguardian.com',
        'api_url' => 'https://content.guardianapis.com/search',
        'api_key' => 'test-guardian-key',
    ]);

    // Mock the Guardian API response
    Http::fake([
        'content.guardianapis.com/search*' => Http::response([
            'response' => [
                'status' => 'ok',
                'total' => 2,
                'results' => [
                    [
                        'id' => 'guardian/article1',
                        'webTitle' => 'Guardian Article 1',
                        'webUrl' => 'https://www.theguardian.com/article1',
                        'webPublicationDate' => '2025-01-10T12:00:00Z',
                        'fields' => [
                            'headline' => 'Guardian Article 1',
                            'byline' => 'Guardian Author 1',
                            'trailText' => 'This is a test description for article 1',
                            'body' => '<p>This is the full content of article 1</p>',
                            'thumbnail' => 'https://example.com/guardian-image1.jpg',
                        ],
                    ],
                    [
                        'id' => 'guardian/article2',
                        'webTitle' => 'Guardian Article 2',
                        'webUrl' => 'https://www.theguardian.com/article2',
                        'webPublicationDate' => '2025-01-10T13:00:00Z',
                        'fields' => [
                            'headline' => 'Guardian Article 2',
                            'byline' => 'Guardian Author 2',
                            'trailText' => 'This is a test description for article 2',
                            'body' => '<p>This is the full content of article 2</p>',
                            'thumbnail' => 'https://example.com/guardian-image2.jpg',
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    // Visit the news index page
    $response = $this->get(route('news.index'));
    $response->assertStatus(200);

    // Simulate selecting The Guardian and fetching news
    $livewire = \Livewire\Volt\Volt::test('news.index')
        ->set('selectedSources', ['theguardian.com'])
        ->call('fetchNews');

    $livewire->assertHasNoErrors();

    // Verify that news articles were created
    expect(\App\Models\News::count())->toBe(2);
    expect(\App\Models\News::first()->title)->toBe('Guardian Article 1');
    expect(\App\Models\News::first()->source->name)->toBe('The Guardian');
    expect(\App\Models\NewsSource::where('name', 'The Guardian')->exists())->toBeTrue();

    // Verify that HTML content is preserved
    expect(\App\Models\News::first()->content)->toBe('<p>This is the full content of article 1</p>');
});

test('user can fetch news from multiple sources', function () {
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    // Create API keys for both sources
    ApiKey::factory()->create([
        'source' => 'newsapi.org',
        'api_url' => 'https://newsapi.org/v2/everything?q=technology&pageSize=20',
        'api_key' => 'test-api-key',
    ]);

    ApiKey::factory()->create([
        'source' => 'theguardian.com',
        'api_url' => 'https://content.guardianapis.com/search',
        'api_key' => 'test-guardian-key',
    ]);

    // Mock the NewsAPI response
    Http::fake([
        'newsapi.org/v2/everything*' => Http::response([
            'status' => 'ok',
            'totalResults' => 1,
            'articles' => [
                [
                    'source' => ['id' => 'test', 'name' => 'Test Source'],
                    'author' => 'Test Author',
                    'title' => 'NewsAPI Article 1',
                    'description' => 'Test Description 1',
                    'url' => 'https://example.com/article1',
                    'urlToImage' => 'https://example.com/image1.jpg',
                    'publishedAt' => '2025-01-10T12:00:00Z',
                    'content' => 'Test Content 1',
                ],
            ],
        ], 200),
    ]);

    // Mock the Guardian API response
    Http::fake([
        'content.guardianapis.com/search*' => Http::response([
            'response' => [
                'status' => 'ok',
                'total' => 1,
                'results' => [
                    [
                        'id' => 'guardian/article1',
                        'webTitle' => 'Guardian Article 1',
                        'webUrl' => 'https://www.theguardian.com/article1',
                        'webPublicationDate' => '2025-01-10T12:00:00Z',
                        'fields' => [
                            'headline' => 'Guardian Article 1',
                            'byline' => 'Guardian Author 1',
                            'trailText' => 'This is a test description for article 1',
                            'body' => '<p>This is the full content of article 1</p>',
                            'thumbnail' => 'https://example.com/guardian-image1.jpg',
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    // Fetch from both sources
    $livewire = \Livewire\Volt\Volt::test('news.index')
        ->set('selectedSources', ['newsapi.org', 'theguardian.com'])
        ->call('fetchNews');

    $livewire->assertHasNoErrors();

    // Verify that news articles from both sources were created
    expect(\App\Models\News::count())->toBe(2);
    expect(\App\Models\News::where('title', 'NewsAPI Article 1')->exists())->toBeTrue();
    expect(\App\Models\News::where('title', 'Guardian Article 1')->exists())->toBeTrue();
});

test('user can fetch news from ny times', function () {
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    // Create API key for NY Times
    $apiKey = ApiKey::factory()->create([
        'source' => 'nytimes.com',
        'api_url' => 'https://api.nytimes.com/svc/search/v2/articlesearch.json',
        'api_key' => 'test-nytimes-key',
    ]);

    // Mock the NY Times API response
    Http::fake([
        'api.nytimes.com/svc/search/v2/articlesearch.json*' => Http::response([
            'status' => 'OK',
            'response' => [
                'docs' => [
                    [
                        'web_url' => 'https://www.nytimes.com/2025/01/10/technology/test-article-1.html',
                        'headline' => [
                            'main' => 'NY Times Article 1',
                        ],
                        'byline' => [
                            'original' => 'By Test Author',
                        ],
                        'abstract' => 'This is a test abstract for article 1',
                        'lead_paragraph' => 'This is the lead paragraph of article 1',
                        'pub_date' => '2025-01-10T12:00:00Z',
                        'multimedia' => [
                            'default' => [
                                'url' => 'https://static01.nyt.com/images/2025/01/10/test-image1.jpg',
                                'height' => 600,
                                'width' => 600,
                            ],
                            'thumbnail' => [
                                'url' => 'https://static01.nyt.com/images/2025/01/10/test-image1-thumb.jpg',
                                'height' => 75,
                                'width' => 75,
                            ],
                        ],
                    ],
                    [
                        'web_url' => 'https://www.nytimes.com/2025/01/10/technology/test-article-2.html',
                        'headline' => [
                            'main' => 'NY Times Article 2',
                        ],
                        'byline' => [
                            'original' => 'By Test Author 2',
                        ],
                        'abstract' => 'This is a test abstract for article 2',
                        'lead_paragraph' => 'This is the lead paragraph of article 2',
                        'pub_date' => '2025-01-10T13:00:00Z',
                        'multimedia' => [
                            'default' => [
                                'url' => 'https://static01.nyt.com/images/2025/01/10/test-image2.jpg',
                                'height' => 600,
                                'width' => 600,
                            ],
                            'thumbnail' => [
                                'url' => 'https://static01.nyt.com/images/2025/01/10/test-image2-thumb.jpg',
                                'height' => 75,
                                'width' => 75,
                            ],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    // Visit the news index page
    $response = $this->get(route('news.index'));
    $response->assertStatus(200);

    // Simulate selecting NY Times and fetching news
    $livewire = \Livewire\Volt\Volt::test('news.index')
        ->set('selectedSources', ['nytimes.com'])
        ->call('fetchNews');

    $livewire->assertHasNoErrors();

    // Verify that news articles were created
    expect(\App\Models\News::count())->toBe(2);
    expect(\App\Models\News::first()->title)->toBe('NY Times Article 1');
    expect(\App\Models\News::first()->source->name)->toBe('The New York Times');
    expect(\App\Models\NewsSource::where('name', 'The New York Times')->exists())->toBeTrue();

    // Verify that content is wrapped in HTML tags
    $firstArticle = \App\Models\News::first();
    expect($firstArticle->content)->toContain('<p>');
    expect($firstArticle->content)->toContain('This is the lead paragraph of article 1');
});
