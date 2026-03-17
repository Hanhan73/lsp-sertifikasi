<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Drop old column jika ada
            if (Schema::hasColumn('schedules', 'asesmen_id')) {
                $table->dropForeign(['asesmen_id']);
                $table->dropColumn('asesmen_id');
            }
            
            // Add new columns
            $table->foreignId('tuk_id')->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('skema_id')->after('tuk_id')->constrained()->onDelete('cascade');
        });

        // Update asesmens table
        Schema::table('asesmens', function (Blueprint $table) {
            if (!Schema::hasColumn('asesmens', 'schedule_id')) {
                $table->foreignId('schedule_id')->nullable()->after('skema_id')->constrained()->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['tuk_id']);
            $table->dropForeign(['skema_id']);
            $table->dropColumn(['tuk_id', 'skema_id']);
            
            $table->foreignId('asesmen_id')->after('id')->constrained()->onDelete('cascade');
        });

        Schema::table('asesmens', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropColumn('schedule_id');
        });
    }
};