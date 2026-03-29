<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asesor_skema', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesor_id')
                  ->constrained('asesors')
                  ->cascadeOnDelete();
            $table->foreignId('skema_id')
                  ->constrained('skemas')
                  ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['asesor_id', 'skema_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asesor_skema');
    }
};