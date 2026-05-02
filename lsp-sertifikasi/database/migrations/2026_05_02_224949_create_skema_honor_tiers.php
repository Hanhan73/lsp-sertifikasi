<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skema_honor_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skema_id')->constrained('skemas')->cascadeOnDelete();
            $table->string('label', 100);   // contoh: "Standar", "Senior", "Khusus TUK Swasta"
            $table->unsignedInteger('amount');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Migrasi data lama honor_per_asesi → tier default
        // (jalankan setelah migrate jika mau)
    }

    public function down(): void
    {
        Schema::dropIfExists('skema_honor_tiers');
    }
};