<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah honor_per_asesi ke tabel skemas
        Schema::table('skemas', function (Blueprint $table) {
            $table->unsignedInteger('honor_per_asesi')->default(0)->after('fee')
                  ->comment('Tarif honor asesor per asesi dalam rupiah');
        });
    }

    public function down(): void
    {
        Schema::table('skemas', function (Blueprint $table) {
            $table->dropColumn('honor_per_asesi');
        });
    }
};