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
        Schema::table('skemas', function (Blueprint $table) {
            if (Schema::hasColumn('skemas', 'duration_days')) {
                $table->dropColumn('duration_days');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skemas', function (Blueprint $table) {
            if (!Schema::hasColumn('skemas', 'duration_days')) {
                $table->integer('duration_days')->nullable()->after('description');
            }
        });
    }
};
