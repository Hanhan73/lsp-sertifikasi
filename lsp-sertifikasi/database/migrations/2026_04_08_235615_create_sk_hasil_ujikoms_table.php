<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sk_hasil_ujikoms', function (Blueprint $table) {
            $table->id();

            // Batch kolektif yang di-cover SK ini
            $table->string('collective_batch_id');

            // Field yang diisi manajer saat pengajuan
            $table->string('nomor_sk')->nullable();
            $table->date('tanggal_pleno');
            $table->string('tempat_dikeluarkan')->default('Bandung');

            // Workflow status
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])
                  ->default('draft');

            // Direktur
            $table->text('catatan_direktur')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            // File PDF hasil generate
            $table->string('sk_path')->nullable();

            // Audit
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            // Satu batch hanya boleh punya satu SK (tidak duplikat)
            $table->unique('collective_batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sk_hasil_ujikoms');
    }
};