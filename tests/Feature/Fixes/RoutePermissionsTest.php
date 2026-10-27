<?php

use App\Models\Supplier;

function supplierPayload(array $overrides = []): array
{
    return array_merge([
        'supplier_name' => 'Acme Hardware Supply',
        'contact_person' => 'Juan Dela Cruz',
        'company_address' => 'Davao City',
        'contact_number' => '09170000000',
        'contact_email' => 'acme@example.com',
        'status' => 'active',
    ], $overrides);
}

// Bug #8: each supplier action must check its own permission.
test('#8 a manager without suppliers.delete cannot delete a supplier', function () {
    $manager = userWithRole('manager');
    $supplier = Supplier::create(supplierPayload());

    $this->actingAs($manager)->delete("/suppliers/{$supplier->id}")->assertForbidden();

    expect(Supplier::find($supplier->id))->not->toBeNull();
});

test('#8 a user without suppliers.create cannot add a supplier', function () {
    $user = userWithRole('cashier');
    $user->givePermissionTo('suppliers.view');

    $this->actingAs($user)->post('/suppliers', supplierPayload())->assertForbidden();

    expect(Supplier::count())->toBe(0);
});

test('#8 a user without suppliers.edit cannot update a supplier', function () {
    $user = userWithRole('cashier');
    $user->givePermissionTo('suppliers.view');
    $supplier = Supplier::create(supplierPayload());

    $this->actingAs($user)->put("/suppliers/{$supplier->id}", supplierPayload(['contact_person' => 'Changed']))
        ->assertForbidden();

    expect($supplier->fresh()->contact_person)->toBe('Juan Dela Cruz');
});

test('#8 a manager can still add and edit suppliers, and an admin can delete', function () {
    $manager = userWithRole('manager');
    $admin = userWithRole('admin');

    $this->actingAs($manager)->post('/suppliers', supplierPayload())->assertRedirect();
    $supplier = Supplier::firstOrFail();

    $this->actingAs($manager)->put("/suppliers/{$supplier->id}", supplierPayload(['contact_person' => 'Maria']))
        ->assertRedirect();
    expect($supplier->fresh()->contact_person)->toBe('Maria');

    $this->actingAs($admin)->delete("/suppliers/{$supplier->id}")->assertRedirect();
    expect(Supplier::find($supplier->id))->toBeNull();
});
