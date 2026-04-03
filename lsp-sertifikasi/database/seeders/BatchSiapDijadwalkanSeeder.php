<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Asesmen;
use App\Models\AplSatu;
use App\Models\AplSatuBukti;
use App\Models\AplDua;
use App\Models\AplDuaJawaban;
use App\Models\FrAk01;
use App\Models\Tuk;
use App\Models\Skema;
use App\Models\Payment;

/**
 * BatchSiapDijadwalkanSeeder
 *
 * Membuat 1 batch kolektif yang sudah melewati seluruh tahapan pra-asesmen:
 *   ✅ TUK terverifikasi
 *   ✅ Admin verifikasi & set fee
 *   ✅ Pembayaran lunas
 *   ✅ APL-01 status: verified
 *   ✅ APL-02 status: submitted
 *   ✅ FR.AK.01 status: submitted
 *   ✅ schedule_id: null  ← siap dijadwalkan oleh admin
 *
 * Jalankan: php artisan db:seed --class=BatchSiapDijadwalkanSeeder
 */
class BatchSiapDijadwalkanSeeder extends Seeder
{
    // ── Konfigurasi batch ─────────────────────────────────────────
    private const JUMLAH_PESERTA = 5;
    private const NAMA_PREFIX    = 'Peserta Siap Jadwal';
    private const INSTITUSI      = 'Universitas Potensi Utama';

    public function run(): void
    {
        // Ambil TUK dan Skema yang sudah ada
        $tuk   = Tuk::where('is_active', true)->firstOrFail();
        $skema = Skema::where('is_active', true)->firstOrFail();
        $admin = User::where('role', 'admin')->firstOrFail();

        $batchId = 'BATCH-READY-' . strtoupper($tuk->code ?? 'TUK') . '-' . now()->format('YmdHis');

        $this->command->info("Membuat batch: {$batchId}");
        $this->command->info("TUK   : {$tuk->name}");
        $this->command->info("Skema : {$skema->name}");

        DB::transaction(function () use ($tuk, $skema, $admin, $batchId) {
            for ($i = 1; $i <= self::JUMLAH_PESERTA; $i++) {
                $this->buatPeserta($i, $tuk, $skema, $admin, $batchId);
            }
        });

        $this->command->info('✅ Seeder selesai. ' . self::JUMLAH_PESERTA . ' peserta siap dijadwalkan.');
        $this->command->info("   Batch ID : {$batchId}");
        $this->command->info("   Login    : kolektif.ready.1@test.com / password123");
    }

    // ─────────────────────────────────────────────────────────────
    // BUAT SATU PESERTA
    // ─────────────────────────────────────────────────────────────

