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

function startDemo($test, string $role, Branch $branch): void
{
    $test->post('/demo/start', ['role' => $role, 'branch_id' => $branch->id]);
}

test('every app page shows the demo banner with role and branch', function () {
    startDemo($this, 'manager', $this->tiles);

    foreach (['/dashboard', '/pos', '/inventory/overview', '/suppliers'] as $page) {
        $this->get($page)->assertOk()
            ->assertSee('Demo mode')
            ->assertSee('Manager')
            ->assertSee('Tiles & Flooring Branch')
            ->assertSee('Switch role')
            ->assertSee('Exit demo');
    }
});

test('the banner links to switch role and switch branch', function () {
    startDemo($this, 'cashier', $this->hardware);

    $this->get('/pos')
        ->assertSee(route('demo', ['role' => 'cashier']), false)
        ->assertSee(route('demo.branch', ['role' => 'cashier']), false);
});

test('the checklist offers tasks for the current role', function () {
    startDemo($this, 'admin', $this->hardware);
    $this->get('/dashboard')->assertSee('Try these')->assertSee('Add a new cashier');

    startDemo($this, 'cashier', $this->hardware);
    $this->get('/pos')->assertSee('Ring up a sale')->assertDontSee('Add a new cashier');
});

test('exit demo signs out and returns to the landing page', function () {
    startDemo($this, 'manager', $this->hardware);

    $this->post('/logout')->assertRedirect('/');
    $this->assertGuest();
    $this->get('/dashboard')->assertRedirect(route('demo'));
});

test('demo accounts cannot be edited, deactivated or re-roled from the Users page', function () {
    startDemo($this, 'admin', $this->hardware);
    $cashier = Demo::user('cashier');

    $this->putJson("/users/{$cashier->id}", [
        'name' => 'Hacked', 'phone' => $cashier->phone, 'role' => 'admin',
        'branch_id' => $cashier->branch_id, 'status' => 'active',
    ])->assertStatus(422);
    $this->patchJson("/users/{$cashier->id}/deactivate")->assertStatus(422);

    expect($cashier->fresh()->name)->toBe('Demo Cashier')
        ->and($cashier->fresh()->hasRole('cashier'))->toBeTrue()
        ->and($cashier->fresh()->status)->toBe('active');
});

test('other staff can still be edited in the demo', function () {
    startDemo($this, 'admin', $this->hardware);
    $staff = userWithRole('cashier', ['name' => 'Ana Reyes']);

    $this->putJson("/users/{$staff->id}", [
        'name' => 'Ana R.', 'phone' => $staff->phone, 'role' => 'cashier',
        'branch_id' => $staff->branch_id, 'status' => 'active',
    ])->assertOk();

    expect($staff->fresh()->name)->toBe('Ana R.');
});

test('demo users cannot rename themselves from the profile page', function () {
    startDemo($this, 'manager', $this->hardware);

    $this->patch('/profile', ['name' => 'Someone Else'])->assertSessionHas('error');

    expect(Demo::user('manager')->name)->toBe('Demo Manager');
});

test('a visitor whose demo account disappeared (reset) is told and sent back to the start', function () {
    startDemo($this, 'manager', $this->hardware);
    Demo::user('manager')->delete();
    app('auth')->forgetGuards(); // a real next request looks the user up again

    $this->get('/dashboard')->assertRedirect(route('demo'));
    $this->get('/demo')->assertSee('The demo data was just reset');
});

test('no demo page mentions the real store', function () {
    startDemo($this, 'admin', $this->hardware);

    foreach (['/dashboard', '/pos', '/inventory/overview', '/users'] as $page) {
        $html = $this->get($page)->assertOk()->getContent();
        expect($html)->not->toMatch('/RNM|Milaran|Mawab|Davao de Oro/i');
    }
});
