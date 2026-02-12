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
        Schema::create('asesmens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tuk_id')->nullable()->constrained('tuks')->onDelete('set null');
            $table->foreignId('skema_id')->nullable()->constrained('skemas')->onDelete('set null');
            
            // Data Pribadi Asesi
            $table->string('full_name');
            $table->string('nik', 16)->unique()->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->text('address')->nullable();
            $table->string('city_code', 4)->nullable();
            $table->string('province_code', 2)->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('education')->nullable();
            $table->string('occupation')->nullable();
            $table->string('budget_source')->nullable();
            $table->string('institution')->nullable();
            
            // Upload Files
            $table->string('photo_path')->nullable();
            $table->string('ktp_path')->nullable();
            $table->string('document_path')->nullable(); // Ijazah/transkrip
            
            // Tanggal & Lokasi
            $table->date('preferred_date')->nullable();
            $table->date('registration_date')->default(now());
            
            // Status Tracking
            $table->enum('status', [
                'registered',
                'data_completed',
                'verified',
                'paid',
                'scheduled',
                'pre_assessment_completed',
                'assessed',
                'certified'
            ])->default('registered');
            
            // Verifikasi & Biaya
            $table->decimal('fee_amount', 15, 2)->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            
            // Hasil Asesmen
            $table->enum('result', ['kompeten', 'belum_kompeten'])->nullable();
            $table->text('result_notes')->nullable();
            $table->foreignId('assessed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assessed_at')->nullable();
            
            // Pra-Asesmen
            $table->text('pre_assessment_data')->nullable(); // JSON data
            $table->string('pre_assessment_file')->nullable();
            
            // Registrasi Kolektif
            $table->foreignId('registered_by')->nullable()->constrained('users')->onDelete('set null'); // TUK yang mendaftarkan
            $table->boolean('is_collective')->default(false);
            $table->string('collective_batch_id')->nullable(); // Group ID untuk pembayaran kolektif
            $table->enum('collective_payment_timing', ['before', 'after'])->nullable(); // Kapan TUK bayar: sebelum atau sesudah asesmen
            
            $table->timestamps();
            
            $table->index('status');
            $table->index('nik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asesmens');
    }
};