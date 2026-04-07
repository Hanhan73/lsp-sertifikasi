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
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('foto_dokumentasi_1')->nullable()->after('notes');
            $table->string('foto_dokumentasi_2')->nullable()->after('foto_dokumentasi_1');
            $table->foreignId('foto_uploaded_by')->nullable()->constrained('users')->nullOnDelete()->after('foto_dokumentasi_2');
            $table->timestamp('foto_uploaded_at')->nullable()->after('foto_uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['foto_dokumentasi_1', 'foto_dokumentasi_2', 'foto_uploaded_by', 'foto_uploaded_at']);
        });
    }
};
