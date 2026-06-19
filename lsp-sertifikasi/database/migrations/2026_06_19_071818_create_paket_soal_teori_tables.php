<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel paket soal teori
        Schema::create('paket_soal_teori', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skema_id')->constrained('skemas')->cascadeOnDelete();
            $table->string('kode_paket', 10); // A, B, C, dst
            $table->string('nama_paket')->nullable(); // "Paket A 2025", dll
            $table->year('tahun')->nullable(); // untuk arsip per tahun
            $table->foreignId('dibuat_oleh')->constrained('users');
            $table->timestamps();

            $table->unique(['skema_id', 'kode_paket', 'tahun']);
        });

        // Tambah kolom ke soal_teori
        Schema::table('soal_teori', function (Blueprint $table) {
            $table->foreignId('paket_soal_teori_id')
                ->nullable()
                ->after('skema_id')
                ->constrained('paket_soal_teori')
                ->nullOnDelete();
        });

        // Tambah kolom ke distribusi_soal_teori
        Schema::table('distribusi_soal_teori', function (Blueprint $table) {
            $table->foreignId('paket_soal_teori_id')
                ->nullable()
                ->after('schedule_id')
                ->constrained('paket_soal_teori')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('distribusi_soal_teori', function (Blueprint $table) {
            $table->dropForeign(['paket_soal_teori_id']);
            $table->dropColumn('paket_soal_teori_id');
        });

        Schema::table('soal_teori', function (Blueprint $table) {
            $table->dropForeign(['paket_soal_teori_id']);
            $table->dropColumn('paket_soal_teori_id');
        });

        Schema::dropIfExists('paket_soal_teori');
    }
};