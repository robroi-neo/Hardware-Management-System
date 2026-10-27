<?php

/*
| Public demo settings. The deployment is demo-only: visitors pick a role and
| a sample branch and are signed in as that role's demo user.
*/

return [

    // One demo user per role. The phone numbers are obviously fake on purpose.
    'users' => [
        'admin' => ['name' => 'Demo Admin', 'phone' => '09000000001'],
        'manager' => ['name' => 'Demo Manager', 'phone' => '09000000003'],
        'cashier' => ['name' => 'Demo Cashier', 'phone' => '09000000002'],
    ],

    // How often the data is put back to the clean snapshot.
    'reset_minutes' => (int) env('DEMO_RESET_MINUTES', 60),

    // Clean copy of the demo database, built by `php artisan demo:snapshot`.
    'snapshot_path' => env('DEMO_SNAPSHOT_PATH', database_path('demo-snapshot.sqlite')),

    // When the last reset happened (kept outside the database so a reset can't wipe it).
    'state_path' => storage_path('app/demo-state.json'),

    // Days of sales and purchase history the seeder writes.
    'history_days' => 28,

];
