<?php

declare(strict_types=1);

test('home route redirects to public news listing', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('public.news.index'));
});

test('home route redirects unauthenticated users to public news listing', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('public.news.index'));
});

test('home route redirects authenticated users to public news listing', function () {
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertRedirect(route('public.news.index'));
});
