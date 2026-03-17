<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Tuk;
use App\Models\Skema;
use App\Models\Asesmen;
use App\Models\Schedule;
use App\Models\Payment;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin
        $admin = User::create([
            'name' => 'Admin LSP',
            'email' => 'admin@lsp.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $tukList = [
            [
                'code' => 'TUK-001',
                'name' => 'LSP-KAP Universitas Pendidikan Indonesia',
                'address' => 'Gedung Garnadi Lt. 2 Prodi Pendidikan Manajemen Perkantoran FPEB UPI Jl. Dr. Setiabudhi No 229 Bandung',
                'email' => 'lspkap@upi.edu',
                'phone' => '022-2013163',
                'manager_name' => 'Drs. Hendri Winata, M.Si.',
                'staff_name' => 'Dr. Hady Siti Hadijah, M.Si.',
            ],
            [
                'code' => 'TUK-002',
                'name' => 'TUK LSP-KAP Universitas Negeri Jakarta',
                'address' => 'Prodi Pendidikan Administrasi Perkantoran UNJ Jl. Rawa Mangun Muka Jakarta Timur',
                'email' => 'lspkap@unj.ac.id',
                'phone' => '021-4706287',
                'manager_name' => 'Darma Rika Swaramarinda, S.Pd., M.SE.',
                'staff_name' => null,
            ],
            [
                'code' => 'TUK-003',
                'name' => 'TUK LSP-KAP UHAMKA Jakarta',
                'address' => 'Jurusan Administrasi Perkantoran FKIP UHAMKA Jl. Tanah Merdeka Jakarta',
                'email' => 'lspkap@uhamka.ac.id',
                'phone' => '021-8400941',
                'manager_name' => 'Dr. Hj. Sri Giyanti, MM.',
                'staff_name' => null,
            ],
            [
                'code' => 'TUK-004',
                'name' => 'TUK LSP-KAP Universitas Negeri Malang',
                'address' => 'Fakultas Ekonomi UM Jl. Semarang No. 5 Malang 65145',
                'email' => 'lspkap@um.ac.id',
                'phone' => '0341-551312',
                'manager_name' => 'Dr. Madziatul Churiyah, S.Pd., MM.',
                'staff_name' => null,
            ],
            [
                'code' => 'TUK-005',
                'name' => 'TUK LSP-KAP Universitas Sebelas Maret',
                'address' => 'FKIP UNS Jl. Ir. Sutami 36A Surakarta 57126',
                'email' => 'lspkap@uns.ac.id',
                'phone' => '0271-669124',
                'manager_name' => 'Dra. Patni Ninghardjanti, M.Pd.',
                'staff_name' => null,
            ],
            [
                'code' => 'TUK-006',
                'name' => 'TUK LSP-KAP Universitas Negeri Yogyakarta',
                'address' => 'Fakultas Ekonomi UNY Kampus Karangmalang Yogyakarta 555281',
                'email' => 'lspkap@uny.ac.id',
                'phone' => '0274-586168',
                'manager_name' => 'Arwan Nur Ramadhan, M.Pd.',
                'staff_name' => null,
            ],
            [
                'code' => 'TUK-007',
                'name' => 'TUK LSP-KAP Universitas Taruna Bakti Bandung',
                'address' => 'Jl. L.L.R.E. Martadinata No. 93-95 Bandung 40115',
                'email' => 'lspkap@tarunabakti.ac.id',
                'phone' => '022-7202211',
                'manager_name' => 'Dr. Chandra Hendriyani, M.Si., CHCM.',
                'staff_name' => null,
            ],
            [
                'code' => 'TUK-008',
                'name' => 'TUK LSP-KAP Universitas Negeri Makassar',
                'address' => 'Jl. AP Pettarani Gunungsari Baru Makassar 90222',
                'email' => 'lspkap@unm.ac.id',
                'phone' => '0411-865677',
                'manager_name' => 'Jamaluddin, S.Pd., M.Si.',
                'staff_name' => null,
            ],
        ];

        $tukModels = [];
        foreach ($tukList as $index => $data) {
            // Create user for TUK
            $tukUser = User::create([
                'name' => $data['name'],
                'email' => 'tuk' . ($index + 1) . '@lsp.com',
                'password' => Hash::make('password123'),
                'role' => 'tuk',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Create TUK record
            $tukModels[] = Tuk::create([
                'code' => $data['code'],
                'name' => $data['name'],
                'address' => $data['address'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'manager_name' => $data['manager_name'],
                'staff_name' => $data['staff_name'],
                'logo_path' => null,
                'user_id' => $tukUser->id,
                'is_active' => true,
            ]);
        }

        // Create Skema Sertifikasi
        $skemas = [
            [
                'code' => 'SKM-001',
                'name' => 'Staf Administrasi',
                'description' => 'Sertifikasi kompetensi untuk staf administrasi',
                'fee' => 500000,
                'duration_days' => 30,
            ],
            [
                'code' => 'SKM-002',
                'name' => 'Pengelolaan Administrasi Perkantoran',
                'description' => 'Sertifikasi kompetensi untuk pengelola administrasi perkantoran',
                'fee' => 750000,
                'duration_days' => 45,
            ],
            [
                'code' => 'SKM-003',
                'name' => 'Resepsionis/Front Office',
                'description' => 'Sertifikasi kompetensi untuk resepsionis/front office',
                'fee' => 600000,
                'duration_days' => 30,
            ],
            [
                'code' => 'SKM-004',
                'name' => 'Staf Administrasi Kepegawaian',
                'description' => 'Sertifikasi kompetensi untuk staf administrasi kepegawaian',
                'fee' => 800000,
                'duration_days' => 60,
            ],
            [
                'code' => 'SKM-005',
                'name' => 'Sekretaris',
                'description' => 'Sertifikasi kompetensi untuk sekretaris',
                'fee' => 800000,
                'duration_days' => 60,
            ],
            [
                'code' => 'SKM-006',
                'name' => 'Sekretaris Direksi',
                'description' => 'Sertifikasi kompetensi untuk sekretaris direksi',
                'fee' => 800000,
                'duration_days' => 60,
            ],
            [
                'code' => 'SKM-007',
                'name' => 'Manajer Kantor',
                'description' => 'Sertifikasi kompetensi untuk manajer kantor',
                'fee' => 800000,
                'duration_days' => 60,
            ],
            [
                'code' => 'SKM-008',
                'name' => 'Sekretaris Perusahaan',
                'description' => 'Sertifikasi kompetensi untuk sekretaris perusahaan',
                'fee' => 800000,
                'duration_days' => 60,
            ],
        ];

        $skemaModels = [];
        foreach ($skemas as $skemaData) {
            $skemaModels[] = Skema::create(array_merge($skemaData, ['is_active' => true]));
        }

        // ========================================
        // SCENARIO 1: KOLEKTIF - Menunggu Verifikasi TUK
        // ========================================
        echo "\n🔄 Creating: KOLEKTIF Batch - Menunggu Verifikasi TUK...\n";
        $batch1 = $this->createCollectiveBatch([
            'tuk' => $tukModels[0],
            'skema' => $skemaModels[0],
            'batch_suffix' => 'WAITING-TUK',
            'count' => 5,
            'training_count' => 2,
            'status' => 'data_completed',
            'tuk_verified' => false,
            'admin_verified' => false,
            'payment_phases' => 'two_phase',
        ]);

        // ========================================
        // SCENARIO 2: KOLEKTIF - Menunggu Penetapan Biaya Admin
        // ========================================
        echo "🔄 Creating: KOLEKTIF Batch - Menunggu Penetapan Biaya Admin...\n";
        $batch2 = $this->createCollectiveBatch([
            'tuk' => $tukModels[0],
            'skema' => $skemaModels[1],
            'batch_suffix' => 'WAITING-ADMIN',
            'count' => 4,
            'training_count' => 1,
            'status' => 'data_completed',
            'tuk_verified' => true,
            'admin_verified' => false,
            'payment_phases' => 'single',
        ]);

        // ========================================
        // SCENARIO 3: KOLEKTIF - Siap Bayar (1 Fase)
        // ========================================
        echo "🔄 Creating: KOLEKTIF Batch - Siap Bayar (1 Fase)...\n";
        $batch3Data = $this->createCollectiveBatch([
            'tuk' => $tukModels[1],
            'skema' => $skemaModels[0],
            'batch_suffix' => 'READY-PAY-SINGLE',
            'count' => 3,
            'training_count' => 0,
            'status' => 'verified',
            'tuk_verified' => true,
            'admin_verified' => true,
            'payment_phases' => 'single',
            'fee_amount' => 500000,
        ]);

        // ========================================
        // SCENARIO 4: KOLEKTIF - Sudah Bayar, Perlu Jadwal
        // ========================================
        echo "🔄 Creating: KOLEKTIF Batch - Sudah Bayar, Perlu Jadwal...\n";
        $batch4Data = $this->createCollectiveBatch([
            'tuk' => $tukModels[1],
            'skema' => $skemaModels[1],
            'batch_suffix' => 'PAID-NEED-SCHEDULE',
            'count' => 6,
            'training_count' => 3,
            'status' => 'paid',
            'tuk_verified' => true,
            'admin_verified' => true,
            'payment_phases' => 'two_phase',
            'fee_amount' => 750000,
            'paid' => true,
            'phase_1_paid' => true,
        ]);

        // ========================================
        // SCENARIO 5: KOLEKTIF - Terjadwal
        // ========================================
        echo "🔄 Creating: KOLEKTIF Batch - Terjadwal...\n";
        $batch5Data = $this->createCollectiveBatch([
            'tuk' => $tukModels[2],
            'skema' => $skemaModels[2],
            'batch_suffix' => 'SCHEDULED',
            'count' => 4,
            'training_count' => 2,
            'status' => 'scheduled',
            'tuk_verified' => true,
            'admin_verified' => true,
            'payment_phases' => 'single',
            'fee_amount' => 600000,
            'paid' => true,
            'has_schedule' => true,
        ]);

        // Create shared schedule for batch 5
        $schedule1 = Schedule::create([
            'tuk_id' => $tukModels[2]->id,
            'skema_id' => $skemaModels[2]->id,
            'assessment_date' => now()->addDays(7),
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'location' => 'Ruang Asesmen A',
            'notes' => 'Batch Kolektif SCHEDULED',
            'created_by' => $tukModels[2]->user_id,
        ]);

        // Assign all batch 5 asesmens to this schedule
        foreach ($batch5Data['asesmens'] as $asesmen) {
            $asesmen->update(['schedule_id' => $schedule1->id]);
        }

        // ========================================
        // SCENARIO 6: MANDIRI - Menunggu Verifikasi Admin
        // ========================================
        echo "🔄 Creating: MANDIRI - Menunggu Verifikasi Admin...\n";
        $this->createMandiriAsesmen([
            'name' => 'Agus Mandiri',
            'email' => 'agus.mandiri@test.com',
            'skema' => $skemaModels[0],
            'training_flag' => true,
            'status' => 'data_completed',
            'admin_verified' => false,
        ]);

        // ========================================
        // SCENARIO 7: MANDIRI - Menunggu Assignment ke TUK
        // ========================================
        echo "🔄 Creating: MANDIRI - Menunggu Assignment...\n";
        for ($i = 1; $i <= 3; $i++) {
            $this->createMandiriAsesmen([
                'name' => "Mandiri User $i",
                'email' => "mandiri$i@test.com",
                'skema' => $skemaModels[$i % 3],
                'training_flag' => $i % 2 == 0,
                'status' => 'verified',
                'admin_verified' => true,
                'assigned' => false,
            ]);
        }

        // ========================================
        // SCENARIO 8: MANDIRI - Sudah Di-assign, Siap Bayar
        // ========================================
        echo "🔄 Creating: MANDIRI - Assigned, Ready to Pay...\n";
        
        // Create schedule untuk mandiri
        $schedule2 = Schedule::create([
            'tuk_id' => $tukModels[0]->id,
            'skema_id' => $skemaModels[0]->id,
            'assessment_date' => now()->addDays(10),
            'start_time' => '13:00:00',
            'end_time' => '17:00:00',
            'location' => 'Ruang Asesmen B',
            'notes' => 'Mixed Kolektif + Mandiri',
            'created_by' => $tukModels[0]->user_id,
        ]);

        $mandiri1 = $this->createMandiriAsesmen([
            'name' => 'Siti Mandiri Assigned',
            'email' => 'siti.assigned@test.com',
            'skema' => $skemaModels[0],
            'training_flag' => false,
            'status' => 'verified',
            'admin_verified' => true,
            'assigned' => true,
            'tuk' => $tukModels[0],
            'schedule' => $schedule2,
        ]);

        // ========================================
        // SCENARIO 9: Sample Asesi (Original)
        // ========================================
        echo "🔄 Creating: Sample Asesi (Original)...\n";
        User::create([
            'name' => 'Budi Santoso',
            'email' => 'asesi1@example.com',
            'password' => Hash::make('password123'),
            'role' => 'asesi',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'asesi2@example.com',
            'password' => Hash::make('password123'),
            'role' => 'asesi',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // ========================================
        // PRINT CREDENTIALS
        // ========================================
        echo "\n";
        echo "✅ Database seeded successfully!\n\n";
        echo "===========================================\n";
        echo "           LOGIN CREDENTIALS              \n";
        echo "===========================================\n\n";
        
        echo "👤 ADMIN:\n";
        echo "   Email: admin@lsp.com\n";
        echo "   Password: password123\n\n";
        
        echo "🏢 TUK 1 (UPI Bandung):\n";
        echo "   Email: tuk1@lsp.com\n";
        echo "   Password: password123\n\n";
        
        echo "🏢 TUK 2 (UNJ Jakarta):\n";
        echo "   Email: tuk2@lsp.com\n";
        echo "   Password: password123\n\n";
        
        echo "👥 ASESI SAMPLES:\n";
        echo "   Email: asesi1@example.com\n";
        echo "   Password: password123\n\n";
        
        echo "===========================================\n";
        echo "         TESTING SCENARIOS CREATED        \n";
        echo "===========================================\n\n";
        
        echo "✓ Kolektif - Menunggu Verifikasi TUK (5 asesi)\n";
        echo "✓ Kolektif - Menunggu Penetapan Biaya Admin (4 asesi)\n";
        echo "✓ Kolektif - Siap Bayar 1 Fase (3 asesi)\n";
        echo "✓ Kolektif - Sudah Bayar, Perlu Jadwal (6 asesi)\n";
        echo "✓ Kolektif - Terjadwal (4 asesi)\n";
        echo "✓ Mandiri - Menunggu Verifikasi Admin (1 asesi)\n";
        echo "✓ Mandiri - Menunggu Assignment (3 asesi)\n";
        echo "✓ Mandiri - Assigned, Ready to Pay (1 asesi)\n\n";
        
        echo "===========================================\n\n";
    }

    /**
     * Create Collective Batch
     */
    private function createCollectiveBatch($config)
    {
        $tuk = $config['tuk'];
        $skema = $config['skema'];
        $batchSuffix = $config['batch_suffix'];
        $count = $config['count'];
        $trainingCount = $config['training_count'];
        $status = $config['status'];
        $tukVerified = $config['tuk_verified'];
        $adminVerified = $config['admin_verified'];
        $paymentPhases = $config['payment_phases'];
        $feeAmount = $config['fee_amount'] ?? null;
        $paid = $config['paid'] ?? false;
        $phase1Paid = $config['phase_1_paid'] ?? false;
        $hasSchedule = $config['has_schedule'] ?? false;

        $batchId = 'BATCH-' . $tuk->code . '-' . $batchSuffix . '-' . time();
        $asesmens = [];

        for ($i = 1; $i <= $count; $i++) {
            $needsTraining = $i <= $trainingCount;
            
            // Create user
            $user = User::create([
                'name' => "Peserta Kolektif $i ($batchSuffix)",
                'email' => strtolower("kolektif.{$batchSuffix}.$i@test.com"),
                'password' => Hash::make('password123'),
                'role' => 'asesi',
                'is_active' => true,
                'email_verified_at' => now(),
                'password_changed_at' => now(),
            ]);

            // Create asesmen
            $asesmenData = [
                'user_id' => $user->id,
                'tuk_id' => $tuk->id,
                'skema_id' => $skema->id,
                'full_name' => $user->name,
                'nik' => '327501' . rand(0000000000, 9999999999),
                'birth_place' => 'Jakarta',
                'birth_date' => '1995-01-01',
                'gender' => $i % 2 == 0 ? 'P' : 'L',
                'address' => 'Jl. Test No. ' . $i,
                'city_code' => '31',
                'province_code' => '31',
                'phone' => '08123456' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'education' => 'S1',
                'occupation' => 'Mahasiswa',
                'budget_source' => 'Pribadi',
                'institution' => 'Universitas Test',
                'preferred_date' => now()->addDays(14),
                'registration_date' => now()->subDays(2),
                'status' => $status,
                'registered_by' => $tuk->user_id,
                'is_collective' => true,
                'collective_batch_id' => $batchId,
                'payment_phases' => $paymentPhases,
                'training_flag' => $needsTraining,
                'collective_paid_by_tuk' => true,
                'skip_payment' => true,
            ];

            // TUK Verification
            if ($tukVerified) {
                $asesmenData['tuk_verified_by'] = $tuk->user_id;
                $asesmenData['tuk_verified_at'] = now()->subDays(1);
                $asesmenData['tuk_verification_notes'] = 'Data lengkap dan valid';
            }

            // Admin Verification
            if ($adminVerified && $feeAmount) {
                $finalFee = $feeAmount + ($needsTraining ? 1500000 : 0);
                
                $asesmenData['fee_amount'] = $finalFee;
                $asesmenData['admin_verified_by'] = 1; // Admin user
                $asesmenData['admin_verified_at'] = now()->subHours(12);
                
                if ($paymentPhases === 'two_phase') {
                    $asesmenData['phase_1_amount'] = $feeAmount / 2;
                    $asesmenData['phase_2_amount'] = $finalFee - ($feeAmount / 2);
                }
            }

            $asesmen = Asesmen::create($asesmenData);
            $asesmens[] = $asesmen;

            // Create Payment if paid
            if ($paid) {
                $paymentAmount = $paymentPhases === 'two_phase' ? 
                    $asesmen->phase_1_amount : $asesmen->fee_amount;
                
                Payment::create([
                    'asesmen_id' => $asesmen->id,
                    'amount' => $paymentAmount,
                    'method' => 'midtrans',
                    'status' => 'verified',
                    'payment_phase' => $paymentPhases === 'two_phase' ? 'phase_1' : 'full',
                    'order_id' => 'ORDER-' . time() . '-' . $asesmen->id,
                    'transaction_id' => 'TRX-' . time() . '-' . $asesmen->id,
                    'payment_type' => 'bank_transfer',
                    'verified_at' => now()->subHours(6),
                    'notes' => 'Auto verified by seeder',
                ]);
            }
        }

        return [
            'batch_id' => $batchId,
            'asesmens' => $asesmens,
        ];
    }

    /**
     * Create Mandiri Asesmen
     */
    private function createMandiriAsesmen($config)
    {
        $name = $config['name'];
        $email = $config['email'];
        $skema = $config['skema'];
        $trainingFlag = $config['training_flag'];
        $status = $config['status'];
        $adminVerified = $config['admin_verified'];
        $assigned = $config['assigned'] ?? false;
        $tuk = $config['tuk'] ?? null;
        $schedule = $config['schedule'] ?? null;

        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => 'asesi',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Calculate fee
        $feeAmount = $skema->fee + ($trainingFlag ? 1500000 : 0);

        // Create asesmen
        $asesmenData = [
            'user_id' => $user->id,
            'tuk_id' => $assigned && $tuk ? $tuk->id : null,
            'skema_id' => $skema->id,
            'full_name' => $name,
            'nik' => '3275' . rand(1000000000, 9999999999),
            'birth_place' => 'Bandung',
            'birth_date' => '1998-05-15',
            'gender' => 'L',
            'address' => 'Jl. Mandiri Test No. 123',
            'city_code' => '32',
            'province_code' => '32',
            'phone' => '08' . rand(1000000000, 9999999999),
            'education' => 'S1',
            'occupation' => 'Karyawan',
            'budget_source' => 'Pribadi',
            'institution' => 'PT Test Indonesia',
            'preferred_date' => now()->addDays(20),
            'registration_date' => now()->subDays(3),
            'status' => $status,
            'is_collective' => false,
            'training_flag' => $trainingFlag,
            'fee_amount' => $adminVerified ? $feeAmount : null,
        ];

        if ($adminVerified) {
            $asesmenData['admin_verified_by'] = 1;
            $asesmenData['admin_verified_at'] = now()->subHours(8);
        }

        if ($assigned && $tuk) {
            $asesmenData['assigned_tuk_id'] = $tuk->id;
            $asesmenData['assigned_at'] = now()->subHours(4);
            $asesmenData['assigned_by'] = 1;
        }

        if ($schedule) {
            $asesmenData['schedule_id'] = $schedule->id;
        }

        return Asesmen::create($asesmenData);
    }
}