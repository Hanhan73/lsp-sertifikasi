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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesmen_id')->constrained('asesmens')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->enum('method', ['midtrans', 'transfer', 'cash', 'qris', 'other'])->default('midtrans');
            $table->string('proof_path')->nullable(); // Bukti pembayaran (untuk manual payment)
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            
            // Midtrans specific fields
            $table->string('transaction_id')->nullable(); // Midtrans transaction ID
            $table->string('order_id')->nullable(); // Order ID
            $table->string('payment_type')->nullable(); // Payment method used (gopay, bank_transfer, etc)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};