<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // call database seeders
        $this->call([
            BranchSeeder::class,
            ProductSeeder::class,
            ProductFactorySeeder::class,
            BranchInventorySeeder::class,
            SupplierSeeder::class,
            RefundReasonSeeder::class,
        ]);

        $this->call(RolePermissionSeeder::class);

        // Default admin user
        $admin = User::firstOrCreate(
            ['phone' => '09000000001'],
            [
                'name' => 'Admin',
                'pin' => Hash::make('1234'),
                'status' => 'active',
                'branch_id' => 1, // assign to first branch by default
            ]
        );

        // Default cashier user
        $cashier = User::firstOrCreate(
            ['phone' => '09000000002'],
            [
                'name' => 'Cashier',
                'pin' => Hash::make('1234'),
                'status' => 'active',
                'branch_id' => 1,
            ]
        );

        // Default manager user
        $manager = User::firstOrCreate(
            ['phone' => '09000000003'],
            [
                'name' => 'Manager',
                'pin' => Hash::make('1234'),
                'status' => 'active',
                'branch_id' => 1,
            ]
        );

        $admin->syncRoles(['admin']);
        $cashier->syncRoles(['cashier']);
        $manager->syncRoles(['manager']);

        User::factory(50)->create()->each(function ($user) {
            $user->assignRole('cashier');
        });
    }
}
