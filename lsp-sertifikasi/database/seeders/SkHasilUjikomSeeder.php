<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Tuk;
use App\Models\Skema;
use App\Models\Asesor;
use App\Models\Asesmen;
use App\Models\Schedule;
use App\Models\BeritaAcara;
use App\Models\BeritaAcaraAsesi;
use App\Models\SkHasilUjikom;

/**
 * SkHasilUjikomSeeder
 *
 * Membuat 4 skenario berbeda untuk testing halaman SK Hasil Ujikom:
 *
 * [A] Batch 1 jadwal, 5 peserta, semua K → SK sudah APPROVED
 * [B] Batch 1 jadwal, 4 peserta, campuran K/BK → SK SUBMITTED (menunggu direktur)
 * [C] Batch 2 jadwal berbeda, masing-masing 3 peserta, semua K → SK DRAFT (belum diajukan)
 * [D] Batch 2 jadwal berbeda, peserta campuran → SK REJECTED
 *
 * Jalankan: php artisan db:seed --class=SkHasilUjikomSeeder
 */
class SkHasilUjikomSeeder extends Seeder
{
    private User $admin;
    private User $direktur;
    private User $manajer;
    private Asesor $asesor;
    private Tuk $tuk;
    private Skema $skema;

    public function run(): void
    {
        $this->command->info('🌱 Seeding SK Hasil Ujikom...');

        $this->admin    = User::where('role', 'admin')->firstOrFail();
        $this->direktur = User::where('role', 'direktur')->firstOrFail();
        $this->manajer  = User::where('role', 'manajer_sertifikasi')->firstOrFail();
        $this->tuk      = Tuk::where('is_active', true)->firstOrFail();
        $this->skema    = Skema::where('is_active', true)->firstOrFail();

        // Ambil atau buat asesor
        $this->asesor = Asesor::first() ?? $this->buatAsesor();

        DB::transaction(function () {
            $this->skenarioA(); // SK approved, 1 jadwal, semua K
            $this->skenarioB(); // SK submitted, 1 jadwal, campuran K/BK
            $this->skenarioC(); // SK belum diajukan (BA ada, tapi SK belum dibuat)
            $this->skenarioD(); // SK rejected, 2 jadwal berbeda
        });

        $this->command->newLine();
        $this->command->line('══════════════════════════════════════════════════════');
        $this->command->info('  ✅ SkHasilUjikomSeeder selesai');
        $this->command->line('══════════════════════════════════════════════════════');
        $this->command->line('  [A] Batch SK-UJIKOM-A → SK APPROVED  (1 jadwal, 5 K)');
        $this->command->line('  [B] Batch SK-UJIKOM-B → SK SUBMITTED (1 jadwal, 3K+1BK)');
        $this->command->line('  [C] Batch SK-UJIKOM-C → Belum diajukan (2 jadwal, 6K)');
        $this->command->line('  [D] Batch SK-UJIKOM-D → SK REJECTED  (2 jadwal, 4K+2BK)');
        $this->command->line('══════════════════════════════════════════════════════');
    }

    // =========================================================================
    // [A] 1 batch · 1 jadwal · 5 peserta semua K · SK approved
    // =========================================================================

    private function skenarioA(): void
    {
        $batchId = 'SK-UJIKOM-A-' . now()->format('YmdHis');

        $asesmens = $this->buatPeserta($batchId, 5, 'SKA', 'Universitas Alpha');
        $jadwal   = $this->buatJadwal($asesmens, now()->subDays(20), $batchId, 'approved');

        // Berita Acara — semua K
        $ba = BeritaAcara::create([
            'schedule_id'       => $jadwal->id,
            'tanggal_pelaksanaan' => $jadwal->assessment_date,
            'dibuat_oleh'       => $this->asesor->user_id,
        ]);
        foreach ($asesmens as $a) {
            BeritaAcaraAsesi::create([
                'berita_acara_id' => $ba->id,
                'asesmen_id'      => $a->id,
                'rekomendasi'     => 'K',
            ]);
        }

        // SK approved
        SkHasilUjikom::create([
            'collective_batch_id' => $batchId,
            'nomor_sk'            => '001/LSP-KAP/SER.20.06/III/2026',
            'tanggal_pleno'       => now()->subDays(14),
            'tempat_dikeluarkan'  => 'Bandung',
            'status'              => 'approved',
            'submitted_at'        => now()->subDays(12),
            'approved_by'         => $this->direktur->id,
            'approved_at'         => now()->subDays(10),
            'created_by'          => $this->manajer->id,
        ]);

        $this->command->line("  [A] ✓ {$batchId}");
    }

    // =========================================================================
    // [B] 1 batch · 1 jadwal · 4 peserta (3 K + 1 BK) · SK submitted
    // =========================================================================

