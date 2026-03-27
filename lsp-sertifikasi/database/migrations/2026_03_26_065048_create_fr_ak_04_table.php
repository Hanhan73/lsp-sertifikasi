<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fr_ak_04', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesmen_id')->constrained('asesmens')->onDelete('cascade');

            // Data referensi (auto-filled dari asesmen)
            $table->string('nama_asesi')->nullable();
            $table->string('nama_asesor')->nullable();
            $table->string('tanggal_asesmen')->nullable();
            $table->string('skema_sertifikasi')->nullable();
            $table->string('no_skema_sertifikasi')->nullable();

            // 3 pertanyaan Ya/Tidak
            $table->boolean('proses_banding_dijelaskan')->nullable(); // null = belum diisi
            $table->boolean('sudah_diskusi_dengan_asesor')->nullable();
            $table->boolean('melibatkan_orang_lain')->nullable();

            // Alasan banding (text area)
            $table->text('alasan_banding')->nullable();

            // Tanda tangan asesi
            $table->longText('ttd_asesi')->nullable();
            $table->string('nama_ttd_asesi')->nullable();
            $table->timestamp('tanggal_ttd_asesi')->nullable();

            // Status dokumen
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fr_ak_04');
    }
};