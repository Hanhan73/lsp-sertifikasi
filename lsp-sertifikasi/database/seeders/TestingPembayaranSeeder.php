<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Tuk;
use App\Models\Skema;
use App\Models\Asesmen;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\CollectivePayment;

/**
 * TestingPembayaranSeeder
 *
 * Membuat data testing untuk alur pembayaran kolektif dan individu:
 *
 * KOLEKTIF:
 *   - Batch A: Invoice sudah dibuat (draft) — belum ada angsuran
 *   - Batch B: Invoice sudah dikirim (sent) — 1 angsuran pending (TUK sudah upload bukti)
 *   - Batch C: Invoice sent — 1 angsuran verified, 1 pending
 *   - Batch D: Multi-batch (2 batch dalam 1 invoice) — draft
 *
 * INDIVIDU:
 *   - Asesi 1: payment pending (sudah upload bukti, menunggu verifikasi bendahara)
 *   - Asesi 2: payment verified (sudah lunas, bisa download invoice/kwitansi)
 *   - Asesi 3: payment rejected (bukti ditolak, perlu upload ulang)
 *   - Asesi 4: belum upload bukti
 *
 * Jalankan: php artisan db:seed --class=TestingPembayaranSeeder
 */
class TestingPembayaranSeeder extends Seeder
{
    public function run(): void
    {
        $tuk    = Tuk::where('is_active', true)->firstOrFail();
        $skema  = Skema::where('is_active', true)->firstOrFail();
        $admin  = User::where('role', 'admin')->firstOrFail();
        $bendahara = User::where('role', 'bendahara')->first() ?? $admin;

        $this->command->info('');
        $this->command->info('🌱 TestingPembayaranSeeder');
        $this->command->info('TUK   : ' . $tuk->name);
        $this->command->info('Skema : ' . $skema->name);
        $this->command->info('');

        DB::transaction(function () use ($tuk, $skema, $admin, $bendahara) {
            $this->buatKolektif($tuk, $skema, $admin, $bendahara);
            $this->buatIndividu($tuk, $skema, $admin, $bendahara);
        });

        $this->command->info('');
        $this->command->info('✅ Seeder selesai!');
        $this->command->info('');
        $this->command->info('LOGIN BENDAHARA  : bendahara@test.com / password123');
        $this->command->info('LOGIN TUK        : tuk yang sudah ada');
        $this->command->info('');
    }

    // =========================================================================
    // KOLEKTIF
    // =========================================================================

    private function buatKolektif(Tuk $tuk, Skema $skema, User $admin, User $bendahara): void
    {
        $this->command->info('── KOLEKTIF ──────────────────────────────────────────');

        // ── Batch A: Invoice draft, belum ada angsuran ────────────────────
        $batchA = 'BATCH-PAY-A-' . now()->format('His');
        $asesmensA = $this->buatBatchAsesmens($batchA, 3, $tuk, $skema, $admin, 'A');
        $invoiceA  = $this->buatInvoice($batchA, [$batchA], $tuk, $asesmensA, $skema, $bendahara, 'draft');
        $this->command->info("  Batch A (draft, no angsuran): {$invoiceA->invoice_number}");

        // ── Batch B: Invoice sent, 1 angsuran pending (bukti diupload) ───
        $batchB = 'BATCH-PAY-B-' . now()->format('His');
        $asesmensB = $this->buatBatchAsesmens($batchB, 4, $tuk, $skema, $admin, 'B');
        $invoiceB  = $this->buatInvoice($batchB, [$batchB], $tuk, $asesmensB, $skema, $bendahara, 'sent');
        $this->buatAngsuran($invoiceB, 1, $invoiceB->total_amount, $tuk, 'pending', withBukti: true);
        $this->command->info("  Batch B (sent, 1 angsuran pending+bukti): {$invoiceB->invoice_number}");

        // ── Batch C: Invoice sent, angsuran 1 verified + angsuran 2 pending ─
        $batchC = 'BATCH-PAY-C-' . now()->format('His');
        $asesmensC = $this->buatBatchAsesmens($batchC, 5, $tuk, $skema, $admin, 'C');
        $invoiceC  = $this->buatInvoice($batchC, [$batchC], $tuk, $asesmensC, $skema, $bendahara, 'sent');
        $half = $invoiceC->total_amount / 2;
        $this->buatAngsuran($invoiceC, 1, $half, $tuk, 'verified', withBukti: true, verifiedBy: $bendahara);
        $this->buatAngsuran($invoiceC, 2, $half, $tuk, 'pending', withBukti: true);
        $this->command->info("  Batch C (sent, ang1 verified + ang2 pending): {$invoiceC->invoice_number}");

        // ── Batch D: Multi-batch (2 batch dalam 1 invoice) ───────────────
        $batchD1 = 'BATCH-PAY-D1-' . now()->format('His');
        $batchD2 = 'BATCH-PAY-D2-' . now()->format('His');
        $asesmensD1 = $this->buatBatchAsesmens($batchD1, 3, $tuk, $skema, $admin, 'D1');
        $asesmensD2 = $this->buatBatchAsesmens($batchD2, 4, $tuk, $skema, $admin, 'D2');
        $allAsesmens = $asesmensD1->merge($asesmensD2);
        $invoiceD = $this->buatInvoice(
            $batchD1,
            [$batchD1, $batchD2],
            $tuk,
            $allAsesmens,
            $skema,
            $bendahara,
            'sent'
        );
        $this->command->info("  Batch D (multi-batch 2 batch, sent, no angsuran): {$invoiceD->invoice_number}");
    }