    private function skenarioB(): void
    {
        $batchId = 'SK-UJIKOM-B-' . now()->format('YmdHis');

        $asesmens = $this->buatPeserta($batchId, 4, 'SKB', 'Politeknik Beta');
        $jadwal   = $this->buatJadwal($asesmens, now()->subDays(15), $batchId, 'approved');

        // Berita Acara — 3 K + 1 BK
        $ba = BeritaAcara::create([
            'schedule_id'       => $jadwal->id,
            'tanggal_pelaksanaan' => $jadwal->assessment_date,
            'dibuat_oleh'       => $this->asesor->user_id,
        ]);
        foreach ($asesmens as $i => $a) {
            BeritaAcaraAsesi::create([
                'berita_acara_id' => $ba->id,
                'asesmen_id'      => $a->id,
                'rekomendasi'     => $i < 3 ? 'K' : 'BK',
            ]);
        }

        // SK submitted, belum di-approve
        SkHasilUjikom::create([
            'collective_batch_id' => $batchId,
            'nomor_sk'            => '002/LSP-KAP/SER.20.06/III/2026',
            'tanggal_pleno'       => now()->subDays(7),
            'tempat_dikeluarkan'  => 'Bandung',
            'status'              => 'submitted',
            'submitted_at'        => now()->subDays(5),
            'created_by'          => $this->manajer->id,
        ]);

        $this->command->line("  [B] ✓ {$batchId}");
    }

    // =========================================================================
    // [C] 1 batch · 2 JADWAL BERBEDA · 3+3 peserta semua K · SK belum dibuat
    // =========================================================================

    private function skenarioC(): void
    {
        $batchId = 'SK-UJIKOM-C-' . now()->format('YmdHis');

        // Jadwal pertama — 3 peserta
        $asesmens1 = $this->buatPeserta($batchId, 41, 'SKC1', 'SMK Gamma');
        $jadwal1   = $this->buatJadwal($asesmens1, now()->subDays(30), $batchId, 'approved');

        $ba1 = BeritaAcara::create([
            'schedule_id'       => $jadwal1->id,
            'tanggal_pelaksanaan' => $jadwal1->assessment_date,
            'dibuat_oleh'       => $this->asesor->user_id,
        ]);
        foreach ($asesmens1 as $a) {
            BeritaAcaraAsesi::create([
                'berita_acara_id' => $ba1->id,
                'asesmen_id'      => $a->id,
                'rekomendasi'     => 'K',
            ]);
        }

        // Jadwal kedua — 3 peserta berbeda, tapi batch sama
        $asesmens2 = $this->buatPeserta($batchId, 41, 'SKC2', 'SMK Gamma');
        $jadwal2   = $this->buatJadwal($asesmens2, now()->subDays(25), $batchId, 'approved');

        $ba2 = BeritaAcara::create([
            'schedule_id'       => $jadwal2->id,
            'tanggal_pelaksanaan' => $jadwal2->assessment_date,
            'dibuat_oleh'       => $this->asesor->user_id,
        ]);
        foreach ($asesmens2 as $a) {
            BeritaAcaraAsesi::create([
                'berita_acara_id' => $ba2->id,
                'asesmen_id'      => $a->id,
                'rekomendasi'     => 'K',
            ]);
        }

        // SK TIDAK dibuat — ini yang muncul di index sebagai "Belum Diajukan"

        $this->command->line("  [C] ✓ {$batchId} (2 jadwal, no SK yet)");
    }

    // =========================================================================
    // [D] 1 batch · 2 jadwal · 4K + 2BK · SK rejected
    // =========================================================================

