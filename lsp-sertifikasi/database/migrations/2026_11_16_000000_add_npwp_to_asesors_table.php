<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asesors', function (Blueprint $table) {
            $table->string('npwp', 30)->nullable()->after('nik');
        });
    }

    public function down(): void
    {
        Schema::table('asesors', function (Blueprint $table) {
            $table->dropColumn('npwp');
        });
    }
};
