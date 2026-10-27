<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the refund_reasons table.
 *
 * The table was originally created outside of migrations, so a fresh install
 * failed at 2026_05_23_000001_add_fields_to_refund_reasons_table. Columns
 * `name` and `is_active` are added by that later migration.
 *
 * Databases that already have the table skip this migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('refund_reasons')) {
            return;
        }

        Schema::create('refund_reasons', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_reasons');
    }
};
