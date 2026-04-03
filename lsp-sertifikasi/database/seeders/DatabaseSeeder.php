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
use App\Models\AplSatu;
use App\Models\AplSatuBukti;
use App\Models\AplDua;
use App\Models\AplDuaJawaban;
use App\Models\FrAk01;
use App\Models\Schedule;
use App\Models\Payment;
use App\Models\SoalTeori;
use App\Models\DistribusiSoalTeori;
use App\Models\SoalTeoriAsesi;

class DatabaseSeeder extends Seeder
{
    // =========================================================================
    // MASTER DATA
    // =========================================================================

    public function run(): void
    {
        $this->command->info('🌱 Seeding database...');

        DB::transaction(function () {

            // ── Roles utama ───────────────────────────────────────────────
            $admin    = $this->buatAdmin();
            $direktur = $this->buatDirektur();
            $manajer  = $this->buatManajer();
            $this->command->info('✅ Admin, Direktur, Manajer Sertifikasi dibuat.');

            // ── TUK ───────────────────────────────────────────────────────
            $tuks = $this->buatTuks();
            $this->command->info('✅ ' . count($tuks) . ' TUK dibuat.');

            // ── Skema ─────────────────────────────────────────────────────
            $skemas = $this->buatSkemas();
            $this->command->info('✅ ' . count($skemas) . ' Skema dibuat.');

            // ── Asesor ────────────────────────────────────────────────────
            $asesor = $this->buatAsesor();
            $this->command->info('✅ Asesor dibuat.');

            // ── Bank soal ─────────────────────────────────────────────────
            $this->buatSoalTeori($skemas, $admin);
            $this->command->info('✅ Bank soal teori dibuat.');

            // =================================================================
            // SKENARIO KOLEKTIF
            // =================================================================

            // [1] Baru daftar — menunggu verifikasi TUK
            $this->buatBatchKolektif([
                'label'         => 'WAITING-TUK',
                'tuk'           => $tuks[0],
                'skema'         => $skemas[0],
                'jumlah'        => 4,
                'status'=> 'data_completed',
                'tuk_verified'  => false,
                'admin_verified'=> false,
                'payment_phases'=> 'single',
            ]);

            // [2] TUK verified — menunggu penetapan biaya admin
            $this->buatBatchKolektif([
                'label'         => 'WAITING-ADMIN',
                'tuk'           => $tuks[0],
                'skema'         => $skemas[1],
                'jumlah'        => 4,
                'status'=> 'data_completed',
                'tuk_verified'  => true,
                'admin_verified'=> false,
                'payment_phases'=> 'single',
            ]);

            // [3] Admin set fee — menunggu pembayaran
            $this->buatBatchKolektif([
                'label'         => 'WAITING-PAYMENT',
                'tuk'           => $tuks[1],
                'skema'         => $skemas[0],
                'jumlah'        => 3,
                'status'=> 'verified',
                'tuk_verified'  => true,
                'admin_verified'=> true,
                'payment_phases'=> 'single',
                'fee'           => 600000,
            ]);

            // [4] Sudah bayar — pra asesmen (APL-01/02/FRAK sedang diisi)
            $this->buatBatchKolektif([
                'label'         => 'PRA-ASESMEN-STARTED',
                'tuk'           => $tuks[1],
                'skema'         => $skemas[2],
                'jumlah'        => 5,
                'status'=> 'pra_asesmen_started',
                'tuk_verified'  => true,
                'admin_verified'=> true,
                'payment_phases'=> 'single',
                'fee'           => 600000,
                'paid'          => true,
                'apl01_status'  => 'submitted',   // APL-01 submitted, belum diverifikasi admin
                'apl02_status'  => 'draft',
            ]);

            // [5] APL-01 submitted menunggu verifikasi admin
            $this->buatBatchKolektif([
                'label'         => 'APL01-NEED-VERIFY',
                'tuk'           => $tuks[2],
                'skema'         => $skemas[0],
                'jumlah'        => 4,
                'status'=> 'pra_asesmen_started',
                'tuk_verified'  => true,
                'admin_verified'=> true,
                'payment_phases'=> 'single',
                'fee'           => 500000,
                'paid'          => true,
                'apl01_status'  => 'submitted',
                'apl02_status'  => 'submitted',
                'frak01_status' => 'submitted',
            ]);

            // [6] Siap dijadwalkan (APL-01 verified, APL-02 submitted, FR.AK.01 submitted)
            $this->buatBatchKolektif([
                'label'         => 'SIAP-JADWAL',
                'tuk'           => $tuks[0],
                'skema'         => $skemas[1],
                'jumlah'        => 5,
                'status'=> 'pra_asesmen_started',
                'tuk_verified'  => true,
                'admin_verified'=> true,
                'payment_phases'=> 'single',
                'fee'           => 750000,
                'paid'          => true,
                'apl01_status'  => 'verified',
                'apl02_status'  => 'submitted',
                'frak01_status' => 'submitted',
            ]);

            // [7] Jadwal dibuat — menunggu approval Direktur
            $batch7 = $this->buatBatchKolektif([
                'label'         => 'PENDING-APPROVAL',
                'tuk'           => $tuks[1],
                'skema'         => $skemas[2],
                'jumlah'        => 4,
                'status'=> 'pra_asesmen_started',
                'tuk_verified'  => true,
                'admin_verified'=> true,
                'payment_phases'=> 'single',
                'fee'           => 600000,
                'paid'          => true,
                'apl01_status'  => 'verified',
                'apl02_status'  => 'submitted',
                'frak01_status' => 'submitted',
            ]);
            // Buat jadwal pending approval
            $jadwal7 = $this->buatJadwal([
                'tuk'             => $tuks[1],
                'skema'           => $skemas[2],
                'admin'           => $admin,
                'asesor'          => $asesor,
                'approval_status' => 'pending_approval',
                'hari_ke_depan'   => 10,
                'notes'           => '[PENDING-APPROVAL] Menunggu persetujuan Direktur',
            ]);
            $this->assignBatchKeJadwal($batch7, $jadwal7, 'scheduled');

            // [8] Terjadwal dan disetujui — ujian hari ini/mendatang, soal sudah didistribusi
            $batch8 = $this->buatBatchKolektif([
                'label'         => 'SCHEDULED-WITH-SOAL',
                'tuk'           => $tuks[0],
                'skema'         => $skemas[0],
                'jumlah'        => 4,
                'status'=> 'scheduled',
                'tuk_verified'  => true,
                'admin_verified'=> true,
                'payment_phases'=> 'single',
                'fee'           => 500000,
                'paid'          => true,
                'apl01_status'  => 'verified',
                'apl02_status'  => 'verified',
                'frak01_status' => 'verified',
            ]);
            $jadwal8 = $this->buatJadwal([
                'tuk'             => $tuks[0],
                'skema'           => $skemas[0],
                'admin'           => $admin,
                'asesor'          => $asesor,
                'approval_status' => 'approved',
                'hari_ke_depan'   => 2,
                'notes'           => '[SCHEDULED] Jadwal mendatang dengan soal terdistribusi',
                'sk_number'       => 'SK-TEST-001/' . date('Y'),
            ]);
            $this->assignBatchKeJadwal($batch8, $jadwal8, 'scheduled');
            $this->distribusiSoalTeori($jadwal8, $skemas[0], $admin);

            $this->command->info('✅ Semua skenario kolektif dibuat.');

            // =================================================================
            // PRINT RINGKASAN
            // =================================================================
            $this->printRingkasan($tuks, $skemas, $asesor);
        });
    }

