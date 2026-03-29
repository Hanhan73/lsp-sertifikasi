<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // 'offline' = tatap muka di gedung/ruangan
            // 'online'  = via Zoom / Meet / platform lain
            $table->enum('location_type', ['offline', 'online'])
                  ->default('offline')
                  ->after('location');

            // URL meeting (hanya diisi jika location_type = online)
            $table->string('meeting_link', 500)
                  ->nullable()
                  ->after('location_type');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['location_type', 'meeting_link']);
        });
    }
};