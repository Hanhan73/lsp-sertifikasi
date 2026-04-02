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
use App\Models\SoalTeori;
use App\Models\SoalTeoriAsesi;
use App\Models\DistribusiSoalTeori;

class JadwalDenganSoalSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Pastikan prasyarat ada ─────────────────────────────────────
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $this->command->error('Admin user tidak ditemukan. Jalankan DatabaseSeeder dulu.');
            return;
        }

        $tuk = Tuk::first();
        if (!$tuk) {
            $this->command->error('TUK tidak ditemukan. Jalankan DatabaseSeeder dulu.');
            return;
        }

        $skema = Skema::where('is_active', true)->first();
        if (!$skema) {
            $this->command->error('Skema aktif tidak ditemukan. Jalankan DatabaseSeeder dulu.');
            return;
        }

        $bankSoalCount = SoalTeori::where('skema_id', $skema->id)->count();
        if ($bankSoalCount < 30) {
            $this->command->error("Bank soal hanya punya {$bankSoalCount} soal untuk skema [{$skema->code}]. Jalankan SoalTeoriSeeder dulu.");
            return;
        }

        $this->command->info("✅ Menggunakan TUK: {$tuk->name}");
        $this->command->info("✅ Menggunakan Skema: {$skema->name} ({$bankSoalCount} soal di bank)");

        // ── 2. Buat / ambil user Asesor ───────────────────────────────────
        $asesorUser = User::firstOrCreate(
            ['email' => 'asesor.test@lsp.com'],
            [
                'name'              => 'Dr. Asesor Test, M.Pd.',
                'password'          => Hash::make('password123'),
                'role'              => 'asesor',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        $asesor = Asesor::firstOrCreate(
            ['user_id' => $asesorUser->id],
            [
                'nama'        => 'Dr. Asesor Test, M.Pd.',
                'nik'         => '3201' . rand(100000000, 999999999),
                'telepon'     => '0812-0000-0001',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '1980-01-01',
                'email'       => 'asesor.test@lsp.com',
                'no_reg_met'  => 'MET-001-TEST',
                'status_reg'  => 'aktif',
                'is_active'   => true,
            ]
        );

        // ── 3. Buat 4 asesi dengan akun login ─────────────────────────────
        $asesiData = [
            ['nama' => 'Rina Wulandari',    'email' => 'asesi.rina@test.com',    'nik' => '3201010101010001'],
            ['nama' => 'Budi Kurniawan',    'email' => 'asesi.budi@test.com',    'nik' => '3201010101010002'],
            ['nama' => 'Sari Dewi Lestari', 'email' => 'asesi.sari@test.com',    'nik' => '3201010101010003'],
            ['nama' => 'Ahmad Fauzi',       'email' => 'asesi.ahmad@test.com',   'nik' => '3201010101010004'],
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
                    'birth_place'       => 'Bandung',
                    'birth_date'        => '1998-06-15',
                    'gender'            => rand(0, 1) ? 'L' : 'P',
                    'address'           => 'Jl. Test No. ' . rand(1, 99) . ', Bandung',
                    'city_code'         => '3273',
                    'province_code'     => '32',
                    'phone'             => '0812' . rand(10000000, 99999999),
                    'education'         => 'S1',
                    'occupation'        => 'Staf Administrasi',
                    'budget_source'     => 'Mandiri',
                    'institution'       => 'Universitas Test',
                    'registration_date' => now()->subDays(30),
                    'status'            => 'scheduled',
                    'fee_amount'        => $skema->fee ?? 600000,
                    'is_collective'     => false,
                    'training_flag'     => false,
                    'admin_started_at'  => now()->subDays(20),
                    'admin_started_by'  => $admin->id,
                ]
            );

            $asesmens[] = $asesmen;
        }

        $this->command->info('✅ ' . count($asesmens) . ' asesi siap.');

        // ── 4. Buat Schedule (approved, hari ini + 2 hari) ────────────────
        $assessmentDate = now()->addDays(2)->toDateString();

        // Cek apakah jadwal test sudah ada
        $schedule = Schedule::where('tuk_id', $tuk->id)
            ->where('skema_id', $skema->id)
            ->where('notes', 'LIKE', '%[TEST-SOAL]%')
            ->first();

        if (!$schedule) {
            $schedule = Schedule::create([
                'tuk_id'          => $tuk->id,
                'skema_id'        => $skema->id,
                'assessment_date' => $assessmentDate,
                'start_time'      => '08:00:00',
                'end_time'        => '12:00:00',
                'location'        => 'Ruang Ujian A — Lantai 2',
                'location_type'   => 'offline',
                'notes'           => '[TEST-SOAL] Jadwal testing fitur ujian soal',
                'created_by'      => $admin->id,
                'asesor_id'       => $asesor->id,
                'approval_status' => 'approved',
                'approved_by'     => $admin->id,
                'approved_at'     => now()->subDays(5),
                'sk_number'       => 'SK-TEST-' . date('Ymd'),
            ]);

            $this->command->info("✅ Jadwal dibuat: {$assessmentDate} ({$schedule->id})");
        } else {
            $this->command->info("ℹ️  Jadwal sudah ada (id: {$schedule->id}), dipakai ulang.");
        }

        // ── 5. Assign asesi ke schedule ───────────────────────────────────
        foreach ($asesmens as $asesmen) {
            $asesmen->update([
                'schedule_id' => $schedule->id,
                'status'      => 'scheduled',
            ]);
        }

        $this->command->info('✅ Semua asesi di-assign ke jadwal.');


        // ── 7. Print credentials ──────────────────────────────────────────
        $this->command->newLine();
        $this->command->line('════════════════════════════════════════════════');
        $this->command->info('  KREDENSIAL LOGIN — JADWAL TEST SOAL');
        $this->command->line('════════════════════════════════════════════════');
        $this->command->line('  Password semua akun: <fg=yellow>password123</>');
        $this->command->newLine();
        $this->command->line('  <fg=cyan>ASESOR:</>');
        $this->command->line("    {$asesorUser->email}");
        $this->command->newLine();
        $this->command->line('  <fg=cyan>ASESI (bisa langsung ujian teori):</>');
        foreach ($asesiData as $d) {
            $this->command->line("    {$d['email']}");
        }
        $this->command->newLine();
        $this->command->line("  <fg=cyan>JADWAL:</>");
        $this->command->line("    ID       : #{$schedule->id}");
        $this->command->line("    Tanggal  : {$assessmentDate}");
        $this->command->line("    Skema    : {$skema->name}");
        $this->command->line("    TUK      : {$tuk->name}");
        $this->command->line("    Status   : approved ✅");
        $this->command->line("    Soal     : 30 soal/asesi, 60 menit");
        $this->command->line('════════════════════════════════════════════════');
    }
}