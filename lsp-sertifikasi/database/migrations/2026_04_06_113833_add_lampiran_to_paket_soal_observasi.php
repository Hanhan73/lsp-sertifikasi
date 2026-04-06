<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('paket_soal_observasi', function (Blueprint $table) {
            $table->string('lampiran_path')->nullable()->after('file_name');
            $table->string('lampiran_name')->nullable()->after('lampiran_path');
        });
    }

    public function down(): void
    {
        Schema::table('paket_soal_observasi', function (Blueprint $table) {
            $table->dropColumn(['lampiran_path', 'lampiran_name']);
        });
    }
};