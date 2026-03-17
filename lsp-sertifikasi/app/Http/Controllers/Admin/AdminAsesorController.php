<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminAsesorController extends Controller
{
    /**
     * List semua Asesor
     */
    public function index(Request $request)
    {
        $query = Asesor::query();

        // Filter pencarian
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('no_reg_met', 'like', "%{$search}%");
            });
        }

        // Filter status
        if ($status = $request->input('status_reg')) {
            $query->where('status_reg', $status);
        }

        // Filter jenis kelamin
        if ($jk = $request->input('jenis_kelamin')) {
            $query->where('jenis_kelamin', $jk);
        }

        $asesors = $query->orderBy('nama')->get();

        $stats = [
            'total'   => Asesor::count(),
            'aktif'   => Asesor::where('status_reg', 'aktif')->count(),
            'expire'  => Asesor::where('status_reg', 'expire')->count(),
            'nonaktif'=> Asesor::where('status_reg', 'nonaktif')->count(),
        ];

        return view('admin.asesors.index', compact('asesors', 'stats'));
    }

    /**
     * Form tambah Asesor
     */
    public function create()
    {
        return view('admin.asesors.create');
    }

    /**
     * Simpan Asesor baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'          => 'required|string|max:255',
            'nik'           => 'required|string|size:16|unique:asesors,nik',
            'tempat_lahir'  => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before:today',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat'        => 'nullable|string',
            'kota'          => 'nullable|string|max:100',
            'provinsi'      => 'nullable|string|max:100',
            'telepon'       => 'nullable|string|max:20',
            'email'         => 'required|email|unique:asesors,email|unique:users,email',
            'no_reg_met'    => 'nullable|string|max:50',
            'no_blanko'     => 'nullable|string|max:50',
            'siap_kerja'    => 'required|in:Memiliki,Tidak',
            'keterangan'    => 'nullable|string',
            'status_reg'    => 'required|in:aktif,expire,nonaktif',
            'expire_date'   => 'nullable|date|required_if:status_reg,expire',
            'foto'          => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'buat_akun'     => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Upload foto
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('asesors/foto', 'public');
            }

            $userId = null;

            // Buat akun user jika diminta
            if ($request->boolean('buat_akun')) {
                $user = User::create([
                    'name'              => $request->nama,
                    'email'             => $request->email,
                    'password'          => Hash::make('asesor123'),
                    'role'              => 'asesor',
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]);
                $userId = $user->id;
            }

            Asesor::create([
                'nama'          => $request->nama,
                'nik'           => $request->nik,
                'tempat_lahir'  => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'alamat'        => $request->alamat,
                'kota'          => $request->kota,
                'provinsi'      => $request->provinsi,
                'telepon'       => $request->telepon,
                'email'         => $request->email,
                'no_reg_met'    => $request->no_reg_met,
                'no_blanko'     => $request->no_blanko,
                'siap_kerja'    => $request->siap_kerja,
                'keterangan'    => $request->keterangan,
                'status_reg'    => $request->status_reg,
                'expire_date'   => $request->expire_date,
                'foto_path'     => $fotoPath,
                'user_id'       => $userId,
                'is_active'     => true,
            ]);

            DB::commit();

            return redirect()->route('admin.asesors.index')
                ->with('success', 'Asesor ' . $request->nama . ' berhasil ditambahkan!' .
                    ($userId ? ' Akun login dibuat dengan password: asesor123' : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating Asesor: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Detail Asesor (modal AJAX)
     */
    public function show(Asesor $asesor)
    {
        try {
            $asesor->load('user');
            $html = view('admin.asesors.partials.detail', compact('asesor'))->render();
            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Form edit Asesor
     */
    public function edit(Asesor $asesor)
    {
        $asesor->load('user');
        return view('admin.asesors.edit', compact('asesor'));
    }

    /**
     * Update Asesor
     */
    public function update(Request $request, Asesor $asesor)
    {
        $request->validate([
            'nama'          => 'required|string|max:255',
            'nik'           => 'required|string|size:16|unique:asesors,nik,' . $asesor->id,
            'tempat_lahir'  => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before:today',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat'        => 'nullable|string',
            'kota'          => 'nullable|string|max:100',
            'provinsi'      => 'nullable|string|max:100',
            'telepon'       => 'nullable|string|max:20',
            'email'         => 'required|email|unique:asesors,email,' . $asesor->id,
            'no_reg_met'    => 'nullable|string|max:50',
            'no_blanko'     => 'nullable|string|max:50',
            'siap_kerja'    => 'required|in:Memiliki,Tidak',
            'keterangan'    => 'nullable|string',
            'status_reg'    => 'required|in:aktif,expire,nonaktif',
            'expire_date'   => 'nullable|date|required_if:status_reg,expire',
            'foto'          => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            // Upload foto baru
            if ($request->hasFile('foto')) {
                if ($asesor->foto_path) {
                    Storage::disk('public')->delete($asesor->foto_path);
                }
                $fotoPath = $request->file('foto')->store('asesors/foto', 'public');
                $asesor->foto_path = $fotoPath;
            }

            $asesor->update([
                'nama'          => $request->nama,
                'nik'           => $request->nik,
                'tempat_lahir'  => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'alamat'        => $request->alamat,
                'kota'          => $request->kota,
                'provinsi'      => $request->provinsi,
                'telepon'       => $request->telepon,
                'email'         => $request->email,
                'no_reg_met'    => $request->no_reg_met,
                'no_blanko'     => $request->no_blanko,
                'siap_kerja'    => $request->siap_kerja,
                'keterangan'    => $request->keterangan,
                'status_reg'    => $request->status_reg,
                'expire_date'   => $request->expire_date,
                'is_active'     => $request->has('is_active'),
            ]);

            // Sinkronisasi nama ke user terkait
            if ($asesor->user) {
                $asesor->user->update(['name' => $request->nama]);
            }

            DB::commit();

            return redirect()->route('admin.asesors.index')
                ->with('success', 'Data asesor ' . $request->nama . ' berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating Asesor: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Asesor
     */
    public function destroy(Asesor $asesor)
    {
        DB::beginTransaction();
        try {
            // Hapus foto
            if ($asesor->foto_path) {
                Storage::disk('public')->delete($asesor->foto_path);
            }

            // Hapus user terkait jika ada
            if ($asesor->user) {
                $asesor->user->delete();
            }

            $nama = $asesor->nama;
            $asesor->delete();

            DB::commit();

            return redirect()->route('admin.asesors.index')
                ->with('success', "Asesor {$nama} berhasil dihapus.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting Asesor: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Import Asesor dari Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file'      => 'required|file|mimes:xlsx,xls|max:5120',
            'buat_akun' => 'nullable|boolean',
        ]);

        try {
            $buatAkun = $request->boolean('buat_akun');

            $import = new \App\Imports\AsesorImport($buatAkun);
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));

            // Susun pesan sukses
            $msg = "Berhasil mengimport <strong>{$import->importedCount}</strong> asesor";
            if ($buatAkun && $import->importedCount > 0) {
                $msg .= " (akun login dibuat, password default: <code>asesor123</code>)";
            }
            if ($import->skippedCount > 0) {
                $msg .= ". <strong>{$import->skippedCount}</strong> baris dilewati.";
            }

            // Simpan error detail ke session agar bisa ditampilkan
            $sessionData = ['import_success' => $msg];
            if (!empty($import->errors)) {
                $sessionData['import_errors'] = $import->errors;
            }

            return redirect()->route('admin.asesors.index')
                ->with($sessionData);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Asesor Import Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

}