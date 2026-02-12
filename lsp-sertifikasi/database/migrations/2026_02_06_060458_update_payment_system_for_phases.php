<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('asesmens', function (Blueprint $table) {
            // HAPUS kolom lama
            $table->dropColumn('collective_payment_timing'); // Hapus before/after
            
            // TAMBAH kolom baru
            $table->enum('payment_phases', ['single', 'two_phase'])->nullable()->after('collective_batch_id');
            $table->decimal('phase_1_amount', 10, 2)->nullable()->after('fee_amount');
            $table->decimal('phase_2_amount', 10, 2)->nullable()->after('phase_1_amount');
        });

        Schema::table('payments', function (Blueprint $table) {
            // Tambah kolom untuk tracking phase
            $table->enum('payment_phase', ['full', 'phase_1', 'phase_2'])->default('full')->after('amount');
        });
    }

    public function down()
    {
        Schema::table('asesmens', function (Blueprint $table) {
            $table->dropColumn(['payment_phases', 'phase_1_amount', 'phase_2_amount']);
            $table->enum('collective_payment_timing', ['before', 'after'])->nullable();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('payment_phase');
        });
    }
};