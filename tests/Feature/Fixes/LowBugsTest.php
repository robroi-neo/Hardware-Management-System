<?php

// Bug #20: the stock overview "Archived" filter must use branch_inventory.status.
test('#20 stock overview status filter matches the branch inventory status', function () {
    $admin = userWithRole('admin');
    $branch = $admin->branch;
    $active = stockedProduct($branch->id, 5, 'Hammer');
    $archived = \App\Models\Product::create(['name' => 'Old Drill', 'capital' => 100, 'unit' => 'piece']);
    \App\Models\BranchInventory::create(['branch_id' => $branch->id, 'product_id' => $archived->id, 'quantity' => 0, 'status' => 'inactive']);

    $names = fn ($status) => collect($this->actingAs($admin)->withSession(branchSession($branch))
        ->get('/inventory/overview?status='.$status)->assertOk()->viewData('inventories')->items())
        ->map(fn ($row) => $row->product->name)->all();

    expect($names('inactive'))->toBe(['Old Drill'])
        ->and($names('active'))->toBe(['Hammer']);
});

// Bug #21: the audit log "Refunds" filter must find refunds.
test('#21 audit log Refunds filter returns sale and purchase refunds', function () {
    $admin = userWithRole('admin');
    \App\Models\AuditLog::create(['user_id' => $admin->id, 'entity_type' => 'sale', 'entity_id' => 1, 'action' => 'created']);
    \App\Models\AuditLog::create(['user_id' => $admin->id, 'entity_type' => 'sale', 'entity_id' => 1, 'action' => 'refund']);
    \App\Models\AuditLog::create(['user_id' => $admin->id, 'entity_type' => 'purchase', 'entity_id' => 2, 'action' => 'refund']);

    $logs = $this->actingAs($admin)->get('/audit-logs/user-activity?entity_type=refund')->assertOk()->viewData('logs');

    expect($logs->total())->toBe(2)
        ->and(collect($logs->items())->pluck('action')->unique()->all())->toBe(['refund']);
});

// Bug #22: the lockout message must be attached to the phone field.
test('#22 too many failed logins shows the lockout message on the phone field', function () {
    $branch = makeBranch();
    userWithRole('cashier', ['phone' => '09777777777']);

    foreach (range(1, 5) as $_) {
        $this->withSession(branchSession($branch))->post('/login', ['phone' => '09777777777', 'pin' => 'wrong']);
    }

    $this->withSession(branchSession($branch))
        ->post('/login', ['phone' => '09777777777', 'pin' => 'wrong'])
        ->assertSessionHasErrors(['phone' => trans('auth.throttle', ['seconds' => 60, 'minutes' => 1])]);
});

// Bug #23: invoice quantity input follows the unit's precision.
test('#23 the new-invoice page gets unit rules and uses a per-unit step', function () {
    $manager = userWithRole('manager');
    $branch = $manager->branch;

    $this->actingAs($manager)->withSession(branchSession($branch))
        ->get('/purchasing/new-invoice')
        ->assertOk()
        ->assertSee(':step="unitStep(item.product_unit)"', false)
        ->assertSee('"kg":{"step":0.01', false)
        ->assertDontSee('step="1"', false);
});

// Bug #24: managers can open the dashboard.
test('#24 a manager can open the dashboard; a cashier still cannot', function () {
    $manager = userWithRole('manager');
    $cashier = userWithRole('cashier');
    $branch = $manager->branch;

    $this->actingAs($manager)->withSession(branchSession($branch))->get('/dashboard')->assertOk();
    $this->actingAs($cashier)->withSession(branchSession($branch))->get('/dashboard')->assertForbidden();
});

// Bug #25, #26: every PHP file parses, and no terminal-era leftovers remain.
test('#25 every PHP file under app/ parses', function () {
    $broken = [];
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path('app')));
    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        exec('php -l '.escapeshellarg($file->getPathname()).' 2>&1', $out, $code);
        if ($code !== 0) {
            $broken[] = str_replace(base_path().'/', '', $file->getPathname());
        }
    }

    expect($broken)->toBe([]);
});

test('#26 the removed terminal feature leaves nothing behind', function () {
    expect(method_exists(\App\Models\Branch::class, 'terminals'))->toBeFalse()
        ->and(file_exists(app_path('Http/Middleware/TestMiddleware.php')))->toBeFalse();

    // A stale pos_terminal session key must not be used as a branch.
    $request = \Illuminate\Http\Request::create('/');
    $request->setLaravelSession(app('session.store'));
    $request->session()->put('pos_terminal', ['branch_id' => 1]);

    expect(fn () => app(\App\Services\Inventory\BranchContextService::class)->resolveTerminalBranchId($request))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

// Bug #27: purchasing checkout/prepare reports the session branch.
test('#27 purchasing checkout/prepare returns the session branch', function () {
    $manager = userWithRole('manager');
    $branch = $manager->branch;
    $product = stockedProduct($branch->id, 0);

    $this->actingAs($manager)->withSession(branchSession($branch));
    $this->postJson('/purchasing/api/cart/add', ['product_id' => $product->id, 'quantity' => 2])->assertSuccessful();

    $this->getJson('/purchasing/api/checkout/prepare')->assertOk()->assertJsonPath('data.branch_id', $branch->id);
});

// Bug #28: no real-looking phone numbers in the seeder.
test('#28 the seeder uses obviously fake phone numbers', function () {
    // Demo user phones now live in config/demo.php.
    $sources = file_get_contents(database_path('seeders/DatabaseSeeder.php'))
        .file_get_contents(config_path('demo.php'));

    expect($sources)->not->toContain('09362690603')
        ->not->toContain('09287476832')
        ->not->toContain('09108712969')
        ->toContain('09000000001');
});
