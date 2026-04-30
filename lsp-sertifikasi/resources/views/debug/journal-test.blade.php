{{-- resources/views/debug/journal-test.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal Testing Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    body {
        background: #0f1117;
        color: #e2e8f0;
        font-family: 'Courier New', monospace;
    }

    .card {
        background: #1a1f2e;
        border: 1px solid #2d3748;
    }

    .card-header {
        background: #252d3d;
        border-bottom: 1px solid #2d3748;
    }

    .badge-kode {
        background: #1e3a5f;
        color: #63b3ed;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: .8rem;
    }

    .log-entry {
        background: #0d1117;
        border: 1px solid #2d3748;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 8px;
        font-size: .82rem;
    }

    .log-entry.success {
        border-left: 3px solid #48bb78;
    }

    .log-entry.error {
        border-left: 3px solid #fc8181;
    }

    .debit-col {
        color: #68d391;
    }

    .kredit-col {
        color: #fc8181;
    }

    .akun-kode {
        color: #f6ad55;
        font-weight: bold;
    }

    table {
        color: #e2e8f0 !important;
    }

    th {
        color: #a0aec0 !important;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .btn-test {
        background: #2b4c7e;
        border: 1px solid #4a7fbd;
        color: #fff;
    }

    .btn-test:hover {
        background: #3a6199;
        color: #fff;
    }

    .btn-test-orange {
        background: #7d4e0a;
        border: 1px solid #c87941;
        color: #fff;
    }

    .btn-test-orange:hover {
        background: #9e6215;
        color: #fff;
    }

    hr {
        border-color: #2d3748;
    }

    .section-title {
        color: #63b3ed;
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .1em;
        margin-bottom: 12px;
    }

    .urutan-badge {
        background: #1a3a1a;
        color: #68d391;
        border: 1px solid #276627;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: .72rem;
    }
    </style>
</head>

<body>
    <div class="container-fluid py-4">

        <div class="d-flex align-items-center gap-3 mb-4">
            <div>
                <h4 class="mb-0" style="color:#63b3ed"><i class="bi bi-terminal me-2"></i>Journal Testing Lab</h4>
                <small class="text-muted">SIKAP LSP — Debug & Testing Environment</small>
            </div>
            <span class="badge bg-warning text-dark ms-auto">⚠ Development Only</span>
        </div>

        {{-- Panduan urutan --}}
        <div class="card mb-4" style="border-color:#276627">
            <div class="card-body py-2 px-3" style="font-size:.8rem;color:#a0aec0">
                <strong style="color:#68d391">📋 Urutan trigger yang benar:</strong>
                &nbsp;
                <span class="urutan-badge">1. Honor Dibuat</span>
                →
                <span class="urutan-badge">2. Honor Dibayar</span>
                →
                <span class="urutan-badge">3. Payment Verified</span>
                &nbsp;|&nbsp;
                Selalu <strong>Clear All</strong> sebelum mulai ulang dari awal.
            </div>
        </div>

        @if(session('result'))
        <div class="log-entry {{ session('result.status') }}">
            <div class="d-flex align-items-center gap-2 mb-2">
                @if(session('result.status') === 'success')
                <i class="bi bi-check-circle text-success"></i>
                <strong class="text-success">SUCCESS</strong>
                @else
                <i class="bi bi-x-circle text-danger"></i>
                <strong class="text-danger">ERROR</strong>
                @endif
                <small class="text-muted ms-auto">{{ now()->format('H:i:s') }}</small>
            </div>
            <div>{{ session('result.message') }}</div>
            @if(session('result.detail'))
            <pre class="mt-2 mb-0"
                style="font-size:.75rem;color:#a0aec0;white-space:pre-wrap">{{ session('result.detail') }}</pre>
            @endif
        </div>
        @endif

        <div class="row g-3">

            {{-- Panel Kiri --}}
            <div class="col-lg-5">

                {{-- Scenario 0a: Piutang Asesi Timbul --}}
                <div class="card mb-3" style="border-color:#276627">
                    <div class="card-header" style="background:#0d1f0d">
                        <div class="section-title mb-0" style="color:#68d391">Scenario 0a — Piutang Timbul</div>
                        <small class="text-muted">Dr. 1-003 Piutang Asesi / Cr. 4-001 Pendapatan</small>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('debug.journal.test') }}">
                            @csrf
                            <input type="hidden" name="action" value="piutang_asesi">
                            <div class="mb-2">
                                <label class="form-label small text-muted">Pilih Payment (semua status)</label>
                                <select name="piutang_payment_id"
                                    class="form-select form-select-sm bg-dark text-light border-secondary">
                                    <option value="">— Pilih —</option>
                                    @foreach($payments_semua as $p)
                                    <option value="{{ $p->id }}">
                                        #{{ $p->id }} — {{ $p->asesmen->full_name ?? '-' }}
                                        (Rp {{ number_format($p->amount,0,',','.') }}) [{{ $p->status }}]
                                        {{ \App\Models\JournalEntry::existsFor(\App\Models\Payment::class.'_piutang', $p->id) ? '✅' : '⬜' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-sm w-100" style="background:#276627;color:#fff">
                                <i class="bi bi-play-fill me-1"></i> Buat Jurnal Piutang Timbul
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Scenario 0b: Piutang Lunas --}}
                <div class="card mb-3" style="border-color:#276627">
                    <div class="card-header" style="background:#0d1f0d">
                        <div class="section-title mb-0" style="color:#68d391">Scenario 0b — Piutang Lunas (Payment
                            Verified)</div>
                        <small class="text-muted">Dr. 1-002 Bank / Cr. 1-003 Piutang Asesi</small>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('debug.journal.test') }}">
                            @csrf
                            <input type="hidden" name="action" value="piutang_lunas">
                            <div class="mb-2">
                                <label class="form-label small text-muted">Pilih Payment (status verified)</label>
                                <select name="lunas_payment_id"
                                    class="form-select form-select-sm bg-dark text-light border-secondary">
                                    <option value="">— Pilih —</option>
                                    @foreach($payments as $p)
                                    <option value="{{ $p->id }}">
                                        #{{ $p->id }} — {{ $p->asesmen->full_name ?? '-' }}
                                        (Rp {{ number_format($p->amount,0,',','.') }})
                                        {{ \App\Models\JournalEntry::existsFor(\App\Models\Payment::class, $p->id) ? '✅' : '⬜' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-sm w-100" style="background:#276627;color:#fff">
                                <i class="bi bi-play-fill me-1"></i> Buat Jurnal Piutang Lunas
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Scenario 0c: Piutang Invoice Kolektif Timbul --}}
<div class="card mb-3" style="border-color:#276627">
    <div class="card-header" style="background:#0d1f0d">
        <div class="section-title mb-0" style="color:#68d391">Scenario 0c — Piutang Invoice Kolektif</div>
        <small class="text-muted">Dr. 1-003 Piutang Asesi / Cr. 4-001 Pendapatan</small>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('debug.journal.test') }}">
            @csrf
            <input type="hidden" name="action" value="piutang_invoice">
            <div class="mb-2">
                <label class="form-label small text-muted">Pilih Invoice (status sent)</label>
                <select name="invoice_id" class="form-select form-select-sm bg-dark text-light border-secondary">
                    <option value="">— Pilih —</option>
                    @foreach($invoices_sent as $inv)
                    <option value="{{ $inv->id }}">
                        {{ $inv->invoice_number }} — {{ $inv->tuk->name ?? '-' }}
                        (Rp {{ number_format($inv->total_amount,0,',','.') }})
                        {{ \App\Models\JournalEntry::existsFor(\App\Models\Invoice::class.'_piutang', $inv->id) ? '✅' : '⬜' }}
                    </option>
                    @endforeach
                </select>
                @if($invoices_sent->isEmpty())
                <small class="text-muted">Belum ada invoice dengan status "sent".</small>
                @endif
            </div>
            <button class="btn btn-sm w-100" style="background:#276627;color:#fff">
                <i class="bi bi-play-fill me-1"></i> Buat Jurnal Piutang Invoice
            </button>
        </form>
    </div>
