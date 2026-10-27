<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Placeholder demo catalog: hardware and motor parts at the first branch,
 * tiles and flooring at the second. Products and starting stock come from
 * database/seeders/data/catalog.csv, so the list can be edited in a spreadsheet.
 *
 * CSV columns: branch (hardware|tiles), category, name, unit, capital, stock.
 * `capital` is the cost price in pesos; cashiers add a markup at the POS.
 */
class CatalogSeeder extends Seeder
{
    private const BRANCH_NAMES = [
        'hardware' => "Milaran's Hardware and Motor Parts",
        'tiles' => "Milaran's Tiles and Flooring Division",
    ];

    public function run(): void
    {
        $branchIds = $this->branchIds();
        $rows = $this->readCatalog(database_path('seeders/data/catalog.csv'));
        $now = now();

        DB::transaction(function () use ($rows, $branchIds, $now) {
            foreach ($rows as $row) {
                $productId = DB::table('products')->insertGetId([
                    'name' => $row['name'],
                    'capital' => (float) $row['capital'],
                    'unit' => $row['unit'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('branch_inventory')->insert([
                    'branch_id' => $branchIds[$row['branch']],
                    'product_id' => $productId,
                    'quantity' => (float) $row['stock'],
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    /**
     * Branch ids by catalog key. Falls back to the first and second branch by id
     * if the branch names were changed.
     */
    private function branchIds(): array
    {
        $ordered = Branch::orderBy('id')->pluck('id')->values();
        $ids = [];

        foreach (array_keys(self::BRANCH_NAMES) as $index => $key) {
            $ids[$key] = Branch::where('name', self::BRANCH_NAMES[$key])->value('id') ?? $ordered->get($index);

            if (! $ids[$key]) {
                throw new RuntimeException('CatalogSeeder needs two branches. Run BranchSeeder first.');
            }
        }

        return $ids;
    }

    private function readCatalog(string $path): array
    {
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, escape: '');
        $rows = [];

        while (($values = fgetcsv($handle, escape: '')) !== false) {
            if ($values === [null] || $values === []) {
                continue; // blank line
            }

            $row = array_combine($header, array_map('trim', $values));

            if (! isset(self::BRANCH_NAMES[$row['branch']])) {
                throw new RuntimeException("Unknown branch \"{$row['branch']}\" in catalog.csv for {$row['name']}");
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }
}
