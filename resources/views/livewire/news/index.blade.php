<?php

use App\Models\ApiKey;
use App\Models\News;
use App\Services\GuardianService;
use App\Services\NewsApiService;
use App\Services\NYTimesService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new
#[Layout('components.layouts.app')]
class extends Component
{
    use WithPagination;

    public string $search = '';

    public array $selectedSources = [];

    public function with(): array
    {
        return [
            'title' => __('News'),
            'newsItems' => News::with('source')
                ->when($this->search, fn ($query) => $query->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('author', 'like', '%'.$this->search.'%')
                    ->orWhereHas('source', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
                )
                ->latest('published_at')
                ->paginate(10),
            'availableSources' => ApiKey::select('source')->distinct()->pluck('source'),
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function fetchNews(): void
    {
        if (empty($this->selectedSources)) {
            session()->flash('error', __('Please select at least one source to fetch news from.'));

            return;
        }

        $totalStoredCount = 0;
        $messages = [];
        $errors = [];

        try {
            foreach ($this->selectedSources as $source) {
                try {
                    if ($source === 'newsapi.org') {
                        $service = new NewsApiService;
                        $result = $service->fetchNewsFromNewsApi();
                        $totalStoredCount += $result['stored_count'];
                        $messages[] = __('Successfully fetched :count news articles from NewsAPI.org.', ['count' => $result['stored_count']]);
                    } elseif (in_array($source, ['open-platform.theguardian.com', 'theguardian.com', 'guardian', 'theguardian'])) {
                        $service = new GuardianService;
                        $result = $service->fetchNewsFromGuardian();
                        $totalStoredCount += $result['stored_count'];
                        $messages[] = __('Successfully fetched :count news articles from The Guardian.', ['count' => $result['stored_count']]);
                    } elseif (in_array($source, ['nytimes.com', 'api.nytimes.com'])) {
                        $service = new NYTimesService;
                        $result = $service->fetchNewsFromNYTimes();
                        $totalStoredCount += $result['stored_count'];
                        $messages[] = __('Successfully fetched :count news articles from The New York Times.', ['count' => $result['stored_count']]);
                    } else {
                        $errors[] = __('The source :source is not yet implemented.', ['source' => $source]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error fetching news from source', [
                        'source' => $source,
                        'message' => $e->getMessage(),
                    ]);
                    $errors[] = __('Failed to fetch news from :source: :error', ['source' => $source, 'error' => $e->getMessage()]);
                }
            }

            if (count($messages) > 0) {
                session()->flash('success', implode(' ', $messages));
            }

            if (count($errors) > 0) {
                session()->flash('error', implode(' ', $errors));
            }

            if ($totalStoredCount > 0) {
                $this->resetPage();
            }
        } catch (\Exception $e) {
            Log::error('Error fetching news', [
                'message' => $e->getMessage(),
                'selected_sources' => $this->selectedSources,
            ]);

            session()->flash('error', __('Failed to fetch news: :error', ['error' => $e->getMessage()]));
        }

        $this->selectedSources = [];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div>
        <flux:heading size="xl">{{ __('News') }}</flux:heading>
        <flux:text class="text-zinc-500 dark:text-zinc-400">
            {{ __('Browse latest news articles') }}
        </flux:text>
    </div>

    @if (session('success'))
        <flux:callout color="green" class="mb-4">
            {{ session('success') }}
        </flux:callout>
    @endif

    @if (session('error'))
        <flux:callout color="red" class="mb-4">
            {{ session('error') }}
        </flux:callout>
    @endif

    <div class="flex flex-col gap-4">
        <div class="flex items-center gap-4">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('Search by title, author, or source...') }}"
                icon="magnifying-glass"
                class="flex-1"
            />
        </div>

        <div>
            <flux:label>{{ __('Fetch News From') }}</flux:label>
            <div class="mt-1.5 flex items-start gap-3">
                <div class="flex-1">
                    <select 
                        wire:model.live="selectedSources" 
                        multiple 
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 shadow-sm transition focus:border-primary-600 focus:outline-none focus:ring focus:ring-primary-600/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-300 dark:focus:border-primary-500 dark:focus:ring-primary-500/20"
                        size="3"
                    >
                        @foreach($availableSources as $source)
                            <option value="{{ $source }}">{{ $source }}</option>
                        @endforeach
                    </select>
                    <flux:text class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                        @if(count($selectedSources) > 0)
                            {{ count($selectedSources) }} {{ __('source(s) selected') }}
                        @else
                            {{ __('Select one or more sources') }}
                        @endif
                    </flux:text>
                </div>
                <flux:button 
                    variant="primary" 
                    icon="arrow-down-tray" 
                    wire:click="fetchNews"
                    wire:loading.attr="disabled"
                    class="shrink-0"
                >
                    <span wire:loading.remove wire:target="fetchNews">{{ __('Fetch') }}</span>
                    <span wire:loading wire:target="fetchNews">{{ __('Fetching...') }}</span>
                </flux:button>
            </div>
        </div>
    </div>

    <div class="overflow-hidden border border-zinc-200 rounded-xl dark:border-zinc-700">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        {{ __('Title') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        {{ __('Author') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        {{ __('Published At') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        {{ __('Actions') }}
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                @forelse($newsItems as $news)
                    <tr wire:key="news-{{ $news->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <td class="px-6 py-4">
                            <flux:text class="font-medium">{{ $news->title }}</flux:text>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $news->author ?? 'N/A' }}
                            </flux:text>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $news->published_at?->format('M d, Y') ?? 'N/A' }}
                            </flux:text>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="eye"
                                wire:navigate
                                :href="route('news.show', $news)"
                            >
                                {{ __('View') }}
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center">
                            <flux:text class="text-zinc-500 dark:text-zinc-400">
                                {{ __('No news articles found.') }}
                            </flux:text>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($newsItems->hasPages())
        <div class="flex justify-center">
            {{ $newsItems->links() }}
        </div>
    @endif
</div>

