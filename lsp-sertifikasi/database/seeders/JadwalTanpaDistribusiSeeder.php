<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Tuk;
use App\Models\Skema;
use App\Models\Asesor;
use App\Models\Asesmen;
use App\Models\Schedule;
use App\Models\SoalTeori;

class JadwalTanpaDistribusiSeeder extends Seeder
{
    public function run(): void
    {
        // ── Prasyarat ─────────────────────────────────────────────────────
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $this->command->error('Admin tidak ditemukan. Jalankan DatabaseSeeder dulu.');
            return;
        }

        $tuk = Tuk::first();
        if (!$tuk) {
            $this->command->error('TUK tidak ditemukan. Jalankan DatabaseSeeder dulu.');
            return;
        }

        // Ambil skema kedua kalau ada, biar beda sama JadwalDenganSoalSeeder
        $skema = Skema::where('is_active', true)->skip(1)->first()
               ?? Skema::where('is_active', true)->first();

        if (!$skema) {
            $this->command->error('Skema tidak ditemukan.');
            return;
        }

        $bankCount = SoalTeori::where('skema_id', $skema->id)->count();
        $this->command->info("✅ Skema: {$skema->name} ({$bankCount} soal di bank)");
        $this->command->info("✅ TUK: {$tuk->name}");

        // ── Asesor ────────────────────────────────────────────────────────
        $asesorUser = User::firstOrCreate(
            ['email' => 'asesor.dua@lsp.com'],
            [
                'name'              => 'Dra. Asesor Dua, M.Si.',
                'password'          => Hash::make('password123'),
                'role'              => 'asesor',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        $asesor = Asesor::firstOrCreate(
            ['user_id' => $asesorUser->id],
            [
                'nama'       => 'Dra. Asesor Dua, M.Si.',
                'nik'        => '3202' . rand(100000000, 999999999),
                'telepon'    => '0813-0000-0002',
                'email'      => 'asesor.dua@lsp.com',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '1985-05-10',
                'no_reg_met' => 'MET-002-TEST',
                'status_reg' => 'aktif',
                'is_active'  => true,
            ]
        );

        // ── 5 Asesi ───────────────────────────────────────────────────────
        $asesiData = [
            ['nama' => 'Dewi Rahayu',      'email' => 'asesi.dewi@test.com',    'nik' => '3202010101010001'],
            ['nama' => 'Hendra Wijaya',    'email' => 'asesi.hendra@test.com',  'nik' => '3202010101010002'],
            ['nama' => 'Melisa Putri',     'email' => 'asesi.melisa@test.com',  'nik' => '3202010101010003'],
            ['nama' => 'Reza Pratama',     'email' => 'asesi.reza@test.com',    'nik' => '3202010101010004'],
            ['nama' => 'Yunita Sari',      'email' => 'asesi.yunita@test.com',  'nik' => '3202010101010005'],
        ];

        $asesmens = [];
        foreach ($asesiData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['nama'],
                    'password'          => Hash::make('password123'),
                    'role'              => 'asesi',
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]
            );

            $asesmen = Asesmen::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'skema_id'          => $skema->id,
                    'tuk_id'            => $tuk->id,
                    'full_name'         => $data['nama'],
                    'nik'               => $data['nik'],
                    'birth_place'       => 'Jakarta',
                    'birth_date'        => '1997-03-20',
                    'gender'            => rand(0, 1) ? 'L' : 'P',
                    'address'           => 'Jl. Merdeka No. ' . rand(1, 50) . ', Jakarta',
                    'city_code'         => '3171',
                    'province_code'     => '31',
                    'phone'             => '0813' . rand(10000000, 99999999),
                    'education'         => 'D3',
                    'occupation'        => 'Sekretaris',
                    'budget_source'     => 'Perusahaan',
                    'institution'       => 'PT. Test Indonesia',
                    'registration_date' => now()->subDays(15),
                    'status'            => 'scheduled',
                    'fee_amount'        => $skema->fee ?? 600000,
                    'is_collective'     => false,
                    'training_flag'     => false,
                    'admin_started_at'  => now()->subDays(10),
                    'admin_started_by'  => $admin->id,
                ]
            );

            $asesmens[] = $asesmen;
        }

        $this->command->info('✅ ' . count($asesmens) . ' asesi siap.');

        // ── Schedule (approved, 5 hari ke depan, TANPA distribusi soal) ───
        $assessmentDate = now()->addDays(5)->toDateString();

        $schedule = Schedule::where('notes', 'LIKE', '%[TEST-NODIST]%')->first();

        if (!$schedule) {
            $schedule = Schedule::create([
                'tuk_id'          => $tuk->id,
                'skema_id'        => $skema->id,
                'assessment_date' => $assessmentDate,
                'start_time'      => '09:00:00',
                'end_time'        => '13:00:00',
                'location'        => 'Ruang Ujian B — Lantai 3',
                'location_type'   => 'offline',
                'notes'           => '[TEST-NODIST] Jadwal belum didistribusikan soal',
                'created_by'      => $admin->id,
                'asesor_id'       => $asesor->id,
                'approval_status' => 'approved',
                'approved_by'     => $admin->id,
                'approved_at'     => now()->subDays(3),
                'sk_number'       => 'SK-NODIST-' . date('Ymd'),
            ]);

            $this->command->info("✅ Jadwal dibuat: {$assessmentDate} (id: #{$schedule->id})");
        } else {
            $this->command->info("ℹ️  Jadwal sudah ada (id: #{$schedule->id}), dipakai ulang.");
        }

        // Assign asesi ke schedule
        foreach ($asesmens as $asesmen) {
            $asesmen->update([
                'schedule_id' => $schedule->id,
                'status'      => 'scheduled',
            ]);
        }

        $this->command->info('✅ Semua asesi di-assign ke jadwal.');
        $this->command->warn('⚠️  Soal BELUM didistribusikan — login sebagai manajer untuk mendistribusikan.');

        // ── Credentials ───────────────────────────────────────────────────
        $this->command->newLine();
        $this->command->line('════════════════════════════════════════════════');
        $this->command->info('  JADWAL BELUM DIDISTRIBUSIKAN — TEST MANAJER');
        $this->command->line('════════════════════════════════════════════════');
        $this->command->line('  Password semua akun: <fg=yellow>password123</>');
        $this->command->newLine();
        $this->command->line('  <fg=cyan>ASESOR:</> ' . $asesorUser->email);
        $this->command->newLine();
        $this->command->line('  <fg=cyan>ASESI:</>');
        foreach ($asesiData as $d) {
            $this->command->line("    {$d['email']}");
        }
        $this->command->newLine();
        $this->command->line("  <fg=cyan>JADWAL:</>");
        $this->command->line("    ID      : #{$schedule->id}");
        $this->command->line("    Tanggal : {$assessmentDate}");
        $this->command->line("    Skema   : {$skema->name}");
        $this->command->line("    Bank    : {$bankCount} soal tersedia");
        $this->command->line("    Soal    : ❌ Belum didistribusikan");
        $this->command->newLine();
        $this->command->line('  <fg=green>Login manajer → Bank Soal / Distribusi ke Jadwal → pilih jadwal ini.</>');
        $this->command->line('════════════════════════════════════════════════');
    }
}