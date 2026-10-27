<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * When the demo data was last reset. Kept in a small JSON file outside the
 * database, so resetting the database can't wipe it.
 */
class DemoState
{
    public static function lastResetAt(): ?Carbon
    {
        $path = config('demo.state_path');

        if (! is_file($path)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($path), true);

        return isset($data['last_reset_at']) ? Carbon::parse($data['last_reset_at']) : null;
    }

    public static function markReset(?Carbon $at = null): void
    {
        $path = config('demo.state_path');

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode(['last_reset_at' => ($at ?? now())->toIso8601String()]));
    }

    public static function nextResetAt(): Carbon
    {
        $minutes = max(1, (int) config('demo.reset_minutes', 60));
        $last = self::lastResetAt();

        // Before the first reset, count to the next whole interval.
        return $last
            ? $last->copy()->addMinutes($minutes)
            : now()->startOfHour()->addMinutes($minutes);
    }

    public static function isDue(): bool
    {
        return now()->greaterThanOrEqualTo(self::nextResetAt());
    }
}
