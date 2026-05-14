<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support altering enum columns directly
        // We need to recreate the table with the new enum values

        Schema::table('products', function (Blueprint $table) {
            // Drop the old enum constraint by recreating the column
            $table->string('status')->default('active')->change();
        });

        // Update any existing 'inactive' status to 'active' if needed
        DB::table('products')->where('status', 'inactive')->update(['status' => 'active']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the original enum values
        Schema::table('products', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active')->change();
        });
    }
};
