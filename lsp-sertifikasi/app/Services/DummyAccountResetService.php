<?php

namespace App\Services;

use App\Models\User;
use App\Models\Asesor;
use App\Models\Tuk;
use App\Models\Schedule;
use App\Models\Asesmen;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DummyAccountResetService
{
    protected string $defaultPassword = 'dummy123';
    protected array $disksToTry = ['public', 'public_html', 'private'];

    /**
     * @param string $scope 'all' | 'tuk' | 'asesor' | 'asesi_mandiri' | 'asesi_kolektif'
     */
    public function reset(string $scope = 'all'): array
    {
        $result = ['asesmen_deleted' => 0, 'schedule_deleted' => 0, 'akun_direset' => 0];

        DB::beginTransaction();
        try {
            $tukUser    = User::where('role', 'tuk')->where('is_dummy', true)->first();
            $asesorUser = User::where('role', 'asesor')->where('is_dummy', true)->first();
            $asesor     = $asesorUser ? Asesor::where('user_id', $asesorUser->id)->first() : null;
            $tuk        = $tukUser ? Tuk::where('user_id', $tukUser->id)->first() : null;

            if (in_array($scope, ['all', 'asesi_mandiri'])) {
                $u = User::where('email', 'dummy.asesi.mandiri@sikaplsp.local')->where('is_dummy', true)->first();
                if ($u) { $this->resetAsesiAccount($u, $tuk, $asesor, $result); }
            }

            if (in_array($scope, ['all', 'asesi_kolektif'])) {
                $u = User::where('email', 'dummy.asesi.kolektif@sikaplsp.local')->where('is_dummy', true)->first();
                if ($u) { $this->resetAsesiAccount($u, $tuk, $asesor, $result); }
            }

            if (in_array($scope, ['all', 'asesor']) && $asesorUser) {
                if ($asesor) {
                    foreach ($asesor->documents as $doc) {
                        $this->deleteFileSafely($doc->file_path);
                        $doc->delete();
                    }
                    $this->deleteFileSafely($asesor->foto_path);
                    $this->deleteFileSafely($asesor->sk_pengangkatan_path);
                    $asesor->update([
                        'foto_path' => null, 'sk_pengangkatan_number' => null,
                        'sk_pengangkatan_date' => null, 'sk_pengangkatan_valid_until' => null,
                        'sk_pengangkatan_path' => null, 'sk_pengangkatan_filename' => null,
                    ]);
                }
                $this->resetUserCredentials($asesorUser);
                $result['akun_direset']++;
            }

            if (in_array($scope, ['all', 'tuk']) && $tukUser) {
                $this->resetUserCredentials($tukUser);
                $result['akun_direset']++;
            }

            DB::commit();
            return $result;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('DummyAccountResetService error: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function resetAsesiAccount(User $asesiUser, ?Tuk $tuk, ?Asesor $asesor, array &$result): void
    {
        $asesmens = Asesmen::where('user_id', $asesiUser->id)->get();

        foreach ($asesmens as $asesmen) {
            $scheduleId = $asesmen->schedule_id;

            $this->deleteFileSafely($asesmen->photo_path);
            $this->deleteFileSafely($asesmen->ktp_path);
            $this->deleteFileSafely($asesmen->document_path);
            $this->deleteFileSafely($asesmen->pre_assessment_file);

            $asesmen->aplsatu?->delete();
            $asesmen->apldua?->delete();
            $asesmen->frak01?->delete();
            $asesmen->frAk03?->delete();
            $asesmen->frak04?->delete();
            $asesmen->soalTeoriAsesi()->delete();
            $asesmen->jawabanObservasi()->delete();
            $asesmen->payments()->delete();
            $asesmen->certificate?->delete();

            $asesmen->delete();
            $result['asesmen_deleted']++;

            if ($scheduleId) {
                $schedule = Schedule::find($scheduleId);
                if (
                    $schedule && $schedule->asesmens()->count() === 0 &&
                    ($schedule->tuk_id === $tuk?->id || $schedule->asesor_id === $asesor?->id)
                ) {
                    $this->deleteScheduleAndChildren($schedule);
                    $result['schedule_deleted']++;
                }
            }
        }

        $this->resetUserCredentials($asesiUser);
        $result['akun_direset']++;
    }

    protected function resetUserCredentials(User $user): void
    {
        $this->deleteFileSafely($user->photo_path);
        $user->update([
            'password'            => Hash::make($this->defaultPassword),
            'password_changed_at' => null,
            'photo_path'          => null,
            'signature'           => null,
            'email_verified_at'   => now(),
        ]);
    }

    protected function deleteScheduleAndChildren(Schedule $schedule): void
    {
        $schedule->distribusiSoalObservasi()->delete();
        $schedule->distribusiPaketSoal()->delete();
        $schedule->hasilObservasi()->delete();
        $schedule->hasilPortofolio()->delete();
        $schedule->distribusiSoalTeori()?->delete();
        $schedule->distribusiPortofolio()->delete();
        $schedule->honorPaymentDetails()->delete();
        $schedule->beritaAcara()?->delete();
        $schedule->assignmentHistories()->delete();
        $this->deleteFileSafely($schedule->sk_path);
        $this->deleteFileSafely($schedule->foto_dokumentasi_1);
        $this->deleteFileSafely($schedule->foto_dokumentasi_2);
        $schedule->delete();
    }

    protected function deleteFileSafely(?string $path): void
    {
        if (!$path) return;
        foreach ($this->disksToTry as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    Storage::disk($disk)->delete($path);
                    return;
                }
            } catch (\Throwable $e) {}
        }
    }
}