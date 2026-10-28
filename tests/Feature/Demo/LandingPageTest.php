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
        ->assertDontSee('/login');
});

test('every call to action on the landing page leads to the demo', function () {
    $html = $this->get('/')->getContent();

    preg_match_all('/<a [^>]*href="([^"]+)"[^>]*>\s*(?:Try|Open|Start)[^<]*/', $html, $ctas);

    expect($ctas[1])->not->toBeEmpty()
        ->and(collect($ctas[1])->unique()->values()->all())->toBe([route('demo')]);
});

test('the landing page shows both branches', function () {
    $this->get('/')
        ->assertSee('Hardware & Motor Parts Branch')
        ->assertSee('Tiles & Flooring Branch');
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

test('the old branch picker and login are gone; guests are sent to the demo', function () {
    foreach (['/login', '/select-branch'] as $url) {
        $this->get($url)->assertNotFound();
    }

    $this->get('/dashboard')->assertRedirect(route('demo'));
    $this->get('/pos')->assertRedirect(route('demo'));
});

test('public pages never mention the real store', function () {
    foreach (['/', '/demo', '/demo/branch?role=manager'] as $page) {
        expect($this->get($page)->getContent())->not->toMatch('/RNM|Milaran|Mawab/i');
    }
});
