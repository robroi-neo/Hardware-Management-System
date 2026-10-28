<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


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
            CatalogSeeder::class, // placeholder catalog from seeders/data/catalog.csv
            SupplierSeeder::class,
            RefundReasonSeeder::class,
        ]);

        $this->call(RolePermissionSeeder::class);

        $hardware = Branch::orderBy('id')->value('id');
        $tiles = Branch::orderBy('id')->skip(1)->value('id') ?? $hardware;

        // Demo users, one per role (config/demo.php). Visitors are signed in as these.
        foreach (config('demo.users') as $role => $demo) {
            $user = User::updateOrCreate(
                ['phone' => $demo['phone']],
                [
                    'name' => $demo['name'],
                    'pin' => Hash::make('1234'),
                    'status' => 'active',
                    'branch_id' => $hardware,
                ]
            );
            $user->syncRoles([$role]);
        }

        // A few named staff so the Users page and logs look like a real store.
        $admin = User::where('phone', config('demo.users.admin.phone'))->first();
        $staff = [
            ['Jun Mercado', '09000000011', 'cashier', $hardware, 'active'],
            ['Ana Reyes', '09000000012', 'cashier', $tiles, 'active'],
            ['Liza Bautista', '09000000013', 'manager', $tiles, 'active'],
            ['Ramon Cruz', '09000000014', 'cashier', $hardware, 'inactive'],
        ];

        foreach ($staff as [$name, $phone, $role, $branchId, $status]) {
            $user = User::updateOrCreate(
                ['phone' => $phone],
                [
                    'name' => $name,
                    'pin' => Hash::make(Str::random(12)), // not meant to sign in
                    'status' => $status,
                    'branch_id' => $branchId,
                    'created_by' => $admin?->id,
                    'address' => 'Davao de Oro',
                ]
            );
            $user->syncRoles([$role]);
        }

        // About four weeks of sales, refunds, purchases and stock movements.
        $this->call(DemoHistorySeeder::class);
    }
}
