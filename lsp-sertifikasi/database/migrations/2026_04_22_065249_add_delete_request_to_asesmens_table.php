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
            $table->boolean('delete_requested')->default(false);
            $table->text('delete_request_reason')->nullable();
            $table->timestamp('delete_requested_at')->nullable();
            $table->unsignedBigInteger('delete_requested_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asesmens', function (Blueprint $table) {
            $table->dropColumn('delete_requested');
            $table->dropColumn('delete_request_reason');
            $table->dropColumn('delete_requested_at');
            $table->dropColumn('delete_requested_by');
        });
    }
};
