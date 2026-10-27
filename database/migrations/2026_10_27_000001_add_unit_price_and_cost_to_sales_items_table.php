<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Store the price each line was sold at, so sale details don't change when a
 * product's capital changes later.
 *
 * Existing rows are backfilled exactly: unit_price = subtotal / quantity, and
 * because selling price = capital + markup, unit_cost = unit_price - markup.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_items', function (Blueprint $table) {
            $table->double('unit_price')->nullable()->after('quantity');
            $table->double('unit_cost')->nullable()->after('unit_price');
        });

        DB::table('sales_items')
            ->where('quantity', '>', 0)
            ->update([
                'unit_price' => DB::raw('subtotal / quantity'),
                'unit_cost' => DB::raw('(subtotal / quantity) - COALESCE(markup, 0)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('sales_items', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'unit_cost']);
        });
    }
};
