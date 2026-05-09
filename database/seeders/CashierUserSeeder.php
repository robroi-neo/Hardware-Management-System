<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class CashierUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create role if it doesn't exist
        $cashierRole = Role::firstOrCreate([
            'name' => 'cashier'
        ]);

        // Create user
        $user = User::create([
            'name' => 'Cashier User',
            'phone' => '09123456789',
            'address' => 'Sample Address',
            'pin' => bcrypt('1234'),
            'status' => 'active',
        ]);

        // Assign role
        $user->assignRole($cashierRole);
    }
}