    private function buatPeserta(int $no, Tuk $tuk, Skema $skema, User $admin, string $batchId): void
    {
        // 1. USER
        $user = User::create([
            'name'                => self::NAMA_PREFIX . " $no",
            'email'               => "kolektif.ready.{$no}@test.com",
            'password'            => Hash::make('password123'),
            'role'                => 'asesi',
            'is_active'           => true,
            'email_verified_at'   => now(),
            'password_changed_at' => now(),
        ]);

        // 2. ASESMEN
        $feeAmount = $skema->fee ?? 1500000;
        $asesmen = Asesmen::create([
            'user_id'              => $user->id,
            'tuk_id'               => $tuk->id,
            'skema_id'             => $skema->id,
            'full_name'            => $user->name,
            'nik'                  => '3275' . str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
            'birth_place'          => 'Bandung',
            'birth_date'           => '1998-06-' . str_pad($no, 2, '0', STR_PAD_LEFT),
            'gender'               => $no % 2 === 0 ? 'P' : 'L',
            'address'              => 'Jl. Test No. ' . $no . ', Bandung',
            'city_code'            => '3273',
            'province_code'        => '32',
            'phone'                => '0812345' . str_pad($no, 5, '0', STR_PAD_LEFT),
            'education'            => 'S1',
            'occupation'           => 'Staf Administrasi',
            'budget_source'        => 'Institusi',
            'institution'          => self::INSTITUSI,
            'registration_date'    => now()->subDays(10),
            // Status: pra_asesmen_started — sudah melewati verifikasi admin
            'status'               => 'pra_asesmen_started',
            // TUK verification
            'tuk_verified_by'      => $tuk->user_id,
            'tuk_verified_at'      => now()->subDays(8),
            'tuk_verification_notes' => 'Data lengkap dan valid',
            // Admin verification
            'admin_verified_by'    => $admin->id,
            'admin_verified_at'    => now()->subDays(7),
            'admin_started_by'     => $admin->id,
            'admin_started_at'     => now()->subDays(7),
            'fee_amount'           => $feeAmount,
            // Kolektif
            'registered_by'        => $tuk->user_id,
            'is_collective'        => true,
            'collective_batch_id'  => $batchId,
            'collective_paid_by_tuk' => true,
            'skip_payment'         => true,
            // Belum punya jadwal
            'schedule_id'          => null,
        ]);

        // 3. PAYMENT — lunas
        Payment::create([
            'asesmen_id'     => $asesmen->id,
            'amount'         => $feeAmount,
            'method'         => 'bank_transfer',
            'status'         => 'verified',
            'payment_phase'  => 'full',
            'order_id'       => 'ORDER-SEED-' . $asesmen->id . '-' . time(),
            'transaction_id' => 'TRX-SEED-' . $asesmen->id,
            'payment_type'   => 'bank_transfer',
            'verified_at'    => now()->subDays(6),
            'notes'          => 'Seeder - lunas',
        ]);

        // 4. APL-01 — verified
        $apl01 = AplSatu::create([
            'asesmen_id'       => $asesmen->id,
            'nama_lengkap'     => $asesmen->full_name,
            'nik'              => $asesmen->nik,
            'tempat_lahir'     => $asesmen->birth_place,
            'tanggal_lahir'    => $asesmen->birth_date,
            'jenis_kelamin'    => $asesmen->gender,
            'alamat'           => $asesmen->address,
            'telepon'          => $asesmen->phone,
            'pendidikan'       => $asesmen->education,
            'pekerjaan'        => $asesmen->occupation,
            'nama_institusi'   => $asesmen->institution,
            'tujuan_asesmen'   => 'sertifikasi',
            'status'           => 'verified',
            'submitted_at'     => now()->subDays(9),
            'verified_at'      => now()->subDays(7),
            'verified_by'      => $admin->id,
            // TTD asesi (raw base64 kosong — simulasi sudah TTD)
            'ttd_asesi'        => null,
            'nama_ttd_asesi'   => $asesmen->full_name,
            'tanggal_ttd_asesi'=> now()->subDays(9),
        ]);

        // Bukti kelengkapan APL-01 — semua "Ada Memenuhi Syarat"
        $buktis = [
            'Ijazah Terakhir',
            'KTP',
            'Foto 3x4',
            'CV / Riwayat Hidup',
            'Sertifikat Pelatihan (jika ada)',
        ];
        foreach ($buktis as $namaBukti) {
            AplSatuBukti::create([
                'apl_satu_id' => $apl01->id,
                'nama_bukti'  => $namaBukti,
                'status'      => 'Ada Memenuhi Syarat',
                'verified_by' => $admin->id,
                'verified_at' => now()->subDays(7),
            ]);
        }

        // 5. APL-02 — submitted
        $apl02 = AplDua::create([
            'asesmen_id'       => $asesmen->id,
            'status'           => 'submitted',
            'submitted_at'     => now()->subDays(6),
            'ttd_asesi'        => null,
            'nama_ttd_asesi'   => $asesmen->full_name,
            'tanggal_ttd_asesi'=> now()->subDays(6),
        ]);

        // Jawaban APL-02 — isi semua elemen kompetensi skema
        $skema->loadMissing('unitKompetensis.elemens');
        foreach ($skema->unitKompetensis as $unit) {
            foreach ($unit->elemens as $elemen) {
                AplDuaJawaban::create([
                    'apl_02_id' => $apl02->id,
                    'elemen_id' => $elemen->id,
                    'jawaban'   => 'K',
                    'bukti'     => 'Pengalaman kerja selama 2 tahun di bidang administrasi perkantoran.',
                ]);
            }
        }

        // 6. FR.AK.01 — submitted (menunggu TTD asesor)
        FrAk01::create([
            'asesmen_id'       => $asesmen->id,
            'status'           => 'submitted',
            'submitted_at'     => now()->subDays(5),
            // Isian form FR.AK.01
            'nama_asesi'       => $asesmen->full_name,
            'nik_asesi'        => $asesmen->nik,
            'nama_skema'       => $skema->name,
            'tujuan_asesmen'   => 'Mendapatkan pengakuan kompetensi di bidang administrasi perkantoran.',
            'konfirmasi_mandiri'        => true,
            'konfirmasi_bukti_valid'    => true,
            'konfirmasi_proses_asesmen' => true,
            // TTD asesi
            'ttd_asesi'        => null,
            'nama_ttd_asesi'   => $asesmen->full_name,
            'tanggal_ttd_asesi'=> now()->subDays(5),
            // TTD asesor belum ada
            'ttd_asesor'       => null,
        ]);

        $this->command->line("  [{$no}] {$user->name} ({$user->email}) ✓");
    }
}