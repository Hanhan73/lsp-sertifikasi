<?php
// File: database/migrations/xxxx_xx_xx_create_apl_02_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel header APL-02
        Schema::create('apl_02', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesmen_id')->constrained('asesmens')->cascadeOnDelete();

            // Status: draft | submitted | verified | approved
            $table->string('status', 20)->default('draft');

            // Tanda tangan asesi
            $table->longText('ttd_asesi')->nullable();          // base64 PNG
            $table->string('nama_ttd_asesi')->nullable();
            $table->timestamp('tanggal_ttd_asesi')->nullable();

            // Tanda tangan asesor (diisi admin/asesor nanti)
            $table->longText('ttd_asesor')->nullable();
            $table->string('nama_ttd_asesor')->nullable();
            $table->timestamp('tanggal_ttd_asesor')->nullable();

            // Rekomendasi asesor
            $table->enum('rekomendasi_asesor', ['lanjut', 'tidak_lanjut'])->nullable();
            $table->text('catatan_asesor')->nullable();

            // Audit
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });

        // Tabel jawaban per elemen/KUK
        Schema::create('apl_02_jawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apl_02_id')->constrained('apl_02')->cascadeOnDelete();

            // Referensi ke elemen kompetensi
            $table->foreignId('elemen_id')->constrained('elemens')->cascadeOnDelete();

            // Jawaban asesi: K = Kompeten, BK = Belum Kompeten (self-assessment)
            $table->enum('jawaban', ['K', 'BK'])->nullable();

            // Bukti yang dimiliki asesi (opsional, teks bebas)
            $table->text('bukti')->nullable();

            $table->timestamps();

            // Unique: 1 jawaban per elemen per APL-02
            $table->unique(['apl_02_id', 'elemen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apl_02_jawaban');
        Schema::dropIfExists('apl_02');
    }
};