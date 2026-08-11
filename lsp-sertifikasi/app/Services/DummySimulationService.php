<?php

namespace App\Services;

use App\Models\User;
use App\Models\Asesor;
use App\Models\Tuk;
use App\Models\Asesmen;
use App\Models\Skema;
use Illuminate\Support\Facades\DB;

class DummySimulationService
{
    /**
     * @param int $skemaId
     * @param string $jenis 'mandiri' | 'kolektif'
     */
    public function createAsesmen(int $skemaId, string $jenis): Asesmen
    {
        $skema = Skema::findOrFail($skemaId);

        $email = $jenis === 'kolektif'
            ? 'dummy.asesi.kolektif@sikaplsp.local'
            : 'dummy.asesi.mandiri@sikaplsp.local';

        $asesiUser = User::where('email', $email)->where('is_dummy', true)->firstOrFail();

        // Kalau masih ada Asesmen aktif sebelumnya, tolak — suruh reset dulu
        $existing = Asesmen::where('user_id', $asesiUser->id)->first();
        if ($existing) {
            throw new \RuntimeException("Asesi dummy {$jenis} masih punya asesmen aktif (#{$existing->id}). Reset dulu sebelum bikin simulasi baru.");
        }

        return DB::transaction(function () use ($skema, $jenis, $asesiUser) {
            $data = [
                'user_id'            => $asesiUser->id,
                'skema_id'           => $skema->id,
                'full_name'          => $asesiUser->name,
                'nik'                => $jenis === 'kolektif' ? '9999999999999902' : '9999999999999901',
                'birth_place'        => 'Simulasi',
                'birth_date'         => '1990-01-01',
                'gender'             => 'L',
                'address'            => 'Simulasi - Bukan Data Riil',
                'phone'              => '000000000000',
                'email'              => $asesiUser->email,
                'education'          => 'S1',
                'occupation'         => 'Simulasi',
                'registration_date'  => now(),
                'status'             => 'registered',
                'is_collective'      => $jenis === 'kolektif',
            ];

            if ($jenis === 'kolektif') {
                $tukUser = User::where('role', 'tuk')->where('is_dummy', true)->firstOrFail();
                $tuk     = Tuk::where('user_id', $tukUser->id)->firstOrFail();

                $data['tuk_id']              = $tuk->id;
                $data['registered_by']       = $tukUser->id;
                $data['collective_batch_id'] = 'DUMMY-BATCH-' . now()->format('Ymd-His');
                $data['collective_paid_by_tuk'] = true;
                $data['skip_payment']        = true;
            } else {
                $data['registered_by'] = $asesiUser->id;
            }

            return Asesmen::create($data);
        });
    }
}