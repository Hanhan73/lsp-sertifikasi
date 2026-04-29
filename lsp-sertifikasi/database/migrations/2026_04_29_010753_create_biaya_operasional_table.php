<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biaya_operasional', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->unique();           // Auto-generated: BO-2025-001
            $table->date('tanggal');
            $table->string('uraian');                    // Deskripsi kegiatan
            $table->string('nama_penerima');             // Nama penerima pembayaran
            $table->unsignedBigInteger('tarif');         // Harga satuan
            $table->unsignedInteger('jumlah');           // Kuantitas
            $table->unsignedBigInteger('total');         // tarif × jumlah (simpan agar tidak recalc)
            $table->string('bukti_transaksi')->nullable(); // path image
            $table->string('bukti_kegiatan')->nullable();  // path image
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biaya_operasional');
    }
};