<?php
// database/fix_dummy_asesi_split.php
// Jalankan sekali: php artisan tinker --execute="require base_path('database/fix_dummy_asesi_split.php');"

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

DB::beginTransaction();

try {
    $defaultPassword = 'dummy123';

    $oldAsesi = User::where('email', 'dummy.asesi@sikaplsp.local')->first();

    if ($oldAsesi) {
        // Rename akun lama -> jadi akun "Mandiri"
        $oldAsesi->update([
            'email' => 'dummy.asesi.mandiri@sikaplsp.local',
            'name'  => 'Asesi Dummy Mandiri (Simulasi)',
        ]);
        echo "✅ Akun lama di-rename jadi: dummy.asesi.mandiri@sikaplsp.local\n";
    } else {
        // Kalau ternyata sudah pernah di-rename / belum ada, buat baru
        $oldAsesi = User::firstOrCreate(
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
        echo "ℹ️ Akun lama tidak ditemukan, dummy.asesi.mandiri@sikaplsp.local dibuat/dipastikan ada.\n";
    }

    // Buat akun baru untuk "Kolektif"
    $newAsesiKolektif = User::firstOrCreate(
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
    $newAsesiKolektif->forceFill(['is_dummy' => true])->save();

    DB::commit();

    echo "✅ Sekarang ada 2 asesi dummy:\n";
    echo "   Mandiri  : dummy.asesi.mandiri@sikaplsp.local\n";
    echo "   Kolektif : dummy.asesi.kolektif@sikaplsp.local\n";
    echo "   Password : {$defaultPassword}\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "❌ Gagal: " . $e->getMessage() . "\n";
}