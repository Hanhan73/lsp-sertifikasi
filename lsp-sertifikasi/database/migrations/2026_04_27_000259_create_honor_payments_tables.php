<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Header pembayaran honor asesor
        Schema::create('honor_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesor_id')->constrained('asesors')->cascadeOnDelete();
            $table->string('nomor_kwitansi')->nullable()->unique();   // e.g. 052/LSP-KAP/KEU.KK/IV/2026
            $table->date('tanggal_kwitansi')->nullable();
            $table->unsignedBigInteger('total');                       // total honor (rupiah)
            $table->enum('status', [
                'menunggu_pembayaran',
                'sudah_dibayar',
                'dikonfirmasi',
            ])->default('menunggu_pembayaran');
            // Bukti transfer yang diupload bendahara
            $table->string('bukti_transfer_path')->nullable();
            $table->string('bukti_transfer_name')->nullable();
            $table->timestamp('dibayar_at')->nullable();
            $table->foreignId('dibayar_oleh')->nullable()->constrained('users')->nullOnDelete();
            // Konfirmasi dari asesor
            $table->timestamp('dikonfirmasi_at')->nullable();
            $table->foreignId('dibuat_oleh')->constrained('users');
            $table->timestamps();
        });

        // Detail per jadwal dalam satu pembayaran honor
        Schema::create('honor_payment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('honor_payment_id')->constrained('honor_payments')->cascadeOnDelete();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->unsignedInteger('jumlah_asesi');
            $table->unsignedInteger('honor_per_asesi');   // snapshot tarif saat kwitansi dibuat
            $table->unsignedBigInteger('subtotal');        // jumlah_asesi * honor_per_asesi
            $table->timestamps();

            $table->unique(['honor_payment_id', 'schedule_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honor_payment_details');
        Schema::dropIfExists('honor_payments');
    }
};