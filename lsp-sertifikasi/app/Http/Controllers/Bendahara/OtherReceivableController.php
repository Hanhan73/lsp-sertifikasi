<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\OtherReceivable;
use App\Services\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OtherReceivableController extends Controller
{
    public function index(Request $request)
    {
        $query = OtherReceivable::with(['coa', 'creator'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('uraian', 'like', '%' . $request->search . '%')
                  ->orWhere('nama_pihak', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        $receivables         = $query->paginate(20)->withQueryString();
        $totalOutstanding    = OtherReceivable::where('status', 'outstanding')->sum('jumlah');
        $totalLunas          = OtherReceivable::where('status', 'lunas')->sum('jumlah_lunas');

        $coaOptions = ChartOfAccount::where('tipe', 'aset')
            ->where('is_active', true)
            ->orderBy('kode')
            ->get();

        // Tambah ini:
        $coaLawanOptions = ChartOfAccount::where('is_active', true)
            ->whereNotIn('tipe', ['aset'])
            ->orderBy('kode')
            ->get();

        $tahunList = OtherReceivable::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()->orderByDesc('tahun')->pluck('tahun');

        return view('bendahara.other-receivables.index', compact(
            'receivables', 'totalOutstanding', 'totalLunas',
            'coaOptions', 'tahunList', 'coaLawanOptions'
        ));
    }

    public function store(Request $request)
    {
        $modeBaru = $request->filled('coa_baru_kode') && $request->filled('coa_baru_nama');

        $request->validate([
            'jenis'         => 'required|in:pinjaman,tagihan',
            'nama_pihak'    => 'required|string|max:255',
            'uraian'        => 'required|string|max:500',
            'jumlah'        => 'required|integer|min:1',
            'tanggal'       => 'required|date',
            'jatuh_tempo'   => 'nullable|date|after_or_equal:tanggal',
            'coa_id'        => $modeBaru ? 'nullable' : 'required|integer|exists:chart_of_accounts,id',
            'bukti'         => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'catatan'       => 'nullable|string|max:1000',
            'coa_baru_kode' => $modeBaru ? 'required|string|max:20|unique:chart_of_accounts,kode' : 'nullable',
            'coa_baru_nama' => $modeBaru ? 'required|string|max:255' : 'nullable',
            'coa_lawan_id' => $request->jenis === 'tagihan' ? 'required|integer|exists:chart_of_accounts,id' : 'nullable',
        ]);

        DB::beginTransaction();
        try {
            $coaId = (int) $request->coa_id;

            if ($modeBaru) {
                $newCoa = ChartOfAccount::create([
                    'kode'      => $request->coa_baru_kode,
                    'nama'      => $request->coa_baru_nama,
                    'tipe'      => 'aset',
                    'sub_tipe'  => 'piutang',
                    'is_active' => true,
                    'is_system' => false,
                    'urutan'    => ChartOfAccount::where('tipe', 'aset')->max('urutan') + 10,
                ]);
                $coaId = $newCoa->id;
            }

            $data = [
                'coa_id'      => $coaId,
                'jenis'       => $request->jenis,
                'nama_pihak'  => $request->nama_pihak,
                'uraian'      => $request->uraian,
                'jumlah'      => (int) $request->jumlah,
                'tanggal'     => $request->tanggal,
                'jatuh_tempo' => $request->jatuh_tempo,
                'catatan'     => $request->catatan,
                'created_by'  => auth()->id(),
                'coa_lawan_id' => $request->jenis === 'tagihan' ? (int)$request->coa_lawan_id : null,

            ];

            if ($request->hasFile('bukti')) {
                $file               = $request->file('bukti');
                $data['bukti_path'] = $file->store('piutang-lainnya/' . now()->format('Y/m'), 'private');
                $data['bukti_name'] = $file->getClientOriginalName();
            }

            $piutang = OtherReceivable::create($data);
            app(JournalService::class)->jurnalPiutangLainnya($piutang->load('coa'));

            DB::commit();
            return redirect()->route('bendahara.other-receivables.index')
                ->with('success', 'Piutang berhasil dicatat dan jurnal telah dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('[OTHER-RECEIVABLE] ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function markLunas(Request $request, OtherReceivable $receivable)
    {
        abort_if($receivable->status === 'lunas', 422, 'Sudah lunas.');

        $request->validate([
            'tanggal_lunas' => 'required|date',
            'jumlah_lunas'  => 'required|numeric|min:1|max:' . $receivable->jumlah,
            'catatan'       => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $receivable->update([
                'status'        => 'lunas',
                'tanggal_lunas' => $request->tanggal_lunas,
                'jumlah_lunas'  => $request->jumlah_lunas,
                'catatan'       => $request->catatan ?? $receivable->catatan,
                'updated_by'    => auth()->id(),
            ]);

            if (!JournalEntry::existsFor(OtherReceivable::class . '_lunas', $receivable->id)) {
                app(JournalService::class)->jurnalPiutangLainnyaLunas($receivable->fresh(['coa']));
            }

            DB::commit();
            return back()->with('success', 'Piutang ditandai lunas dan jurnal pelunasan dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function destroy(OtherReceivable $receivable)
    {
        abort_if(
            JournalEntry::existsFor(OtherReceivable::class, $receivable->id),
            403,
            'Data tidak dapat dihapus karena sudah masuk jurnal keuangan.'
        );

        if ($receivable->bukti_path) {
            Storage::disk('private')->delete($receivable->bukti_path);
        }
        $receivable->delete();

        return back()->with('success', 'Data piutang berhasil dihapus.');
    }

    public function downloadBukti(OtherReceivable $receivable, Request $request)
    {
        $path = $receivable->bukti_path;
        abort_unless($path && Storage::disk('private')->exists($path), 404, 'File tidak ditemukan.');

        $ext     = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $isImage = in_array($ext, ['jpg', 'jpeg', 'png']);

        if ($request->boolean('download')) {
            return Storage::disk('private')->download($path, $receivable->bukti_name);
        }

        return response(Storage::disk('private')->get($path), 200, [
            'Content-Type'        => $isImage ? 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext) : 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $receivable->bukti_name . '"',
        ]);
    }
}