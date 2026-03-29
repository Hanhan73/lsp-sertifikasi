<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perbesar semua kolom tanda tangan di tabel apl_01 dari text → longText.
     * Error sebelumnya: SQLSTATE[22001] String data, right truncated
     * karena base64 gambar TTD bisa mencapai ratusan KB.
     */
    public function up(): void
    {
        Schema::table('apl_01', function (Blueprint $table) {
            $table->longText('ttd_pemohon')->nullable()->change();
            $table->longText('ttd_admin')->nullable()->change();
        });
        
    }

    public function down(): void
    {
        Schema::table('apl_01', function (Blueprint $table) {
            $table->text('ttd_pemohon')->nullable()->change();
            $table->text('ttd_admin')->nullable()->change();
        });
    }
};