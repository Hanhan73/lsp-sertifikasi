<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fr_ak_01', function (Blueprint $table) {
            $table->text('rejection_notes')->nullable()->after('verified_at');
            $table->timestamp('returned_at')->nullable()->after('rejection_notes');
            $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete()->after('returned_at');
        });
 

    }
 
    public function down(): void
    {
        Schema::table('fr_ak_01', function (Blueprint $table) {
            $table->dropForeign(['returned_by']);
            $table->dropColumn(['rejection_notes', 'returned_at', 'returned_by']);
        });
    }
};