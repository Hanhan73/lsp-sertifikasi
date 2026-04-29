<?php
// database/migrations/xxxx_create_journal_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nomor')->unique();          // JRN-2026-001
            $table->string('keterangan');
            $table->string('ref_type')->nullable();     // App\Models\Payment
            $table->unsignedBigInteger('ref_id')->nullable(); // id transaksi asal
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['ref_type', 'ref_id']);
        });

        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')
                ->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignId('chart_of_account_id')
                ->constrained('chart_of_accounts')->restrictOnDelete();
            $table->unsignedBigInteger('debit')->default(0);
            $table->unsignedBigInteger('kredit')->default(0);
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
    }
};
