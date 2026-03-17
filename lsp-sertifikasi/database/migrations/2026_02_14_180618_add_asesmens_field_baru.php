<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asesmens', function (Blueprint $table) {
            // Untuk assignment ke TUK oleh admin
            $table->unsignedBigInteger('assigned_tuk_id')->nullable()->after('tuk_id');
            $table->timestamp('assigned_at')->nullable()->after('assigned_tuk_id');
            $table->unsignedBigInteger('assigned_by')->nullable()->after('assigned_at');
            
            // Foreign keys
            $table->foreign('assigned_tuk_id')->references('id')->on('tuks')->onDelete('set null');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('asesmens', function (Blueprint $table) {
            $table->dropForeign(['assigned_tuk_id']);
            $table->dropForeign(['assigned_by']);
            $table->dropColumn([
                'assigned_tuk_id',
                'assigned_at',
                'assigned_by',
                'phase_1_percentage',
                'phase_2_percentage'
            ]);
        });
    }
};