<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Load the real roles and permissions (RolePermissionSeeder).
 */
function seedRoles(): void
{
    test()->seed(\Database\Seeders\RolePermissionSeeder::class);
}

function makeBranch(string $name = 'Hardware Branch'): \App\Models\Branch
{
    return \App\Models\Branch::create(['name' => $name, 'address' => 'Test Address']);
}

/**
 * An active user with the given spatie role. Seeds roles and a branch if needed.
 */
function userWithRole(string $role, array $attributes = []): \App\Models\User
{
    if (\Spatie\Permission\Models\Role::count() === 0) {
        seedRoles();
    }

    $branch = \App\Models\Branch::first() ?? makeBranch();

    $user = \App\Models\User::factory()->create(array_merge([
        'status' => 'active',
        'branch_id' => $branch->id,
        'created_by' => null,
    ], $attributes));

    $user->assignRole($role);

    return $user;
}

/**
 * Session payload the app expects after branch selection.
 */
function branchSession(\App\Models\Branch $branch): array
{
    return ['branch' => ['id' => $branch->id, 'name' => $branch->name, 'address' => $branch->address]];
}
