<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('asesmens', function (Blueprint $table) {
            // Rename existing columns untuk admin
            $table->renameColumn('verified_by', 'admin_verified_by');
            $table->renameColumn('verified_at', 'admin_verified_at');
            
            // Add TUK verification columns
            $table->unsignedBigInteger('tuk_verified_by')->nullable()->after('admin_verified_by');
            $table->timestamp('tuk_verified_at')->nullable()->after('admin_verified_at');
            $table->text('tuk_verification_notes')->nullable()->after('tuk_verified_at');
            
            // Foreign key for TUK verifier
            $table->foreign('tuk_verified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('asesmens', function (Blueprint $table) {
            $table->dropForeign(['tuk_verified_by']);
            $table->dropColumn(['tuk_verified_by', 'tuk_verified_at', 'tuk_verification_notes']);
            
            // Rename back
            $table->renameColumn('admin_verified_by', 'verified_by');
            $table->renameColumn('admin_verified_at', 'verified_at');
        });
    }
};