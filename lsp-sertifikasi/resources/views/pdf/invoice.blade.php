<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoiceNumber }}</title>

    <style>
    @page {
        margin: 15px 20px;
        size: A4;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'DejaVu Sans', 'Arial', sans-serif;
        color: #333;
        line-height: 1.5;
        font-size: 11px;
    }

    .container {
        width: 100%;
        max-width: 750px;
        margin: 0 auto;
        padding: 15px;
    }

    .header {
        border-bottom: 3px solid #9cd2ff;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }

    .header-top {
        width: 100%;
        margin-bottom: 10px;
    }

    .company-info {
        float: left;
        width: 58%;
    }

    .invoice-info {
        float: right;
        width: 40%;
        text-align: right;
    }

    .clearfix::after {
        content: "";
        display: table;
        clear: both;
    }

    .company-name {
        font-size: 24px;
        font-weight: bold;
        color: #2196F3;
        margin-bottom: 4px;
    }

    .company-tagline {
        font-size: 10px;
        color: #666;
        margin-bottom: 8px;
    }

    .company-details {
        font-size: 9px;
        color: #666;
        line-height: 1.3;
    }

    .invoice-title {
        font-size: 28px;
        font-weight: bold;
        color: #333;
        margin-bottom: 4px;
    }

    .invoice-number {
        font-size: 12px;
        color: #666;
        word-wrap: break-word;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        background: #28a745;
        color: white;
        border-radius: 15px;
        font-size: 10px;
        font-weight: bold;
        margin-top: 4px;
    }

    .info-section {
        width: 100%;
        margin-bottom: 20px;
    }

    .billing-info,
    .invoice-details {
        float: left;
        width: 48%;
    }

    .billing-info {
        margin-right: 4%;
    }

    .info-title {
        font-size: 10px;
        color: #999;
        text-transform: uppercase;
        margin-bottom: 8px;
        letter-spacing: 0.3px;
    }

    .info-content {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 4px;
        border-left: 3px solid #2196F3;
        font-size: 10px;
        word-wrap: break-word;
    }

    .info-content p {
        margin-bottom: 4px;
        line-height: 1.4;
    }

    .info-content .name {
        font-size: 13px;
        font-weight: bold;
        color: #2196F3;
        margin-bottom: 6px;
        word-wrap: break-word;
    }

    .info-content .label {
        color: #666;
        font-size: 9px;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        table-layout: fixed;
    }

    .items-table thead {
        background: #2196F3;
        color: white;
    }

    .items-table th {
        padding: 8px 6px;
        text-align: left;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .items-table td {
        padding: 8px 6px;
        border-bottom: 1px solid #e0e0e0;
        font-size: 10px;
        vertical-align: top;
        word-wrap: break-word;
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    .summary {
        width: 100%;
        margin-bottom: 20px;
    }

    .summary-row {
        width: 100%;
        margin-bottom: 6px;
    }

    .summary-label {
        float: left;
        width: 65%;
        text-align: right;
        padding-right: 15px;
        font-size: 11px;
        color: #666;
    }

    .summary-value {
        float: right;
        width: 35%;
        text-align: right;
        font-size: 11px;
        font-weight: 600;
    }

    .summary-total {
        border-top: 2px solid #2196F3;
        padding-top: 8px;
        margin-top: 8px;
    }

    .summary-total .summary-label {
        font-size: 14px;
        font-weight: bold;
        color: #333;
    }

    .summary-total .summary-value {
        font-size: 16px;
        font-weight: bold;
        color: #2196F3;
    }

    .payment-info {
        background: #E3F2FD;
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .payment-info h3 {
        font-size: 12px;
        color: #2196F3;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .payment-info-grid {
        width: 100%;
    }

    .payment-info-item {
        float: left;
        width: 32%;
        margin-right: 2%;
        font-size: 10px;
    }

    .payment-info-item:last-child {
        margin-right: 0;
    }

    .payment-info-item .label {
        font-size: 9px;
        color: #666;
        margin-bottom: 2px;
    }

    .payment-info-item .value {
        font-size: 11px;
        font-weight: 600;
        color: #333;
        word-wrap: break-word;
    }

    .notes {
        background: #fff3cd;
        border-left: 3px solid #ffc107;
        padding: 12px;
        margin-bottom: 20px;
        border-radius: 3px;
    }

    .notes h3 {
        font-size: 11px;
        color: #856404;
        margin-bottom: 6px;
        text-transform: uppercase;
    }

    .notes p {
        font-size: 10px;
        color: #856404;
        line-height: 1.4;
    }

    .footer {
        border-top: 2px solid #e0e0e0;
        padding-top: 15px;
        margin-top: 30px;
        text-align: center;
    }

    .footer p {
        font-size: 9px;
        color: #999;
        margin-bottom: 4px;
    }

    .footer .website {
        color: #2196F3;
        font-weight: 600;
    }

    .badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 9px;
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
        padding: 8px 12px;
        border-radius: 4px;
        margin-top: 8px;
        margin-bottom: 15px;
        font-size: 10px;
    }

    .phase-info strong {
        color: #2196F3;
    }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header clearfix">
            <div class="header-top clearfix">
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
        <div class="info-section clearfix">
            <div class="billing-info">
                <div class="info-title">{{ $isCollective ? 'Ditagih Kepada (TUK)' : 'Ditagih Kepada' }}</div>
                <div class="info-content">
                    @if($isCollective)
                    <div class="name">{{ $tuk->name }}</div>
                    <p><span class="label">Email:</span> {{ $tuk->email }}</p>
                    <p><span class="label">Telepon:</span> {{ $tuk->phone ?? '-' }}</p>
                    <p><span class="label">Batch ID:</span> {{ Str::limit($batchId, 30) }}</p>
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
                        {{ $payment->verified_at ? $payment->verified_at->translatedFormat('d F Y') : date('d F Y') }}</p>
                    <p><span class="label">Tanggal Bayar:</span>
                        {{ $payment->verified_at ? $payment->verified_at->translatedFormat('d F Y') : '-' }}</p>
                    <p><span class="label">Metode:</span> {{ strtoupper($payment->method) }}</p>
                    @if($payment->transaction_id)
                    <p><span class="label">Trans ID:</span> {{ Str::limit($payment->transaction_id, 25) }}</p>
                    @endif
                    @if($isCollective)
                    <p><span class="label">Jenis:</span> <span class="badge badge-primary">Kolektif</span></p>
                    @endif
                </div>
            </div>
        </div>

        @if($isCollective && $phase)
        <!-- Phase Information -->
        <div class="phase-info clearfix">
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
                    <th style="width: 5%;">No</th>
                    <th style="width: 35%;">Deskripsi</th>
                    <th style="width: 30%;">Skema</th>
                    <th style="width: 10%;" class="text-center">Qty</th>
                    <th style="width: 20%;" class="text-right">Harga</th>
                </tr>
            </thead>
            <tbody>
                @if($isCollective)
                @foreach($asesmens as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ Str::limit($item->full_name, 25) }}</strong><br>
                        <small style="color: #666;">Sertifikasi Profesi</small>
                    </td>
                    <td>{{ Str::limit($item->skema->name ?? '-', 30) }}</td>
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
                        <small style="color: #666;">{{ Str::limit($asesmen->full_name, 25) }}</small>
                    </td>
                    <td>{{ Str::limit($asesmen->skema->name ?? '-', 30) }}</td>
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
                        <strong>Pelatihan</strong><br>
                        <small style="color: #666;">Program Pelatihan</small>
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
        <div class="summary clearfix">
            @if($isCollective)
            <div class="summary-row clearfix">
                <div class="summary-label">Jumlah Peserta:</div>
                <div class="summary-value">{{ $asesmens->count() }} orang</div>
            </div>
            <div class="summary-row clearfix">
                <div class="summary-label">Harga per Peserta:</div>
                <div class="summary-value">Rp {{ number_format($payment->amount / $asesmens->count(), 0, ',', '.') }}
                </div>
            </div>
            @endif

            <div class="summary-row summary-total clearfix">
                <div class="summary-label">TOTAL PEMBAYARAN:</div>
                <div class="summary-value">Rp {{ number_format($payment->amount, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="payment-info clearfix">
            <h3>Informasi Pembayaran</h3>
            <div class="payment-info-grid clearfix">
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
                    <div class="value">{{ $payment->verified_at ? $payment->verified_at->translatedFormat('d M Y H:i') : '-' }}
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
            <p style="margin-top: 10px; color: #ccc; font-size: 8px;">
                Dicetak pada: {{ now()->translatedFormat('d F Y H:i:s') }}
            </p>
        </div>
    </div>
</body>

</html>