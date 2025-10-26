<?php

use App\Models\News;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.public')]
class extends Component
{
    public News $news;

    public function mount(News $news): void
    {
        $this->news = $news->load('source');
    }

    public function with(): array
    {
        return [
            'relatedNews' => News::with('source')
                ->where('id', '!=', $this->news->id)
                ->when($this->news->source_id, fn ($query) => $query->where('source_id', $this->news->source_id))
                ->latest('published_at')
                ->limit(3)
                ->get(),
        ];
    }
}; ?>

<div class="flex flex-col gap-6">
    <!-- Back Button -->
    <div>
        <flux:button
            :href="route('public.news.index')"
            variant="ghost"
            icon="arrow-left"
            wire:navigate
        >
            {{ __('Back to News') }}
        </flux:button>
    </div>

    <!-- Article Card -->
    <article class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
        @if($news->image_url)
            <div class="relative w-full h-96 overflow-hidden">
                <img src="{{ $news->image_url }}" alt="{{ $news->title }}" class="w-full h-full object-cover">
            </div>
        @endif

        <div class="p-8">
            <!-- Article Meta -->
            <div class="flex flex-wrap items-center gap-4 mb-6 text-sm text-zinc-600 dark:text-zinc-400">
                @if($news->source)
                    <flux:badge color="blue">
                        {{ $news->source->name }}
                    </flux:badge>
                @endif

                @if($news->published_at)
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>{{ $news->published_at->format('F d, Y \a\t H:i') }}</span>
                    </div>
                @endif

                @if($news->author)
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span>{{ __('By :author', ['author' => $news->author]) }}</span>
                    </div>
                @endif
            </div>

            <!-- Title -->
            <flux:heading size="2xl" class="mb-6">{{ $news->title }}</flux:heading>

            <!-- Description -->
            @if($news->description)
                <div class="mb-6">
                    <flux:text class="text-lg text-zinc-700 dark:text-zinc-300 leading-relaxed">
                        {{ $news->description }}
                    </flux:text>
                </div>
            @endif

            <!-- Content -->
            @if($news->content)
                <div class="prose prose-zinc dark:prose-invert max-w-none mb-8">
                    <flux:text class="text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap leading-relaxed">
                        {!! nl2br(e($news->content)) !!}
                    </flux:text>
                </div>
            @endif

            <!-- Read Original Article Button -->
            @if($news->url)
                <div class="pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button
                        :href="$news->url"
                        target="_blank"
                        rel="noopener noreferrer"
                        variant="primary"
                        icon="arrow-top-right-on-square"
                    >
                        {{ __('Read Original Article') }}
                    </flux:button>
                </div>
            @endif
        </div>
    </article>

    <!-- Related Articles -->
    @if($relatedNews->isNotEmpty())
        <div>
            <flux:heading size="xl" class="mb-6">{{ __('Related Articles') }}</flux:heading>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($relatedNews as $related)
                    <div wire:key="related-{{ $related->id }}" class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden hover:shadow-lg transition-shadow">
                        @if($related->image_url)
                            <img src="{{ $related->image_url }}" alt="{{ $related->title }}" class="w-full h-40 object-cover">
                        @else
                            <div class="w-full h-40 bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-800 dark:to-zinc-900 flex items-center justify-center">
                                <svg class="w-12 h-12 text-zinc-400 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                </svg>
                            </div>
                        @endif

                        <div class="p-4 flex flex-col gap-2">
                            @if($related->published_at)
                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $related->published_at->diffForHumans() }}
                                </flux:text>
                            @endif

                            <flux:heading size="sm" class="line-clamp-2">
                                {{ $related->title }}
                            </flux:heading>

                            <flux:button
                                :href="route('public.news.show', $related)"
                                variant="ghost"
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
        </div>
    @endif
</div>

