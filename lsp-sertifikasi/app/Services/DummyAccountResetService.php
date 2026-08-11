<?php

namespace App\Services;

use App\Models\User;
use App\Models\Asesor;
use App\Models\Tuk;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DummyAccountResetService
{
    protected string $defaultPassword = 'dummy123';

    /** Disk yang dicoba saat menghapus file (urutan tidak masalah) */
    protected array $disksToTry = ['public', 'public_html', 'private'];

    public function reset(): array
    {
        $result = [
            'asesmen_deleted'  => 0,
            'schedule_deleted' => 0,
        ];

        DB::beginTransaction();
        try {
            $asesiUser  = User::where('role', 'asesi')->where('is_dummy', true)->first();
            $asesorUser = User::where('role', 'asesor')->where('is_dummy', true)->first();
            $tukUser    = User::where('role', 'tuk')->where('is_dummy', true)->first();

            if (!$asesiUser || !$asesorUser || !$tukUser) {
                throw new \RuntimeException('Akun dummy belum lengkap. Jalankan seeder dummy_accounts_seed.php dulu.');
            }

            $asesor = Asesor::where('user_id', $asesorUser->id)->first();
            $tuk    = Tuk::where('user_id', $tukUser->id)->first();

            // ===== 1. Bersihkan semua Asesmen milik asesi dummy =====
            $asesmens = \App\Models\Asesmen::where('user_id', $asesiUser->id)->get();

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

                // Hapus schedule HANYA jika sudah tidak dipakai asesmen lain
                // dan schedule tsb memang bagian dari trio dummy (tuk/asesor dummy)
                if ($scheduleId) {
                    $schedule = Schedule::find($scheduleId);
                    if (
                        $schedule &&
                        $schedule->asesmens()->count() === 0 &&
                        ($schedule->tuk_id === $tuk?->id || $schedule->asesor_id === $asesor?->id)
                    ) {
                        $this->deleteScheduleAndChildren($schedule);
                        $result['schedule_deleted']++;
                    }
                }
            }

            // ===== 2. Reset akun Asesi dummy =====
            $this->deleteFileSafely($asesiUser->photo_path);
            $asesiUser->update([
                'password'             => Hash::make($this->defaultPassword),
                'password_changed_at'  => null,
                'photo_path'           => null,
                'signature'            => null,
                'email_verified_at'    => now(),
            ]);

            // ===== 3. Reset akun & data Asesor dummy =====
            if ($asesor) {
                foreach ($asesor->documents as $doc) {
                    $this->deleteFileSafely($doc->file_path);
                    $doc->delete();
                }
                $this->deleteFileSafely($asesor->foto_path);
                $this->deleteFileSafely($asesor->sk_pengangkatan_path);

                $asesor->update([
                    'foto_path'                    => null,
                    'sk_pengangkatan_number'       => null,
                    'sk_pengangkatan_date'         => null,
                    'sk_pengangkatan_valid_until'  => null,
                    'sk_pengangkatan_path'         => null,
                    'sk_pengangkatan_filename'     => null,
                ]);
            }
            $asesorUser->update([
                'password'             => Hash::make($this->defaultPassword),
                'password_changed_at'  => null,
                'photo_path'           => null,
                'signature'            => null,
                'email_verified_at'    => now(),
            ]);

            // ===== 4. Reset akun TUK dummy =====
            $tukUser->update([
                'password'             => Hash::make($this->defaultPassword),
                'password_changed_at'  => null,
                'photo_path'           => null,
                'signature'            => null,
                'email_verified_at'    => now(),
            ]);

            DB::commit();
            return $result;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('DummyAccountResetService error: ' . $e->getMessage());
            throw $e;
        }
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
            } catch (\Throwable $e) {
                // disk mungkin tidak terkonfigurasi, skip
            }
        }
    }
}