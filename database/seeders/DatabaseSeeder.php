<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
        ]);

        // create permission for spatie (dot-separated resource.action format)
        $permissions = [
            // Dashboard
            'dashboard.view',

            // POS / Sales
            'pos.access',
            'sales.create',
            'sales.view-history',
            'sales.refund',
            'sales.print-receipt',

            // Purchasing
            'purchases.create',
            'purchases.view-history',
            'purchases.refund',

            // Inventory
            'inventory.view-overview',
            'inventory.update',
            'inventory.view-movements',
            'inventory.archive',
            'inventory.delete',

            // Audit Logs
            'audit.user-activity.view',
            'audit.system-logs.view',

            // Reports
            'reports.view',

            // Users
            'users.create',
            'users.edit',
            'users.delete',
            'users.view-list',

            // Suppliers
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',
            'suppliers.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // create roles for spatie
        $adminRole = Role::firstOrCreate(['name'=>'admin']);
        $managerRole = Role::firstOrCreate(['name'=>'manager']);
        $cashierRole = Role::firstOrCreate(['name'=>'cashier']);

        // give permissions to role
        $adminRole->givePermissionTo(Permission::all());

        // Manager: mostly sales, inventory, reports, users
        $managerRole->syncPermissions([
            'pos.access',

            'sales.create',
            'sales.view-history',
            'sales.refund',

            'purchases.create',
            'purchases.view-history',
            'purchases.refund',
            'inventory.view-overview',
            'inventory.update',
            'inventory.view-movements',
            'inventory.archive',

            'suppliers.create',
            'suppliers.edit',
            'suppliers.view',
        ]);

        // Cashier: sales only
        $cashierRole->syncPermissions([
            'pos.access',
            'sales.create',
            'sales.print-receipt',
        ]);


        // Default admin user
        $admin = User::firstOrCreate(
            ['phone' => '09362690603'],
            [
                'name' => 'Admin',
                'pin' => Hash::make('1234'),
                'status' => 'active',
                'branch_id' => 1, // assign to first branch by default
            ]
        );

        // Default cashier user
        $cashier = User::firstOrCreate(
            ['phone' => '09287476832'],
            [
                'name' => 'Cashier',
                'pin' => Hash::make('1234'),
                'status' => 'active',
                'branch_id' => 1,
            ]
        );

        // Default manager user
        $manager = User::firstOrCreate(
            ['phone' => '09108712969'],
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
