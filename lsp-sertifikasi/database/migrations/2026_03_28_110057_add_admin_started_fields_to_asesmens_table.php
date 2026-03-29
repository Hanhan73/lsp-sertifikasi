<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asesmens', function (Blueprint $table) {

        
            // Admin yang memulai asesmen
            $table->foreignId('admin_started_by')
                  ->nullable()
                  ->after('registration_date')
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('admin_started_at')->nullable()->after('admin_started_by');
        
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
                'pra_asesmen_started' // <-- tambah di sini
            ])->default('registered')->change();
            // Jika pakai string biasa, tidak perlu migration tambahan
        });
    }

    public function down(): void
    {
        
        Schema::table('asesmens', function (Blueprint $table) {
            if (Schema::hasColumn('asesmens', 'admin_started_by')) {
                try {
                    $table->dropForeign(['admin_started_by']);
                } catch (\Exception $e) {}
                
                $table->dropColumn('admin_started_by');
            }

            if (Schema::hasColumn('asesmens', 'admin_started_at')) {
                $table->dropColumn('admin_started_at');
            }
            $table->enum('status', [
                'registered',
                'data_completed',
                'verified',
                'paid',
                'scheduled',
                'pra_asesmen_completed',
                'assessed',
                'certified'
            ])->default('registered')->change();

        });
    }
};