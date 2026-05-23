<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('refund_reason_id')->nullable()->constrained('refund_reasons')->nullOnDelete()->after('refunded_at');
            $table->text('refund_note')->nullable()->after('refund_reason_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['refund_reason_id']);
            $table->dropColumn(['refund_reason_id', 'refund_note']);
        });
    }
};
