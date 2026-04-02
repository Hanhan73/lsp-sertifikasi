<?php
// =========================================================================
// Migration: xxxx_create_hasil_penilaian_tables.php
// =========================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Upload hasil observasi per jadwal (satu file untuk semua asesi) ──
        Schema::create('hasil_observasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->foreignId('soal_observasi_id')->constrained('soal_observasi')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->text('catatan')->nullable();
            $table->timestamps();

            // Satu file per soal observasi per jadwal
            $table->unique(['schedule_id', 'soal_observasi_id']);
        });

        // ── Upload hasil portofolio per jadwal (satu file untuk semua asesi) ──
        Schema::create('hasil_portofolio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->foreignId('portofolio_id')->constrained('portofolio')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->text('catatan')->nullable();
            $table->timestamps();

            // Satu file per portofolio per jadwal
            $table->unique(['schedule_id', 'portofolio_id']);
        });

        // ── Berita Acara per jadwal ────────────────────────────────────────
        // Bisa dari form web atau upload file, atau keduanya
        Schema::create('berita_acara', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete()->unique();
            $table->date('tanggal_pelaksanaan');
            $table->text('catatan')->nullable();

            // Upload file berita acara (opsional — bisa pakai form atau file)
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();

            $table->foreignId('dibuat_oleh')->constrained('users');
            $table->timestamps();
        });

        // ── Rekomendasi per asesi di berita acara ─────────────────────────
        Schema::create('berita_acara_asesi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('berita_acara_id')->constrained('berita_acara')->cascadeOnDelete();
            $table->foreignId('asesmen_id')->constrained('asesmens')->cascadeOnDelete();
            $table->enum('rekomendasi', ['K', 'BK']); // Kompeten / Belum Kompeten
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['berita_acara_id', 'asesmen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berita_acara_asesi');
        Schema::dropIfExists('berita_acara');
        Schema::dropIfExists('hasil_portofolio');
        Schema::dropIfExists('hasil_observasi');
    }
};