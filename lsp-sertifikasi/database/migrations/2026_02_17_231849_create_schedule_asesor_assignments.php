<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update tabel schedules - tambah kolom asesor
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreignId('asesor_id')->nullable()->after('tuk_id')
                  ->constrained('asesors')->onDelete('set null');
            $table->foreignId('assigned_by')->nullable()->after('asesor_id')
                  ->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable()->after('assigned_by');
            $table->text('assignment_notes')->nullable()->after('assigned_at');
        });

        // Tabel untuk riwayat penugasan asesor ke jadwal
        Schema::create('schedule_asesor_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->onDelete('cascade');
            $table->foreignId('asesor_id')->nullable()->constrained('asesors')->onDelete('set null');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            $table->enum('action', ['assigned', 'reassigned', 'unassigned']);
            $table->text('notes')->nullable();
            $table->timestamp('action_at');
            $table->timestamps();
        });

        // Tabel notifikasi asesor
        Schema::create('asesor_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesor_id')->constrained('asesors')->onDelete('cascade');
            $table->string('type'); // 'assignment', 'schedule_update', 'reminder'
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // {schedule_id, asesmen_ids, etc}
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asesor_notifications');
        Schema::dropIfExists('schedule_asesor_histories');
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['asesor_id']);
            $table->dropForeign(['assigned_by']);
            $table->dropColumn(['asesor_id', 'assigned_by', 'assigned_at', 'assignment_notes']);
        });
    }
};