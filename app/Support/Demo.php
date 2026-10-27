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
}
