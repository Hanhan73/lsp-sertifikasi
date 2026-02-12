<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Tuk;
use App\Models\Skema;

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

    foreach ($tukList as $index => $data) {

        // Create user for TUK
        $tukUser = User::create([
            'name' => $data['name'],
            'email' => 'tuk' . ($index + 1) . '@lsp.com',
            'password' => Hash::make('password123'),
            'role' => 'tuk',
            'is_active' => true,
        ]);

        // Create TUK record
        Tuk::create([
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
        Skema::create([
            'code' => 'SKM-001',
            'name' => 'Staf Administrasi',
            'description' => 'Sertifikasi kompetensi untuk staf administrasi',
            'fee' => 500000,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        Skema::create([
            'code' => 'SKM-002',
            'name' => 'Pengelolaan Administrasi Perkantoran',
            'description' => 'Sertifikasi kompetensi untuk pengelola administrasi perkantoran',
            'fee' => 750000,
            'duration_days' => 45,
            'is_active' => true,
        ]);

        Skema::create([
            'code' => 'SKM-003',
            'name' => 'Resepsionis/Front Office',
            'description' => 'Sertifikasi kompetensi untuk resepsionis/front office',
            'fee' => 600000,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        Skema::create([
            'code' => 'SKM-004',
            'name' => 'Staf Administrasi Kepegawaian',
            'description' => 'Sertifikasi kompetensi untuk staf administrasi kepegawaian',
            'fee' => 800000,
            'duration_days' => 60,
            'is_active' => true,
        ]);
        Skema::create([
            'code' => 'SKM-005',
            'name' => 'Sekretaris',
            'description' => 'Sertifikasi kompetensi untuk sekretaris',
            'fee' => 800000,
            'duration_days' => 60,
            'is_active' => true,
        ]);
        Skema::create([
            'code' => 'SKM-006',
            'name' => 'Sekretaris Direksi',
            'description' => 'Sertifikasi kompetensi untuk sekretaris direksi',
            'fee' => 800000,
            'duration_days' => 60,
            'is_active' => true,
        ]);
        Skema::create([
            'code' => 'SKM-007',
            'name' => 'Manajer Kantor',
            'description' => 'Sertifikasi kompetensi untuk manajer kantor',
            'fee' => 800000,
            'duration_days' => 60,
            'is_active' => true,
        ]);
        Skema::create([
            'code' => 'SKM-008',
            'name' => 'Sekretaris Perusahaan',
            'description' => 'Sertifikasi kompetensi untuk sekretaris perusahaan',
            'fee' => 800000,
            'duration_days' => 60,
            'is_active' => true,
        ]);

        // Create sample Asesi
        $asesi1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'asesi1@example.com',
            'password' => Hash::make('password123'),
            'role' => 'asesi',
            'is_active' => true,
        ]);

        $asesi2 = User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'asesi2@example.com',
            'password' => Hash::make('password123'),
            'role' => 'asesi',
            'is_active' => true,
        ]);

        echo "Database seeded successfully!\n\n";
        echo "=== LOGIN CREDENTIALS ===\n";
        echo "Admin:\n";
        echo "  Email: admin@lsp.com\n";
        echo "  Password: password123\n\n";
        echo "TUK 1 (Jakarta):\n";
        echo "  Email: tuk1@lsp.com\n";
        echo "  Password: password123\n\n";
        echo "TUK 2 (Bandung):\n";
        echo "  Email: tuk2@lsp.com\n";
        echo "  Password: password123\n\n";
        echo "Asesi Sample:\n";
        echo "  Email: asesi1@example.com\n";
        echo "  Password: password123\n";
    }
}