<?php

use App\Models\News;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Redirect;

new
#[Layout('components.layouts.app')]
class extends Component
{
    public News $news;

    public function mount(News $news): void
    {
        $this->news = $news;
    }

    public function with(): array
    {
        $this->news->load('source');
        
        return [
            'title' => $this->news->title,
        ];
    }

    public function back(): void
    {
        Redirect::route('news.index');
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('News Details') }}</flux:heading>
            <flux:text class="text-zinc-500 dark:text-zinc-400">
                {{ __('View news article details') }}
            </flux:text>
        </div>
        <flux:button variant="ghost" icon="arrow-left" wire:click="back">
            {{ __('Back') }}
        </flux:button>
    </div>

    <div class="overflow-hidden border border-zinc-200 rounded-xl dark:border-zinc-700 bg-white dark:bg-zinc-900">
        <div class="p-6">
            @if($news->image_url)
                <div class="mb-6">
                    <img src="{{ $news->image_url }}" alt="{{ $news->title }}" class="w-full h-64 object-cover rounded-lg">
                </div>
            @endif

            <div class="mb-4">
                <flux:heading size="lg" class="mb-2">{{ $news->title }}</flux:heading>
                
                <div class="flex flex-wrap gap-4 text-sm text-zinc-500 dark:text-zinc-400 mb-4">
                    @if($news->author)
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            {{ $news->author }}
                        </div>
                    @endif
                    @if($news->source)
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                            </svg>
                            {{ $news->source->name }}
                        </div>
                    @endif
                    @if($news->published_at)
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ $news->published_at->format('M d, Y H:i') }}
                        </div>
                    @endif
                </div>
            </div>

            @if($news->description)
                <div class="mb-4">
                    <flux:text class="text-zinc-700 dark:text-zinc-300 leading-relaxed">
                        {{ $news->description }}
                    </flux:text>
                </div>
            @endif

            @if($news->content)
                <div class="prose prose-zinc dark:prose-invert max-w-none">
                    <flux:text class="text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">
                        {!! $news->content !!}
                    </flux:text>
                </div>
            @endif

            @if($news->url)
                <div class="mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button :href="$news->url" target="_blank" variant="primary" icon="arrow-top-right-on-square">
                        {{ __('View Original Article') }}
                    </flux:button>
                </div>
            @endif
        </div>
    </div>
</div>

