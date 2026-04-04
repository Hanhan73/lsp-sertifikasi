<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asesmens', function (Blueprint $table) {
            $table->boolean('hadir')->default(true)->change();
        });

        // Opsional tapi direkomendasikan: update data lama yang null jadi true
        DB::table('asesmens')->where('hadir', false)->update(['hadir' => true]);
    }

    public function down(): void
    {
        Schema::table('asesmens', function (Blueprint $table) {
            $table->boolean('hadir')->default(false)->nullable()->change();
        });
    }
};