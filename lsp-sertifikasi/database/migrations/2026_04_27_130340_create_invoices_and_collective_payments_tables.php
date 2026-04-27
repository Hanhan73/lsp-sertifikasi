<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Tabel invoices (kolektif) ──────────────────────────────────────
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Format: 42/LSP-KAP/KU.00.01/IV/2026 — urutan auto per tahun
            $table->string('invoice_number')->unique();
            $table->unsignedInteger('sequence_number'); // urutan dalam tahun
            $table->year('invoice_year');               // tahun invoice

            $table->string('batch_id');
            $table->foreignId('tuk_id')->constrained('tuks')->onDelete('restrict');
            $table->foreignId('issued_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('issued_at');

            // Tujuan invoice (nama lembaga TUK / institusi)
            $table->string('recipient_name');
            $table->text('recipient_address')->nullable();

            // Items: JSON array [{skema_id, skema_name, jumlah_asesi, harga_satuan, subtotal}]
            $table->json('items');

            $table->decimal('total_amount', 15, 2);
            $table->text('notes')->nullable();

            // Status: draft → sent → paid (fully paid setelah semua angsuran lunas)
            $table->enum('status', ['draft', 'sent', 'paid'])->default('draft');

            $table->timestamps();

            $table->index('batch_id');
            $table->index(['invoice_year', 'sequence_number']);
        });

        // ── Tabel collective_payments (angsuran kolektif) ─────────────────
        Schema::create('collective_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->string('batch_id');
            $table->foreignId('tuk_id')->constrained('tuks')->onDelete('restrict');

            // Angsuran ke-1, 2, atau 3
            $table->unsignedTinyInteger('installment_number');

            $table->decimal('amount', 15, 2);
            $table->date('due_date')->nullable();

            // Upload bukti bayar oleh TUK
            $table->string('proof_path')->nullable();
            $table->timestamp('proof_uploaded_at')->nullable();

            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');

            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_notes')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('batch_id');
            $table->unique(['invoice_id', 'installment_number']); // 1 record per angsuran per invoice
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collective_payments');
        Schema::dropIfExists('invoices');
    }
};