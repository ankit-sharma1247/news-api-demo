<?php

use Livewire\Volt\Volt;

it('can render', function () {
    $component = Volt::test('api-keys.index');

    $component->assertSee('');
});
