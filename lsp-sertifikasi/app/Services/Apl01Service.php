<?php

namespace App\Services;

use App\Models\AplSatu;
use App\Models\AplSatuBukti;
use App\Models\Asesmen;
use Illuminate\Support\Facades\Log;

class Apl01Service
{
    /**
     * Create atau get APL-01 untuk asesmen
     * Pre-fill dengan data dari asesmen
     */
    public function getOrCreateApl01(Asesmen $asesmen): AplSatu
    {
        if ($asesmen->aplsatu) {
            return $asesmen->aplsatu;
        }

        // Map gender - database stores 'L'/'P' or 'male'/'female', form uses 'Laki-laki'/'Perempuan'
        $gender = $asesmen->gender;
        $jenisKelamin = match(strtolower((string)$gender)) {
            'l', 'laki-laki', 'male', 'laki', 'm'  => 'Laki-laki',
            'p', 'perempuan', 'female', 'wanita', 'f' => 'Perempuan',
            default => 'Laki-laki',
        };

        // Pre-fill data dari asesmen
        return AplSatu::create([
            'asesmen_id'             => $asesmen->id,
            'nama_lengkap'           => $asesmen->full_name ?? $asesmen->user->name ?? '',
            'nik'                    => $asesmen->nik ?? '',
            'tempat_lahir'           => $asesmen->birth_place ?? '',
            'tanggal_lahir'          => $asesmen->birth_date,
            'jenis_kelamin'          => $jenisKelamin,
            'kebangsaan'             => 'Indonesia',
            'alamat_rumah'           => $asesmen->address ?? '',
            'kode_pos'               => '', // Not in Asesmen model
            'hp'                     => $asesmen->phone ?? '',
            'email'                  => $asesmen->user->email ?? '',
            'kualifikasi_pendidikan' => $asesmen->education ?? '',
            'tujuan_asesmen'         => 'Sertifikasi',
            'status'                 => 'draft',
        ]);
    }

    /**
     * Update APL-01 data
     */
    public function updateApl01(AplSatu $aplsatu, array $data): bool
    {
        try {
            $aplsatu->update($data);
            return true;
        } catch (\Exception $e) {
            Log::error('APL-01 Update Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return false;
        }
    }

    /**
     * Asesi submit APL-01. Boleh dari draft atau returned.
     */
    public function submitApl01(AplSatu $aplsatu, string $signatureBase64): bool
    {
        // Hanya bisa submit dari draft atau returned
        if (!in_array($aplsatu->status, ['draft', 'returned'])) {
            \Log::warning('[APL01-SERVICE] submitApl01 ditolak — status bukan draft/returned', [
                'status' => $aplsatu->status,
            ]);
            return false;
        }

        try {
            $aplsatu->update([
                'status'               => 'submitted',
                'ttd_pemohon'          => $signatureBase64,
                'tanggal_ttd_pemohon'  => now(),
                'nama_ttd_pemohon'     => $aplsatu->nama_lengkap,
                'submitted_at'         => now(),
            ]);

            \Log::info('[APL01-SERVICE] submitApl01 OK', ['aplsatu_id' => $aplsatu->id]);
            return true;
        } catch (\Throwable $e) {
            \Log::error('[APL01-SERVICE] submitApl01 FAILED', ['error' => $e->getMessage()]);
            return false;
        }
    }


    /**
     * Admin verifikasi APL-01, simpan TTD + nama admin.
     */
    public function verifyApl01(AplSatu $aplsatu, string $signatureBase64, string $namaAdmin = ''): bool
    {
        try {
            $aplsatu->update([
                'status'           => 'verified',
                'ttd_admin'        => $signatureBase64,
                'tanggal_ttd_admin' => now(),
                'nama_ttd_admin'   => $namaAdmin ?: \Auth::user()?->name,
                'verified_at'      => now(),
                'verified_by'      => \Auth::id(),
            ]);

            \Log::info('[APL01-SERVICE] verifyApl01 OK', [
                'aplsatu_id' => $aplsatu->id,
                'nama_admin' => $namaAdmin,
                'verified_by' => \Auth::id(),
            ]);

            return true;
        } catch (\Throwable $e) {
            \Log::error('[APL01-SERVICE] verifyApl01 FAILED', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get default dokumen bukti untuk skema
     */
    public function getDefaultBuktiDokumen(): array
    {
        return [
            'persyaratan_dasar' => [
                'SK Pegawai (minimal 3 tahun)',
                'Ijasah & Transkrip Nilai',
                'Sertifikat Pelatihan',
            ],
            'administratif' => [
                'Foto Copy KTP',
                'Pas Foto 3x4 (3 lembar, latar merah)',
                'Biodata/CV',
            ],
        ];
    }

    /**
     * Initialize bukti dokumen untuk APL-01
     */
    public function initializeBuktiDokumen(AplSatu $aplsatu): void
    {
        $defaults = $this->getDefaultBuktiDokumen();

        foreach ($defaults as $kategori => $dokumenList) {
            foreach ($dokumenList as $dokumen) {
                AplSatuBukti::firstOrCreate([
                    'apl_01_id'    => $aplsatu->id,
                    'kategori'     => $kategori,
                    'nama_dokumen' => $dokumen,
                ], [
                    'status' => 'Tidak Ada',
                ]);
            }
        }
    }
}