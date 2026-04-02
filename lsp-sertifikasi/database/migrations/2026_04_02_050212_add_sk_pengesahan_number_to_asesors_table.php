<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asesors', function (Blueprint $table) {
            $table->string('sk_pengangkatan_number')->nullable()->after('no_blanko');
            $table->date('sk_pengangkatan_date')->nullable()->after('sk_pengangkatan_number');
            $table->date('sk_pengangkatan_valid_until')->nullable()->after('sk_pengangkatan_date');
            $table->string('sk_pengangkatan_path')->nullable()->after('sk_pengangkatan_valid_until');
            $table->string('sk_pengangkatan_filename')->nullable()->after('sk_pengangkatan_path');
        });
    }

    public function down(): void
    {
        Schema::table('asesors', function (Blueprint $table) {
            $table->dropColumn([
                'sk_pengangkatan_number',
                'sk_pengangkatan_date',
                'sk_pengangkatan_valid_until',
                'sk_pengangkatan_path',
                'sk_pengangkatan_filename',
            ]);
        });
    }
};