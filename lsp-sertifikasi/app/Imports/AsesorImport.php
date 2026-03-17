<?php

namespace App\Imports;

use App\Models\Asesor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AsesorImport implements ToCollection, WithHeadingRow
{
    public int  $importedCount = 0;
    public int  $skippedCount  = 0;
    public array $errors       = [];

    private bool $buatAkun;

    public function __construct(bool $buatAkun = false)
    {
        $this->buatAkun = $buatAkun;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 karena header di baris 1

            try {
                // ── 1. Skip baris kosong ──────────────────────────────────
                $nama = trim((string)($row['nama'] ?? ''));
                if ($nama === '') {
                    continue;
                }

                // ── 2. Ambil email — header "E-mail" jadi key "e-mail" ────
                //    Laravel Excel memakai HeadingRowFormatter yang mengganti
                //    spasi dengan underscore, tapi TIDAK mengganti tanda "-".
                //    Jadi kita coba kedua kemungkinan kunci.
                $email = trim((string)(
                    $row['email']   ??   // kalau header sudah "Email"
                    $row['e-mail']  ??   // header asli "E-mail"
                    $row['e_mail']  ??   // antisipasi variasi lain
                    ''
                ));

                if ($email === '') {
                    $this->errors[]  = "Baris {$rowNumber} ({$nama}): Email kosong, dilewati.";
                    $this->skippedCount++;
                    continue;
                }

                // ── 3. Cek duplikat NIK ───────────────────────────────────
                $nik = trim((string)($row['nik'] ?? ''));
                if ($nik && Asesor::where('nik', $nik)->exists()) {
                    $this->errors[]  = "Baris {$rowNumber} ({$nama}): NIK {$nik} sudah ada, dilewati.";
                    $this->skippedCount++;
                    continue;
                }

                // ── 4. Cek duplikat email ─────────────────────────────────
                if (Asesor::where('email', $email)->exists()) {
                    $this->errors[]  = "Baris {$rowNumber} ({$nama}): Email {$email} sudah ada, dilewati.";
                    $this->skippedCount++;
                    continue;
                }

                // ── 5. Parse tanggal lahir ────────────────────────────────
                $tanggalLahir = null;
                $rawTgl       = $row['tanggal_lahir'] ?? null;
                if ($rawTgl) {
                    try {
                        if (is_numeric($rawTgl)) {
                            // Excel serial date number
                            $tanggalLahir = Carbon::createFromTimestamp(
                                ((int)$rawTgl - 25569) * 86400
                            )->format('Y-m-d');
                        } else {
                            $tanggalLahir = Carbon::parse((string)$rawTgl)->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        $this->errors[] = "Baris {$rowNumber} ({$nama}): Format tanggal lahir tidak valid.";
                        $this->skippedCount++;
                        continue;
                    }
                }

                // ── 6. Jenis kelamin — kolom "P/L" → key "pl" ────────────
                $jkRaw   = strtoupper(trim((string)($row['pl'] ?? $row['jenis_kelamin'] ?? '')));
                $jkValue = in_array($jkRaw, ['L', 'P']) ? $jkRaw : 'L';

                // ── 7. SIAPKerja ──────────────────────────────────────────
                $siapKerja = stripos((string)($row['siapkerja'] ?? ''), 'memiliki') !== false
                    ? 'Memiliki'
                    : 'Tidak';

                // ── 8. Status & keterangan ────────────────────────────────
                $keterangan = trim((string)($row['keterangan'] ?? ''));
                $statusReg  = stripos($keterangan, 'expire') !== false ? 'expire' : 'aktif';

                DB::beginTransaction();

                // ── 9. Buat akun user (opsional) ──────────────────────────
                $userId = null;
                if ($this->buatAkun) {
                    // Cek juga duplikat di tabel users
                    if (User::where('email', $email)->exists()) {
                        $this->errors[] = "Baris {$rowNumber} ({$nama}): Email {$email} sudah ada di tabel users, akun tidak dibuat.";
                        // Lanjut import tanpa akun
                    } else {
                        $user = User::create([
                            'name'              => $nama,
                            'email'             => $email,
                            'password'          => Hash::make('asesor123'),
                            'role'              => 'asesor',
                            'is_active'         => true,
                            'email_verified_at' => now(),
                        ]);
                        $userId = $user->id;
                    }
                }

                // ── 10. Simpan Asesor ─────────────────────────────────────
                Asesor::create([
                    'nama'          => $nama,
                    'nik'           => $nik,
                    'tempat_lahir'  => trim((string)($row['tempat_lahir'] ?? '')),
                    'tanggal_lahir' => $tanggalLahir,
                    'jenis_kelamin' => $jkValue,
                    'alamat'        => trim((string)($row['alamat'] ?? '')),
                    'kota'          => trim((string)($row['kota'] ?? '')),
                    'provinsi'      => trim((string)($row['provinsi'] ?? '')),
                    'telepon'       => trim((string)($row['telepon'] ?? '')),
                    'email'         => $email,
                    'no_reg_met'    => trim((string)($row['no_reg_met'] ?? '')),
                    'no_blanko'     => trim((string)($row['no_blanko'] ?? '')),
                    'siap_kerja'    => $siapKerja,
                    'keterangan'    => $keterangan ?: null,
                    'status_reg'    => $statusReg,
                    'user_id'       => $userId,
                    'is_active'     => true,
                ]);

                DB::commit();
                $this->importedCount++;

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("AsesorImport baris {$rowNumber}: " . $e->getMessage());
                $this->errors[]  = "Baris {$rowNumber}: " . $e->getMessage();
                $this->skippedCount++;
            }
        }
    }
}