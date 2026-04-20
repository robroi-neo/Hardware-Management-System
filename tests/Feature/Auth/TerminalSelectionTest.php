<?php

use App\Models\Branch;
use App\Models\PosTerminal;

test('login screen redirects to terminal selection when no terminal is selected', function () {
    $response = $this->get('/login');

    $response->assertRedirect(route('terminal.select', absolute: false));
});

test('guest can select terminal before logging in', function () {
    $branch = Branch::create([
        'name' => 'Test Branch',
        'address' => 'Test Address',
    ]);

    $terminal = PosTerminal::create([
        'terminal_id' => 2001,
        'terminal_name' => 'Terminal Test',
        'branch_id' => $branch->id,
    ]);

    $response = $this->post('/terminal/select', [
        'terminal_id' => $terminal->id,
    ]);

    $response->assertRedirect(route('login', absolute: false));
    $response->assertSessionHas('pos_terminal.id', $terminal->id);
    $response->assertSessionHas('pos_terminal.branch_id', $branch->id);
});

