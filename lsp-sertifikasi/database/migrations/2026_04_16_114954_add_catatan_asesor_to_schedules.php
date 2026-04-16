<?php
// database/migrations/2026_04_16_000001_add_catatan_asesor_to_schedules.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->text('catatan_asesor')->nullable()->after('foto_uploaded_at');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('catatan_asesor');
        });
    }
};