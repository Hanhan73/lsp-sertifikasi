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
            // Add new fields after existing ones
            $table->string('treasurer_name')->nullable()->after('manager_name');
            $table->string('sk_document_path')->nullable()->after('logo_path');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tuks', function (Blueprint $table) {
            $table->dropColumn(['treasurer_name', 'sk_document_path']);
        });
    }
};