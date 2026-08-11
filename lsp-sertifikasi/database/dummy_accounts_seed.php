<?php
// database/dummy_accounts_seed.php
// Jalankan: php artisan tinker --execute="require base_path('database/dummy_accounts_seed.php');"

use App\Models\User;
use App\Models\Asesor;
use App\Models\Tuk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

DB::beginTransaction();

try {
    $defaultPassword = 'dummy123';

    // ===== 1. Dummy TUK =====
    $tukUser = User::firstOrCreate(
        ['email' => 'dummy.tuk@sikaplsp.local'],
        [
            'name'              => 'TUK Dummy (Simulasi)',
            'password'          => Hash::make($defaultPassword),
            'role'              => 'tuk',
            'is_active'         => true,
            'is_dummy'          => true,
            'email_verified_at' => now(),
        ]
    );
    $tukUser->forceFill(['is_dummy' => true])->save();

    $tuk = Tuk::firstOrCreate(
        ['user_id' => $tukUser->id],
        [
            'code'         => 'DUMMY-TUK',
            'name'         => 'TUK Dummy (Simulasi)',
            'address'      => 'Simulasi - Bukan Data Riil',
            'email'        => 'dummy.tuk@sikaplsp.local',
            'phone'        => '000000000000',
            'manager_name' => 'Dummy Manager',
            'is_active'    => true,
        ]
    );

    // ===== 2. Dummy Asesor =====
    $asesorUser = User::firstOrCreate(
        ['email' => 'dummy.asesor@sikaplsp.local'],
        [
            'name'              => 'Asesor Dummy (Simulasi)',
            'password'          => Hash::make($defaultPassword),
            'role'              => 'asesor',
            'is_active'         => true,
            'is_dummy'          => true,
            'email_verified_at' => now(),
        ]
    );
    $asesorUser->forceFill(['is_dummy' => true])->save();

    Asesor::firstOrCreate(
        ['user_id' => $asesorUser->id],
        [
            'nama'          => 'Asesor Dummy (Simulasi)',
            'nik'           => '0000000000000001',
            'tempat_lahir'  => 'Simulasi',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'email'         => 'dummy.asesor@sikaplsp.local',
            'siap_kerja'    => 'Memiliki',
            'status_reg'    => 'aktif',
            'is_active'     => true,
        ]
    );

    // ===== 3. Dummy Asesi — Mandiri =====
    $asesiMandiri = User::firstOrCreate(
        ['email' => 'dummy.asesi.mandiri@sikaplsp.local'],
        [
            'name'              => 'Asesi Dummy Mandiri (Simulasi)',
            'password'          => Hash::make($defaultPassword),
            'role'              => 'asesi',
            'is_active'         => true,
            'is_dummy'          => true,
            'email_verified_at' => now(),
        ]
    );
    $asesiMandiri->forceFill(['is_dummy' => true])->save();

    // ===== 4. Dummy Asesi — Kolektif (tetap lewat TUK dummy yang sama) =====
    $asesiKolektif = User::firstOrCreate(
        ['email' => 'dummy.asesi.kolektif@sikaplsp.local'],
        [
            'name'              => 'Asesi Dummy Kolektif (Simulasi)',
            'password'          => Hash::make($defaultPassword),
            'role'              => 'asesi',
            'is_active'         => true,
            'is_dummy'          => true,
            'email_verified_at' => now(),
        ]
    );
    $asesiKolektif->forceFill(['is_dummy' => true])->save();

    DB::commit();

    echo "✅ Akun dummy siap:\n";
    echo "   TUK             : dummy.tuk@sikaplsp.local\n";
    echo "   Asesor          : dummy.asesor@sikaplsp.local\n";
    echo "   Asesi Mandiri   : dummy.asesi.mandiri@sikaplsp.local\n";
    echo "   Asesi Kolektif  : dummy.asesi.kolektif@sikaplsp.local\n";
    echo "   Password semua  : {$defaultPassword}\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "❌ Gagal: " . $e->getMessage() . "\n";
}