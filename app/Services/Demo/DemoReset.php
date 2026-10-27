<?php

namespace App\Services\Demo;

use App\Support\DemoState;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use PDO;
use RuntimeException;

/**
 * Puts the demo database back to a clean snapshot.
 *
 * - buildSnapshot(): migrate + seed a fresh SQLite file and keep it as the snapshot.
 * - restore(): copy the snapshot over the live database (atomic rename), shifting
 *   its dates forward so the latest sale is always a few minutes old.
 *
 * Only works with a file-based SQLite database. The snapshot's build time is kept
 * next to it in "<snapshot>.json" for reference.
 */
class DemoReset
{
    /** Date/time columns moved forward on restore. */
    private const DATE_COLUMNS = [
        'sales' => ['date', 'refunded_at', 'created_at', 'updated_at'],
        'sales_items' => ['created_at', 'updated_at'],
        'purchases' => ['date', 'created_at', 'updated_at'],
        'purchase_details' => ['created_at', 'updated_at'],
        'invoices' => ['date_issued', 'date_due', 'refunded_at', 'created_at', 'updated_at'],
        'inventory_movements' => ['created_at', 'updated_at'],
        'audit_logs' => ['created_at'],
    ];

    public function snapshotPath(): string
    {
        return config('demo.snapshot_path');
    }

    public function snapshotExists(): bool
    {
        return is_file($this->snapshotPath());
    }

    /**
     * Build the snapshot by running migrate:fresh --seed into a temporary file.
     */
    public function buildSnapshot(): void
    {
        $snapshot = $this->snapshotPath();
        $temp = $snapshot.'.building';
        @unlink($temp);
        touch($temp);

        $result = Process::path(base_path())
            ->env(['DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $temp, 'DB_URL' => ''])
            ->timeout(300)
            ->run([PHP_BINARY, 'artisan', 'migrate:fresh', '--seed', '--force', '--no-interaction']);

        if (! $result->successful()) {
            @unlink($temp);
            throw new RuntimeException("Could not build the demo snapshot:\n".$result->errorOutput().$result->output());
        }

        $this->replaceFile($temp, $snapshot);
        file_put_contents($snapshot.'.json', json_encode(['built_at' => now()->toIso8601String()]));
    }

    /**
     * Copy the snapshot over the database. Returns false if there is nothing to do
     * (no snapshot, or the database is not a SQLite file).
     */
    public function restore(?string $target = null): bool
    {
        $target ??= $this->liveDatabasePath();

        if (! $target || ! $this->snapshotExists()) {
            return false;
        }

        // One reset at a time, across processes (a file lock survives the swap).
        $lock = fopen(storage_path('app/demo-reset.lock'), 'c');
        if (! flock($lock, LOCK_EX | LOCK_NB)) {
            fclose($lock);

            return false; // another reset is running
        }

        try {
            $temp = $target.'.restoring';
            if (! copy($this->snapshotPath(), $temp)) {
                throw new RuntimeException('Could not copy the demo snapshot.');
            }

            $this->shiftDates($temp, $this->secondsToShift($temp));

            $isLive = $target === $this->liveDatabasePath();
            if ($isLive) {
                DB::disconnect();
            }

            $this->replaceFile($temp, $target);

            if ($isLive) {
                DB::purge();
                DB::reconnect();
            }

            DemoState::markReset();

            return true;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /**
     * Seconds to move history forward so the latest sale lands 15 minutes ago.
     * That keeps "today" filled at any hour and nothing in the future.
     */
    public function secondsToShift(string $path): int
    {
        $pdo = new PDO('sqlite:'.$path);
        $hasSales = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'sales'")->fetchColumn();
        $latest = $hasSales ? $pdo->query('SELECT MAX(date) FROM sales')->fetchColumn() : null;

        if (! $latest) {
            return 0;
        }

        // Stored dates are in the app timezone (Laravel's default for SQLite).
        $latestAt = Carbon::parse($latest, config('app.timezone'));

        return max(0, (int) $latestAt->diffInSeconds(now()->subMinutes(15), false));
    }

    /** Move every history date forward by $seconds, in the SQLite file at $path. */
    public function shiftDates(string $path, int $seconds): void
    {
        if ($seconds <= 0) {
            return;
        }

        $pdo = new PDO('sqlite:'.$path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        foreach (self::DATE_COLUMNS as $table => $columns) {
            $existing = array_column($pdo->query("PRAGMA table_info(\"{$table}\")")->fetchAll(PDO::FETCH_ASSOC), 'name');
            foreach (array_intersect($columns, $existing) as $column) {
                $pdo->exec("UPDATE \"{$table}\" SET \"{$column}\" = datetime(\"{$column}\", '+{$seconds} seconds') WHERE \"{$column}\" IS NOT NULL");
            }
        }
    }

    /** Path of the app's SQLite database file, or null if it isn't one. */
    public function liveDatabasePath(): ?string
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (($config['driver'] ?? null) !== 'sqlite') {
            return null;
        }

        $path = $config['database'] ?? null;

        return $path && $path !== ':memory:' && is_file($path) ? $path : null;
    }

    /** Atomic replace where the OS allows it (Linux); copy as a fallback (Windows). */
    private function replaceFile(string $from, string $to): void
    {
        if (@rename($from, $to)) {
            return;
        }

        if (! copy($from, $to)) {
            throw new RuntimeException("Could not replace {$to}.");
        }
        @unlink($from);
    }
}
