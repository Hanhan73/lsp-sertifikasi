<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asesor_rekenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesor_id')->constrained('asesors')->cascadeOnDelete();
            $table->string('nama_bank', 100);           // BCA, BRI, Mandiri, dll
            $table->string('nomor_rekening', 50);
            $table->string('nama_pemilik', 255);        // sesuai buku tabungan
            $table->string('cabang', 150)->nullable();
            $table->boolean('is_utama')->default(false); // rekening utama/default
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asesor_rekenings');
    }
};