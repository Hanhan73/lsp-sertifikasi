{{-- resources/views/emails/asesor-assignment.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        color: #333;
    }

    .container {
        max-width: 600px;
        margin: 0 auto;
        padding: 20px;
    }

    .header {
        background: linear-gradient(135deg, #889cf1 0%, #dcdadf 100%);
        color: white;
        padding: 30px;
        text-align: center;
        border-radius: 8px 8px 0 0;
    }

    .content {
        background: #f9fafb;
        padding: 30px;
        border: 1px solid #e5e7eb;
    }

    .info-box {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin: 20px 0;
        border-left: 4px solid #667eea;
    }

    .info-row {
        display: flex;
        padding: 8px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .info-label {
        font-weight: bold;
        width: 140px;
        color: #6b7280;
    }

    .info-value {
        flex: 1;
    }

    .button {
        display: inline-block;
        padding: 12px 24px;
        background: #bcc5f0;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        margin-top: 20px;
    }

    .footer {
        text-align: center;
        padding: 20px;
        color: #9ca3af;
        font-size: 12px;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">Penugasan Jadwal Asesmen</h1>
        </div>

        <div class="content">
            <p>Yth. <strong>{{ $asesor->nama }}</strong>,</p>

            <p>
                @if($action === 'reassigned')
                Anda telah <strong>ditugaskan ulang</strong> untuk melaksanakan asesmen dengan detail sebagai berikut:
                @else
                Anda telah <strong>ditugaskan</strong> untuk melaksanakan asesmen dengan detail sebagai berikut:
                @endif
            </p>

            <div class="info-box">
                <div class="info-row">
                    <div class="info-label">Tanggal</div>
                    <div class="info-value">{{ $schedule->assessment_date->translatedFormat('d F Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Waktu</div>
                    <div class="info-value">{{ $schedule->start_time }} - {{ $schedule->end_time }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Lokasi (TUK)</div>
                    <div class="info-value"><strong>{{ $schedule->tuk->name }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Skema</div>
                    <div class="info-value">{{ $schedule->skema->name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Jumlah Asesi</div>
                    <div class="info-value">{{ $schedule->asesmens->count() }} orang</div>
                </div>
            </div>

            {{-- Tampilkan notes hanya jika ada --}}
            @if(!empty($schedule->assignment_notes))
            <div style="background:#fef3c7; padding:15px; border-radius:6px; border-left:4px solid #f59e0b;">
                <strong>📝 Catatan:</strong><br>
                {{ $schedule->assignment_notes }}
            </div>
            @endif

            <p style="margin-top:20px;">
                Mohon untuk mempersiapkan diri dan hadir tepat waktu.
                Jika ada kendala atau pertanyaan, silakan hubungi admin.
            </p>

            <center>
                <a href="{{ url('/') }}" class="button">Lihat Detail di Sistem</a>
            </center>
        </div>

        <div class="footer">
            <p>Email ini dikirim otomatis oleh Sistem LSP KAP</p>
            <p>&copy; {{ date('Y') }} LSP Kantor dan Administrasi Profesional</p>
        </div>
    </div>
</body>

</html>