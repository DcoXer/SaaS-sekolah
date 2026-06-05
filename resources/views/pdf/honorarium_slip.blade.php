<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a1a; }

        .kop { width: 100%; border-bottom: 3px double #1a1a1a; padding-bottom: 8px; margin-bottom: 12px; }
        .kop table { width: 100%; }
        .kop .logo-cell { width: 70px; vertical-align: middle; }
        .kop .logo { width: 60px; height: 60px; object-fit: contain; }
        .kop .logo-placeholder { width: 60px; height: 60px; display: inline-block; }
        .kop .info-cell { vertical-align: middle; padding-left: 10px; text-align: center; }
        .kop .school-name { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .kop .school-sub  { font-size: 10px; color: #444; margin-top: 2px; }

        h2.title { text-align: center; font-size: 13px; text-transform: uppercase;
                   letter-spacing: 1px; margin-bottom: 4px; }
        .period   { text-align: center; font-size: 11px; color: #555; margin-bottom: 14px; }

        .info-table { width: 100%; margin-bottom: 14px; }
        .info-table td { padding: 3px 6px; font-size: 10.5px; }
        .info-table td:first-child { width: 120px; font-weight: bold; }
        .info-table td:nth-child(2) { width: 10px; }

        .calc-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .calc-table th, .calc-table td { border: 1px solid #aaa; padding: 6px 8px; font-size: 10.5px; }
        .calc-table th { background: #f0f0f0; font-weight: bold; }
        .calc-table .right { text-align: right; }
        .calc-table .total-row td { font-weight: bold; background: #f8f8f8; }

        .status-badge { display: inline-block; padding: 3px 12px; border-radius: 4px;
                        font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .status-paid  { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .status-draft { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; }

        .verify-section  { margin-top: 20px; border-top: 1px solid #d1d5db; padding-top: 10px; }
        .verify-table    { width: 100%; border-collapse: collapse; }
        .verify-left     { width: 80px; vertical-align: middle; }
        .verify-right    { padding-left: 12px; vertical-align: middle; }
        .verify-title    { font-size: 8.5px; font-weight: bold; text-transform: uppercase; color: #6b7280; margin-bottom: 3px; }
        .verify-note     { font-size: 8px; color: #9ca3af; }
        .verify-url      { font-size: 7.5px; font-family: monospace; color: #4b5563; word-break: break-all; margin: 3px 0; }
        .footer-note     { margin-top: 10px; font-size: 8px; text-align: center; color: #aaa; border-top: 1px solid #e5e7eb; padding-top: 6px; }
    </style>
</head>
<body>

{{-- KOP SURAT --}}
<table class="kop">
    <tr>
        <td class="logo-cell">
            @if($logo_base64)
                <img src="data:{{ $logo_mime }};base64,{{ $logo_base64 }}" class="logo" alt="Logo">
            @else
                <span class="logo-placeholder"></span>
            @endif
        </td>
        <td class="info-cell">
            <div class="school-name">{{ $school?->name ?? 'Nama Sekolah' }}</div>
            <div class="school-sub">{{ $school?->address ?? '' }}</div>
            <div class="school-sub">Telp. {{ $school?->phone ?? '-' }} &nbsp;|&nbsp; Email: {{ $school?->email ?? '-' }}</div>
        </td>
    </tr>
</table>

<h2 class="title">Slip Honor Guru</h2>
<div class="period">Periode: {{ $period_label }}</div>

{{-- INFO GURU --}}
<table class="info-table">
    <tr>
        <td>Nama Guru</td><td>:</td>
        <td>{{ $honorarium->teacher->user->name }}</td>
    </tr>
    <tr>
        <td>NIP</td><td>:</td>
        <td>{{ $honorarium->teacher->nip ?? '-' }}</td>
    </tr>
    <tr>
        <td>Jenis Guru</td><td>:</td>
        <td>{{ $honorarium->teacher->type === 'guru_kelas' ? 'Guru Kelas' : 'Guru Bidang' }}</td>
    </tr>
    <tr>
        <td>Tahun Ajaran</td><td>:</td>
        <td>{{ $honorarium->academicYear->name }}</td>
    </tr>
    <tr>
        <td>Status</td><td>:</td>
        <td>
            @if($honorarium->isPaid())
                <span class="status-badge status-paid">Lunas</span>
            @else
                <span class="status-badge status-draft">Belum Dibayar</span>
            @endif
        </td>
    </tr>
    @if($honorarium->isPaid())
    <tr>
        <td>Tanggal Bayar</td><td>:</td>
        <td>{{ $honorarium->paid_at->locale('id')->isoFormat('D MMMM Y') }}</td>
    </tr>
    @endif
</table>

{{-- RINCIAN --}}
<table class="calc-table">
    <thead>
        <tr>
            <th>Komponen</th>
            <th>Keterangan</th>
            <th class="right">Jumlah</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Jam Pelajaran</td>
            <td>{{ $honorarium->teaching_hours }} jam &times; Rp {{ number_format($honorarium->hourly_rate, 0, ',', '.') }}</td>
            <td class="right">Rp {{ number_format($honorarium->teaching_hours_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Uang Transport</td>
            <td>{{ $honorarium->transport_days }} hari hadir &times; Rp {{ number_format($honorarium->daily_transport_rate, 0, ',', '.') }}</td>
            <td class="right">Rp {{ number_format($honorarium->transport_amount, 0, ',', '.') }}</td>
        </tr>
        @if($honorarium->position_allowance > 0)
        <tr>
            <td>Uang Jabatan</td>
            <td>{{ $honorarium->position_name ?? 'Jabatan' }}</td>
            <td class="right">Rp {{ number_format($honorarium->position_allowance, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td colspan="2" style="text-align:right;">Total Honor</td>
            <td class="right">Rp {{ number_format($honorarium->total_amount, 0, ',', '.') }}</td>
        </tr>
    </tbody>
</table>

{{-- QR Verifikasi --}}
<div class="verify-section">
    <table class="verify-table">
        <tr>
            <td class="verify-left">
                @if(!empty($qr_png))
                    <img src="data:image/png;base64,{{ $qr_png }}" style="width:76px; height:76px; display:block;" />
                @endif
            </td>
            <td class="verify-right">
                <div class="verify-title">Verifikasi Keaslian Slip</div>
                <div class="verify-note">Scan QR code atau kunjungi tautan berikut untuk memverifikasi slip honor ini.</div>
                <div class="verify-url">{{ $verify_url }}</div>
                <div class="verify-note">
                    Kode: <strong style="color:#374151;">{{ $honorarium->slip_code }}</strong>
                    @if($honorarium->tuKeuangan)
                        &middot; Diproses oleh: <strong style="color:#374151;">{{ $honorarium->tuKeuangan->name }}</strong>
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="footer-note">Dokumen ini dicetak otomatis oleh sistem manajemen sekolah</div>

</body>
</html>
