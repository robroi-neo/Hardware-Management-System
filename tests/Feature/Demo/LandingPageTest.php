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

test('the landing page shows the team from config/team.php', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Meet the team')
        ->assertSee('href="#team"', false)
        ->assertSee('Team Member A')
        ->assertSee('Project lead · Back end')
        ->assertSee('Adviser')
        ->assertSee('Built by Team Member A, Team Member B and Team Member C')
        ->assertDontSee('[Team / course credit]');
});

test('team members show initials without a photo and links only when set', function () {
    config(['team.members' => [
        ['name' => 'Ana Reyes', 'role' => 'Back end', 'note' => null, 'photo' => null, 'github' => 'https://github.com/ana-example', 'linkedin' => null],
        ['name' => 'Ben', 'role' => 'Front end', 'note' => 'Built the POS.', 'photo' => 'images/team/ben.jpg', 'github' => null, 'linkedin' => null],
    ]]);

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)
        ->toContain('>AR</div>')                          // initials for a member with no photo
        ->toContain('src="'.asset('images/team/ben.jpg').'"')
        ->toContain('href="https://github.com/ana-example"')
        ->not->toContain('LinkedIn<span')                 // nobody set a LinkedIn link
        ->toContain('Built by Ana Reyes and Ben');
});

test('the team section is hidden when no members are configured', function () {
    config(['team.members' => []]);

    $this->get('/')
        ->assertOk()
        ->assertDontSee('Meet the team')
        ->assertDontSee('href="#team"', false)
        ->assertDontSee('Built by');
});

test('public pages never mention the real store', function () {
    foreach (['/', '/demo', '/demo/branch?role=manager'] as $page) {
        expect($this->get($page)->getContent())->not->toMatch('/RNM|Milaran|Mawab/i');
    }
});
