<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE asesmens MODIFY COLUMN status ENUM(
            'registered',
            'data_completed',
            'payment_pending',
            'pra_asesmen_started',
            'scheduled',
            'pre_assessment_completed',
            'asesmen_started',
            'assessed',
            'certified',
            'verified',
            'paid'
        ) DEFAULT 'registered'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE asesmens MODIFY COLUMN status ENUM(
            'registered',
            'data_completed',
            'pra_asesmen_started',
            'scheduled',
            'pre_assessment_completed',
            'asesmen_started',
            'assessed',
            'certified',
            'verified',
            'paid'
        ) DEFAULT 'registered'");
    }
};