<?php

use App\Models\ApiKey;
use Illuminate\Support\Facades\Redirect;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\Attributes\Validate;

new
#[Layout('components.layouts.app')]
class extends Component
{
    #[Validate('required|string|max:255')]
    public string $source = '';

    #[Validate('required|url')]
    public string $api_url = '';

    #[Validate('required|string|max:255')]
    public string $api_key = '';

    public function with(): array
    {
        return [
            'title' => __('Add API Key'),
        ];
    }

    public function save(): void
    {
        $this->validate();

        ApiKey::create([
            'source' => $this->source,
            'api_url' => $this->api_url,
            'api_key' => $this->api_key,
        ]);

        session()->flash('success', __('API key created successfully.'));

        Redirect::route('api-keys.index');
    }

    public function cancel(): void
    {
        Redirect::route('api-keys.index');
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div>
        <flux:heading size="xl">{{ __('Add API Key') }}</flux:heading>
        <flux:text class="text-zinc-500 dark:text-zinc-400">
            {{ __('Create a new API key for a news source') }}
        </flux:text>
    </div>

    <div class="flex flex-col gap-4">
        <form wire:submit="save">
            <div class="space-y-6">
                <flux:input wire:model="source" :label="__('Source')" type="text" required autofocus placeholder="{{ __('News Source Name') }}" />

                <flux:input wire:model="api_url" :label="__('API URL')" type="url" required placeholder="https://api.example.com/news" />

                <flux:input wire:model="api_key" :label="__('API Key')" type="text" required placeholder="{{ __('Your API key') }}" />

                <div class="flex items-center gap-4">
                    <flux:button type="submit" variant="primary">
                        {{ __('Save') }}
                    </flux:button>

                    <flux:button type="button" variant="ghost" wire:click="cancel">
                        {{ __('Cancel') }}
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</div>
