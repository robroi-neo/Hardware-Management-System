<?php

// Bug #29 (found while fixing): the profile page was Breeze's email/password version.
test('#29 the profile page loads and a user can change their name', function () {
    $user = userWithRole('cashier');

    $this->actingAs($user)->get('/profile')->assertOk()->assertDontSee('Email')->assertDontSee('Update Password');

    $this->actingAs($user)->patch('/profile', ['name' => 'New Name'])->assertSessionHasNoErrors()->assertRedirect('/profile');

    expect($user->fresh()->name)->toBe('New Name');
});

test('#29 the email-based password and verification routes are gone', function () {
    foreach (['/forgot-password', '/reset-password/abc', '/verify-email', '/confirm-password'] as $url) {
        $this->get($url)->assertNotFound();
    }

    $user = userWithRole('cashier');
    expect($this->actingAs($user)->put('/password', [])->status())->toBeIn([404, 405]);
});
