<?php
// database/migrations/xxxx_create_account_balances_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_balances', function (Blueprint $table) {
            $table->id();
            $table->year('tahun')->unique();

            // ── ASET (input manual) ───────────────────────────────────────
            $table->bigInteger('kas')->default(0);
            $table->bigInteger('bank')->default(0);
            $table->bigInteger('perlengkapan')->default(0);

            // ── KEWAJIBAN (input manual) ──────────────────────────────────
            $table->bigInteger('utang_operasional')->default(0);

            // ── EKUITAS (input manual) ────────────────────────────────────
            $table->bigInteger('saldo_dana')->default(0);

            // ── Distribusi ke yayasan ─────────────────────────────────────
            $table->bigInteger('distribusi_yayasan')->default(0);   // total distribusi tahun ini
            $table->bigInteger('hutang_distribusi')->default(0);     // distribusi belum dibayar
            $table->date('tanggal_distribusi')->nullable();
            $table->text('catatan_distribusi')->nullable();
            $table->boolean('jurnal_balik_done')->default(false);

            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('diupdate_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_balances');
    }
};