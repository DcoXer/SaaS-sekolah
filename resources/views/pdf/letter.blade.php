<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $letter->letterTemplate?->letterType?->name ?? 'Surat' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #111;
            background: #fff;
        }

        .page { padding: 28px 48px 24px; }

        /* ── Kop Surat ── */
        .kop-table { width: 100%; border-collapse: collapse; }
        .kop-logo  { width: 76px; vertical-align: middle; text-align: center; }
        .kop-logo img { width: 72px; height: 72px; }
        .kop-info  { vertical-align: middle; padding-left: 12px; text-align: center; }
        .kop-name  { font-size: 18px; font-weight: bold; color: #000; letter-spacing: 0.5px; }
        .kop-sub   { font-size: 10px; color: #333; margin-top: 2px; }
        .kop-divider-thick { border: none; border-top: 3px solid #000; margin: 7px 0 2px; }
        .kop-divider-thin  { border: none; border-top: 1px solid #000; margin-bottom: 20px; }

        /* ── Judul Surat ── */
        .title-section { text-align: center; margin-bottom: 18px; }
        .title-text {
            font-size: 13px;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .title-number { font-size: 12px; font-weight: bold; margin-top: 3px; }

        /* ── Body ── */
        .body-para {
            text-align: justify;
            font-size: 12px;
            line-height: 1.7;
            margin-bottom: 6px;
        }

        /* ── Data Table ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 2px 0 6px;
        }
        .dt-label {
            width: 145px;
            padding: 2px 4px 2px 0;
            vertical-align: top;
            font-size: 12px;
        }
        .dt-colon {
            width: 14px;
            padding: 2px 6px 2px 0;
            vertical-align: top;
            font-size: 12px;
        }
        .dt-value {
            padding: 2px 0;
            vertical-align: top;
            font-size: 12px;
            line-height: 1.6;
        }

        /* ── TTD ── */
        .ttd-table { width: 100%; border-collapse: collapse; margin-top: 28px; }
        .ttd-cell  { width: 40%; text-align: center; font-size: 12px; line-height: 1.7; }
        .ttd-stamp { width: 88px; height: 88px; display: block; margin: 4px auto; opacity: 0.85; }
        .ttd-name  { font-weight: bold; border-top: 1px solid #000; padding-top: 2px; display: inline-block; min-width: 160px; }
        .ttd-nip   { font-size: 11px; }

        /* ── Barcode ── */
        .barcode-section { margin-top: 20px; border-top: 1px solid #d1d5db; padding-top: 10px; }
        .barcode-table   { width: 100%; border-collapse: collapse; }
        .barcode-left    { width: 88px; vertical-align: middle; }
        .barcode-right   { padding-left: 12px; vertical-align: middle; }
        .barcode-title   { font-size: 8.5px; font-weight: bold; text-transform: uppercase; color: #6b7280; margin-bottom: 3px; }
        .barcode-note    { font-size: 8px; color: #9ca3af; }
        .barcode-code    { font-size: 7.5px; font-family: monospace; color: #4b5563; word-break: break-all; margin: 3px 0; }

        /* ── Footer ── */
        .footer { margin-top: 12px; border-top: 1px solid #e5e7eb; padding-top: 6px; text-align: center; font-size: 8px; color: #9ca3af; }
    </style>
</head>
<body>
<div class="page">

    {{-- ── Kop Surat ── --}}
    <table class="kop-table">
        <tr>
            @if(!empty($logo_base64))
            <td class="kop-logo">
                <img src="data:{{ $logo_mime }};base64,{{ $logo_base64 }}" />
            </td>
            @endif
            <td class="kop-info">
                <div class="kop-name">{{ $school?->name ?? 'Nama Sekolah' }}</div>
                @if($school?->npsn)
                <div class="kop-sub">NPSN: {{ $school->npsn }}</div>
                @endif
                @if($school?->address)
                <div class="kop-sub">{{ $school->address }}</div>
                @endif
                @if($school?->phone || $school?->email)
                <div class="kop-sub">
                    {{ $school?->phone }}{{ ($school?->phone && $school?->email) ? ' | ' : '' }}{{ $school?->email }}
                </div>
                @endif
            </td>
        </tr>
    </table>
    <hr class="kop-divider-thick">
    <hr class="kop-divider-thin">

    {{-- ── Judul + Nomor ── --}}
    <div class="title-section">
        <div class="title-text">{{ $letter->letterTemplate?->letterType?->name ?? 'Surat Keterangan' }}</div>
        @if($letter_number)
        <div class="title-number">{{ $letter_number }}</div>
        @endif
    </div>

    {{-- ── Body Content ── --}}
    @php
        $lines   = explode("\n", $body_content);
        $output  = '';
        $inTable = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                if ($inTable) { $output .= '</table>'; $inTable = false; }
                $output .= '<div style="height:6px;"></div>';
                continue;
            }

            // Baris dengan pola "Label : Nilai" (label ≤ 28 karakter, nilai tidak kosong)
            if (preg_match('/^([^:]{1,28}?)\s*:\s*(.+)$/', $trimmed, $m)) {
                if (!$inTable) { $output .= '<table class="data-table">'; $inTable = true; }
                $output .= '<tr>'
                    . '<td class="dt-label">' . htmlspecialchars(trim($m[1])) . '</td>'
                    . '<td class="dt-colon">:</td>'
                    . '<td class="dt-value">' . htmlspecialchars(trim($m[2])) . '</td>'
                    . '</tr>';
            } else {
                if ($inTable) { $output .= '</table>'; $inTable = false; }
                $output .= '<p class="body-para">' . htmlspecialchars($trimmed) . '</p>';
            }
        }

        if ($inTable) { $output .= '</table>'; }
    @endphp
    <div>{!! $output !!}</div>

    {{-- ── TTD ── --}}
    @php
        $city = '';
        if ($school?->address && preg_match('/(?:Kab|Kota)\.\s*([\w\s]+?)(?:,|$)/i', $school->address, $m)) {
            $city = trim($m[1]);
        }
        $ttdDate = $letter->approved_at?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y');
    @endphp
    <table class="ttd-table">
        <tr>
            <td style="width: 60%;"></td>
            <td class="ttd-cell">
                <div>{{ $city ? $city . ', ' : '' }}{{ $ttdDate }}</div>
                <div>Kepala Madrasah,</div>
                @if(!empty($stamp_base64))
                    <img src="data:{{ $stamp_mime }};base64,{{ $stamp_base64 }}" class="ttd-stamp" />
                @else
                    <div style="height: 80px;"></div>
                @endif
                <div><span class="ttd-name">{{ $school?->principal_name ?? '—' }}</span></div>
                <div class="ttd-nip">NIP. {{ $school?->principal_nip ?: '-' }}</div>
            </td>
        </tr>
    </table>

    {{-- ── Barcode Verifikasi ── --}}
    <div class="barcode-section">
        <table class="barcode-table">
            <tr>
                <td class="barcode-left">
                    @if(!empty($qr_png))
                    <img src="data:image/png;base64,{{ $qr_png }}" style="width: 80px; height: 80px; display: block;" />
                    @endif
                </td>
                <td class="barcode-right">
                    <div class="barcode-title">Verifikasi Keaslian Surat</div>
                    <div class="barcode-note">Scan QR code atau kunjungi tautan berikut untuk memverifikasi keaslian surat ini.</div>
                    <div class="barcode-code">{{ $verify_url }}</div>
                    <div class="barcode-note">
                        Disetujui oleh: <strong style="color:#374151;">{{ $letter->approvedBy?->name ?? '—' }}</strong>
                        &middot; {{ $letter->approved_at?->translatedFormat('d F Y') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── Footer ── --}}
    <div class="footer">
        Dokumen ini dicetak otomatis oleh sistem &middot; {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
    </div>

</div>
</body>
</html>
