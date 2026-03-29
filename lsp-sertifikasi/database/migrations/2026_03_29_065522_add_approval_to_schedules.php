<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Status approval workflow
            // pending_approval → approved / rejected
            $table->enum('approval_status', [
                'pending_approval',
                'approved',
                'rejected',
            ])->default('pending_approval')->after('notes');

            $table->text('approval_notes')->nullable()->after('approval_status');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('approval_notes');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');

            // SK (Surat Keputusan / Surat Tugas)
            $table->string('sk_number')->nullable()->after('rejected_at');   // Nomor SK: 025/LSP-KAP/...
            $table->string('sk_path')->nullable()->after('sk_number');       // Path file PDF
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'approval_status',
                'approval_notes',
                'approved_by',
                'approved_at',
                'rejected_at',
                'sk_number',
                'sk_path',
            ]);
        });
    }
};