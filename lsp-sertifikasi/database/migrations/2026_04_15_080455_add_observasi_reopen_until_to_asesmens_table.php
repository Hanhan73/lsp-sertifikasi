<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asesmens', function (Blueprint $table) {
            // Batas waktu reopen soal observasi yang di-set asesor per-asesi
            $table->timestamp('observasi_reopen_until')->nullable()->after('admin_started_at');
            // Siapa yang mereopen (untuk audit trail)
            $table->foreignId('observasi_reopen_by')
                  ->nullable()
                  ->after('observasi_reopen_until')
                  ->constrained('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('asesmens', function (Blueprint $table) {
            $table->dropForeign(['observasi_reopen_by']);
            $table->dropColumn(['observasi_reopen_until', 'observasi_reopen_by']);
        });
    }
};