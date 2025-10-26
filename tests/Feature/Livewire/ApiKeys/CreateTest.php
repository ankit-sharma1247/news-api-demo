<?php

namespace Tests\Feature\Livewire\ApiKeys;

use Livewire\Volt\Volt;
use Tests\TestCase;

class CreateTest extends TestCase
{
    public function test_it_can_render(): void
    {
        $component = Volt::test('api-keys.create');

        $component->assertSee('');
    }
}
