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
        Schema::table('skemas', function (Blueprint $table) {
            $table->enum('jenis_skema', ['okupasi', 'kkni', 'klaster'])->after('code');
            $table->string('dokumen_pengesahan_path')->nullable()->after('description');
            $table->string('dokumen_pengesahan_name')->nullable()->after('dokumen_pengesahan_path');
            $table->date('tanggal_pengesahan')->nullable()->after('dokumen_pengesahan_name');
            $table->string('nomor_skema')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
            Schema::table('skemas', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_skema', 'nomor_skema',
                'dokumen_pengesahan_path', 'dokumen_pengesahan_name',
                'tanggal_pengesahan',
            ]);
        });
    }
};