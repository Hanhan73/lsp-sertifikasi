<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendapatan_luar', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('uraian', 255);
            $table->string('kategori', 100)->nullable();   // bebas input
            $table->unsignedBigInteger('jumlah');
            $table->foreignId('coa_id')->constrained('chart_of_accounts'); // akun pendapatan
            $table->string('bukti_path', 500);
            $table->string('bukti_name', 255);
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendapatan_luar');
    }
};