    private function skenarioD(): void
    {
        $batchId = 'SK-UJIKOM-D-' . now()->format('YmdHis');

        // Jadwal 1 — 3 peserta: 2K + 1BK
        $asesmens1 = $this->buatPeserta($batchId, 3, 'SKD1', 'Akademi Delta');
        $jadwal1   = $this->buatJadwal($asesmens1, now()->subDays(40), $batchId, 'approved');

        $ba1 = BeritaAcara::create([
            'schedule_id'       => $jadwal1->id,
            'tanggal_pelaksanaan' => $jadwal1->assessment_date,
            'dibuat_oleh'       => $this->asesor->user_id,
        ]);
        foreach ($asesmens1 as $i => $a) {
            BeritaAcaraAsesi::create([
                'berita_acara_id' => $ba1->id,
                'asesmen_id'      => $a->id,
                'rekomendasi'     => $i < 2 ? 'K' : 'BK',
            ]);
        }

        // Jadwal 2 — 3 peserta: 2K + 1BK
        $asesmens2 = $this->buatPeserta($batchId, 3, 'SKD2', 'Akademi Delta');
        $jadwal2   = $this->buatJadwal($asesmens2, now()->subDays(35), $batchId, 'approved');

        $ba2 = BeritaAcara::create([
            'schedule_id'       => $jadwal2->id,
            'tanggal_pelaksanaan' => $jadwal2->assessment_date,
            'dibuat_oleh'       => $this->asesor->user_id,
        ]);
        foreach ($asesmens2 as $i => $a) {
            BeritaAcaraAsesi::create([
                'berita_acara_id' => $ba2->id,
                'asesmen_id'      => $a->id,
                'rekomendasi'     => $i < 2 ? 'K' : 'BK',
            ]);
        }

        // SK rejected
        SkHasilUjikom::create([
            'collective_batch_id' => $batchId,
            'nomor_sk'            => '003/LSP-KAP/SER.20.06/II/2026',
            'tanggal_pleno'       => now()->subDays(30),
            'tempat_dikeluarkan'  => 'Bandung',
            'status'              => 'rejected',
            'submitted_at'        => now()->subDays(28),
            'catatan_direktur'    => 'Nomor SK tidak sesuai format. Harap diperbaiki dan ajukan ulang.',
            'rejected_at'         => now()->subDays(27),
            'created_by'          => $this->manajer->id,
        ]);

        $this->command->line("  [D] ✓ {$batchId} (2 jadwal, SK rejected)");
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Buat n peserta dalam satu batch.
     * Return: array of Asesmen
     */
    private function buatPeserta(string $batchId, int $n, string $prefix, string $institusi): array
    {
        $asesmens = [];
        for ($i = 1; $i <= $n; $i++) {
            $slug  = strtolower("{$prefix}-{$i}");
            $email = "sk.ujikom.{$slug}@test.com";

            $user = User::firstOrCreate(['email' => $email], [
                'name'                => "Peserta {$prefix} {$i}",
                'password'            => Hash::make('password123'),
                'role'                => 'asesi',
                'is_active'           => true,
                'email_verified_at'   => now(),
                'password_changed_at' => now(),
            ]);

            $asesmen = Asesmen::create([
                'user_id'              => $user->id,
                'tuk_id'               => $this->tuk->id,
                'skema_id'             => $this->skema->id,
                'full_name'            => $user->name,
                'nik'                  => '3275' . str_pad($i + rand(1000000, 9999999), 10, '0', STR_PAD_LEFT),
                'birth_place'          => 'Bandung',
                'birth_date'           => '1998-0' . max(1, $i % 9) . '-01',
                'gender'               => $i % 2 === 0 ? 'P' : 'L',
                'address'              => 'Jl. Test No.' . $i . ', Bandung',
                'institution'          => $institusi,
                'education'            => 'S1',
                'is_collective'        => true,
                'collective_batch_id'  => $batchId,
                'status'               => 'assessed',
                'tuk_verified_at'      => now()->subDays(60),
                'admin_verified_at'    => now()->subDays(58),
                'registered_by'        => $this->admin->id,
                'registration_date'    => now()->subDays(65)->toDateString(),
            ]);

            $asesmens[] = $asesmen;
        }
        return $asesmens;
    }

    /**
     * Buat jadwal dan assign asesmens ke dalamnya.
     */
    private function buatJadwal(array $asesmens, $tanggal, string $batchId, string $approvalStatus): Schedule
    {
        $jadwal = Schedule::create([
            'tuk_id'          => $this->tuk->id,
            'skema_id'        => $this->skema->id,
            'assessment_date' => $tanggal,
            'start_time'      => '08:00:00',
            'end_time'        => '12:00:00',
            'location'        => 'Ruang Asesmen ' . $this->tuk->code,
            'location_type'   => 'offline',
            'created_by'      => $this->admin->id,
            'asesor_id'       => $this->asesor->id,
            'approval_status' => $approvalStatus,
            'approved_by'     => $approvalStatus === 'approved' ? $this->admin->id : null,
            'approved_at'     => $approvalStatus === 'approved' ? now()->subDays(3) : null,
            'assessment_start' => true,
        ]);

        foreach ($asesmens as $a) {
            $a->update(['schedule_id' => $jadwal->id]);
        }

        return $jadwal;
    }

    private function buatAsesor(): Asesor
    {
        $user = User::firstOrCreate(['email' => 'asesor.sk@test.com'], [
            'name'              => 'Drs. Asesor SK, M.Pd.',
            'password'          => Hash::make('password123'),
            'role'              => 'asesor',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        return Asesor::firstOrCreate(['user_id' => $user->id], [
            'nama'         => 'Drs. Asesor SK, M.Pd.',
            'nik'          => '3201' . rand(100000000, 999999999),
            'telepon'      => '0812-9999-0001',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '1975-05-10',
            'email'        => 'asesor.sk@test.com',
            'no_reg_met'   => 'MET-SK-001',
            'status_reg'   => 'aktif',
            'is_active'    => true,
        ]);
    }
}