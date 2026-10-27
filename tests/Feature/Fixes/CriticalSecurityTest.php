<?php

use App\Models\User;

// Bug #1: public registration must not exist.
test('#1 guests cannot open or submit the registration form', function () {
    $this->get('/register')->assertNotFound();

    $this->post('/register', [
        'name' => 'Stranger',
        'phone' => '09123456789',
        'pin' => '1234',
        'pin_confirmation' => '1234',
        'role' => 'admin',
    ])->assertNotFound();

    expect(User::where('phone', '09123456789')->exists())->toBeFalse();
    $this->assertGuest();
});
