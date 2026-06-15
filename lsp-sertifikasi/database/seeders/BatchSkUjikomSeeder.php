<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Tuk;
use App\Models\Skema;
use App\Models\Asesor;
use App\Models\Asesmen;
use App\Models\Schedule;
use App\Models\BeritaAcara;
use App\Models\BeritaAcaraAsesi;

/**
 * BatchSkUjikomSeeder
 *
 * 1 batch kolektif → 2 jadwal berbeda → 2 asesor berbeda
 * Semua sudah ada Berita Acara dengan rekomendasi K
 * → Siap di-generate SK-nya lewat Admin
 *
 * Jalankan: php artisan db:seed --class=BatchSkUjikomSeeder
 */
class BatchSkUjikomSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $tuk   = Tuk::where('is_active', true)->firstOrFail();
        $skema = Skema::where('is_active', true)->firstOrFail();

        $batchId = 'BATCH-SK-DEMO-' . now()->format('YmdHis');

        $this->command->info("📦 Batch ID : {$batchId}");
        $this->command->info("🏫 TUK      : {$tuk->name}");
        $this->command->info("📋 Skema    : {$skema->name}");

        DB::transaction(function () use ($admin, $tuk, $skema, $batchId) {

            // ── 1. Buat 2 Asesor ──────────────────────────────────────────

            $asesor1 = $this->buatAsesor(
                email:     'asesor.sk.demo1@lsp.com',
                nama:      'Darma Rika Swaramarinda, M.M.',
                noRegMet:  '000.010993 2018',
            );

            $asesor2 = $this->buatAsesor(
                email:     'asesor.sk.demo2@lsp.com',
                nama:      'Dewi Nurmalasari, S.Pd., M.M.',
                noRegMet:  '000.011016 2018',
            );

            $this->command->info("✅ Asesor 1 : {$asesor1->nama}");
            $this->command->info("✅ Asesor 2 : {$asesor2->nama}");

            // ── 2. Buat Jadwal 1 (asesor 1, 5 peserta) ───────────────────

            $jadwal1 = $this->buatJadwal(
                tuk:    $tuk,
                skema:  $skema,
                asesor: $asesor1,
                admin:  $admin,
                hariLalu: 10,
                label:  'Jadwal A',
            );

            $peserta1 = $this->buatPeserta(
                jumlah:  5,
                prefix:  'Asesi Kelompok A',
                emailPrefix: 'asesi.a',
                tuk:     $tuk,
                skema:   $skema,
                admin:   $admin,
                batchId: $batchId,
                jadwal:  $jadwal1,
            );

            // ── 3. Buat Jadwal 2 (asesor 2, 4 peserta) ───────────────────

            $jadwal2 = $this->buatJadwal(
                tuk:    $tuk,
                skema:  $skema,
                asesor: $asesor2,
                admin:  $admin,
                hariLalu: 7,
                label:  'Jadwal B',
            );

            $peserta2 = $this->buatPeserta(
                jumlah:  4,
                prefix:  'Asesi Kelompok B',
                emailPrefix: 'asesi.b',
                tuk:     $tuk,
                skema:   $skema,
                admin:   $admin,
                batchId: $batchId,
                jadwal:  $jadwal2,
            );

            // ── 4. Buat Berita Acara Jadwal 1 ────────────────────────────

            $this->buatBeritaAcara($jadwal1, $peserta1, $admin);
            $this->command->info("✅ Berita Acara Jadwal A dibuat ({$peserta1->count()} peserta → semua K)");

            // ── 5. Buat Berita Acara Jadwal 2 ────────────────────────────

            $this->buatBeritaAcara($jadwal2, $peserta2, $admin);
            $this->command->info("✅ Berita Acara Jadwal B dibuat ({$peserta2->count()} peserta → semua K)");

        });

        $this->command->newLine();
        $this->command->info('🎉 Seeder selesai!');
        $this->command->info("   Batch ID : {$batchId}");
        $this->command->info('   Login admin lalu buka: Admin → SK Hasil Ujikom');
        $this->command->info('   Login asesi: asesi.a.1@test.com / password123');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function buatAsesor(string $email, string $nama, string $noRegMet): Asesor
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'              => $nama,
                'password'          => Hash::make('password123'),
                'role'              => 'asesor',
                'is_active'         => true,
                'email_verified_at' => now(),
                'password_changed_at' => now(),
            ]
        );

        return Asesor::firstOrCreate(
            ['user_id' => $user->id],
            [
                'nama'          => $nama,
                'nik'           => '32' . rand(100000000000, 999999999999),
                'email'         => $email,
                'telepon'       => '0812' . rand(10000000, 99999999),
                'tempat_lahir'  => 'Jakarta',
                'tanggal_lahir' => '1975-06-15',
                'no_reg_met'    => $noRegMet,
                'status_reg'    => 'aktif',
                'is_active'     => true,
            ]
        );
    }

    private function buatJadwal(
        Tuk    $tuk,
        Skema  $skema,
        Asesor $asesor,
        User   $admin,
        int    $hariLalu,
        string $label,
    ): Schedule {
        return Schedule::create([
            'tuk_id'          => $tuk->id,
            'skema_id'        => $skema->id,
            'asesor_id'       => $asesor->id,
            'assessment_date' => now()->subDays($hariLalu)->toDateString(),
            'start_time'      => '08:00:00',
            'end_time'        => '14:00:00',
            'location'        => $tuk->name,
            'location_type'   => 'offline',
            'notes'           => "[SK-DEMO] {$label}",
            'created_by'      => $admin->id,
            'approval_status' => 'approved',
            'approved_by'     => $admin->id,
            'approved_at'     => now()->subDays($hariLalu + 2),
            'sk_number'       => 'SK-DEMO-' . strtoupper(str_replace(' ', '', $label)) . '-' . date('Ymd'),
        ]);
    }

    private function buatPeserta(
        int    $jumlah,
        string $prefix,
        string $emailPrefix,
        Tuk    $tuk,
        Skema  $skema,
        User   $admin,
        string $batchId,
        Schedule $jadwal,
    ) {
        $asesmens = collect();

        for ($i = 1; $i <= $jumlah; $i++) {
            $email = "{$emailPrefix}.{$i}@test.com";
            $nama  = "{$prefix} {$i}";

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'                => $nama,
                    'password'            => Hash::make('password123'),
                    'role'                => 'asesi',
                    'is_active'           => true,
                    'email_verified_at'   => now(),
                    'password_changed_at' => now(),
                ]
            );

            $asesmen = Asesmen::create([
                'user_id'             => $user->id,
                'tuk_id'              => $tuk->id,
                'skema_id'            => $skema->id,
                'schedule_id'         => $jadwal->id,
                'collective_batch_id' => $batchId,
                'is_collective'       => true,
                'registered_by'       => $admin->id,
                'registration_date'   => now()->subDays(20),
                'status'              => 'assessed',
                'fee_amount'          => $skema->fee ?? 600000,
                'institution'         => $tuk->name,
                // Data pribadi minimal
                'full_name'           => $nama,
                'nik'                 => '32' . rand(1000000000000, 9999999999999),
                'phone'               => '0812' . rand(10000000, 99999999),
                'email'               => $email,
                'tempat_lahir'        => 'Jakarta',
                'tanggal_lahir'       => '2005-01-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'jenis_kelamin'       => $i % 2 === 0 ? 'L' : 'P',
                'address'             => 'Jl. Demo No. ' . $i . ', Jakarta',
                'education'           => 'SMA',
                'occupation'          => 'Siswa',
                'budget_source'       => 'Institusi',
                'admin_started_at'    => now()->subDays(18),
                'admin_started_by'    => $admin->id,
            ]);

            $asesmens->push($asesmen);
        }

        return $asesmens;
    }

    private function buatBeritaAcara(Schedule $jadwal, $asesmens, User $admin): void
    {
        $ba = BeritaAcara::create([
            'schedule_id'         => $jadwal->id,
            'tanggal_pelaksanaan' => $jadwal->assessment_date,
            'catatan'             => 'Pelaksanaan berjalan lancar. Seluruh peserta hadir.',
            'dibuat_oleh'         => $admin->id,
        ]);

        foreach ($asesmens as $asesmen) {
            BeritaAcaraAsesi::create([
                'berita_acara_id' => $ba->id,
                'asesmen_id'      => $asesmen->id,
                'rekomendasi'     => 'K',
                'catatan'         => null,
            ]);

            // Tandai hadir
            $asesmen->update(['hadir' => true]);
        }
    }
}
