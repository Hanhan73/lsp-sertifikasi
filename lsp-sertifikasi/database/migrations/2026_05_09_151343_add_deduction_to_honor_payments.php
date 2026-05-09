<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('honor_payments', function (Blueprint $table) {
            // Cicilan hutang asesor (opsional)
            $table->foreignId('deduction_receivable_id')
                  ->nullable()
                  ->after('bukti_transfer_path')
                  ->constrained('other_receivables')
                  ->nullOnDelete();
            $table->decimal('deduction_amount', 15, 2)->nullable()->after('deduction_receivable_id');
            $table->text('deduction_note')->nullable()->after('deduction_amount');
        });
    }

    public function down(): void
    {
        Schema::table('honor_payments', function (Blueprint $table) {
            $table->dropForeign(['deduction_receivable_id']);
            $table->dropColumn(['deduction_receivable_id', 'deduction_amount', 'deduction_note']);
        });
    }
};