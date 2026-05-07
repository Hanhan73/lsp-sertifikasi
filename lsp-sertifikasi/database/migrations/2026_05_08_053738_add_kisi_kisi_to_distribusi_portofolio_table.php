// php artisan make:migration add_kisi_kisi_to_distribusi_portofolio_table
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distribusi_portofolio', function (Blueprint $table) {
            $table->string('kisi_kisi_path')->nullable()->after('form_penilaian_name');
            $table->string('kisi_kisi_name')->nullable()->after('kisi_kisi_path');
        });
    }

    public function down(): void
    {
        Schema::table('distribusi_portofolio', function (Blueprint $table) {
            $table->dropColumn(['kisi_kisi_path', 'kisi_kisi_name']);
        });
    }
};