<?php

declare(strict_types=1);

use App\Models\ApiKey;
use App\Models\User;
use Livewire\Volt\Volt;

test('it can render', function () {
    $user = User::factory()->create();
    $apiKey = ApiKey::factory()->create();

    $this->actingAs($user);

    $component = Volt::test('api-keys.edit', ['apiKey' => $apiKey]);

    $component->assertSee('Edit API Key');
});