    // =========================================================================
    // BUAT AKTOR UTAMA
    // =========================================================================

    private function buatAdmin(): User
    {
        return User::firstOrCreate(['email' => 'admin@lsp.com'], [
            'name'              => 'Admin LSP',
            'password'          => Hash::make('password123'),
            'role'              => 'admin',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);
    }

    private function buatDirektur(): User
    {
        return User::firstOrCreate(['email' => 'direktur@lsp-kap.com'], [
            'name'              => 'Drs. HM. Jamil Latief, MM., M.Pd.',
            'password'          => Hash::make('direktur123!'),
            'role'              => 'direktur',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);
    }

    private function buatManajer(): User
    {
        return User::firstOrCreate(['email' => 'manajer_sertifikasi@lsp-kap.com'], [
            'name'              => 'Manajer Sertifikasi',
            'password'          => Hash::make('manajer123!'),
            'role'              => 'manajer_sertifikasi',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);
    }

    private function buatAsesor(): Asesor
    {
        $user = User::firstOrCreate(['email' => 'asesor.test@lsp.com'], [
            'name'              => 'Dr. Asesor Test, M.Pd.',
            'password'          => Hash::make('password123'),
            'role'              => 'asesor',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        return Asesor::firstOrCreate(['user_id' => $user->id], [
            'nama'         => 'Dr. Asesor Test, M.Pd.',
            'nik'          => '3201010101850001',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir'=> '1985-01-01',
            'jenis_kelamin'=> 'L',
            'telepon'      => '0812-0000-0001',
            'email'        => 'asesor.test@lsp.com',
            'no_reg_met'   => 'MET.000.00.0001.2020',
            'status_reg'   => 'aktif',
            'is_active'    => true,
        ]);
    }

    // =========================================================================
    // TUK & SKEMA
    // =========================================================================

    private function buatTuks(): array
    {
        $data = [
            ['code'=>'TUK-001','name'=>'LSP-KAP Universitas Pendidikan Indonesia','address'=>'Jl. Dr. Setiabudhi No 229 Bandung','email'=>'lspkap@upi.edu','phone'=>'022-2013163','manager_name'=>'Drs. Hendri Winata, M.Si.'],
            ['code'=>'TUK-002','name'=>'TUK LSP-KAP Universitas Negeri Jakarta','address'=>'Jl. Rawa Mangun Muka Jakarta Timur','email'=>'lspkap@unj.ac.id','phone'=>'021-4706287','manager_name'=>'Darma Rika Swaramarinda, S.Pd., M.SE.'],
            ['code'=>'TUK-003','name'=>'TUK LSP-KAP UHAMKA Jakarta','address'=>'Jl. Tanah Merdeka Jakarta','email'=>'lspkap@uhamka.ac.id','phone'=>'021-8400941','manager_name'=>'Dr. Hj. Sri Giyanti, MM.'],
        ];

        return collect($data)->map(function ($d, $i) {
            $user = User::firstOrCreate(['email' => 'tuk' . ($i + 1) . '@lsp.com'], [
                'name'              => $d['name'],
                'password'          => Hash::make('password123'),
                'role'              => 'tuk',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]);
            return Tuk::firstOrCreate(['code' => $d['code']], array_merge($d, ['user_id' => $user->id, 'is_active' => true]));
        })->all();
    }

    private function buatSkemas(): array
    {
        $data = [
            ['code'=>'SKM-001','name'=>'Staf Administrasi','description'=>'Sertifikasi kompetensi untuk staf administrasi','fee'=>500000],
            ['code'=>'SKM-002','name'=>'Pengelolaan Administrasi Perkantoran','description'=>'Sertifikasi untuk pengelola administrasi perkantoran','fee'=>750000],
            ['code'=>'SKM-003','name'=>'Resepsionis/Front Office','description'=>'Sertifikasi untuk resepsionis','fee'=>600000],
        ];

        return collect($data)->map(fn($d) =>
            Skema::firstOrCreate(['code' => $d['code']], array_merge($d, ['is_active' => true]))
        )->all();
    }

    // =========================================================================
    // BANK SOAL TEORI (90 soal per skema)
    // =========================================================================

    private function buatSoalTeori(array $skemas, User $admin): void
    {
        $soalList = $this->getSoalList();

        foreach ($skemas as $skema) {
            $existing = SoalTeori::where('skema_id', $skema->id)->count();
            if ($existing >= 30) continue;

            foreach ($soalList as $soal) {
                SoalTeori::firstOrCreate(
                    ['skema_id' => $skema->id, 'pertanyaan' => $soal['pertanyaan']],
                    [
                        'pilihan_a'     => $soal['a'],
                        'pilihan_b'     => $soal['b'],
                        'pilihan_c'     => $soal['c'],
                        'pilihan_d'     => $soal['d'],
                        'pilihan_e'     => $soal['e'] ?? null,
                        'jawaban_benar' => $soal['jawaban'],
                        'dibuat_oleh'   => $admin->id,
                    ]
                );
            }
        }
    }

    private function distribusiSoalTeori(Schedule $jadwal, Skema $skema, User $admin): void
    {
        $dist = DistribusiSoalTeori::firstOrCreate(
            ['schedule_id' => $jadwal->id],
            ['jumlah_soal' => 30, 'didistribusikan_oleh' => $admin->id]
        );

        $bankSoal = SoalTeori::where('skema_id', $skema->id)->inRandomOrder()->take(30)->get();
        if ($bankSoal->isEmpty()) return;

        foreach ($jadwal->asesmens as $asesmen) {
            if (SoalTeoriAsesi::where('distribusi_soal_teori_id', $dist->id)
                ->where('asesmen_id', $asesmen->id)->exists()) continue;

            $acak = $bankSoal->shuffle()->values();
            foreach ($acak as $urutan => $soal) {
                SoalTeoriAsesi::create([
                    'distribusi_soal_teori_id' => $dist->id,
                    'asesmen_id'               => $asesmen->id,
                    'soal_teori_id'            => $soal->id,
                    'urutan'                   => $urutan + 1,
                    'jawaban'                  => null,
                ]);
            }
        }
    }

    // =========================================================================
    // BATCH KOLEKTIF — satu method untuk semua skenario
    // =========================================================================

    private function buatBatchKolektif(array $cfg): array
    {
        $tuk    = $cfg['tuk'];
        $skema  = $cfg['skema'];
        $label  = $cfg['label'];
        $jumlah = $cfg['jumlah'];

        $admin = User::where('role', 'admin')->first();
        $batchId = 'BATCH-' . $label . '-' . now()->translatedFormat('YmdHis');

        $asesmens = [];

        for ($i = 1; $i <= $jumlah; $i++) {
            $email = strtolower("kolektif.{$label}.{$i}@test.com");
            $user  = User::firstOrCreate(['email' => $email], [
                'name'                => "Peserta {$label} {$i}",
                'password'            => Hash::make('password123'),
                'role'                => 'asesi',
                'is_active'           => true,
                'email_verified_at'   => now(),
                'password_changed_at' => now(),
            ]);

            $fee = $cfg['fee'] ?? null;

            $data = [
                'user_id'              => $user->id,
                'tuk_id'               => $tuk->id,
                'skema_id'             => $skema->id,
                'full_name'            => $user->name,
                'nik'                  => '3275' . str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
                'birth_place'          => 'Bandung',
                'birth_date'           => '1998-0' . $i . '-01',
                'gender'               => $i % 2 === 0 ? 'P' : 'L',
                'address'              => 'Jl. Test No. ' . $i . ', Bandung',
                'city_code'            => '3273',
                'province_code'        => '32',
                'phone'                => '0812' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'email'                => $email,
                'education'            => 'S1',
                'occupation'           => 'Staf Administrasi',
                'budget_source'        => 'Institusi',
                'institution'          => 'Universitas Potensi Utama',
                'registration_date'    => now()->subDays(14),
                'status'               => $cfg['status'],
                'is_collective'        => true,
                'collective_batch_id'  => $batchId,
                'payment_phases'       => $cfg['payment_phases'],
                'collective_paid_by_tuk' => true,
                'skip_payment'         => true,
                'registered_by'        => $tuk->user_id,
            ];

            if ($cfg['tuk_verified']) {
                $data['tuk_verified_by']       = $tuk->user_id;
                $data['tuk_verified_at']        = now()->subDays(10);
                $data['tuk_verification_notes'] = 'Data lengkap dan valid';
            }

            if ($cfg['admin_verified'] && $fee) {
                $data['fee_amount']       = $fee;
                $data['admin_verified_by'] = $admin->id;
                $data['admin_verified_at'] = now()->subDays(8);
                $data['admin_started_by']  = $admin->id;
                $data['admin_started_at']  = now()->subDays(8);
            }

            $asesmen = Asesmen::firstOrCreate(['user_id' => $user->id], $data);
            $asesmens[] = $asesmen;

            // Payment
            if (($cfg['paid'] ?? false) && $fee) {
                Payment::firstOrCreate(['asesmen_id' => $asesmen->id], [
                    'amount'         => $fee,
                    'method'         => 'cash',
                    'status'         => 'verified',
                    'payment_phase'  => 'full',
                    'order_id'       => 'ORDER-SEED-' . $asesmen->id,
                    'transaction_id' => 'TRX-SEED-' . $asesmen->id,
                    'payment_type'   => 'bank_transfer',
                    'verified_at'    => now()->subDays(6),
                    'notes'          => 'Seeder',
                ]);
            }

            // APL-01
            $apl01Status = $cfg['apl01_status'] ?? null;
            if ($apl01Status) {
                $apl01 = AplSatu::firstOrCreate(['asesmen_id' => $asesmen->id], [
                    'nama_lengkap'     => $asesmen->full_name,
                    'nik'              => $asesmen->nik,
                    'tempat_lahir'     => $asesmen->birth_place,
                    'tanggal_lahir'    => $asesmen->birth_date,
                    'jenis_kelamin'    => $asesmen->gender === 'L' ? 'Laki-laki' : 'Perempuan',
                    'alamat_rumah'     => $asesmen->address,
                    'hp'               => $asesmen->phone,
                    'email'            => $asesmen->email,
                    'kualifikasi_pendidikan' => 'S1',
                    'nama_institusi'   => $asesmen->institution,
                    'tujuan_asesmen'   => 'Sertifikasi',
                    'status'           => $apl01Status,
                    'submitted_at'     => now()->subDays(7),
                    'verified_at'      => in_array($apl01Status, ['verified', 'approved']) ? now()->subDays(5) : null,
                    'verified_by'      => in_array($apl01Status, ['verified', 'approved']) ? $admin->id : null,
                    'nama_ttd_pemohon' => $asesmen->full_name,
                    'tanggal_ttd_pemohon' => now()->subDays(7)->toDateString(),
                ]);

                // Bukti kelengkapan
                $buktis = ['Ijazah Terakhir', 'KTP', 'Foto 3x4', 'CV'];
                foreach ($buktis as $b) {
                    AplSatuBukti::firstOrCreate(
                        ['apl_01_id' => $apl01->id, 'nama_dokumen' => $b],
                        [
                            'status'      => 'Ada Memenuhi Syarat',
                            'verified_by' => $admin->id,
                            'verified_at' => now()->subDays(5),
                        ]
                    );
                }
            }

            // APL-02
            $apl02Status = $cfg['apl02_status'] ?? null;
            if ($apl02Status) {
                $apl02 = AplDua::firstOrCreate(['asesmen_id' => $asesmen->id], [
                    'status'           => $apl02Status,
                    'submitted_at'     => $apl02Status !== 'draft' ? now()->subDays(6) : null,
                    'verified_at'      => in_array($apl02Status, ['verified', 'approved']) ? now()->subDays(4) : null,
                    'verified_by'      => in_array($apl02Status, ['verified', 'approved']) ? $admin->id : null,
                    'nama_ttd_asesi'   => $asesmen->full_name,
                    'tanggal_ttd_asesi'=> $apl02Status !== 'draft' ? now()->subDays(6) : null,
                ]);

                // Isi jawaban semua elemen
                $skema->loadMissing('unitKompetensis.elemens');
                foreach ($skema->unitKompetensis as $unit) {
                    foreach ($unit->elemens as $elemen) {
                        AplDuaJawaban::firstOrCreate(
                            ['apl_02_id' => $apl02->id, 'elemen_id' => $elemen->id],
                            ['jawaban' => 'K', 'bukti' => 'Pengalaman kerja 2 tahun di bidang administrasi.']
                        );
                    }
                }
            }

            // FR.AK.01
            $frak01Status = $cfg['frak01_status'] ?? null;
            if ($frak01Status) {
                FrAk01::firstOrCreate(['asesmen_id' => $asesmen->id], [
                    'skema_judul'                   => $skema->name,
                    'skema_nomor'                   => $skema->code,
                    'tuk_nama'                      => $tuk->name,
                    'nama_asesi'                    => $asesmen->full_name,
                    'bukti_verifikasi_portofolio'   => true,
                    'bukti_observasi_langsung'      => true,
                    'bukti_pertanyaan_tertulis'     => true,
                    'status'                        => $frak01Status,
                    'submitted_at'                  => $frak01Status !== 'draft' ? now()->subDays(5) : null,
                    'verified_at'                   => in_array($frak01Status, ['verified', 'approved']) ? now()->subDays(3) : null,
                    'verified_by'                   => in_array($frak01Status, ['verified', 'approved']) ? $admin->id : null,
                    'nama_ttd_asesi'                => $asesmen->full_name,
                    'tanggal_ttd_asesi'             => $frak01Status !== 'draft' ? now()->subDays(5) : null,
                ]);
            }
        }

        $this->command->line("  [✓] Batch {$label}: {$jumlah} peserta");

        return ['batch_id' => $batchId, 'asesmens' => $asesmens];
    }

    // =========================================================================
    // JADWAL
    // =========================================================================

    private function buatJadwal(array $cfg): Schedule
    {
        return Schedule::create([
            'tuk_id'          => $cfg['tuk']->id,
            'skema_id'        => $cfg['skema']->id,
            'assessment_date' => now()->addDays($cfg['hari_ke_depan'])->toDateString(),
            'start_time'      => '08:00:00',
            'end_time'        => '12:00:00',
            'location'        => 'Ruang Asesmen ' . $cfg['tuk']->code,
            'location_type'   => 'offline',
            'notes'           => $cfg['notes'] ?? '',
            'created_by'      => $cfg['admin']->id,
            'asesor_id'       => $cfg['asesor']->id,
            'approval_status' => $cfg['approval_status'],
            'approved_by'     => $cfg['approval_status'] === 'approved' ? $cfg['admin']->id : null,
            'approved_at'     => $cfg['approval_status'] === 'approved' ? now()->subDays(3) : null,
            'sk_number'       => $cfg['sk_number'] ?? null,
        ]);
    }

    private function assignBatchKeJadwal(array $batch, Schedule $jadwal, string $statusAsesmen): void
    {
        foreach ($batch['asesmens'] as $asesmen) {
            $asesmen->update([
                'schedule_id' => $jadwal->id,
                'status'      => $statusAsesmen,
            ]);
        }
    }

    // =========================================================================
    // PRINT RINGKASAN
    // =========================================================================

    private function printRingkasan(array $tuks, array $skemas, Asesor $asesor): void
    {
        $this->command->newLine();
        $this->command->line('══════════════════════════════════════════════════════════');
        $this->command->info('  ✅ DATABASE SEEDED — RINGKASAN KREDENSIAL & SKENARIO');
        $this->command->line('══════════════════════════════════════════════════════════');
        $this->command->line('  Password default: <fg=yellow>password123</>');
        $this->command->newLine();

        $this->command->line('  <fg=cyan>ROLE UTAMA:</>');
        $this->command->line('  ┌─────────────────────┬──────────────────────────────────┬──────────────────┐');
        $this->command->line('  │ Role                │ Email                            │ Password         │');
        $this->command->line('  ├─────────────────────┼──────────────────────────────────┼──────────────────┤');
        $this->command->line('  │ Admin               │ admin@lsp.com                    │ password123      │');
        $this->command->line('  │ Direktur            │ direktur@lsp-kap.com             │ direktur123!     │');
        $this->command->line('  │ Manajer Sertifikasi │ manajer_sertifikasi@lsp-kap.com  │ manajer123!      │');
        $this->command->line('  │ Asesor              │ asesor.test@lsp.com              │ password123      │');
        $this->command->line('  │ TUK 1 (UPI)         │ tuk1@lsp.com                     │ password123      │');
        $this->command->line('  │ TUK 2 (UNJ)         │ tuk2@lsp.com                     │ password123      │');
        $this->command->line('  │ TUK 3 (UHAMKA)      │ tuk3@lsp.com                     │ password123      │');
        $this->command->line('  └─────────────────────┴──────────────────────────────────┴──────────────────┘');
        $this->command->newLine();

        $this->command->line('  <fg=cyan>SKENARIO TESTING:</>');
        $this->command->line('  ┌────┬──────────────────────────────────────┬──────────────────────────────────────────────┐');
        $this->command->line('  │ #  │ Batch                                │ Kondisi                                      │');
        $this->command->line('  ├────┼──────────────────────────────────────┼──────────────────────────────────────────────┤');
        $this->command->line('  │ 1  │ WAITING-TUK (4 asesi)                │ Menunggu verifikasi TUK                      │');
        $this->command->line('  │ 2  │ WAITING-ADMIN (4 asesi)              │ TUK verified, menunggu penetapan biaya admin │');
        $this->command->line('  │ 3  │ WAITING-PAYMENT (3 asesi)            │ Fee ditetapkan, belum bayar                  │');
        $this->command->line('  │ 4  │ PRA-ASESMEN-STARTED (5 asesi)        │ Sudah bayar, APL sedang diisi                │');
        $this->command->line('  │ 5  │ APL01-NEED-VERIFY (4 asesi)          │ APL-01 submitted, menunggu verifikasi admin  │');
        $this->command->line('  │ 6  │ SIAP-JADWAL (5 asesi)                │ APL-01 verified + APL-02 + FR.AK.01 submitted│');
        $this->command->line('  │ 7  │ PENDING-APPROVAL (4 asesi)           │ Jadwal dibuat, menunggu approval Direktur    │');
        $this->command->line('  │ 8  │ SCHEDULED-WITH-SOAL (4 asesi)        │ Jadwal approved, soal terdistribusi          │');
        $this->command->line('  └────┴──────────────────────────────────────┴──────────────────────────────────────────────┘');
        $this->command->newLine();

        $this->command->line('  <fg=cyan>LOGIN ASESI PER SKENARIO (email pattern):</>');
        $this->command->line('  kolektif.WAITING-TUK.1@test.com s/d .4       → password123');
        $this->command->line('  kolektif.WAITING-ADMIN.1@test.com s/d .4     → password123');
        $this->command->line('  kolektif.WAITING-PAYMENT.1@test.com s/d .3   → password123');
        $this->command->line('  kolektif.PRA-ASESMEN-STARTED.1@test.com s/d .5 → password123');
        $this->command->line('  kolektif.APL01-NEED-VERIFY.1@test.com s/d .4 → password123');
        $this->command->line('  kolektif.SIAP-JADWAL.1@test.com s/d .5       → password123');
        $this->command->line('  kolektif.PENDING-APPROVAL.1@test.com s/d .4  → password123');
        $this->command->line('  kolektif.SCHEDULED-WITH-SOAL.1@test.com s/d .4 → password123');
        $this->command->newLine();
        $this->command->line('══════════════════════════════════════════════════════════');
    }

    // =========================================================================
    // BANK SOAL (30 soal cukup untuk distribusi)
    // =========================================================================

    private function getSoalList(): array
    {
        return [
            ['pertanyaan'=>'Surat resmi yang dibuat oleh instansi untuk keperluan dinas disebut...','a'=>'Surat pribadi','b'=>'Surat dinas','c'=>'Surat niaga','d'=>'Surat edaran', 'e'=>'Surat permintaan', 'jawaban'=>'b'],
            ['pertanyaan'=>'Bagian surat yang berisi inti pokok permasalahan disebut...','a'=>'Kepala surat','b'=>'Pembuka surat','c'=>'Isi surat','d'=>'Penutup surat','e'=>'Tanda tangan','jawaban'=>'c'],
            ['pertanyaan'=>'Singkatan "u.p." dalam surat bisnis berarti...','a'=>'Untuk perhatian','b'=>'Untuk pengecekan','c'=>'Untuk pertimbangan','d'=>'Atas perhatian', 'e'=>'Untuk informasi', 'jawaban'=>'a'],
            ['pertanyaan'=>'Sistem kearsipan berdasarkan abjad nama disebut sistem...','a'=>'Numerik','b'=>'Alfabetis','c'=>'Geografis','d'=>'Kronologis', 'e'=>'Topik', 'jawaban'=>'b'],
            ['pertanyaan'=>'Arsip yang masih sering digunakan dalam kegiatan sehari-hari disebut arsip...','a'=>'Inaktif','b'=>'Statis','c'=>'Dinamis','d'=>'Aktif', 'e'=>'Arsip digital', 'jawaban'=>'d'],
            ['pertanyaan'=>'Jadwal Retensi Arsip (JRA) digunakan untuk menentukan...','a'=>'Cara pengindeksan','b'=>'Jangka waktu penyimpanan dan nasib akhir arsip','c'=>'Format penomoran surat','d'=>'Metode digitalisasi', 'e'=>'Prosedur penghapusan', 'jawaban'=>'b'],
            ['pertanyaan'=>'Prioritas tugas dalam matriks Eisenhower yang paling utama adalah...','a'=>'Penting dan mendesak','b'=>'Tidak penting dan mendesak','c'=>'Penting dan tidak mendesak','d'=>'Tidak penting dan tidak mendesak', 'e'=>'Tidak mendesak', 'jawaban'=>'a'],
            ['pertanyaan'=>'Dokumen yang berisi rencana topik yang akan dibahas dalam rapat disebut...','a'=>'Notula','b'=>'Agenda rapat','c'=>'Risalah rapat','d'=>'Laporan rapat','e'=>'Makalah rapat','jawaban'=>'b'],
            ['pertanyaan'=>'Shortcut keyboard untuk menyimpan dokumen di Microsoft Office adalah...','a'=>'Ctrl+P','b'=>'Ctrl+Z','c'=>'Ctrl+S','d'=>'Ctrl+C', 'e'=>'Ctrl+V', 'jawaban'=>'c'],
            ['pertanyaan'=>'Format file yang aman untuk berbagi dokumen agar tidak mudah diubah adalah...','a'=>'.docx','b'=>'.xlsx','c'=>'.pdf','d'=>'.txt', 'e'=>'.doc', 'jawaban'=>'c'],
            ['pertanyaan'=>'Perangkat lunak pengolah kata yang paling umum digunakan di perkantoran adalah...','a'=>'Microsoft Excel','b'=>'Microsoft PowerPoint','c'=>'Microsoft Word','d'=>'Microsoft Access', 'e'=>'Microsoft OneNote', 'jawaban'=>'c'],
            ['pertanyaan'=>'Fungsi VLOOKUP di Microsoft Excel digunakan untuk...','a'=>'Membuat grafik','b'=>'Mencari nilai dalam kolom pertama suatu tabel','c'=>'Menghitung rata-rata','d'=>'Menyortir data', 'e'=>'Menghitung jumlah', 'jawaban'=>'b'],
            ['pertanyaan'=>'Sikap menjaga kerahasiaan informasi perusahaan dari pihak luar disebut...','a'=>'Loyalitas','b'=>'Kejujuran','c'=>'Kerahasiaan (confidentiality)','d'=>'Disiplin', 'e'=>'Integritas', 'jawaban'=>'c'],
            ['pertanyaan'=>'Kode etik profesi dibuat untuk...','a'=>'Membatasi kreativitas','b'=>'Menjadi pedoman perilaku dan standar moral','c'=>'Meningkatkan gaji','d'=>'Mempersulit perizinan', 'e'=>'Meningkatkan profesionalisme', 'jawaban'=>'b'],
            ['pertanyaan'=>'Kemampuan mengelola emosi di tempat kerja disebut...','a'=>'IQ','b'=>'SQ','c'=>'EQ','d'=>'AQ', 'e'=>'CQ', 'jawaban'=>'c'],
            ['pertanyaan'=>'Petty cash (kas kecil) digunakan untuk...','a'=>'Pembayaran gaji','b'=>'Pengeluaran rutin kecil yang tidak memerlukan cek','c'=>'Investasi jangka panjang','d'=>'Membayar hutang', 'e'=>'Membayar biaya operasional', 'jawaban'=>'b'],
            ['pertanyaan'=>'SOP (Standard Operating Procedure) berfungsi untuk...','a'=>'Mempersulit pekerjaan','b'=>'Memberikan panduan langkah-langkah standar','c'=>'Menggantikan peran atasan','d'=>'Mengurangi produktivitas', 'e'=>'Meningkatkan efisiensi', 'jawaban'=>'b'],
            ['pertanyaan'=>'Tata ruang kantor terbuka tanpa sekat permanen disebut...','a'=>'Private office','b'=>'Cubicle layout','c'=>'Open plan office','d'=>'Combination layout', 'e'=>'Hot desk', 'jawaban'=>'c'],
            ['pertanyaan'=>'Cloud storage digunakan di perkantoran untuk...','a'=>'Mengedit video','b'=>'Menyimpan dan berbagi dokumen secara online','c'=>'Membuat presentasi offline','d'=>'Mengamankan Wi-Fi', 'e'=>'Mengelola file secara kolaboratif', 'jawaban'=>'b'],
            ['pertanyaan'=>'Kegiatan membuat cadangan data secara berkala disebut...','a'=>'Formatting','b'=>'Defragmenting','c'=>'Backup','d'=>'Partitioning', 'e'=>'Archiving', 'jawaban'=>'c'],
            ['pertanyaan'=>'Lembar yang digunakan untuk mencatat instruksi pimpinan mengenai surat disebut...','a'=>'Lembar ekspedisi','b'=>'Lembar disposisi','c'=>'Kartu indeks','d'=>'Buku agenda', 'e'=>'Lembar pemberitahuan', 'jawaban'=>'b'],
            ['pertanyaan'=>'Buku yang mencatat surat masuk dan keluar secara berurutan tanggal disebut...','a'=>'Buku ekspedisi','b'=>'Buku agenda','c'=>'Lembar disposisi','d'=>'Kartu kendali', 'e'=>'Lembar pemberitahuan', 'jawaban'=>'b'],
            ['pertanyaan'=>'Format surat yang pengetikannya dimulai dari margin kiri semua bagian disebut...','a'=>'Full block style','b'=>'Semi block style','c'=>'Indented style','d'=>'Simplified style', 'e'=>'Modified style', 'jawaban'=>'a'],
            ['pertanyaan'=>'Alat bantu manajemen proyek yang menampilkan jadwal dalam bentuk batang horizontal disebut...','a'=>'Flow chart','b'=>'Gantt chart','c'=>'Mind map','d'=>'Pie chart', 'e'=>'Bar chart', 'jawaban'=>'b'],
            ['pertanyaan'=>'Proses mengalihkan arsip kertas ke bentuk digital disebut...','a'=>'Digitalisasi arsip','b'=>'Destruksi arsip','c'=>'Akuisisi arsip','d'=>'Indeksasi arsip', 'e'=>'Archiving', 'jawaban'=>'a'],
            ['pertanyaan'=>'Reimbursement dalam administrasi keuangan berarti...','a'=>'Pemberian pinjaman','b'=>'Penggantian biaya yang telah dikeluarkan karyawan untuk dinas','c'=>'Pemotongan gaji','d'=>'Bonus akhir tahun', 'e'=>'Pengembalian biaya', 'jawaban'=>'b'],
            ['pertanyaan'=>'Prinsip SMART dalam menetapkan target kerja berarti...','a'=>'Simple, Meaningful, Achievable, Realistic, Timely','b'=>'Specific, Measurable, Achievable, Relevant, Time-bound','c'=>'Strategic, Manageable, Attainable, Reasonable, Trackable','d'=>'Simple, Measurable, Achievable, Reachable, Time-based', 'e'=>'Specific, Measurable, Achievable, Relevant, Time-bound', 'jawaban'=>'b'],
            ['pertanyaan'=>'Konfirmasi ulang janji temu kepada pimpinan sebaiknya dilakukan...','a'=>'Seminggu sebelum pertemuan','b'=>'Sehari sebelum dan pada pagi hari pertemuan','c'=>'Hanya jika pimpinan meminta','d'=>'Saat pertemuan sudah dimulai', 'e'=>'Sebelum pertemuan', 'jawaban'=>'b'],
            ['pertanyaan'=>'Hambatan komunikasi dari perbedaan bahasa atau istilah teknis disebut hambatan...','a'=>'Fisik','b'=>'Psikologis','c'=>'Semantik','d'=>'Sosiokultural', 'e'=>'Kognitif', 'jawaban'=>'c'],
            ['pertanyaan'=>'Tanda tangan digital dalam dokumen elektronik berfungsi untuk...','a'=>'Memperindah tampilan','b'=>'Membuktikan keaslian dan integritas dokumen','c'=>'Mempercepat pengiriman email','d'=>'Mengkompresi ukuran file', 'e'=>'Mengautentikasi dokumen', 'jawaban'=>'b'],
        ];
    }
}