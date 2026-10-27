<?php

// Landing page (demo flow, screen 1): demo-only, no staff login.
test('guests see the landing page at /', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Two divisions. One counter.')
        ->assertSee('Try the live demo')
        ->assertSee('Everything the store runs on')
        ->assertSee('The demo in three steps')
        ->assertDontSee('Staff login')
        ->assertDontSee(route('login'));
});

test('every call to action on the landing page leads to the demo', function () {
    $html = $this->get('/')->getContent();

    preg_match_all('/<a [^>]*href="([^"]+)"[^>]*>\s*(?:Try|Open|Start)[^<]*/', $html, $ctas);

    expect($ctas[1])->not->toBeEmpty()
        ->and(collect($ctas[1])->unique()->values()->all())->toBe([route('demo')]);
});

test('the landing page shows both branches', function () {
    $this->get('/')
        ->assertSee('Hardware and Motor Parts')
        ->assertSee('Tiles and Flooring Division');
});

test('signed-in users are sent from the landing page into the app', function () {
    $manager = userWithRole('manager');
    $cashier = userWithRole('cashier');

    $this->actingAs($manager)->get('/')->assertRedirect(route('dashboard'));
    $this->actingAs($cashier)->get('/')->assertRedirect(route('pos'));
});


test('logging out returns to the landing page', function () {
    $user = userWithRole('cashier');

    $this->actingAs($user)->post('/logout')->assertRedirect('/');
    $this->get('/')->assertOk()->assertSee('Try the live demo');
});
