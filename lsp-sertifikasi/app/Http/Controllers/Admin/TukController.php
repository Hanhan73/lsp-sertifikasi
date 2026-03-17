<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tuk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TukController extends Controller
{
    public function index()
    {
        $tuks = Tuk::with('user')->withCount('asesmens')->get();

        return view('admin.tuks.index', compact('tuks'));
    }

    public function create()
    {
        return view('admin.tuks.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'          => 'required|unique:tuks',
            'name'          => 'required|string|max:255',
            'address'       => 'required|string',
            'manager_name'  => 'nullable|string|max:255',
            'treasurer_name'=> 'nullable|string|max:255',
            'staff_name'    => 'nullable|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'logo'          => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'sk_document'   => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'email'         => 'required|email|unique:users',
            'password'      => 'required|min:8|confirmed',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'role'      => 'tuk',
                'is_active' => true,
            ]);

            Tuk::create([
                'code'             => $request->code,
                'name'             => $request->name,
                'address'          => $request->address,
                'email'            => $request->email,
                'phone'            => $request->phone,
                'manager_name'     => $request->manager_name,
                'treasurer_name'   => $request->treasurer_name,
                'staff_name'       => $request->staff_name,
                'logo_path'        => $this->uploadFile($request, 'logo', 'logos'),
                'sk_document_path' => $this->uploadFile($request, 'sk_document', 'sk-documents'),
                'user_id'          => $user->id,
                'is_active'        => $request->has('is_active'),
            ]);

            DB::commit();

            return redirect()->route('admin.tuks')
                ->with('success', 'TUK berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[TUK][store] ' . $e->getMessage());

            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(Tuk $tuk)
    {
        $tuk->load('user', 'asesmens');

        return response()->json([
            'success' => true,
            'html'    => view('admin.tuks.partials.detail', compact('tuk'))->render(),
        ]);
    }

    public function edit(Tuk $tuk)
    {
        return view('admin.tuks.edit', compact('tuk'));
    }

    public function update(Request $request, Tuk $tuk)
    {
        $request->validate([
            'code'          => 'required|unique:tuks,code,' . $tuk->id,
            'name'          => 'required|string|max:255',
            'address'       => 'required|string',
            'manager_name'  => 'nullable|string|max:255',
            'treasurer_name'=> 'nullable|string|max:255',
            'staff_name'    => 'nullable|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'logo'          => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'sk_document'   => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        DB::beginTransaction();

        try {
            if ($request->hasFile('logo')) {
                Storage::disk('public')->delete($tuk->logo_path ?? '');
                $tuk->logo_path = $this->uploadFile($request, 'logo', 'logos');
                $tuk->save();
            }

            if ($request->hasFile('sk_document')) {
                Storage::disk('public')->delete($tuk->sk_document_path ?? '');
                $tuk->sk_document_path = $this->uploadFile($request, 'sk_document', 'sk-documents');
                $tuk->save();
            }

            $tuk->update([
                'code'           => $request->code,
                'name'           => $request->name,
                'address'        => $request->address,
                'phone'          => $request->phone,
                'manager_name'   => $request->manager_name,
                'treasurer_name' => $request->treasurer_name,
                'staff_name'     => $request->staff_name,
                'is_active'      => $request->has('is_active'),
            ]);

            $tuk->user?->update(['name' => $request->name]);

            DB::commit();

            return redirect()->route('admin.tuks')
                ->with('success', 'TUK berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[TUK][update] ' . $e->getMessage());

            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(Tuk $tuk)
    {
        $hasActiveAsesi = $tuk->asesmens()
            ->whereIn('status', ['registered', 'data_completed', 'verified', 'paid', 'scheduled'])
            ->exists();

        if ($hasActiveAsesi) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus TUK yang masih memiliki asesi aktif!');
        }

        DB::beginTransaction();

        try {
            Storage::disk('public')->delete(array_filter([
                $tuk->logo_path,
                $tuk->sk_document_path,
            ]));

            $tuk->user?->delete();
            $tuk->delete();

            DB::commit();

            return redirect()->route('admin.tuks')
                ->with('success', 'TUK berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[TUK][destroy] ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // =========================================================
    // PRIVATE
    // =========================================================

    private function uploadFile(Request $request, string $field, string $folder): ?string
    {
        if (!$request->hasFile($field)) {
            return null;
        }

        return $request->file($field)->store($field === 'logo' ? $folder : $folder, 'public');
    }
}