    private function buatBatchAsesmens(
        string $batchId,
        int $jumlah,
        Tuk $tuk,
        Skema $skema,
        User $admin,
        string $prefix
    ): \Illuminate\Support\Collection {
        $asesmens = collect();
        for ($i = 1; $i <= $jumlah; $i++) {
            $email = "kolektif.{$prefix}.{$i}." . now()->format('His') . "@test.com";
            $user  = User::create([
                'name'                => "Asesi Kolektif {$prefix}-{$i}",
                'email'               => $email,
                'password'            => Hash::make('password123'),
                'role'                => 'asesi',
                'is_active'           => true,
                'email_verified_at'   => now(),
                'password_changed_at' => now(),
            ]);

            $asesmen = Asesmen::create([
                'user_id'               => $user->id,
                'tuk_id'                => $tuk->id,
                'skema_id'              => $skema->id,
                'full_name'             => $user->name,
                'nik'                   => '327501' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                'birth_place'           => 'Bandung',
                'birth_date'            => '2000-01-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'gender'                => $i % 2 === 0 ? 'P' : 'L',
                'address'               => 'Jl. Testing No. ' . $i,
                'city_code'             => '3273',
                'province_code'         => '32',
                'phone'                 => '08111' . str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                'email'                 => $email,
                'education'             => 'S1',
                'occupation'            => 'Staf',
                'institution'           => 'Institusi Testing ' . $prefix,
                'registration_date'     => now()->subDays(5),
                'status'                => 'pra_asesmen_started',
                'fee_amount'            => $skema->fee ?? 750000,
                'is_collective'         => true,
                'collective_batch_id'   => $batchId,
                'collective_paid_by_tuk'=> true,
                'skip_payment'          => true,
                'registered_by'         => $tuk->user_id ?? null,
                'admin_verified_by'     => $admin->id,
                'admin_verified_at'     => now()->subDays(3),
                'admin_started_by'      => $admin->id,
                'admin_started_at'      => now()->subDays(3),
            ]);

            $asesmens->push($asesmen);
        }
        return $asesmens;
    }

    private function buatInvoice(
        string $primaryBatchId,
        array $batchIds,
        Tuk $tuk,
        \Illuminate\Support\Collection $asesmens,
        Skema $skema,
        User $bendahara,
        string $status
    ): Invoice {
        // Build items per skema
        $items = $asesmens->groupBy('skema_id')->map(function ($group) use ($skema) {
            $s      = $group->first()->skema ?? $skema;
            $jumlah = $group->count();
            $harga  = (float) ($s->fee ?? 750000);
            return [
                'skema_id'     => $s->id,
                'skema_name'   => $s->name,
                'jumlah'       => $jumlah,
                'harga_satuan' => $harga,
                'subtotal'     => $harga * $jumlah,
            ];
        })->values()->toArray();

        $total = collect($items)->sum('subtotal');
        $n     = Invoice::generateNumber();

        return Invoice::create([
            'invoice_number'    => $n['invoice_number'],
            'sequence_number'   => $n['sequence_number'],
            'invoice_year'      => $n['invoice_year'],
            'batch_ids'         => $batchIds,
            'tuk_id'            => $tuk->id,
            'issued_by'         => $bendahara->id,
            'issued_at'         => now()->subDays(2),
            'recipient_name'    => $tuk->name,
            'recipient_address' => $tuk->address ?? 'Jl. Testing, Bandung',
            'items'             => $items,
            'total_amount'      => $total,
            'notes'             => 'Data testing — ' . count($batchIds) . ' batch',
            'status'            => $status,
        ]);
    }

    private function buatAngsuran(
        Invoice $invoice,
        int $nomor,
        float $amount,
        Tuk $tuk,
        string $status,
        bool $withBukti = false,
        ?User $verifiedBy = null
    ): CollectivePayment {
        $data = [
            'invoice_id'         => $invoice->id,
            'batch_id'           => $invoice->batch_ids[0] ?? '',
            'tuk_id'             => $tuk->id,
            'installment_number' => $nomor,
            'amount'             => $amount,
            'due_date'           => now()->addDays(7),
            'notes'              => 'Testing angsuran ke-' . $nomor,
            'status'             => $status,
        ];

        if ($withBukti) {
            // Simpan path dummy — file tidak perlu ada untuk testing UI
            $data['proof_path']         = 'collective-payments/testing/dummy-bukti-' . $invoice->id . '-' . $nomor . '.jpg';
            $data['proof_uploaded_at']  = now()->subHours(2);
        }

        if ($status === 'verified' && $verifiedBy) {
            $data['verified_by'] = $verifiedBy->id;
            $data['verified_at'] = now()->subHours(1);
        }

        if ($status === 'rejected') {
            $data['rejection_notes'] = 'Bukti transfer tidak jelas, mohon upload ulang.';
        }

        return CollectivePayment::create($data);
    }

