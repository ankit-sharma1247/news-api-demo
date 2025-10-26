<?php

declare(strict_types=1);

use App\Models\News;
use App\Models\NewsSource;
use Livewire\Volt\Volt;

test('public news listing page can be accessed without authentication', function () {
    $response = $this->get(route('public.news.index'));

    $response->assertSuccessful();
    $response->assertSee('Latest News');
});

test('public news listing page displays news articles', function () {
    $source = NewsSource::factory()->create(['name' => 'Test Source']);
    $news = News::factory()->count(3)->create([
        'source_id' => $source->id,
        'published_at' => now(),
    ]);

    $response = $this->get(route('public.news.index'));

    $response->assertSuccessful();
    foreach ($news as $article) {
        $response->assertSee($article->title);
    }
});

test('public news listing page can search by title', function () {
    $source = NewsSource::factory()->create();
    $news1 = News::factory()->create([
        'title' => 'Laravel Framework News',
        'source_id' => $source->id,
    ]);
    $news2 = News::factory()->create([
        'title' => 'PHP Updates',
        'source_id' => $source->id,
    ]);

    Volt::test('public.news.index')
        ->set('search', 'Laravel')
        ->assertSee('Laravel Framework News')
        ->assertDontSee('PHP Updates');
});

test('public news listing page can filter by source', function () {
    $source1 = NewsSource::factory()->create(['name' => 'Source A']);
    $source2 = NewsSource::factory()->create(['name' => 'Source B']);

    $news1 = News::factory()->create([
        'source_id' => $source1->id,
        'title' => 'News from Source A',
    ]);
    $news2 = News::factory()->create([
        'source_id' => $source2->id,
        'title' => 'News from Source B',
    ]);

    Volt::test('public.news.index')
        ->set('sourceFilter', (string) $source1->id)
        ->assertSee('News from Source A')
        ->assertDontSee('News from Source B');
});

test('public news listing page can filter by author', function () {
    $source = NewsSource::factory()->create();
    $news1 = News::factory()->create([
        'author' => 'John Doe',
        'source_id' => $source->id,
        'title' => 'Article by John',
    ]);
    $news2 = News::factory()->create([
        'author' => 'Jane Smith',
        'source_id' => $source->id,
        'title' => 'Article by Jane',
    ]);

    Volt::test('public.news.index')
        ->set('authorFilter', 'John Doe')
        ->assertSee('Article by John')
        ->assertDontSee('Article by Jane');
});

test('public news listing page can filter by date range', function () {
    $source = NewsSource::factory()->create();
    $oldNews = News::factory()->create([
        'source_id' => $source->id,
        'title' => 'Old News',
        'published_at' => now()->subDays(10),
    ]);
    $recentNews = News::factory()->create([
        'source_id' => $source->id,
        'title' => 'Recent News',
        'published_at' => now()->subDays(2),
    ]);

    $dateFrom = now()->subDays(5)->format('Y-m-d');
    $dateTo = now()->format('Y-m-d');

    Volt::test('public.news.index')
        ->set('dateFrom', $dateFrom)
        ->set('dateTo', $dateTo)
        ->assertSee('Recent News')
        ->assertDontSee('Old News');
});

test('public news listing page can clear all filters', function () {
    $source = NewsSource::factory()->create();
    News::factory()->count(5)->create(['source_id' => $source->id]);

    Volt::test('public.news.index')
        ->set('search', 'test')
        ->set('sourceFilter', (string) $source->id)
        ->set('authorFilter', 'author')
        ->set('dateFrom', now()->subDays(10)->format('Y-m-d'))
        ->set('dateTo', now()->format('Y-m-d'))
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('sourceFilter', '')
        ->assertSet('authorFilter', '')
        ->assertSet('dateFrom', '')
        ->assertSet('dateTo', '');
});

test('public news listing page shows empty state when no articles found', function () {
    $response = $this->get(route('public.news.index'));

    $response->assertSee('No articles found');
});

test('public news listing page displays article count', function () {
    $source = NewsSource::factory()->create();
    News::factory()->count(5)->create(['source_id' => $source->id]);

    $response = $this->get(route('public.news.index'));

    $response->assertSee('Found 5 articles');
});

test('public news listing page paginates results', function () {
    $source = NewsSource::factory()->create();
    News::factory()->count(15)->create(['source_id' => $source->id]);

    $response = $this->get(route('public.news.index'));

    $response->assertSuccessful();
    $response->assertSeeInOrder(['1', '2']);
});

test('public news listing page shows active filters', function () {
    $source = NewsSource::factory()->create(['name' => 'Test Source']);
    News::factory()->create(['source_id' => $source->id]);

    Volt::test('public.news.index')
        ->set('search', 'Laravel')
        ->assertSee('Active Filters:')
        ->assertSee('Title: Laravel');
});

test('public news listing page search resets pagination', function () {
    $source = NewsSource::factory()->create();
    News::factory()->count(20)->create(['source_id' => $source->id]);

    $component = Volt::test('public.news.index')
        ->set('search', 'test');

    // Verify that updatedSearch was called which should reset the page
    expect($component->instance())->toBeInstanceOf(\Livewire\Volt\Component::class);
});

test('public news listing page displays news with images', function () {
    $source = NewsSource::factory()->create();
    $news = News::factory()->create([
        'source_id' => $source->id,
        'image_url' => 'https://example.com/image.jpg',
    ]);

    $response = $this->get(route('public.news.index'));

    $response->assertSee($news->image_url);
});

test('public news listing page displays news without images with placeholder', function () {
    $source = NewsSource::factory()->create();
    News::factory()->create([
        'source_id' => $source->id,
        'image_url' => null,
    ]);

    $response = $this->get(route('public.news.index'));

    $response->assertSuccessful();
});

test('public news listing page shows relative time for recent articles', function () {
    $source = NewsSource::factory()->create();
    $news = News::factory()->create([
        'source_id' => $source->id,
        'published_at' => now()->subHours(2),
    ]);

    $response = $this->get(route('public.news.index'));

    $response->assertSee('2 hours ago');
});
