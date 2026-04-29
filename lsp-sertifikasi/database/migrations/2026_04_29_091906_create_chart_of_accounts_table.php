<?php
// database/migrations/xxxx_create_chart_of_accounts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();   // e.g. 1-001
            $table->string('nama');                  // e.g. Kas
            $table->enum('tipe', [
                'aset',
                'kewajiban',
                'ekuitas',
                'pendapatan',
                'beban',
            ]);
            $table->string('sub_tipe')->nullable();  // e.g. aset_lancar, beban_operasional
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // akun bawaan sistem, tidak bisa dihapus
            $table->integer('urutan')->default(0);   // untuk sorting di laporan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};