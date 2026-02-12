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
            // Add flag to indicate TUK will handle payment
            $table->boolean('collective_paid_by_tuk')->default(false)->after('collective_payment_timing');
            
            // Add flag to skip payment step for asesi
            $table->boolean('skip_payment')->default(false)->after('collective_paid_by_tuk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asesmens', function (Blueprint $table) {
            $table->dropColumn('collective_paid_by_tuk');
            $table->dropColumn('skip_payment');
        });
    }
};