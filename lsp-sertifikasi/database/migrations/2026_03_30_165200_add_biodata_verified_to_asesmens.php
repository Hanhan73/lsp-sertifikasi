<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asesmens', function (Blueprint $table) {
            $table->timestamp('biodata_verified_at')->nullable()->after('biodata_needs_revision');
            $table->foreignId('biodata_verified_by')->nullable()
                  ->constrained('users')->nullOnDelete()
                  ->after('biodata_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('asesmens', function (Blueprint $table) {
            $table->dropForeign(['biodata_verified_by']);
            $table->dropColumn(['biodata_verified_at', 'biodata_verified_by']);
        });
    }
};