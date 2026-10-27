<?php

use Illuminate\Support\Facades\Schema;

// Bug #6, #7: a fresh database must migrate.
test('migrations create the refund_reasons table on a fresh database', function () {
    expect(Schema::hasTable('refund_reasons'))->toBeTrue()
        ->and(Schema::hasColumns('refund_reasons', ['id', 'name', 'is_active']))->toBeTrue();
});

test('roles and permissions seed without demo data', function () {
    seedRoles();

    expect(\Spatie\Permission\Models\Role::pluck('name')->sort()->values()->all())
        ->toBe(['admin', 'cashier', 'manager']);
});

test('user helper creates an active user with a role', function () {
    $user = userWithRole('cashier');

    expect($user->hasRole('cashier'))->toBeTrue()
        ->and($user->status)->toBe('active');
});
