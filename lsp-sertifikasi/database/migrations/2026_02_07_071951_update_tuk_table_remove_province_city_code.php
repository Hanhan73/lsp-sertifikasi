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
        Schema::table('tuks', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['province_code', 'city_code']);
            
            // Add new columns
            $table->string('manager_name')->nullable()->after('name');
            $table->string('staff_name')->nullable()->after('manager_name');
            $table->string('logo_path')->nullable()->after('staff_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tuks', function (Blueprint $table) {
            // Add back old columns

            $table->string('province_code', 2);
            $table->string('city_code', 4);
            
            // Drop new columns
            $table->dropColumn(['manager_name', 'staff_name', 'logo_path']);
        });
    }
};