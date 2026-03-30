<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paket_soal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skema_id')->constrained('skemas')->cascadeOnDelete();
            $table->string('judul');
            $table->string('file_path');
            $table->string('file_name');
            $table->foreignId('dibuat_oleh')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paket_soal');
    }
};