</div>

{{-- Scenario 0d: Angsuran Kolektif Lunas --}}
<div class="card mb-3" style="border-color:#276627">
    <div class="card-header" style="background:#0d1f0d">
        <div class="section-title mb-0" style="color:#68d391">Scenario 0d — Angsuran Kolektif Lunas</div>
        <small class="text-muted">Dr. 1-002 Bank / Cr. 1-003 Piutang Asesi</small>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('debug.journal.test') }}">
            @csrf
            <input type="hidden" name="action" value="piutang_invoice_lunas">
            <div class="mb-2">
                <label class="form-label small text-muted">Pilih Angsuran (status verified)</label>
                <select name="angsuran_id" class="form-select form-select-sm bg-dark text-light border-secondary">
                    <option value="">— Pilih —</option>
                    @foreach($angsuran_pending as $ang)
                    <option value="{{ $ang->id }}">
                        {{ $ang->invoice->invoice_number ?? '-' }} — Angsuran ke-{{ $ang->installment_number }}
                        (Rp {{ number_format($ang->amount,0,',','.') }})
                        {{ \App\Models\JournalEntry::existsFor(\App\Models\CollectivePayment::class, $ang->id) ? '✅' : '⬜' }}
                    </option>
                    @endforeach
                </select>
                @if($angsuran_pending->isEmpty())
                <small class="text-muted">Belum ada angsuran yang verified.</small>
                @endif
            </div>
            <button class="btn btn-sm w-100" style="background:#276627;color:#fff">
                <i class="bi bi-play-fill me-1"></i> Buat Jurnal Angsuran Lunas
            </button>
        </form>
    </div>
