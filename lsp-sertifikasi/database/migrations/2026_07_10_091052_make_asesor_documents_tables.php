<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asesor_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesor_id')->constrained()->cascadeOnDelete();
            $table->string('jenis_dokumen', 50);
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedInteger('file_size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['asesor_id', 'jenis_dokumen']); // 1 file per jenis, reupload = replace
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asesor_documents');
    }
};