<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jalankan dengan:
 *   php artisan migrate
 *
 * File ini menambahkan:
 * 1. kolom `durasi_menit` ke tabel distribusi_soal_teori  (Fix #2)
 * 2. kolom `form_penilaian_path` & `form_penilaian_name` ke distribusi_portofolio (Fix #4)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Fix #4 — tambah form_penilaian ke distribusi_portofolio
        if (Schema::hasTable('distribusi_portofolio')) {
            Schema::table('distribusi_portofolio', function (Blueprint $table) {
                if (!Schema::hasColumn('distribusi_portofolio', 'form_penilaian_path')) {
                    $table->string('form_penilaian_path')->nullable()->after('didistribusikan_oleh')
                          ->comment('Path file form penilaian portofolio di disk private');
                }
                if (!Schema::hasColumn('distribusi_portofolio', 'form_penilaian_name')) {
                    $table->string('form_penilaian_name')->nullable()->after('form_penilaian_path')
                          ->comment('Nama file asli form penilaian portofolio');
                }
            });
        }
    }

    public function down(): void
    {


        if (Schema::hasTable('distribusi_portofolio')) {
            Schema::table('distribusi_portofolio', function (Blueprint $table) {
                $table->dropColumn(['form_penilaian_path', 'form_penilaian_name']);
            });
        }
    }
};