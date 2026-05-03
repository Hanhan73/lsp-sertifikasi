<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('other_receivables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coa_id')->constrained('chart_of_accounts');         // akun piutang (Dr)
            $table->foreignId('coa_lawan_id')->nullable()->constrained('chart_of_accounts'); // akun lawan (Cr) — wajib kalau tagihan
            $table->enum('jenis', ['pinjaman', 'tagihan'])->default('pinjaman');
            $table->string('nama_pihak');
            $table->string('uraian');
            $table->decimal('jumlah', 15, 2);
            $table->date('tanggal');
            $table->date('jatuh_tempo')->nullable();
            $table->enum('status', ['outstanding', 'lunas'])->default('outstanding');
            $table->date('tanggal_lunas')->nullable();
            $table->decimal('jumlah_lunas', 15, 2)->nullable();
            $table->string('bukti_path')->nullable();
            $table->string('bukti_name')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('other_receivables');
    }
};