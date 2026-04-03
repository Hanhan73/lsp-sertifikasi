<?php
// database/migrations/2026_04_03_add_paket_to_distribusi_soal_observasi.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distribusi_soal_observasi', function (Blueprint $table) {
            // Paket spesifik yang dipilih untuk jadwal ini (misal: Paket B)
            // nullable karena data lama mungkin belum punya
            $table->foreignId('paket_soal_observasi_id')
                  ->nullable()
                  ->after('soal_observasi_id')
                  ->constrained('paket_soal_observasi')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('distribusi_soal_observasi', function (Blueprint $table) {
            $table->dropForeign(['paket_soal_observasi_id']);
            $table->dropColumn('paket_soal_observasi_id');
        });
    }
};