<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_masuks', function (Blueprint $table) {
            $table->id();
            $table->integer('nomor_urut')->unsigned();
            $table->date('tanggal_agenda');
            $table->string('nomor_surat');
            $table->date('tanggal_surat');
            $table->string('dari');
            $table->text('isi_ringkas');
            $table->string('file_path')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_masuks');
    }
};