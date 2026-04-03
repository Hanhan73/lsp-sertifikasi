<?php
// database/migrations/2026_04_03_add_form_penilaian_to_distribusi_soal_observasi.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distribusi_soal_observasi', function (Blueprint $table) {
            $table->string('form_penilaian_path')->nullable()->after('didistribusikan_oleh');
            $table->string('form_penilaian_name')->nullable()->after('form_penilaian_path');
        });
    }

    public function down(): void
    {
        Schema::table('distribusi_soal_observasi', function (Blueprint $table) {
            $table->dropColumn(['form_penilaian_path', 'form_penilaian_name']);
        });
    }
};