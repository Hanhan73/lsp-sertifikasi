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
        Schema::create('kuks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('elemen_id')->constrained()->onDelete('cascade');
            $table->string('kode')->nullable();        // Nomor KUK: 1.1, 1.2, dst
            $table->text('deskripsi');                 // Deskripsi KUK
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuks');
    }
};