{{-- resources/views/asesor/schedule/soal-progress.blade.php --}}
{{--
    Komponen partial: monitor progress pengerjaan soal semua asesi.
    Di-include dari asesor.schedule.detail
    
    Variables dibutuhkan:
    - $schedule (with asesmens, distribusiSoalTeori, distribusiSoalObservasi, dll)
    - $asesor
--}}

@php
    $distribusiTeori     = $schedule->distribusiSoalTeori;
    $distribusiObservasi = $schedule->distribusiSoalObservasi;
    $asesmens            = $schedule->asesmens;
@endphp

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-graph-up text-primary"></i>
        <h6 class="fw-bold mb-0">Monitor Progress Soal</h6>
        <span class="badge bg-secondary ms-auto">{{ $asesmens->count() }} asesi</span>
    </div>

    {{-- Tab: Teori | Observasi --}}
    <div class="card-header pb-0 pt-0 border-0">
        <ul class="nav nav-tabs" id="progressTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#progress-teori">
                    <i class="bi bi-journal-text me-1"></i> Soal Teori
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#progress-observasi">
                    <i class="bi bi-eye me-1"></i> Observasi
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#bank-soal-tab">
                    <i class="bi bi-database me-1"></i> Bank Soal
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content">

        {{-- ── TAB: SOAL TEORI ── --}}
        <div class="tab-pane fade show active p-0" id="progress-teori">
            @if(!$distribusiTeori)
            <div class="text-center py-4 text-muted">
                <i class="bi bi-exclamation-circle" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem"></i>
                <p class="mb-0 fw-semibold">Soal teori belum didistribusikan</p>
            </div>
            @else
            {{-- Info distribusi --}}
            <div class="px-4 py-3 border-bottom bg-light d-flex flex-wrap gap-4" style="font-size:.82rem">
                <span><i class="bi bi-collection me-1 text-muted"></i>{{ $distribusiTeori->jumlah_soal }} soal/asesi</span>
                <span><i class="bi bi-clock me-1 text-muted"></i>{{ $distribusiTeori->durasi_menit }} menit</span>
                <span><i class="bi bi-people me-1 text-muted"></i>{{ $asesmens->count() }} peserta</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Nama Asesi</th>
                            <th class="text-center">Dijawab</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Dimulai</th>
                            <th class="text-center">Disubmit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($asesmens as $i => $asesmen)
                        @php
                            $soalAsesi    = $asesmen->soalTeoriAsesi ?? collect();
                            $total        = $soalAsesi->count();
                            $answered     = $soalAsesi->whereNotNull('jawaban')->count();
                            $submitted    = $soalAsesi->whereNotNull('submitted_at')->count() > 0;
                            $started      = $soalAsesi->whereNotNull('started_at')->count() > 0;
                            $startedAt    = $soalAsesi->whereNotNull('started_at')->min('started_at');
                            $submittedAt  = $soalAsesi->whereNotNull('submitted_at')->max('submitted_at');
                            $pct          = $total > 0 ? round($answered / $total * 100) : 0;
                        @endphp
                        <tr>
                            <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                            <td>
                                <div class="fw-semibold" style="font-size:.875rem">{{ $asesmen->full_name }}</div>
                                <small class="text-muted">{{ $asesmen->user->email ?? '-' }}</small>
                            </td>
                            <td class="text-center" style="min-width:120px">
                                @if($total > 0)
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px">
                                        <div class="progress-bar {{ $submitted ? 'bg-success' : 'bg-primary' }}"
                                             style="width:{{ $pct }}%"></div>
                                    </div>
                                    <span style="font-size:.75rem;color:#6b7280;min-width:40px">
                                        {{ $answered }}/{{ $total }}
                                    </span>
                                </div>
                                @else
                                <span class="text-muted" style="font-size:.78rem">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($total === 0)
                                <span class="badge bg-light text-muted border" style="font-size:.7rem">Belum Ada Soal</span>
                                @elseif($submitted)
                                <span class="badge bg-success" style="font-size:.7rem">
                                    <i class="bi bi-check-circle me-1"></i>Disubmit
                                </span>
                                @elseif($started)
                                <span class="badge bg-warning text-dark" style="font-size:.7rem">
                                    <i class="bi bi-pencil me-1"></i>Sedang Mengerjakan
                                </span>
                                @else
                                <span class="badge bg-secondary" style="font-size:.7rem">Belum Mulai</span>
                                @endif
                            </td>
                            <td class="text-center" style="font-size:.78rem;color:#6b7280">
                                {{ $startedAt ? \Carbon\Carbon::parse($startedAt)->format('H:i') : '—' }}
                            </td>
                            <td class="text-center" style="font-size:.78rem;color:#6b7280">
                                {{ $submittedAt ? \Carbon\Carbon::parse($submittedAt)->format('H:i') : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- ── TAB: OBSERVASI ── --}}
        <div class="tab-pane fade p-0" id="progress-observasi">
            @if($distribusiObservasi->isEmpty())
            <div class="text-center py-4 text-muted">
                <i class="bi bi-exclamation-circle" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem"></i>
                <p class="mb-0 fw-semibold">Soal observasi belum didistribusikan</p>
            </div>
            @else
            @foreach($distribusiObservasi as $dist)
            @php $obs = $dist->soalObservasi; @endphp
            <div class="border-bottom px-4 py-3">
                <div class="fw-semibold mb-2" style="font-size:.875rem">
                    <i class="bi bi-file-earmark-pdf text-danger me-2"></i>{{ $obs->judul }}
                    <span class="text-muted fw-normal">({{ $obs->paket->count() }} paket)</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Asesi</th>
                                @foreach($obs->paket as $paket)
                                <th class="text-center">Paket {{ $paket->kode_paket }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($asesmens as $asesmen)
                            <tr>
                                <td style="font-size:.82rem">{{ $asesmen->full_name }}</td>
                                @foreach($obs->paket as $paket)
                                @php
                                    $jawaban = $asesmen->jawabanObservasi
                                        ->where('paket_soal_observasi_id', $paket->id)
                                        ->first();
                                @endphp
                                <td class="text-center">
                                    @if($jawaban?->hasLink())
                                    <a href="{{ $jawaban->gdrive_link }}" target="_blank"
                                       class="badge bg-success text-decoration-none" style="font-size:.7rem">
                                        <i class="bi bi-check-circle me-1"></i>Upload
                                    </a>
                                    @else
                                    <span class="badge bg-light text-muted border" style="font-size:.7rem">—</span>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
            @endif
        </div>

        {{-- ── TAB: BANK SOAL (read-only) ── --}}
        <div class="tab-pane fade p-4" id="bank-soal-tab">
            <div class="row g-4">
                {{-- Soal Teori --}}
                <div class="col-md-6">
                    <h6 class="fw-bold mb-2">
                        <i class="bi bi-journal-text text-primary me-2"></i>
                        Bank Soal Teori
                        @if($distribusiTeori)
                        <span class="badge bg-primary ms-1" style="font-size:.65rem">{{ $distribusiTeori->jumlah_soal }} soal/asesi</span>
                        @endif
                    </h6>
                    @php
                        $bankTeori = \App\Models\SoalTeori::where('skema_id', $schedule->skema_id)
                            ->latest()->take(10)->get();
                        $totalTeori = \App\Models\SoalTeori::where('skema_id', $schedule->skema_id)->count();
                    @endphp
                    @if($bankTeori->isEmpty())
                    <p class="text-muted" style="font-size:.82rem">Belum ada soal teori untuk skema ini.</p>
                    @else
                    <div class="d-flex flex-column gap-2">
                        @foreach($bankTeori as $i => $s)
                        <div class="border rounded-3 px-3 py-2 bg-light">
                            <div style="font-size:.8rem;font-weight:600">
                                {{ $i + 1 }}. {{ Str::limit($s->pertanyaan, 100) }}
                            </div>
                            <div style="font-size:.72rem;color:#6b7280">
                                A. {{ Str::limit($s->pilihan_a, 30) }} &bull;
                                B. {{ Str::limit($s->pilihan_b, 30) }}
                                <span class="ms-2 badge bg-success-subtle text-success">
                                    Jwb: {{ strtoupper($s->jawaban_benar) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                        @if($totalTeori > 10)
                        <p class="text-muted mb-0" style="font-size:.78rem">
                            + {{ $totalTeori - 10 }} soal lainnya di bank soal...
                        </p>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Soal Observasi --}}
                <div class="col-md-6">
                    <h6 class="fw-bold mb-2">
                        <i class="bi bi-eye text-primary me-2"></i>Soal Observasi
                    </h6>
                    @php
                        $observasiList = \App\Models\SoalObservasi::with('paket')
                            ->where('skema_id', $schedule->skema_id)->get();
                    @endphp
                    @if($observasiList->isEmpty())
                    <p class="text-muted" style="font-size:.82rem">Belum ada soal observasi.</p>
                    @else
                    <div class="d-flex flex-column gap-2">
                        @foreach($observasiList as $obs)
                        <div class="border rounded-3 p-3 bg-light">
                            <div class="fw-semibold" style="font-size:.82rem">{{ $obs->judul }}</div>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @foreach($obs->paket as $p)
                                <span class="badge bg-primary-subtle text-primary" style="font-size:.7rem">
                                    Paket {{ $p->kode_paket }}
                                </span>
                                @endforeach
                                @if($obs->paket->isEmpty())
                                <span class="badge bg-warning text-dark" style="font-size:.7rem">Belum ada paket</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- /tab-content --}}
</div>