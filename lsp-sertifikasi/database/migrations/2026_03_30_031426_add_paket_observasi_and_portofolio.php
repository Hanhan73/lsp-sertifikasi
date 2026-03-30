<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perubahan struktur soal observasi:
     * - soal_observasi sekarang adalah "judul/kelompok" observasi per skema
     * - paket_soal_observasi adalah paket turunannya (Paket A, B, C, dst)
     *   yang masing-masing punya file PDF sendiri
     *
     * Tabel paket_soal tetap untuk paket soal reguler (bukan observasi).
     * Tabel portofolio baru untuk tipe soal portofolio (format TBD).
     */
    public function up(): void
    {
        // ── Tambah paket di dalam soal observasi ──────────────────────────
        Schema::create('paket_soal_observasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soal_observasi_id')
                  ->constrained('soal_observasi')
                  ->cascadeOnDelete();
            $table->string('kode_paket'); // A, B, C, D, dst
            $table->string('judul');
            $table->string('file_path');
            $table->string('file_name');
            $table->foreignId('dibuat_oleh')->constrained('users');
            $table->timestamps();

            $table->unique(['soal_observasi_id', 'kode_paket']);
        });

        // ── Tabel portofolio ───────────────────────────────────────────────
        // Format file TBD — bisa PDF, Excel, atau lainnya
        // Untuk sementara hanya menyimpan metadata, file_path opsional
        Schema::create('portofolio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skema_id')->constrained('skemas')->cascadeOnDelete();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('file_path')->nullable();  // akan diisi setelah format ditentukan
            $table->string('file_name')->nullable();
            $table->string('tipe_file')->nullable();  // pdf, xlsx, docx, dll — TBD
            $table->foreignId('dibuat_oleh')->constrained('users');
            $table->timestamps();
        });

        // ── Distribusi portofolio → schedule ──────────────────────────────
        Schema::create('distribusi_portofolio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->foreignId('portofolio_id')->constrained('portofolio')->cascadeOnDelete();
            $table->foreignId('didistribusikan_oleh')->constrained('users');
            $table->timestamps();

            $table->unique(['schedule_id', 'portofolio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribusi_portofolio');
        Schema::dropIfExists('portofolio');
        Schema::dropIfExists('paket_soal_observasi');
    }
};