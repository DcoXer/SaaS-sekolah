<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Kwitansi — {{ $payment_type->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10.5px;
            color: #1e293b;
            background: #fff;
        }

        .page { padding: 28px 36px; }

        /* ── Kop Surat ── */
        .kop-table { width: 100%; border-collapse: collapse; text-align: center; }
        .kop-logo  { width: 64px; vertical-align: middle; }
        .kop-logo img { width: 60px; height: 60px; }
        .kop-info  { vertical-align: middle; padding-left: 12px; }
        .kop-name  { font-size: 15px; font-weight: bold; color: #0f172a; }
        .kop-sub   { font-size: 9px; color: #475569; margin-top: 2px; }
        .kop-divider-thick { border-top: 2.5px solid #1e3a5f; margin: 8px 0 2px; }
        .kop-divider-thin  { border-top: 1px solid #1e3a5f; margin-bottom: 10px; }

        /* ── Judul kwitansi ── */
        .receipt-title {
            text-align: center;
            padding: 10px 0 12px;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 14px;
        }
        .receipt-title .label {
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #94a3b8;
        }
        .receipt-title h1 {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            margin: 3px 0 5px;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: bold;
        }
        .status-unpaid  { background: #fee2e2; color: #b91c1c; }
        .status-partial { background: #fef3c7; color: #92400e; }
        .status-paid    { background: #d1fae5; color: #065f46; }

        /* ── Info Grid ── */
        .info-outer {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
            margin-bottom: 16px;
        }
        .info-outer td.col {
            width: 50%;
            vertical-align: top;
            padding: 11px 14px;
        }
        .info-outer td.col-left { border-right: 1px solid #e2e8f0; }
        .section-label {
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            margin-bottom: 8px;
        }
        .detail-table { width: 100%; border-collapse: collapse; }
        .detail-table td { padding: 2.5px 0; font-size: 10.5px; }
        .detail-table td.lbl { color: #64748b; width: 48%; }
        .detail-table td.val { text-align: right; font-weight: bold; color: #0f172a; }
        .detail-table td.val-green { text-align: right; font-weight: bold; color: #059669; }
        .detail-table td.val-red   { text-align: right; font-weight: bold; color: #dc2626; }
        .detail-divider { border-top: 1px solid #e2e8f0; height: 1px; }

        /* ── Riwayat Pembayaran ── */
        .section-title {
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            margin-bottom: 7px;
        }
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
        }
        .payments-table th {
            background: #f8fafc;
            text-align: left;
            padding: 6px 10px;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
        }
        .payments-table th.right,
        .payments-table td.right { text-align: right; }
        .payments-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
            font-size: 10.5px;
        }
        .payments-table tr:last-child td { border-bottom: none; }
        .payments-table tfoot td {
            border-top: 1.5px solid #e2e8f0;
            font-weight: bold;
            color: #059669;
            padding: 6px 10px;
        }
        .no-payments { color: #94a3b8; font-style: italic; font-size: 10px; padding: 6px 0; }

        /* ── Signature ── */
        .sig-table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        .sig-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 8px;
        }
        .sig-label { font-size: 9.5px; color: #475569; }
        .sig-space { height: 52px; }
        .sig-line  { border-top: 1px solid #334155; width: 85%; margin: 0 auto; }
        .sig-name  { font-size: 9.5px; font-weight: bold; color: #1e293b; margin-top: 4px; }
        .qr-label  { font-size: 8px; color: #94a3b8; margin-top: 4px; }

        /* ── Footer ── */
        .footer {
            margin-top: 16px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            text-align: center;
            font-size: 8.5px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
<div class="page">

    @php
        $statusClass = ['unpaid' => 'status-unpaid', 'partial' => 'status-partial', 'paid' => 'status-paid'][$status] ?? 'status-unpaid';
        $statusLabel = ['unpaid' => 'Belum Bayar', 'partial' => 'Kurang Bayar', 'paid' => 'Lunas'][$status] ?? $status;
    @endphp

    <!-- ── Kop Surat ── -->
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
                    {{ $school?->phone }}{{ ($school?->phone && $school?->email) ? ' &middot; ' : '' }}{{ $school?->email }}
                </div>
                @endif
            </td>
        </tr>
    </table>
    <div class="kop-divider-thick"></div>
    <div class="kop-divider-thin"></div>

    <!-- ── Judul ── -->
    <div class="receipt-title">
        <div class="label">Kwitansi Pembayaran</div>
        <h1>{{ $payment_type->name }}</h1>
        <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
    </div>

    <!-- ── Data Siswa & Detail Tagihan ── -->
    <table class="info-outer">
        <tr>
            <td class="col col-left">
                <div class="section-label">Data Siswa</div>
                <table class="detail-table">
                    <tr>
                        <td class="lbl">Nama</td>
                        <td class="val">{{ $student->name }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">NIS</td>
                        <td class="val">{{ $student->nis }}</td>
                    </tr>
                    @if($student->classrooms->isNotEmpty())
                    <tr>
                        <td class="lbl">Kelas</td>
                        <td class="val">{{ $student->classrooms->first()->name }}</td>
                    </tr>
                    @endif
                    @if($invoice->due_date)
                    <tr>
                        <td class="lbl">Jatuh Tempo</td>
                        <td class="val">{{ \Carbon\Carbon::parse($invoice->due_date)->translatedFormat('d F Y') }}</td>
                    </tr>
                    @endif
                </table>
            </td>
            <td class="col">
                <div class="section-label">Detail Tagihan</div>
                <table class="detail-table">
                    <tr>
                        <td class="lbl">Total Tagihan</td>
                        <td class="val">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Terbayar</td>
                        <td class="val-green">Rp {{ number_format($total_paid, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="2"><div class="detail-divider"></div></td>
                    </tr>
                    <tr>
                        <td class="lbl" style="font-weight: bold; color: #1e293b;">Sisa</td>
                        <td class="{{ $remaining > 0 ? 'val-red' : 'val-green' }}">
                            Rp {{ number_format($remaining, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ── Riwayat Pembayaran ── -->
    <div class="section-title">Riwayat Pembayaran</div>

    @if($invoice->payments->isEmpty())
        <p class="no-payments">Belum ada pembayaran.</p>
    @else
        <table class="payments-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Metode</th>
                    <th>Catatan</th>
                    <th class="right">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->payments as $pay)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($pay->paid_at)->translatedFormat('d F Y') }}</td>
                    <td style="text-transform: capitalize;">{{ $pay->method }}</td>
                    <td>{{ $pay->note ?? '-' }}</td>
                    <td class="right" style="color: #059669; font-weight: bold;">Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">Total Terbayar</td>
                    <td class="right">Rp {{ number_format($total_paid, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- ── Tanda Tangan & QR ── -->
    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-label">Wali Murid / Siswa,</div>
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $wali_name ?? '..............................' }}</div>
            </td>

            @if(!empty($is_online))
                {{-- Pembayaran online: kolom tengah kosong, kanan = QR verifikasi --}}
                <td></td>
                <td style="vertical-align: middle; text-align: center;">
                    @if(!empty($qr_png))
                        <img src="data:image/png;base64,{{ $qr_png }}"
                             style="width: 84px; height: 84px; display: block; margin: 0 auto;" />
                    @endif
                    <div class="sig-label" style="margin-top: 6px;">Terverifikasi Otomatis,</div>
                    <div class="sig-name" style="margin-top: 4px;">Sistem Pembayaran Online</div>
                </td>
            @else
                {{-- Pembayaran cash: tengah = QR, kanan = TTD TU --}}
                <td style="vertical-align: middle;">
                    @if(!empty($qr_png))
                        <img src="data:image/png;base64,{{ $qr_png }}"
                             style="width: 84px; height: 84px; display: block; margin: 0 auto;" />
                        <div class="qr-label">Scan untuk verifikasi</div>
                    @endif
                </td>
                <td>
                    <div class="sig-label">Petugas TU Keuangan,</div>
                    <div class="sig-space"></div>
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ $confirmed_by ?? '..............................' }}</div>
                </td>
            @endif
        </tr>
    </table>

    <!-- ── Footer ── -->
    <div class="footer">
        Dicetak otomatis oleh sistem &middot; {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
    </div>

</div>
</body>
</html>
