<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fr_ak_01', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesmen_id')->constrained('asesmens')->onDelete('cascade');

            // Data skema & jadwal (pre-filled)
            $table->string('skema_judul')->nullable();
            $table->string('skema_nomor')->nullable();
            $table->string('tuk_nama')->nullable();
            $table->string('waktu_asesmen')->nullable();
            $table->string('hari_tanggal')->nullable();
            $table->string('nama_asesor')->nullable();
            $table->string('nama_asesi')->nullable();

            // Bukti yang akan dikumpulkan
            $table->boolean('bukti_verifikasi_portofolio')->default(false);
            $table->boolean('bukti_observasi_langsung')->default(false);
            $table->boolean('bukti_pertanyaan_lisan')->default(false);
            $table->boolean('bukti_lainnya')->default(false);
            $table->string('bukti_lainnya_keterangan')->nullable();
            $table->boolean('bukti_hasil_review_produk')->default(false);
            $table->boolean('bukti_hasil_kegiatan_terstruktur')->default(false);
            $table->boolean('bukti_pertanyaan_tertulis')->default(false);
            $table->boolean('bukti_pertanyaan_wawancara')->default(false);

            // Status
            $table->enum('status', ['draft', 'submitted', 'verified', 'approved', 'returned'])
                  ->default('draft');

            // Tanda tangan Asesi
            $table->longText('ttd_asesi')->nullable();
            $table->string('nama_ttd_asesi')->nullable();
            $table->timestamp('tanggal_ttd_asesi')->nullable();

            // Tanda tangan Asesor
            $table->longText('ttd_asesor')->nullable();
            $table->string('nama_ttd_asesor')->nullable();
            $table->timestamp('tanggal_ttd_asesor')->nullable();

            // Audit
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fr_ak_01');
    }
};