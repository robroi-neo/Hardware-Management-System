<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Roles and permissions only. Kept separate from DatabaseSeeder so tests
 * can load the real permission matrix without seeding demo data.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

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
    }
}
