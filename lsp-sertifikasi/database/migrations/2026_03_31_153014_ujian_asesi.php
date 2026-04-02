<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah durasi_menit ke distribusi_soal_teori
        Schema::table('distribusi_soal_teori', function (Blueprint $table) {
            $table->unsignedSmallInteger('durasi_menit')->default(60)->after('jumlah_soal');
        });

        // Tambah tracking timer dan status pengerjaan per asesi
        Schema::table('soal_teori_asesi', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('jawaban');
            $table->timestamp('submitted_at')->nullable()->after('started_at');
            // Enum jawaban diperluas ke a-e
            // Catatan: kalau enum lama hanya a-d, perlu ALTER manual atau ubah migration lama
            // Di sini kita tambah kolom baru 'jawaban_asesi' yang nullable string(1)
            // agar tidak conflict dengan enum lama
        });

        // Ganti kolom jawaban dari enum(a,b,c,d) ke string agar bisa e
        // Cara aman: modify via doctrine
        // Pastikan di composer ada doctrine/dbal
        Schema::table('soal_teori_asesi', function (Blueprint $table) {
            $table->string('jawaban', 1)->nullable()->change();
        });

        // Tabel baru: jawaban observasi per asesi (link GDrive)
        Schema::create('jawaban_observasi_asesi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesmen_id')->constrained('asesmens')->cascadeOnDelete();
            // distribusi_soal_observasi menghubungkan soal ke schedule
            $table->foreignId('distribusi_soal_observasi_id')
                  ->constrained('distribusi_soal_observasi')
                  ->cascadeOnDelete();
            $table->foreignId('paket_soal_observasi_id')
                  ->nullable()
                  ->constrained('paket_soal_observasi')
                  ->nullOnDelete();
            $table->string('gdrive_link')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();

            $table->unique(['asesmen_id', 'paket_soal_observasi_id'], 'uniq_jawaban_observasi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawaban_observasi_asesi');

        Schema::table('soal_teori_asesi', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'submitted_at']);
        });

        Schema::table('distribusi_soal_teori', function (Blueprint $table) {
            $table->dropColumn('durasi_menit');
        });
    }
};