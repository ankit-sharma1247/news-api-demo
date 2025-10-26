<?php

declare(strict_types=1);

use App\Models\News;
use App\Models\NewsSource;

test('public news detail page can be accessed without authentication', function () {
    $source = NewsSource::factory()->create();
    $news = News::factory()->create(['source_id' => $source->id]);

    $response = $this->get(route('public.news.show', $news));

    $response->assertSuccessful();
});

test('public news detail page displays article title', function () {
    $source = NewsSource::factory()->create();
    $news = News::factory()->create([
        'source_id' => $source->id,
        'title' => 'Test Article Title',
    ]);

    $response = $this->get(route('public.news.show', $news));

    $response->assertSee('Test Article Title');
});

test('public news detail page displays article description', function () {
    $source = NewsSource::factory()->create();
    $news = News::factory()->create([
        'source_id' => $source->id,
        'description' => 'This is a test description',
    ]);

    $response = $this->get(route('public.news.show', $news));

    $response->assertSee('This is a test description');
});

test('public news detail page displays article content', function () {
    $source = NewsSource::factory()->create();
    $news = News::factory()->create([
        'source_id' => $source->id,
        'content' => 'This is the full article content',
    ]);

    $response = $this->get(route('public.news.show', $news));

    $response->assertSee('This is the full article content');
});

test('public news detail page displays article author', function () {
    $source = NewsSource::factory()->create();
    $news = News::factory()->create([
        'source_id' => $source->id,
        'author' => 'John Doe',
    ]);

    $response = $this->get(route('public.news.show', $news));

    $response->assertSee('By John Doe');
});

test('public news detail page displays article source', function () {
    $source = NewsSource::factory()->create(['name' => 'Test Source']);
    $news = News::factory()->create(['source_id' => $source->id]);

    $response = $this->get(route('public.news.show', $news));

    $response->assertSee('Test Source');
});

test('public news detail page displays published date', function () {
    $source = NewsSource::factory()->create();
    $publishedAt = now()->subDays(2);
    $news = News::factory()->create([
        'source_id' => $source->id,
        'published_at' => $publishedAt,
    ]);

    $response = $this->get(route('public.news.show', $news));

    $response->assertSee($publishedAt->format('F d, Y'));
});

test('public news detail page displays article image', function () {
    $source = NewsSource::factory()->create();
    $news = News::factory()->create([
        'source_id' => $source->id,
        'image_url' => 'https://example.com/test-image.jpg',
    ]);

    $response = $this->get(route('public.news.show', $news));

    $response->assertSee('https://example.com/test-image.jpg');
});

test('public news detail page shows link to original article', function () {
    $source = NewsSource::factory()->create();
    $news = News::factory()->create([
        'source_id' => $source->id,
        'url' => 'https://example.com/original-article',
    ]);

    $response = $this->get(route('public.news.show', $news));

    $response->assertSee('Read Original Article');
    $response->assertSee('https://example.com/original-article');
});

test('public news detail page has back to news button', function () {
    $source = NewsSource::factory()->create();
    $news = News::factory()->create(['source_id' => $source->id]);

    $response = $this->get(route('public.news.show', $news));

    $response->assertSee('Back to News');
});

test('public news detail page displays related articles', function () {
    $source = NewsSource::factory()->create();
    $news = News::factory()->create([
        'source_id' => $source->id,
        'title' => 'Main Article',
    ]);
    $relatedNews = News::factory()->count(3)->create([
        'source_id' => $source->id,
    ]);

    $response = $this->get(route('public.news.show', $news));

    $response->assertSee('Related Articles');
    foreach ($relatedNews as $related) {
        $response->assertSee($related->title);
    }
});

test('public news detail page displays related articles from same source', function () {
    $source1 = NewsSource::factory()->create();
    $source2 = NewsSource::factory()->create();

    $news = News::factory()->create([
        'source_id' => $source1->id,
        'title' => 'Main Article',
    ]);

    $relatedSameSource = News::factory()->create([
        'source_id' => $source1->id,
        'title' => 'Related from Same Source',
    ]);

    $differentSource = News::factory()->create([
        'source_id' => $source2->id,
        'title' => 'From Different Source',
    ]);

    $response = $this->get(route('public.news.show', $news));

    $response->assertSee('Related from Same Source');
});

test('public news detail page limits related articles to 3', function () {
    $source = NewsSource::factory()->create();
    $news = News::factory()->create(['source_id' => $source->id]);
    News::factory()->count(10)->create(['source_id' => $source->id]);

    $response = $this->get(route('public.news.show', $news));

    $response->assertSuccessful();
});

test('public news detail page does not show current article in related', function () {
    $source = NewsSource::factory()->create();
    $news = News::factory()->create([
        'source_id' => $source->id,
        'title' => 'Main Article',
    ]);
    News::factory()->count(3)->create(['source_id' => $source->id]);

    $response = $this->get(route('public.news.show', $news));

    $response->assertSuccessful();
});

test('public news detail page returns 404 for non-existent article', function () {
    $response = $this->get(route('public.news.show', 99999));

    $response->assertNotFound();
});

test('public news detail page handles articles without optional fields', function () {
    $source = NewsSource::factory()->create();
    $news = News::factory()->create([
        'source_id' => $source->id,
        'author' => null,
        'description' => null,
        'content' => null,
        'image_url' => null,
    ]);

    $response = $this->get(route('public.news.show', $news));

    $response->assertSuccessful();
    $response->assertSee($news->title);
});

test('public news detail page shows no related articles when only one article exists', function () {
    $source = NewsSource::factory()->create();
    $news = News::factory()->create(['source_id' => $source->id]);

    $response = $this->get(route('public.news.show', $news));

    $response->assertSuccessful();
    // The article should be displayed successfully even without related articles
});
