<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asesmens', function (Blueprint $table) {
            // Rejection biodata oleh admin
            $table->text('biodata_rejection_notes')->nullable()->after('admin_started_at');
            $table->timestamp('biodata_rejected_at')->nullable()->after('biodata_rejection_notes');
            $table->foreignId('biodata_rejected_by')->nullable()
                  ->constrained('users')->nullOnDelete()
                  ->after('biodata_rejected_at');

            // Flag: apakah biodata sedang dalam status perlu direvisi asesi
            // true = admin minta revisi, asesi harus lengkapi ulang
            // false/null = normal
            $table->boolean('biodata_needs_revision')->default(false)->after('biodata_rejected_by');
        });
    }

    public function down(): void
    {
        Schema::table('asesmens', function (Blueprint $table) {
            $table->dropForeign(['biodata_rejected_by']);
            $table->dropColumn([
                'biodata_rejection_notes',
                'biodata_rejected_at',
                'biodata_rejected_by',
                'biodata_needs_revision',
            ]);
        });
    }
};