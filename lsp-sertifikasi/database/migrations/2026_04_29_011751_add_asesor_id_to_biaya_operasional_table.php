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
        Schema::table('biaya_operasional', function (Blueprint $table) {
            $table->foreignId('asesor_id')->nullable()->after('nama_penerima')
                ->constrained('asesors')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('biaya_operasional', function (Blueprint $table) {
            $table->dropForeign(['asesor_id']);
            $table->dropColumn('asesor_id');
        });
    }
};
