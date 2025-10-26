<?php

use App\Models\News;
use App\Models\NewsSource;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new
#[Layout('components.layouts.public')]
class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sourceFilter = '';

    public string $authorFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function with(): array
    {
        $query = News::with('source')
            ->when($this->search, fn ($query) => $query->where('title', 'like', '%'.$this->search.'%')
                ->orWhere('description', 'like', '%'.$this->search.'%')
            )
            ->when($this->sourceFilter, fn ($query) => $query->whereHas('source', fn ($q) => $q->where('id', $this->sourceFilter)))
            ->when($this->authorFilter, fn ($query) => $query->where('author', 'like', '%'.$this->authorFilter.'%'))
            ->when($this->dateFrom, fn ($query) => $query->whereDate('published_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('published_at', '<=', $this->dateTo))
            ->latest('published_at');

        return [
            'newsItems' => $query->paginate(12),
            'sources' => NewsSource::orderBy('name')->get(),
            'authors' => News::select('author')
                ->whereNotNull('author')
                ->distinct()
                ->orderBy('author')
                ->pluck('author'),
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSourceFilter(): void
    {
        $this->resetPage();
    }

    public function updatedAuthorFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->sourceFilter = '';
        $this->authorFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="text-center">
        <flux:heading size="2xl" class="mb-2">{{ __('Latest News') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">
            {{ __('Browse and search through the latest news articles from various sources') }}
        </flux:text>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
        <flux:heading size="lg" class="mb-4">{{ __('Search & Filters') }}</flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Search by Title -->
            <div>
                <flux:field>
                    <flux:label>{{ __('Search by Title') }}</flux:label>
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('Enter keywords...') }}"
                        icon="magnifying-glass"
                    />
                </flux:field>
            </div>

            <!-- Filter by Source -->
            <div>
                <flux:field>
                    <flux:label>{{ __('Source') }}</flux:label>
                    <flux:select wire:model.live="sourceFilter" placeholder="{{ __('All Sources') }}">
                        <option value="">{{ __('All Sources') }}</option>
                        @foreach($sources as $source)
                            <option value="{{ $source->id }}">{{ $source->name }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            <!-- Filter by Author -->
            <div>
                <flux:field>
                    <flux:label>{{ __('Author') }}</flux:label>
                    <flux:select wire:model.live="authorFilter" placeholder="{{ __('All Authors') }}">
                        <option value="">{{ __('All Authors') }}</option>
                        @foreach($authors as $author)
                            <option value="{{ $author }}">{{ $author }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            <!-- Date From -->
            <div>
                <flux:field>
                    <flux:label>{{ __('From Date') }}</flux:label>
                    <flux:input
                        type="date"
                        wire:model.live="dateFrom"
                        placeholder="{{ __('Start date') }}"
                    />
                </flux:field>
            </div>

            <!-- Date To -->
            <div>
                <flux:field>
                    <flux:label>{{ __('To Date') }}</flux:label>
                    <flux:input
                        type="date"
                        wire:model.live="dateTo"
                        placeholder="{{ __('End date') }}"
                    />
                </flux:field>
            </div>

            <!-- Clear Filters Button -->
            <div class="flex items-end">
                <flux:button
                    wire:click="clearFilters"
                    variant="ghost"
                    class="w-full"
                    icon="x-mark"
                >
                    {{ __('Clear Filters') }}
                </flux:button>
            </div>
        </div>

        <!-- Active Filters Display -->
        @if($search || $sourceFilter || $authorFilter || $dateFrom || $dateTo)
            <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <div class="flex flex-wrap gap-2">
                    <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ __('Active Filters:') }}
                    </flux:text>
                    
                    @if($search)
                        <flux:badge color="blue">
                            {{ __('Title: ') }}{{ $search }}
                        </flux:badge>
                    @endif

                    @if($sourceFilter)
                        <flux:badge color="green">
                            {{ __('Source: ') }}{{ $sources->find($sourceFilter)?->name }}
                        </flux:badge>
                    @endif

                    @if($authorFilter)
                        <flux:badge color="purple">
                            {{ __('Author: ') }}{{ $authorFilter }}
                        </flux:badge>
                    @endif

                    @if($dateFrom)
                        <flux:badge color="orange">
                            {{ __('From: ') }}{{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }}
                        </flux:badge>
                    @endif

                    @if($dateTo)
                        <flux:badge color="orange">
                            {{ __('To: ') }}{{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
                        </flux:badge>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Results Count -->
    <div>
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Found :count articles', ['count' => $newsItems->total()]) }}
        </flux:text>
    </div>

    <!-- News Grid -->
    @if($newsItems->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($newsItems as $news)
                <div wire:key="news-{{ $news->id }}" class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden hover:shadow-lg transition-shadow">
                    @if($news->image_url)
                        <img src="{{ $news->image_url }}" alt="{{ $news->title }}" class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-800 dark:to-zinc-900 flex items-center justify-center">
                            <svg class="w-16 h-16 text-zinc-400 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                            </svg>
                        </div>
                    @endif

                    <div class="p-4 flex flex-col gap-3">
                        <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                            @if($news->source)
                                <flux:badge color="zinc" size="sm">
                                    {{ $news->source->name }}
                                </flux:badge>
                            @endif
                            @if($news->published_at)
                                <span>{{ $news->published_at->diffForHumans() }}</span>
                            @endif
                        </div>

                        <flux:heading size="md" class="line-clamp-2">
                            {{ $news->title }}
                        </flux:heading>

                        @if($news->description)
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 line-clamp-3">
                                {{ $news->description }}
                            </flux:text>
                        @endif

                        @if($news->author)
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ __('By :author', ['author' => $news->author]) }}
                            </flux:text>
                        @endif

                        <flux:button
                            :href="route('public.news.show', $news)"
                            variant="primary"
                            size="sm"
                            wire:navigate
                            class="mt-auto"
                        >
                            {{ __('Read More') }}
                        </flux:button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-12 text-center">
            <svg class="w-16 h-16 text-zinc-400 dark:text-zinc-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <flux:heading size="lg" class="mb-2">{{ __('No articles found') }}</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400 mb-4">
                {{ __('Try adjusting your search criteria or clearing the filters') }}
            </flux:text>
            <flux:button wire:click="clearFilters" variant="primary">
                {{ __('Clear All Filters') }}
            </flux:button>
        </div>
    @endif

    <!-- Pagination -->
    @if($newsItems->hasPages())
        <div class="flex justify-center">
            {{ $newsItems->links() }}
        </div>
    @endif
</div>

