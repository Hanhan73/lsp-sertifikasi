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
        Schema::table('asesmens', function (Blueprint $table) {
        
            // Tambah status baru ke enum jika pakai enum
            $table->enum('status', [
                'registered',
                'data_completed',
                'verified',
                'paid',
                'scheduled',
                'pra_asesmen_completed',
                'assessed',
                'certified',
                'pra_asesmen_started', 
                'asesmen_started'      
            ])->default('registered')->change();
            // Jika pakai string biasa, tidak perlu migration tambahan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asesmens', function (Blueprint $table) {
            $table->enum('status', [
                'registered',
                'data_completed',
                'verified',
                'paid',
                'scheduled',
                'pra_asesmen_completed',
                'assessed',
                'certified',
            ])->default('registered')->change();
        });
    }
};