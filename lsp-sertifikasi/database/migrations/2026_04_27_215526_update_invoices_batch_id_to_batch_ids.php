<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Tambah kolom batch_ids (JSON) — nullable dulu agar bisa isi data lama
            $table->json('batch_ids')->nullable()->after('batch_id');
        });

        // Migrate data lama: batch_id → batch_ids
        DB::statement('UPDATE invoices SET batch_ids = JSON_ARRAY(batch_id) WHERE batch_ids IS NULL');

        Schema::table('invoices', function (Blueprint $table) {
            // Hapus kolom lama
            $table->dropIndex(['batch_id']); // drop index kalau ada
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('batch_id');
            // batch_ids tidak boleh null setelah migrasi data
            $table->json('batch_ids')->nullable(false)->change();
        });

        // Update collective_payments juga — batch_id tetap ada (per angsuran masih terkait batch tertentu tidak masalah)
        // Tapi tambah invoice_id foreign key sudah ada, jadi tidak perlu ubah
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('batch_id')->nullable()->after('batch_ids');
        });

        // Restore: ambil batch_id pertama dari batch_ids
        DB::statement("UPDATE invoices SET batch_id = JSON_UNQUOTE(JSON_EXTRACT(batch_ids, '$[0]'))");

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('batch_ids');
            $table->string('batch_id')->nullable(false)->change();
            $table->index('batch_id');
        });
    }
};