</div>
                {{-- Scenario 1: Honor Dibuat --}}
                <div class="card mb-3" style="border-color:#c87941">
                    <div class="card-header" style="background:#2d1f0a">
                        <div class="section-title mb-0" style="color:#f6ad55">Scenario 1 — Honor Dibuat (Pengakuan
                            Utang)</div>
                        <small class="text-muted">Dr. 5-001 Beban Honor / Cr. 2-001 Utang Honor Asesor</small>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('debug.journal.test') }}">
                            @csrf
                            <input type="hidden" name="action" value="honor_dibuat">
                            <div class="mb-2">
                                <label class="form-label small text-muted">Pilih Honor Payment (semua status)</label>
                                <select name="honor_dibuat_id"
                                    class="form-select form-select-sm bg-dark text-light border-secondary">
                                    <option value="">— Pilih —</option>
                                    @foreach($honors_semua as $h)
                                    <option value="{{ $h->id }}">
                                        {{ $h->nomor_kwitansi }} — {{ $h->asesor->nama ?? '-' }}
                                        (Rp {{ number_format($h->total,0,',','.') }})
                                        [{{ $h->status }}]
                                        {{ \App\Models\JournalEntry::existsFor(\App\Models\HonorPayment::class.'_dibuat', $h->id) ? '✅' : '⬜' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-test-orange btn-sm w-100">
                                <i class="bi bi-play-fill me-1"></i> Buat Jurnal Honor Dibuat
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Scenario 2: Honor Dibayar --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="section-title mb-0">Scenario 2 — Honor Dibayar (Pelunasan)</div>
                        <small class="text-muted">Dr. 2-001 Utang Honor / Cr. 1-002 Bank</small>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('debug.journal.test') }}">
                            @csrf
                            <input type="hidden" name="action" value="honor">
                            <div class="mb-2">
                                <label class="form-label small text-muted">Pilih Honor Payment
                                    (sudah_dibayar/dikonfirmasi)</label>
                                <select name="honor_id"
                                    class="form-select form-select-sm bg-dark text-light border-secondary">
                                    <option value="">— Pilih —</option>
                                    @foreach($honors as $h)
                                    <option value="{{ $h->id }}">
                                        {{ $h->nomor_kwitansi }} — {{ $h->asesor->nama ?? '-' }}
                                        (Rp {{ number_format($h->total,0,',','.') }})
                                        {{ \App\Models\JournalEntry::existsFor(\App\Models\HonorPayment::class, $h->id) ? '✅' : '⬜' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-test btn-sm w-100">
                                <i class="bi bi-play-fill me-1"></i> Buat Jurnal Honor Dibayar
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Scenario 3: Payment --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="section-title mb-0">Scenario 3 — Payment Verified</div>
                        <small class="text-muted">Dr. 1-002 Bank / Cr. 4-001 Pendapatan</small>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('debug.journal.test') }}">
                            @csrf
                            <input type="hidden" name="action" value="payment">
                            <div class="mb-2">
                                <label class="form-label small text-muted">Pilih Payment (status verified)</label>
                                <select name="payment_id"
                                    class="form-select form-select-sm bg-dark text-light border-secondary">
                                    <option value="">— Pilih —</option>
                                    @foreach($payments as $p)
                                    <option value="{{ $p->id }}">
                                        #{{ $p->id }} — {{ $p->asesmen->full_name ?? '-' }}
                                        (Rp {{ number_format($p->amount,0,',','.') }})
                                        {{ \App\Models\JournalEntry::existsFor(\App\Models\Payment::class, $p->id) ? '✅' : '⬜' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-test btn-sm w-100">
                                <i class="bi bi-play-fill me-1"></i> Buat Jurnal Payment
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Scenario 4: Biaya Ops --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="section-title mb-0">Scenario 4 — Biaya Operasional</div>
                        <small class="text-muted">Dr. 5-002 Beban Ops / Cr. 1-002 Bank</small>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('debug.journal.test') }}">
                            @csrf
                            <input type="hidden" name="action" value="biaya_ops">
                            <div class="mb-2">
                                <label class="form-label small text-muted">Pilih Biaya Operasional</label>
                                <select name="biaya_id"
                                    class="form-select form-select-sm bg-dark text-light border-secondary">
                                    <option value="">— Pilih —</option>
                                    @foreach($biayaOps as $b)
                                    <option value="{{ $b->id }}">
                                        {{ $b->nomor }} — {{ Str::limit($b->uraian, 30) }}
                                        (Rp {{ number_format($b->total,0,',','.') }})
                                        {{ \App\Models\JournalEntry::existsFor(\App\Models\BiayaOperasional::class, $b->id) ? '✅' : '⬜' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-test btn-sm w-100">
                                <i class="bi bi-play-fill me-1"></i> Buat Jurnal Biaya Ops
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Validasi + Clear --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-6">
                                <form method="POST" action="{{ route('debug.journal.test') }}">
                                    @csrf
                                    <input type="hidden" name="action" value="check_balance">
                                    <div class="mb-1 small text-muted">
                                        {{ $totalEntries }} jurnal, {{ $totalLines }} baris
                                    </div>
                                    <button class="btn btn-test btn-sm w-100">
                                        <i class="bi bi-calculator me-1"></i> Validasi Balance
                                    </button>
                                </form>
                            </div>
                            <div class="col-6">
                                <form method="POST" action="{{ route('debug.journal.test') }}">
                                    @csrf
                                    <input type="hidden" name="action" value="clear_all">
                                    <div class="mb-1 small text-muted">&nbsp;</div>
                                    <button class="btn btn-sm btn-outline-danger w-100"
                                        onclick="return confirm('Hapus SEMUA jurnal?')">
                                        <i class="bi bi-trash"></i> Clear All
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Panel Kanan: Log --}}
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <div class="section-title mb-0">Journal Entries Log</div>
                        <small class="text-muted">{{ $entries->count() }} jurnal terbaru</small>
                    </div>
                    <div class="card-body p-0" style="max-height:700px;overflow-y:auto;">
                        @forelse($entries as $entry)
                        <div class="p-3 border-bottom" style="border-color:#2d3748!important">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div>
                                    <span class="badge-kode">{{ $entry->nomor }}</span>
                                    <span class="ms-2" style="font-size:.85rem;">{{ $entry->keterangan }}</span>
                                </div>
                                <small
                                    class="text-muted flex-shrink-0 ms-2">{{ $entry->tanggal->format('d/m/Y') }}</small>
                            </div>
                            <table class="table table-sm mb-0" style="font-size:.78rem;">
                                <thead>
                                    <tr>
                                        <th style="width:80px">Kode</th>
                                        <th>Nama Akun</th>
                                        <th class="text-end">Debit</th>
                                        <th class="text-end">Kredit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($entry->lines as $line)
                                    <tr>
                                        <td><span class="akun-kode">{{ $line->akun->kode }}</span></td>
                                        <td>{{ $line->akun->nama }}</td>
                                        <td class="text-end debit-col">
                                            {{ $line->debit > 0 ? number_format($line->debit,0,',','.') : '' }}
                                        </td>
                                        <td class="text-end kredit-col">
                                            {{ $line->kredit > 0 ? number_format($line->kredit,0,',','.') : '' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                @php
                                $td = $entry->lines->sum('debit');
                                $tk = $entry->lines->sum('kredit');
                                $bal = $td === $tk;
                                @endphp
                                <tfoot>
                                    <tr>
                                        <td colspan="2" class="text-end" style="color:#a0aec0">
                                            {{ $bal ? '✅ Balance' : '❌ Tidak Balance' }}
                                        </td>
                                        <td class="text-end debit-col fw-bold">{{ number_format($td,0,',','.') }}</td>
                                        <td class="text-end kredit-col fw-bold">{{ number_format($tk,0,',','.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                            @if($entry->ref_type)
                            <small class="text-muted d-block mt-1">
                                ref: {{ class_basename($entry->ref_type) }}#{{ $entry->ref_id }}
                            </small>
                            @endif
                        </div>
                        @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x" style="font-size:2rem"></i><br>
                            Belum ada jurnal.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        {{-- CoA Status --}}
        <div class="card mt-3">
            <div class="card-header">
                <div class="section-title mb-0">Chart of Account Status</div>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0" style="font-size:.8rem;">
                    <thead>
                        <tr>
                            <th style="width:80px">Kode</th>
                            <th>Nama</th>
                            <th>Tipe</th>
                            <th class="text-center">Sistem</th>
                            <th class="text-center">Aktif</th>
                            <th class="text-end">Jml Baris Jurnal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($coas as $coa)
                        <tr>
                            <td><span class="akun-kode">{{ $coa->kode }}</span></td>
                            <td>{{ $coa->nama }}</td>
                            <td><span class="badge bg-{{ $coa->tipe_badge }}">{{ $coa->tipe_label }}</span></td>
                            <td class="text-center">{{ $coa->is_system ? '🔒' : '—' }}</td>
                            <td class="text-center">{{ $coa->is_active ? '✅' : '❌' }}</td>
                            <td class="text-end" style="color:#63b3ed">{{ $coa->lines_count }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>

</html>