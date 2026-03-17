<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('unit_kompetensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skema_id')->constrained()->onDelete('cascade');
            $table->string('kode_unit');              // Contoh: ADM.PK01.001.01
            $table->string('judul_unit');
            $table->string('standar_kompetensi')->nullable(); // SKKNI, dll
            $table->integer('urutan')->default(0);
            $table->timestamps();
            
            $table->unique(['skema_id', 'kode_unit']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_kompetensis');
    }
};