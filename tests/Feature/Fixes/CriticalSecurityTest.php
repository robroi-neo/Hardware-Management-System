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

// Bug #2: user management must require users.create / users.edit.
test('#2 a cashier cannot create users', function () {
    $cashier = userWithRole('cashier');

    $this->actingAs($cashier)->post('/users/create', [
        'name' => 'Evil', 'phone' => '09222222222', 'address' => 'x', 'role' => 'admin',
        'pin' => '1234', 'pin_confirmation' => '1234', 'branch_id' => $cashier->branch_id,
    ])->assertForbidden();

    expect(User::where('phone', '09222222222')->exists())->toBeFalse();
});

test('#2 a manager cannot edit other users or promote anyone', function () {
    $admin = userWithRole('admin');
    $manager = userWithRole('manager');

    $this->actingAs($manager)->putJson("/users/{$admin->id}", [
        'name' => 'Hacked', 'phone' => $admin->phone, 'role' => 'cashier',
        'branch_id' => $admin->branch_id, 'status' => 'active',
    ])->assertForbidden();

    $this->actingAs($manager)->putJson("/users/{$manager->id}", [
        'name' => $manager->name, 'phone' => $manager->phone, 'role' => 'admin',
        'branch_id' => $manager->branch_id, 'status' => 'active',
    ])->assertForbidden();

    expect($admin->fresh()->name)->not->toBe('Hacked')
        ->and($admin->fresh()->hasRole('admin'))->toBeTrue()
        ->and($manager->fresh()->hasRole('admin'))->toBeFalse();
});

test('#2 an admin can still create and edit users', function () {
    $admin = userWithRole('admin');
    $cashier = userWithRole('cashier');

    $this->actingAs($admin)->post('/users/create', [
        'name' => 'New Cashier', 'phone' => '09333333333', 'address' => 'x', 'role' => 'cashier',
        'pin' => '1234', 'pin_confirmation' => '1234', 'branch_id' => $admin->branch_id,
    ])->assertRedirect();

    expect(User::where('phone', '09333333333')->first()?->hasRole('cashier'))->toBeTrue();

    $this->actingAs($admin)->putJson("/users/{$cashier->id}", [
        'name' => 'Renamed', 'phone' => $cashier->phone, 'role' => 'manager',
        'branch_id' => $cashier->branch_id, 'status' => 'active',
    ])->assertOk();

    expect($cashier->fresh()->name)->toBe('Renamed')
        ->and($cashier->fresh()->hasRole('manager'))->toBeTrue();
});

test('#2 roles outside admin, manager, cashier are rejected', function () {
    $admin = userWithRole('admin');

    $this->actingAs($admin)->post('/users/create', [
        'name' => 'Odd', 'phone' => '09444444444', 'address' => 'x', 'role' => 'superadmin',
        'pin' => '1234', 'pin_confirmation' => '1234', 'branch_id' => $admin->branch_id,
    ])->assertSessionHasErrors('role');

    expect(User::where('phone', '09444444444')->exists())->toBeFalse();
});

test('#2 an admin cannot demote or deactivate their own account', function () {
    $admin = userWithRole('admin');

    $this->actingAs($admin)->putJson("/users/{$admin->id}", [
        'name' => $admin->name, 'phone' => $admin->phone, 'role' => 'cashier',
        'branch_id' => $admin->branch_id, 'status' => 'inactive',
    ])->assertStatus(422);

    expect($admin->fresh()->hasRole('admin'))->toBeTrue()
        ->and($admin->fresh()->status)->toBe('active');
});

test('#2 an admin cannot deactivate themselves via the deactivate action', function () {
    $admin = userWithRole('admin');

    $this->actingAs($admin)->patchJson("/users/{$admin->id}/deactivate")->assertStatus(422);

    expect($admin->fresh()->status)->toBe('active');
});

// Bug #3: deactivated users must not be able to log in or keep using the app.
test('#3 an inactive user cannot log in', function () {
    $branch = makeBranch();
    $user = userWithRole('cashier', ['status' => 'inactive', 'phone' => '09555555555']);

    $this->withSession(branchSession($branch))
        ->post('/login', ['phone' => '09555555555', 'pin' => '1234'])
        ->assertSessionHasErrors('phone');

    $this->assertGuest();
});

test('#3 an active user can still log in', function () {
    $branch = makeBranch();
    userWithRole('cashier', ['phone' => '09666666666']);

    $this->withSession(branchSession($branch))
        ->post('/login', ['phone' => '09666666666', 'pin' => '1234'])
        ->assertRedirect();

    $this->assertAuthenticated();
});

test('#3 a user deactivated mid-session is signed out on the next request', function () {
    $branch = makeBranch();
    $user = userWithRole('cashier');

    $this->actingAs($user)->withSession(branchSession($branch))->get('/pos')->assertOk();

    $user->update(['status' => 'inactive']);

    $this->get('/pos')->assertRedirect('/');
    $this->assertGuest();
});

// Bug #4: users must not be able to delete their own account.
test('#4 a user cannot delete their own account', function () {
    $user = userWithRole('cashier');

    $response = $this->actingAs($user)->delete('/profile', ['password' => '1234']);

    expect($response->status())->toBeIn([404, 405]);
    expect(\App\Models\User::find($user->id))->not->toBeNull();
});