    // =========================================================================
    // INDIVIDU
    // =========================================================================

    private function buatIndividu(Tuk $tuk, Skema $skema, User $admin, User $bendahara): void
    {
        $this->command->info('── INDIVIDU ──────────────────────────────────────────');

        $ts = now()->format('His');

        // Asesi 1 — payment pending (sudah upload bukti)
        $a1 = $this->buatAsesiIndividu($tuk, $skema, $admin, "ind.1.{$ts}@test.com", 'Asesi Individu Pending');
        $p1 = Payment::create([
            'asesmen_id'     => $a1->id,
            'amount'         => $a1->fee_amount,
            'method'         => 'transfer',
            'status'         => 'pending',
            'payment_phase'  => 'full',
            'order_id'       => 'ORDER-IND-1-' . $ts,
            'proof_path'     => 'payments/testing/dummy-bukti-ind-1.jpg',
            'notes'          => 'Transfer BCA mobile',
        ]);
        $this->command->info("  Asesi 1 (pending+bukti): {$a1->full_name} | {$a1->user->email}");

        // Asesi 2 — payment verified (bisa download invoice/kwitansi)
        $a2 = $this->buatAsesiIndividu($tuk, $skema, $admin, "ind.2.{$ts}@test.com", 'Asesi Individu Verified');
        $a2->update(['status' => 'pra_asesmen_started']);
        $p2 = Payment::create([
            'asesmen_id'     => $a2->id,
            'amount'         => $a2->fee_amount,
            'method'         => 'transfer',
            'status'         => 'verified',
            'payment_phase'  => 'full',
            'order_id'       => 'ORDER-IND-2-' . $ts,
            'proof_path'     => 'payments/testing/dummy-bukti-ind-2.jpg',
            'transaction_id' => 'TRX-IND-2-' . $ts,
            'verified_by'    => $bendahara->id,
            'verified_at'    => now()->subHours(3),
            'notes'          => 'Terverifikasi — testing',
        ]);
        $this->command->info("  Asesi 2 (verified): {$a2->full_name} | {$a2->user->email}");

        // Asesi 3 — payment rejected
        $a3 = $this->buatAsesiIndividu($tuk, $skema, $admin, "ind.3.{$ts}@test.com", 'Asesi Individu Rejected');
        $p3 = Payment::create([
            'asesmen_id'      => $a3->id,
            'amount'          => $a3->fee_amount,
            'method'          => 'qris',
            'status'          => 'rejected',
            'payment_phase'   => 'full',
            'order_id'        => 'ORDER-IND-3-' . $ts,
            'proof_path'      => 'payments/testing/dummy-bukti-ind-3.jpg',
            'rejection_notes' => 'Bukti tidak jelas, mohon upload screenshot yang lebih jelas.',
            'verified_by'     => $bendahara->id,
            'verified_at'     => now()->subHours(5),
        ]);
        $this->command->info("  Asesi 3 (rejected): {$a3->full_name} | {$a3->user->email}");

        // Asesi 4 — belum upload bukti
        $a4 = $this->buatAsesiIndividu($tuk, $skema, $admin, "ind.4.{$ts}@test.com", 'Asesi Individu Belum Bayar');
        $this->command->info("  Asesi 4 (belum upload): {$a4->full_name} | {$a4->user->email}");

        $this->command->info('');
        $this->command->info('Login asesi individu: password = password123');
    }

    private function buatAsesiIndividu(Tuk $tuk, Skema $skema, User $admin, string $email, string $nama): Asesmen
    {
        $user = User::create([
            'name'                => $nama,
            'email'               => $email,
            'password'            => Hash::make('password123'),
            'role'                => 'asesi',
            'is_active'           => true,
            'email_verified_at'   => now(),
            'password_changed_at' => now(),
        ]);

        return Asesmen::create([
            'user_id'            => $user->id,
            'tuk_id'             => $tuk->id,
            'skema_id'           => $skema->id,
            'full_name'          => $nama,
            'nik'                => '327501' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT),
            'birth_place'        => 'Jakarta',
            'birth_date'         => '1998-05-15',
            'gender'             => 'L',
            'address'            => 'Jl. Testing Individu, Jakarta',
            'city_code'          => '3171',
            'province_code'      => '31',
            'phone'              => '08122' . str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
            'email'              => $email,
            'education'          => 'S1',
            'occupation'         => 'Staf Administrasi',
            'institution'        => 'Instansi Testing',
            'registration_date'  => now()->subDays(3),
            'status'             => 'payment_pending',
            'fee_amount'         => $skema->fee ?? 750000,
            'is_collective'      => false,
            'admin_verified_by'  => $admin->id,
            'admin_verified_at'  => now()->subDays(2),
            'admin_started_by'   => $admin->id,
            'admin_started_at'   => now()->subDays(2),
        ]);
    }
}