<?php

use App\Models\ApiKey;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new
#[Layout('components.layouts.app')]
class extends Component
{
    use WithPagination;

    public string $search = '';

    public function with(): array
    {
        return [
            'title' => __('API Keys'),
            'apiKeys' => ApiKey::query()
                ->when($this->search, fn ($query) => $query->where('source', 'like', '%'.$this->search.'%')
                    ->orWhere('api_url', 'like', '%'.$this->search.'%')
                    ->orWhere('api_key', 'like', '%'.$this->search.'%')
                )
                ->latest()
                ->paginate(10),
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        @if (session('success'))
            <flux:callout color="green" class="mb-4">
                {{ session('success') }}
            </flux:callout>
        @endif

        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('API Keys') }}</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    {{ __('Manage your API keys for different news sources') }}
                </flux:text>
            </div>
            <flux:button :href="route('api-keys.create')" variant="primary" icon="plus" wire:navigate>
                {{ __('Add API Key') }}
            </flux:button>
        </div>

        <div class="flex items-center gap-4">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('Search by source, API URL, or API key...') }}"
                icon="magnifying-glass"
                class="flex-1"
            />
        </div>

        <div class="overflow-hidden border border-zinc-200 rounded-xl dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Source') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('API URL') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('API Key') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                    @forelse($apiKeys as $apiKey)
                        <tr wire:key="api-key-{{ $apiKey->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <flux:text class="font-medium">{{ $apiKey->source }}</flux:text>
                            </td>
                            <td class="px-6 py-4">
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ Str::limit($apiKey->api_url, 50) }}
                                </flux:text>
                            </td>
                            <td class="px-6 py-4">
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ Str::mask($apiKey->api_key, '*', 0, strlen($apiKey->api_key) - 8) }}
                                </flux:text>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon="pencil"
                                    wire:navigate
                                    :href="route('api-keys.edit', $apiKey)"
                                >
                                    {{ __('Edit') }}
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center">
                                <flux:text class="text-zinc-500 dark:text-zinc-400">
                                    {{ __('No API keys found.') }}
                                </flux:text>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($apiKeys->hasPages())
            <div class="flex justify-center">
                {{ $apiKeys->links() }}
            </div>
        @endif
</div>
