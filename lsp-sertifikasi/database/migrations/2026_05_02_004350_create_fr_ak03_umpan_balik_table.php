<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fr_ak03_umpan_balik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesmen_id')->constrained('asesmens')->cascadeOnDelete();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            // jawaban: array of 10 items [{jawaban: 'ya'|'tidak', catatan: string|null}]
            $table->json('jawaban');
            $table->text('catatan_lain')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique('asesmen_id'); // 1 asesi hanya punya 1 FR.AK.03
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fr_ak03_umpan_balik');
    }
};