<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoiceNumber }}</title>

    <style>
    @page {
        margin: 20px 25px;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Arial', sans-serif;
        color: #333;
        line-height: 1.6;
    }

    .container {
        width: 100%;
        margin: 0 auto;
        padding: 20px;
    }

    .header {
        border-bottom: 3px solid #2196F3;
        padding-bottom: 20px;
        margin-bottom: 30px;
    }

    .header-top {
        display: table;
        width: 100%;
        margin-bottom: 10px;
    }

    .company-info {
        display: table-cell;
        vertical-align: top;
        width: 60%;
    }

    .invoice-info {
        display: table-cell;
        vertical-align: top;
        width: 40%;
        text-align: right;
    }

    .company-name {
        font-size: 28px;
        font-weight: bold;
        color: #2196F3;
        margin-bottom: 5px;
    }

    .company-tagline {
        font-size: 12px;
        color: #666;
        margin-bottom: 10px;
    }

    .company-details {
        font-size: 11px;
        color: #666;
        line-height: 1.4;
    }

    .invoice-title {
        font-size: 32px;
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }

    .invoice-number {
        font-size: 14px;
        color: #666;
    }

    .status-badge {
        display: inline-block;
        padding: 5px 15px;
        background: #28a745;
        color: white;
        border-radius: 20px;
        font-size: 11px;
        font-weight: bold;
        margin-top: 5px;
    }

    .info-section {
        display: table;
        width: 100%;
        margin-bottom: 30px;
    }

    .billing-info,
    .invoice-details {
        display: table-cell;
        vertical-align: top;
        width: 50%;
    }

    .billing-info {
        padding-right: 20px;
    }

    .info-title {
        font-size: 12px;
        color: #999;
        text-transform: uppercase;
        margin-bottom: 10px;
        letter-spacing: 0.5px;
    }

    .info-content {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        border-left: 3px solid #2196F3;
    }

    .info-content p {
        margin-bottom: 5px;
        font-size: 13px;
    }

    .info-content .name {
        font-size: 16px;
        font-weight: bold;
        color: #2196F3;
        margin-bottom: 8px;
    }

    .info-content .label {
        color: #666;
        font-size: 11px;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
    }

    .items-table thead {
        background: #2196F3;
        color: white;
    }

    .items-table th {
        padding: 12px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .items-table td {
        padding: 12px;
        border-bottom: 1px solid #e0e0e0;
        font-size: 13px;
    }

    .items-table tbody tr:hover {
        background: #f8f9fa;
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    .summary {
        width: 100%;
        margin-bottom: 30px;
    }

    .summary-row {
        display: table;
        width: 100%;
        margin-bottom: 8px;
    }

    .summary-label {
        display: table-cell;
        text-align: right;
        padding-right: 20px;
        font-size: 13px;
        color: #666;
        width: 70%;
    }

    .summary-value {
        display: table-cell;
        text-align: right;
        font-size: 13px;
        font-weight: 600;
        width: 30%;
    }

    .summary-total {
        border-top: 2px solid #2196F3;
        padding-top: 10px;
        margin-top: 10px;
    }

    .summary-total .summary-label {
        font-size: 16px;
        font-weight: bold;
        color: #333;
    }

    .summary-total .summary-value {
        font-size: 18px;
        font-weight: bold;
        color: #2196F3;
    }

    .payment-info {
        background: #E3F2FD;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 30px;
    }

    .payment-info h3 {
        font-size: 14px;
        color: #2196F3;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .payment-info-grid {
        display: table;
        width: 100%;
    }

    .payment-info-item {
        display: table-cell;
        width: 33.33%;
        padding-right: 15px;
    }

    .payment-info-item .label {
        font-size: 11px;
        color: #666;
        margin-bottom: 3px;
    }

    .payment-info-item .value {
        font-size: 13px;
        font-weight: 600;
        color: #333;
    }

    .notes {
        background: #fff3cd;
        border-left: 3px solid #ffc107;
        padding: 15px;
        margin-bottom: 30px;
        border-radius: 3px;
    }

    .notes h3 {
        font-size: 12px;
        color: #856404;
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    .notes p {
        font-size: 12px;
        color: #856404;
        line-height: 1.5;
    }

    .footer {
        border-top: 2px solid #e0e0e0;
        padding-top: 20px;
        margin-top: 40px;
        text-align: center;
    }

    .footer p {
        font-size: 11px;
        color: #999;
        margin-bottom: 5px;
    }

    .footer .website {
        color: #2196F3;
        font-weight: 600;
    }

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-primary {
        background: #E3F2FD;
        color: #2196F3;
    }

    .badge-success {
        background: #d4edda;
        color: #28a745;
    }

    .badge-warning {
        background: #fff3cd;
        color: #856404;
    }

    .phase-info {
        background: #f8f9fa;
        padding: 10px 15px;
        border-radius: 5px;
        margin-top: 10px;
        font-size: 12px;
    }

    .phase-info strong {
        color: #2196F3;
    }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div class="company-info">
                    <div class="company-name">SIKAP LSP</div>
                    <div class="company-tagline">Sistem Informasi Kompetensi dan Asesmen Profesi</div>
                    <div class="company-details">
                        Lembaga Sertifikasi Profesi<br>
                        Email: info@sikaplsp.id | Phone: +62 21 1234 5678<br>
                        Jakarta, Indonesia
                    </div>
                </div>
                <div class="invoice-info">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-number">#{{ $invoiceNumber }}</div>
                    <div class="status-badge">{{ strtoupper($payment->status_label) }}</div>
                </div>
            </div>
        </div>

        <!-- Invoice & Billing Info -->
        <div class="info-section">
            <div class="billing-info">
                <div class="info-title">{{ $isCollective ? 'Ditagih Kepada (TUK)' : 'Ditagih Kepada' }}</div>
                <div class="info-content">
                    @if($isCollective)
                    <div class="name">{{ $tuk->name }}</div>
                    <p><span class="label">Email:</span> {{ $tuk->email }}</p>
                    <p><span class="label">Telepon:</span> {{ $tuk->phone ?? '-' }}</p>
                    <p><span class="label">Batch ID:</span> {{ $batchId }}</p>
                    @else
                    <div class="name">{{ $asesmen->full_name }}</div>
                    <p><span class="label">Email:</span> {{ $asesmen->email ?? $asesmen->user->email }}</p>
                    <p><span class="label">Telepon:</span> {{ $asesmen->phone ?? '-' }}</p>
                    <p><span class="label">No. Registrasi:</span> #{{ $asesmen->id }}</p>
                    @endif
                </div>
            </div>
            <div class="invoice-details">
                <div class="info-title">Detail Invoice</div>
                <div class="info-content">
                    <p><span class="label">Tanggal Invoice:</span>
                        {{ $payment->verified_at ? $payment->verified_at->format('d F Y') : date('d F Y') }}</p>
                    <p><span class="label">Tanggal Pembayaran:</span>
                        {{ $payment->verified_at ? $payment->verified_at->format('d F Y') : '-' }}</p>
                    <p><span class="label">Metode Pembayaran:</span> {{ strtoupper($payment->method) }}</p>
                    @if($payment->transaction_id)
                    <p><span class="label">Transaction ID:</span> {{ $payment->transaction_id }}</p>
                    @endif
                    @if($isCollective)
                    <p><span class="label">Jenis:</span> <span class="badge badge-primary">Pembayaran Kolektif</span>
                    </p>
                    @endif
                </div>
            </div>
        </div>

        @if($isCollective && $phase)
        <!-- Phase Information -->
        <div class="phase-info">
            <strong>Fase Pembayaran:</strong>
            @if($phase === 'full')
            Pembayaran Penuh (100%)
            @elseif($phase === 'phase_1')
            Fase 1 - Pembayaran Awal (50%)
            @else
            Fase 2 - Pelunasan (50%)
            @endif
        </div>
        @endif

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="40%">Deskripsi</th>
                    <th width="25%">Skema Sertifikasi</th>
                    <th width="10%" class="text-center">Qty</th>
                    <th width="20%" class="text-right">Harga</th>
                </tr>
            </thead>
            <tbody>
                @if($isCollective)
                @foreach($asesmens as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->full_name }}</strong><br>
                        <small style="color: #666;">Sertifikasi Profesi</small>
                    </td>
                    <td>{{ $item->skema->name ?? '-' }}</td>
                    <td class="text-center">1</td>
                    <td class="text-right">Rp {{ number_format($payment->amount / $asesmens->count(), 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td class="text-center">1</td>
                    <td>
                        <strong>Sertifikasi Profesi</strong><br>
                        <small style="color: #666;">{{ $asesmen->full_name }}</small>
                    </td>
                    <td>{{ $asesmen->skema->name ?? '-' }}</td>
                    <td class="text-center">1</td>
                    <td class="text-right">
                        @php
                        $certFee = $asesmen->training_flag ? ($payment->amount - 1500000) : $payment->amount;
                        @endphp
                        Rp {{ number_format($certFee, 0, ',', '.') }}
                    </td>
                </tr>
                @if($asesmen->training_flag)
                <tr>
                    <td class="text-center">2</td>
                    <td>
                        <strong>Pelatihan Sertifikasi</strong><br>
                        <small style="color: #666;">Program Pelatihan Kompetensi</small>
                    </td>
                    <td>-</td>
                    <td class="text-center">1</td>
                    <td class="text-right">Rp {{ number_format(1500000, 0, ',', '.') }}</td>
                </tr>
                @endif
                @endif
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary">
            @if($isCollective)
            <div class="summary-row">
                <div class="summary-label">Jumlah Peserta:</div>
                <div class="summary-value">{{ $asesmens->count() }} orang</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Harga per Peserta:</div>
                <div class="summary-value">Rp {{ number_format($payment->amount / $asesmens->count(), 0, ',', '.') }}
                </div>
            </div>
            @endif

            <div class="summary-row summary-total">
                <div class="summary-label">TOTAL PEMBAYARAN:</div>
                <div class="summary-value">Rp {{ number_format($payment->amount, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="payment-info">
            <h3>Informasi Pembayaran</h3>
            <div class="payment-info-grid">
                <div class="payment-info-item">
                    <div class="label">Status</div>
                    <div class="value">{{ $payment->status_label }}</div>
                </div>
                <div class="payment-info-item">
                    <div class="label">Metode</div>
                    <div class="value">{{ strtoupper($payment->method) }}</div>
                </div>
                <div class="payment-info-item">
                    <div class="label">Tanggal Verifikasi</div>
                    <div class="value">{{ $payment->verified_at ? $payment->verified_at->format('d M Y H:i') : '-' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($payment->notes)
        <div class="notes">
            <h3>Catatan</h3>
            <p>{{ $payment->notes }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>Terima kasih atas kepercayaan Anda menggunakan layanan SIKAP LSP</strong></p>
            <p>Invoice ini dicetak secara otomatis dan sah tanpa tanda tangan</p>
            <p>Untuk pertanyaan, hubungi: <span class="website">info@sikaplsp.id</span></p>
            <p style="margin-top: 15px; color: #ccc; font-size: 10px;">
                Dicetak pada: {{ now()->format('d F Y H:i:s') }}
            </p>
        </div>
    </div>
</body>

</html>