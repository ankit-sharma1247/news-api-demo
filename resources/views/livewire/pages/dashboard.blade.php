<?php

use App\Models\News;
use App\Models\NewsSource;
use function Livewire\Volt\{computed};

$totalNews = computed(fn () => News::count());

$newsLast24Hours = computed(fn () => News::where('created_at', '>=', now()->subDay())->count());

$newsLast7Days = computed(fn () => News::where('created_at', '>=', now()->subDays(7))->count());

$topSources = computed(fn () => NewsSource::withCount('news')
    ->orderBy('news_count', 'desc')
    ->take(5)
    ->get());

$latestNews = computed(fn () => News::with('source')
    ->orderBy('published_at', 'desc')
    ->take(10)
    ->get());

?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <!-- Stats Cards -->
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <!-- Total News Card -->
        <div class="relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <div class="flex flex-col gap-2">
                <flux:text class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total News Articles</flux:text>
                <flux:heading size="xl" class="text-3xl font-bold text-neutral-900 dark:text-white">{{ number_format($this->totalNews) }}</flux:heading>
            </div>
        </div>

        <!-- Last 24 Hours Card -->
        <div class="relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <div class="flex flex-col gap-2">
                <flux:text class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Last 24 Hours</flux:text>
                <flux:heading size="xl" class="text-3xl font-bold text-neutral-900 dark:text-white">{{ number_format($this->newsLast24Hours) }}</flux:heading>
            </div>
        </div>

        <!-- Last 7 Days Card -->
        <div class="relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <div class="flex flex-col gap-2">
                <flux:text class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Last 7 Days</flux:text>
                <flux:heading size="xl" class="text-3xl font-bold text-neutral-900 dark:text-white">{{ number_format($this->newsLast7Days) }}</flux:heading>
            </div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <!-- Top News Sources -->
        <div class="relative h-full overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
            <div class="p-6">
                <flux:heading size="lg" class="mb-4 text-neutral-900 dark:text-white">Top News Sources</flux:heading>
                <div class="space-y-4">
                    @forelse($this->topSources as $source)
                        <div class="flex items-center justify-between border-b border-neutral-200 pb-3 last:border-0 dark:border-neutral-700">
                            <div class="flex flex-col">
                                <flux:text class="font-medium text-neutral-900 dark:text-white">{{ $source->name }}</flux:text>
                            </div>
                            <flux:badge variant="neutral">{{ number_format($source->news_count) }} articles</flux:badge>
                        </div>
                    @empty
                        <flux:text class="text-neutral-600 dark:text-neutral-400">No news sources available</flux:text>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Latest News -->
        <div class="relative h-full overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
            <div class="p-6">
                <div class="mb-4 flex items-center justify-between">
                    <flux:heading size="lg" class="text-neutral-900 dark:text-white">Latest News</flux:heading>
                    <flux:button variant="subtle" href="{{ route('news.index') }}" size="sm">View All</flux:button>
                </div>
                <div class="max-h-[400px] space-y-4 overflow-y-auto">
                    @forelse($this->latestNews as $news)
                        <div class="border-b border-neutral-200 pb-3 last:border-0 dark:border-neutral-700">
                            <a href="{{ route('news.show', $news) }}" class="group block">
                                <flux:text class="mb-1 font-medium text-neutral-900 group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">
                                    {{ Str::limit($news->title, 60) }}
                                </flux:text>
                                <div class="flex items-center gap-2">
                                    <flux:badge variant="neutral" size="sm">{{ $news->source->name }}</flux:badge>
                                    <flux:text class="text-xs text-neutral-500 dark:text-neutral-400">
                                        {{ $news->published_at->diffForHumans() }}
                                    </flux:text>
                                </div>
                            </a>
                        </div>
                    @empty
                        <flux:text class="text-neutral-600 dark:text-neutral-400">No news articles available</flux:text>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
