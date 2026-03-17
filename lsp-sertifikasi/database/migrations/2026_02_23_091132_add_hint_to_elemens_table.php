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
        Schema::table('elemens', function (Blueprint $table) {
            $table->text('hint_bukti')->nullable()->after('judul');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('elemens', function (Blueprint $table) {
            $table->dropColumn('hint_bukti');
        });
    }
};