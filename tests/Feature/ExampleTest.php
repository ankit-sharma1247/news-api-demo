<?php

test('guests are redirected to public news listing from home', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('public.news.index'));
});

test('authenticated users are redirected to public news listing from home', function () {
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertRedirect(route('public.news.index'));
});
