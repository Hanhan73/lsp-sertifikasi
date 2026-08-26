<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Tambah status baru ke enum
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
            'certificate_distributed',
            'verified',
            'paid'
        ) DEFAULT 'registered'");

        // 2) Kolom baru untuk distribusi & upload sertifikat fisik
        Schema::table('asesmens', function (Blueprint $table) {
            $table->timestamp('distributed_at')->nullable()->after('status');
            $table->foreignId('distributed_by')->nullable()->after('distributed_at')
                ->constrained('users')->nullOnDelete();

            $table->string('physical_certificate_number')->nullable()->after('distributed_by');
            $table->string('physical_certificate_adm_number')->nullable()->after('physical_certificate_number');
            $table->string('physical_certificate_path')->nullable()->after('physical_certificate_adm_number');
            $table->timestamp('physical_certificate_uploaded_at')->nullable()->after('physical_certificate_path');
        });
    }

    public function down(): void
    {
        Schema::table('asesmens', function (Blueprint $table) {
            $table->dropConstrainedForeignId('distributed_by');
            $table->dropColumn([
                'distributed_at',
                'physical_certificate_number',
                'physical_certificate_adm_number',
                'physical_certificate_path',
                'physical_certificate_uploaded_at',
            ]);
        });

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
};