<?php

namespace App\Support;

use App\Models\User;

/**
 * Small helpers for the public demo.
 */
class Demo
{
    /** Roles a visitor can pick, in display order. */
    public const ROLES = ['admin', 'manager', 'cashier'];

    public static function user(string $role): ?User
    {
        $phone = config("demo.users.{$role}.phone");

        return $phone ? User::where('phone', $phone)->first() : null;
    }

    public static function isDemoUser(?User $user): bool
    {
        return $user !== null
            && in_array($user->phone, array_column(config('demo.users'), 'phone'), true);
    }

    /** Where each role lands after signing in. */
    public static function homeRoute(User $user): string
    {
        return $user->can('dashboard.view') ? 'dashboard' : 'pos';
    }

    /**
     * "Try these" checklist for a role: [label, route name].
     */
    public static function tasks(string $role): array
    {
        return match ($role) {
            'admin' => [
                ['Look around the dashboard', 'dashboard'],
                ['Add a new cashier', 'users.create'],
                ['See who did what in the activity log', 'audit-logs.user-activity'],
                ['Compare stock across both branches', 'inventory.overview'],
            ],
            'manager' => [
                ['Ring up a sale', 'pos'],
                ['Receive stock from a supplier', 'purchasing.new-invoice'],
                ['Refund a sale and watch stock return', 'pos.transactions'],
                ['Check the stock movement log', 'inventory.stock-movements'],
            ],
            default => [
                ['Search for a product', 'pos'],
                ['Ring up a sale with a markup', 'pos'],
                ['Switch to the other branch and compare', 'demo.branch'],
            ],
        };
    }
}
