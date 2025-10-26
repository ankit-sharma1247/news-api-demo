<?php

declare(strict_types=1);

use App\Models\News;
use App\Models\NewsSource;
use App\Models\User;
use Livewire\Volt\Volt;

use function Pest\Laravel\actingAs;

test('dashboard displays total news count', function () {
    $user = User::factory()->create();
    $source = NewsSource::factory()->create();
    News::factory()->count(5)->create(['source_id' => $source->id]);

    actingAs($user);

    Volt::test('pages.dashboard')
        ->assertSee('Total News Articles')
        ->assertSee('5');
});

test('dashboard displays news from last 24 hours', function () {
    $user = User::factory()->create();
    $source = NewsSource::factory()->create();

    // Create old news
    News::factory()->create([
        'source_id' => $source->id,
        'created_at' => now()->subDays(2),
    ]);

    // Create recent news
    News::factory()->count(3)->create([
        'source_id' => $source->id,
        'created_at' => now()->subHours(12),
    ]);

    actingAs($user);

    Volt::test('pages.dashboard')
        ->assertSee('Last 24 Hours')
        ->assertSee('3');
});

test('dashboard displays news from last 7 days', function () {
    $user = User::factory()->create();
    $source = NewsSource::factory()->create();

    // Create old news
    News::factory()->create([
        'source_id' => $source->id,
        'created_at' => now()->subDays(10),
    ]);

    // Create news from last week
    News::factory()->count(7)->create([
        'source_id' => $source->id,
        'created_at' => now()->subDays(3),
    ]);

    actingAs($user);

    Volt::test('pages.dashboard')
        ->assertSee('Last 7 Days')
        ->assertSee('7');
});

test('dashboard displays top news sources', function () {
    $user = User::factory()->create();

    $source1 = NewsSource::factory()->create(['name' => 'BBC News']);
    $source2 = NewsSource::factory()->create(['name' => 'CNN']);

    News::factory()->count(10)->create(['source_id' => $source1->id]);
    News::factory()->count(5)->create(['source_id' => $source2->id]);

    actingAs($user);

    Volt::test('pages.dashboard')
        ->assertSee('Top News Sources')
        ->assertSee('BBC News')
        ->assertSee('10 articles')
        ->assertSee('CNN')
        ->assertSee('5 articles');
});

test('dashboard displays latest news', function () {
    $user = User::factory()->create();
    $source = NewsSource::factory()->create();

    $news = News::factory()->create([
        'source_id' => $source->id,
        'title' => 'Breaking News: Test Article',
        'published_at' => now(),
    ]);

    actingAs($user);

    Volt::test('pages.dashboard')
        ->assertSee('Latest News')
        ->assertSee('Breaking News: Test Article')
        ->assertSee($source->name);
});

test('dashboard shows message when no news available', function () {
    $user = User::factory()->create();

    actingAs($user);

    Volt::test('pages.dashboard')
        ->assertSee('No news sources available')
        ->assertSee('No news articles available');
});

test('authenticated user can view dashboard', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSeeLivewire('pages.dashboard');
});
