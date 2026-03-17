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
        Schema::create('asesors', function (Blueprint $table) {
            $table->id();
            $table->string('no')->nullable();          // Nomor urut
            $table->string('nama');
            $table->string('nik', 20)->unique();
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->text('alamat')->nullable();
            $table->string('kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('email')->unique();
            $table->string('no_reg_met')->nullable();   // No. Reg. Met
            $table->string('no_blanko')->nullable();
            $table->enum('siap_kerja', ['Memiliki', 'Tidak'])->default('Memiliki');
            $table->text('keterangan')->nullable();
            $table->enum('status_reg', ['aktif', 'expire', 'nonaktif'])->default('aktif');
            $table->date('expire_date')->nullable();
            $table->string('foto_path')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asesors');

    }
};