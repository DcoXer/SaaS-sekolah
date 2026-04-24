<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Raport {{ $student->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10.5px;
            color: #111;
            background: #fff;
        }

        .page { padding: 24px 32px 20px; }

        /* ── Header ── */
        .header-wrap {
            width: 100%;
            border-bottom: 3px double #111;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header-wrap td { vertical-align: middle; }
        .header-logo { width: 72px; text-align: center; }
        .header-logo img { width: 64px; height: 64px; object-fit: contain; }
        .header-logo-ph {
            width: 64px; height: 64px;
            border: 1px solid #ccc; border-radius: 50%;
            font-size: 8px; color: #aaa; text-align: center; padding-top: 24px;
        }
        .header-stamp { width: 72px; text-align: center; }
        .header-stamp img { width: 64px; height: 64px; object-fit: contain; }
        .header-center { text-align: center; padding: 0 8px; }
        .hc-supra  { font-size: 9px; font-weight: 700; letter-spacing: 0.3px; }
        .hc-name   { font-size: 17px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-top: 1px; }
        .hc-npsn   { font-size: 8.5px; color: #444; margin-top: 1px; }
        .hc-addr   { font-size: 8.5px; color: #444; margin-top: 1px; }
        .hc-title  { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-top: 6px; }
        .hc-sub    { font-size: 9px; color: #555; margin-top: 2px; }

        /* ── Info Siswa ── */
        .info-wrap {
            width: 100%;
            border: 1px solid #bbb;
            margin-bottom: 10px;
        }
        .info-wrap td {
            width: 50%;
            vertical-align: top;
            padding: 6px 12px;
            font-size: 10px;
        }
        .info-wrap td:first-child { border-right: 1px solid #bbb; }
        .info-row { margin-bottom: 3px; }
        .info-key { display: inline-block; width: 80px; color: #333; }
        .info-colon { display: inline-block; width: 8px; }
        .info-val { font-weight: 600; }

        /* ── Section title ── */
        .sec-title {
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 4px;
        }
        .kkm-line {
            font-size: 9.5px;
            margin-bottom: 8px;
        }

        /* ── Tabel Nilai ── */
        table.nilai {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 10px;
        }
        table.nilai th, table.nilai td {
            border: 1px solid #bbb;
            padding: 5px 7px;
            vertical-align: middle;
        }
        table.nilai thead tr:first-child th {
            background: #1e3a5f;
            color: #fff;
            text-align: center;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        table.nilai thead tr:last-child th {
            background: #2d5a9e;
            color: #fff;
            text-align: center;
            font-size: 9px;
            font-weight: 600;
        }
        table.nilai tbody tr:nth-child(even) { background: #f5f8ff; }
        table.nilai tbody tr:nth-child(odd)  { background: #fff; }
        table.nilai tbody td { color: #222; }
        table.nilai .center { text-align: center; }
        table.nilai .no-col { width: 28px; }
        table.nilai .score-col { width: 52px; }
        table.nilai .pred-col  { width: 62px; }
        .score-val { font-weight: 700; font-size: 11px; }
        .predicate {
            display: inline-block;
            padding: 1px 8px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 9.5px;
        }
        .pred-a { background: #d1fae5; color: #065f46; }
        .pred-b { background: #dbeafe; color: #1d4ed8; }
        .pred-c { background: #fef3c7; color: #92400e; }
        .pred-d { background: #fee2e2; color: #991b1b; }
        .narrative { font-size: 9px; color: #555; font-style: italic; }
        .total-row td { font-weight: 700; background: #eef2ff !important; }

        /* ── KKM / Predikat legend ── */
        table.kkm-legend {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 9.5px;
        }
        table.kkm-legend td {
            border: 1px solid #bbb;
            padding: 4px 8px;
            text-align: center;
        }
        table.kkm-legend td:first-child {
            text-align: left;
            font-weight: 700;
            width: 100px;
        }

        /* ── Catatan ── */
        .notes-outer { width: 100%; margin-bottom: 14px; }
        .notes-outer td { width: 50%; vertical-align: top; }
        .notes-outer td:first-child { padding-right: 5px; }
        .notes-outer td:last-child  { padding-left: 5px; }
        .note-box { border: 1px solid #bbb; }
        .note-header {
            background: #f1f5f9;
            padding: 4px 10px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #555;
        }
        .note-body {
            padding: 7px 10px;
            font-size: 10px;
            color: #333;
            min-height: 42px;
        }

        /* ── Tanda Tangan ── */
        .sig-wrap { width: 100%; margin-bottom: 10px; }
        .sig-wrap td { vertical-align: top; text-align: center; }
        .sig-date { text-align: right; font-size: 10px; margin-bottom: 6px; }
        .sig-label { font-size: 10px; color: #333; margin-bottom: 50px; }
        .sig-line  { border-top: 1px solid #555; padding-top: 3px; display: inline-block; min-width: 130px; }
        .sig-name  { font-size: 10.5px; font-weight: 700; }
        .sig-sub   { font-size: 9px; color: #666; margin-top: 1px; }

        /* ── QR & Verifikasi ── */
        .verify-wrap {
            width: 100%;
            border: 1px dashed #bbb;
            border-radius: 4px;
            background: #fafafa;
            margin-bottom: 10px;
        }
        .verify-wrap td { vertical-align: middle; padding: 8px 12px; }
        .verify-qr-td { width: 110px; }
        .verify-qr-td img { width: 96px; height: 96px; }
        .verify-label {
            font-size: 8.5px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.5px;
            color: #888; margin-bottom: 3px;
        }
        .verify-code { font-size: 9.5px; font-weight: 700; color: #1e3a5f; margin-bottom: 3px; word-break: break-all; }
        .verify-url  { font-size: 8px; color: #555; word-break: break-all; margin-bottom: 3px; }
        .verify-hint { font-size: 8px; color: #888; font-style: italic; }

        /* ── Footer ── */
        .footer-wrap { width: 100%; border-top: 1px solid #ccc; padding-top: 5px; }
        .footer-wrap td { font-size: 8.5px; color: #888; vertical-align: middle; }
        .footer-right { text-align: right; }
    </style>
</head>
<body>
<div class="page">

    {{-- ══ HEADER ══ --}}
    <table class="header-wrap" cellpadding="0" cellspacing="0">
        <tr>
            <td class="header-logo">
                @if($schoolSetting?->logo)
                    <img src="{{ storage_path('app/public/' . $schoolSetting->logo) }}" alt="Logo">
                @else
                    <div class="header-logo-ph">Logo</div>
                @endif
            </td>
            <td class="header-center">
                <div class="hc-supra">KEMENTERIAN AGAMA REPUBLIK INDONESIA</div>
                <div class="hc-name">{{ $schoolSetting?->name ?? 'NAMA MADRASAH' }}</div>
                @if($schoolSetting?->npsn)
                    <div class="hc-npsn">NPSN: {{ $schoolSetting->npsn }}</div>
                @endif
                @if($schoolSetting?->address)
                    <div class="hc-addr">{{ $schoolSetting->address }}</div>
                @endif
                @if($schoolSetting?->phone || $schoolSetting?->email)
                    <div class="hc-addr">
                        {{ $schoolSetting?->phone }}{{ $schoolSetting?->phone && $schoolSetting?->email ? '  |  ' : '' }}{{ $schoolSetting?->email }}
                    </div>
                @endif
                <div class="hc-title">Laporan Hasil Belajar Siswa</div>
                <div class="hc-sub">Semester {{ $reportCard->semester }} &mdash; Tahun Pelajaran {{ $academicYear?->name ?? '—' }}</div>
            </td>
            <td class="header-stamp">
                @if($schoolSetting?->stamp)
                    <img src="{{ storage_path('app/public/' . $schoolSetting->stamp) }}" alt="Stempel">
                @endif
            </td>
        </tr>
    </table>

    {{-- ══ INFO SISWA ══ --}}
    <table class="info-wrap" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="info-row">
                    <span class="info-key">NAMA</span><span class="info-colon">:</span>
                    <span class="info-val">{{ $student->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">NIS</span><span class="info-colon">:</span>
                    <span class="info-val">{{ $student->nis }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Jenis Kelamin</span><span class="info-colon">:</span>
                    <span class="info-val">{{ $student->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</span>
                </div>
            </td>
            <td>
                <div class="info-row">
                    <span class="info-key">Madrasah</span><span class="info-colon">:</span>
                    <span class="info-val">{{ $schoolSetting?->name ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Kelas/Semester</span><span class="info-colon">:</span>
                    <span class="info-val">{{ $classroom->name }} / {{ $reportCard->semester }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Tahun Pelajaran</span><span class="info-colon">:</span>
                    <span class="info-val">{{ $academicYear?->name ?? '—' }}</span>
                </div>
            </td>
        </tr>
    </table>

    {{-- ══ JUDUL & KKM ══ --}}
    <div class="sec-title">Capaian Hasil Belajar</div>
    @php
        $kkm = $predicateConfigs->where('grade', 'C')->first()?->min_score
            ?? $predicateConfigs->sortBy('min_score')->skip(1)->first()?->min_score;
    @endphp
    @if($kkm)
        <div class="kkm-line">Kriteria Ketuntasan Minimal = {{ $kkm }}</div>
    @else
        <div class="kkm-line">&nbsp;</div>
    @endif

    {{-- ══ TABEL NILAI ══ --}}
    <table class="nilai" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th rowspan="2" class="no-col">No</th>
                <th rowspan="2" style="text-align:left;">Mata Pelajaran</th>
                <th colspan="2">Pengetahuan (KI 3)</th>
                <th colspan="2">Keterampilan (KI 4)</th>
                <th rowspan="2" style="text-align:left;">Keterangan</th>
            </tr>
            <tr>
                <th class="score-col">Nilai</th>
                <th class="pred-col">Predikat</th>
                <th class="score-col">Nilai</th>
                <th class="pred-col">Predikat</th>
            </tr>
        </thead>
        <tbody>
            @php $ki3Total = 0; $ki3Count = 0; $ki4Total = 0; $ki4Count = 0; @endphp
            @foreach($reportData as $i => $row)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $row['subject']->name }}</td>

                {{-- KI 3 --}}
                <td class="center">
                    @if($row['ki3_score'] !== null)
                        @php $ki3Total += $row['ki3_score']; $ki3Count++; @endphp
                        <span class="score-val">{{ number_format($row['ki3_score'], 1) }}</span>
                    @else
                        <span style="color:#aaa">—</span>
                    @endif
                </td>
                <td class="center">
                    @if($row['ki3_predicate'])
                        @php $p = strtolower($row['ki3_predicate']); @endphp
                        <span class="predicate pred-{{ $p }}">{{ strtoupper($row['ki3_predicate']) }}</span>
                    @else
                        <span style="color:#aaa">—</span>
                    @endif
                </td>

                {{-- KI 4 --}}
                <td class="center">
                    @if($row['ki4_score'] !== null)
                        @php $ki4Total += $row['ki4_score']; $ki4Count++; @endphp
                        <span class="score-val">{{ number_format($row['ki4_score'], 1) }}</span>
                    @else
                        <span style="color:#aaa">—</span>
                    @endif
                </td>
                <td class="center">
                    @if($row['ki4_predicate'])
                        @php $p = strtolower($row['ki4_predicate']); @endphp
                        <span class="predicate pred-{{ $p }}">{{ strtoupper($row['ki4_predicate']) }}</span>
                    @else
                        <span style="color:#aaa">—</span>
                    @endif
                </td>

                <td>
                    @forelse($row['narratives'] as $n)
                        <span class="narrative">{{ $n->narrative }}</span>
                    @empty
                        <span style="color:#aaa">—</span>
                    @endforelse
                </td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="center">Jumlah</td>
                <td class="center">{{ $ki3Count > 0 ? number_format($ki3Total, 1) : '—' }}</td>
                <td></td>
                <td class="center">{{ $ki4Count > 0 ? number_format($ki4Total, 1) : '—' }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>

    {{-- ══ PREDIKAT LEGEND ══ --}}
    @if($predicateConfigs->count())
    <table class="kkm-legend" cellpadding="0" cellspacing="0">
        <tr>
            <td>KKM{{ $kkm ? ' = ' . $kkm : '' }}</td>
            @foreach($predicateConfigs->sortBy('min_score') as $cfg)
                <td>
                    <strong>{{ $cfg->grade }}</strong>
                    &nbsp;:&nbsp;
                    {{ $cfg->min_score }} &ndash; {{ $cfg->max_score }}
                </td>
            @endforeach
        </tr>
    </table>
    @endif

    {{-- ══ CATATAN ══ --}}
    @if($reportCard->notes?->homeroom_notes || $reportCard->notes?->principal_notes)
    <table class="notes-outer" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="note-box">
                    <div class="note-header">Catatan Wali Kelas</div>
                    <div class="note-body">{{ $reportCard->notes?->homeroom_notes ?? '—' }}</div>
                </div>
            </td>
            <td>
                <div class="note-box">
                    <div class="note-header">Catatan Kepala Madrasah</div>
                    <div class="note-body">{{ $reportCard->notes?->principal_notes ?? '—' }}</div>
                </div>
            </td>
        </tr>
    </table>
    @endif

    {{-- ══ TANDA TANGAN ══ --}}
    @php
        $city = $schoolSetting?->city ?? 'Kota';
    @endphp
    <div class="sig-date">
        {{ $reportCard->approved_at?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}
    </div>
    <table class="sig-wrap" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:50%;">
                <div class="sig-label">Mengetahui,<br>Kepala Madrasah</div>
                <div>
                    <span class="sig-line">
                        <div class="sig-name">{{ $schoolSetting?->principal_name ?? '—' }}</div>
                        @if($schoolSetting?->principal_nip)
                            <div class="sig-sub">NIP. {{ $schoolSetting->principal_nip }}</div>
                        @endif
                    </span>
                </div>
            </td>
            <td style="width:50%;">
                <div class="sig-label">Wali Kelas</div>
                <div>
                    <span class="sig-line">
                        <div class="sig-name">{{ $teacher->user->name ?? '—' }}</div>
                        <div class="sig-sub">NIP. &mdash;</div>
                    </span>
                </div>
            </td>
        </tr>
    </table>

    {{-- ══ QR VERIFIKASI ══ --}}
    @if($reportCard->isApproved() && $reportCard->verify_code)
    <table class="verify-wrap" cellpadding="0" cellspacing="0">
        <tr>
            <td class="verify-qr-td">
                <img src="{{ $qrCode }}" width="96" height="96" alt="QR Verifikasi">
            </td>
            <td>
                <div class="verify-label">Verifikasi Keaslian Dokumen</div>
                <div class="verify-code">{{ $reportCard->verify_code }}</div>
                <div class="verify-url">{{ $verifyUrl }}</div>
                <div class="verify-hint">Scan QR code atau kunjungi URL di atas untuk memverifikasi keaslian raport ini.</div>
            </td>
        </tr>
    </table>
    @endif

    {{-- ══ FOOTER ══ --}}
    <table class="footer-wrap" cellpadding="0" cellspacing="0">
        <tr>
            <td>{{ $classroom->name }} &mdash; {{ $student->name }} &mdash; {{ $student->nis }}</td>
            <td class="footer-right">Halaman 1</td>
        </tr>
    </table>

</div>
</body>
</html>
