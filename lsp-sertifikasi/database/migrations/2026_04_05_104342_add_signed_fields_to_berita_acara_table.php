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
        Schema::table('berita_acara', function (Blueprint $table) {
            $table->timestamp('signed_at')->nullable()->after('dibuat_oleh');
            $table->foreignId('signed_by')->nullable()->after('signed_at')->constrained('users');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->timestamp('daftar_hadir_signed_at')->nullable()->after('assessment_start');
            $table->foreignId('daftar_hadir_signed_by')->nullable()->after('daftar_hadir_signed_at')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('berita_acara', function (Blueprint $table) {
            $table->dropForeign(['signed_by']);
            $table->dropColumn(['signed_at', 'signed_by']);
        });
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['daftar_hadir_signed_by']);
            $table->dropColumn(['daftar_hadir_signed_at', 'daftar_hadir_signed_by']);
        });
    }
};
