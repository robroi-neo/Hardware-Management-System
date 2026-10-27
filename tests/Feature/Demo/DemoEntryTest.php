<?php

use App\Models\Branch;
use App\Support\Demo;
use Database\Seeders\BranchSeeder;
use Database\Seeders\CatalogSeeder;

beforeEach(function () {
    $this->seed(BranchSeeder::class);
    $this->seed(CatalogSeeder::class);
    seedRoles();
    foreach (config('demo.users') as $role => $demo) {
        userWithRole($role, ['name' => $demo['name'], 'phone' => $demo['phone']]);
    }
    $this->hardware = Branch::orderBy('id')->first();
    $this->tiles = Branch::orderBy('id')->skip(1)->first();
});

test('step 1 shows the three roles with Manager pre-selected', function () {
    $this->get('/demo')
        ->assertOk()
        ->assertSee('Step 1 of 2')
        ->assertSee('Admin')->assertSee('Manager')->assertSee('Cashier')
        ->assertSee('value="manager" checked', false);
});

test('step 2 lists both branches for the chosen role', function () {
    $this->get('/demo/branch?role=cashier')
        ->assertOk()
        ->assertSee('Step 2 of 2')
        ->assertSee($this->hardware->name)
        ->assertSee($this->tiles->name)
        ->assertSee('50 sample products')
        ->assertSee('35 sample products');
});

test('step 2 sends an unknown or missing role back to step 1', function () {
    $this->get('/demo/branch')->assertRedirect('/demo');
    $this->get('/demo/branch?role=superadmin')->assertRedirect('/demo');
});

test('starting the demo signs in the role\'s demo user at the chosen branch', function (string $role, string $home) {
    $this->withSession(['pos_cart' => [['product_id' => 1, 'quantity' => 2]], 'purchasing_cart' => [['product_id' => 1]]])
        ->post('/demo/start', ['role' => $role, 'branch_id' => $this->tiles->id])
        ->assertRedirect(route($home));

    $this->assertAuthenticatedAs(Demo::user($role));
    expect(session('branch.id'))->toBe($this->tiles->id)
        ->and(session('branch.name'))->toBe($this->tiles->name)
        ->and(session('demo.role'))->toBe($role)
        ->and(session('pos_cart'))->toBeNull()
        ->and(session('purchasing_cart'))->toBeNull();
})->with([
    'admin' => ['admin', 'dashboard'],
    'manager' => ['manager', 'dashboard'],
    'cashier' => ['cashier', 'pos'],
]);

test('starting the demo with a bad role or branch signs nobody in', function () {
    $this->post('/demo/start', ['role' => 'superadmin', 'branch_id' => $this->hardware->id])->assertSessionHasErrors('role');
    $this->post('/demo/start', ['role' => 'manager', 'branch_id' => 999])->assertSessionHasErrors('branch_id');
    $this->assertGuest();
});

test('switching role from inside the demo keeps working', function () {
    $this->post('/demo/start', ['role' => 'cashier', 'branch_id' => $this->hardware->id]);
    $this->get('/demo')->assertOk();
    $this->post('/demo/start', ['role' => 'admin', 'branch_id' => $this->hardware->id])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs(Demo::user('admin'));
});

test('the demo user lands on a working page', function () {
    $this->post('/demo/start', ['role' => 'manager', 'branch_id' => $this->hardware->id]);
    $this->get('/dashboard')->assertOk();

    $this->post('/demo/start', ['role' => 'cashier', 'branch_id' => $this->hardware->id]);
    $this->get('/pos')->assertOk();
});
