<?php

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\PosTerminal;
use App\Models\Product;
use App\Models\User;

function makePosUser(): User
{
    return User::create([
        'name' => 'POS User',
        'phone' => '09'.str_pad((string) random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT),
        'pin' => '1234',
    ]);
}

test('product search uses terminal branch inventory even when branch_id is spoofed in query', function () {
    $user = makePosUser();

    $branchA = Branch::create(['name' => 'Branch A', 'address' => 'A']);
    $branchB = Branch::create(['name' => 'Branch B', 'address' => 'B']);

    $terminal = PosTerminal::create([
        'terminal_id' => 3001,
        'terminal_name' => 'Terminal A',
        'branch_id' => $branchA->id,
    ]);

    $product = Product::create([
        'name' => 'Hammer',
        'capital' => 120,
        'unit' => 'piece',
        'status' => 'active',
    ]);

    BranchInventory::create([
        'branch_id' => $branchA->id,
        'product_id' => $product->id,
        'quantity' => 5,
        'capital' => 120,
    ]);

    BranchInventory::create([
        'branch_id' => $branchB->id,
        'product_id' => $product->id,
        'quantity' => 99,
        'capital' => 120,
    ]);

    $response = $this->actingAs($user)
        ->withSession([
            'pos_terminal' => [
                'id' => $terminal->id,
                'terminal_id' => $terminal->terminal_id,
                'terminal_name' => $terminal->terminal_name,
                'branch_id' => $terminal->branch_id,
            ],
        ])
        ->get(route('pos.api.products.search', [
            'q' => 'Hammer',
            'branch_id' => $branchB->id,
        ]));

    $response->assertOk();
    $response->assertJsonPath('0.id', $product->id);
    $response->assertJsonPath('0.available_quantity', 5);
});

test('checkout finalize always uses terminal branch and ignores spoofed branch_id payload', function () {
    $user = makePosUser();

    $branchA = Branch::create(['name' => 'Branch A', 'address' => 'A']);
    $branchB = Branch::create(['name' => 'Branch B', 'address' => 'B']);

    $terminal = PosTerminal::create([
        'terminal_id' => 3002,
        'terminal_name' => 'Terminal A2',
        'branch_id' => $branchA->id,
    ]);

    $product = Product::create([
        'name' => 'Screwdriver',
        'capital' => 50,
        'unit' => 'piece',
        'status' => 'active',
    ]);

    BranchInventory::create([
        'branch_id' => $branchA->id,
        'product_id' => $product->id,
        'quantity' => 4,
        'capital' => 50,
    ]);

    BranchInventory::create([
        'branch_id' => $branchB->id,
        'product_id' => $product->id,
        'quantity' => 20,
        'capital' => 50,
    ]);

    $response = $this->actingAs($user)
        ->withSession([
            'pos_terminal' => [
                'id' => $terminal->id,
                'terminal_id' => $terminal->terminal_id,
                'terminal_name' => $terminal->terminal_name,
                'branch_id' => $terminal->branch_id,
            ],
            'pos_cart' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ])
        ->post(route('pos.api.checkout.finalize'), [
            'payment_method' => 'cash',
            'branch_id' => $branchB->id,
        ]);

    $response->assertOk();
    $response->assertJsonPath('receipt.payment_method', 'cash');
    $response->assertJsonPath('receipt.branch_id', $branchA->id);
    $response->assertJsonPath('receipt.terminal_id', $terminal->terminal_id);
    $response->assertJsonPath('receipt.items.0.product_id', $product->id);
    $response->assertJsonPath('receipt.items.0.product_name', 'Screwdriver');
    $response->assertJsonPath('receipt.items.0.subtotal', 100);

    $this->assertDatabaseHas('sales', [
        'user_id' => $user->id,
        'branch_id' => $branchA->id,
        'total_amount' => 100,
        'payment_method' => 'cash',
    ]);

    $this->assertDatabaseHas('branch_inventory', [
        'branch_id' => $branchA->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $this->assertDatabaseHas('branch_inventory', [
        'branch_id' => $branchB->id,
        'product_id' => $product->id,
        'quantity' => 20,
    ]);
});

test('checkout finalize rejects non-cash payment method', function () {
    $user = makePosUser();

    $branch = Branch::create(['name' => 'Branch Cash', 'address' => 'Cash']);

    $terminal = PosTerminal::create([
        'terminal_id' => 3003,
        'terminal_name' => 'Terminal Cash',
        'branch_id' => $branch->id,
    ]);

    $product = Product::create([
        'name' => 'Wrench',
        'capital' => 80,
        'unit' => 'piece',
        'status' => 'active',
    ]);

    BranchInventory::create([
        'branch_id' => $branch->id,
        'product_id' => $product->id,
        'quantity' => 10,
        'capital' => 80,
    ]);

    $response = $this->actingAs($user)
        ->withSession([
            'pos_terminal' => [
                'id' => $terminal->id,
                'terminal_id' => $terminal->terminal_id,
                'terminal_name' => $terminal->terminal_name,
                'branch_id' => $terminal->branch_id,
            ],
            'pos_cart' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])
        ->post(route('pos.api.checkout.finalize'), [
            'payment_method' => 'cheque',
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('payment_method');
    $this->assertDatabaseCount('sales', 0);
});

test('checkout prepare exposes cash as only payment method', function () {
    $user = makePosUser();

    $response = $this->actingAs($user)
        ->withSession([
            'pos_terminal' => [
                'id' => 1,
                'terminal_id' => 3004,
                'terminal_name' => 'Terminal Methods',
                'branch_id' => 1,
            ],
        ])
        ->get(route('pos.api.checkout.prepare'));

    $response->assertOk();
    $response->assertJsonPath('payment_methods.0', 'cash');
    $response->assertJsonCount(1, 'payment_methods');
});

