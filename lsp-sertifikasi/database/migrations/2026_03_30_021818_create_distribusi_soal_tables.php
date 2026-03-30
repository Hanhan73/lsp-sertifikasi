<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Distribusi soal observasi → schedule
        Schema::create('distribusi_soal_observasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->foreignId('soal_observasi_id')->constrained('soal_observasi')->cascadeOnDelete();
            $table->foreignId('didistribusikan_oleh')->constrained('users');
            $table->timestamps();

            $table->unique(['schedule_id', 'soal_observasi_id']);
        });

        // Distribusi paket soal → schedule
        Schema::create('distribusi_paket_soal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->foreignId('paket_soal_id')->constrained('paket_soal')->cascadeOnDelete();
            $table->foreignId('didistribusikan_oleh')->constrained('users');
            $table->timestamps();

            $table->unique(['schedule_id', 'paket_soal_id']);
        });

        // Distribusi soal teori → schedule
        // Manajer menentukan jumlah soal yang akan didapat asesi (default 30)
        Schema::create('distribusi_soal_teori', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->integer('jumlah_soal')->default(30);
            $table->foreignId('didistribusikan_oleh')->constrained('users');
            $table->timestamps();

            // Satu jadwal hanya punya satu konfigurasi distribusi soal teori
            $table->unique('schedule_id');
        });

        // Soal teori yang sudah diacak per asesi (snapshot saat distribusi)
        Schema::create('soal_teori_asesi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribusi_soal_teori_id')->constrained('distribusi_soal_teori')->cascadeOnDelete();
            $table->foreignId('asesmen_id')->constrained('asesmens')->cascadeOnDelete();
            $table->foreignId('soal_teori_id')->constrained('soal_teori')->cascadeOnDelete();
            $table->integer('urutan');
            $table->enum('jawaban', ['a', 'b', 'c', 'd'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soal_teori_asesi');
        Schema::dropIfExists('distribusi_soal_teori');
        Schema::dropIfExists('distribusi_paket_soal');
        Schema::dropIfExists('distribusi_soal_observasi');
    }
};