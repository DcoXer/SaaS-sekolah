<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Invoice;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class FinancialReportService
{
    private array $cycleLabel = ['monthly' => 'Bulanan', 'yearly' => 'Tahunan', 'once' => 'Sekali'];

    public function build(AcademicYear $academicYear): Spreadsheet
    {
        $invoices = Invoice::with([
                'paymentType',
                'payments',
                'student',
                'student.classrooms' => fn ($q) => $q->wherePivot('academic_year_id', $academicYear->id),
            ])
            ->where('academic_year_id', $academicYear->id)
            ->get();

        $grouped = $invoices->groupBy('payment_type_id');

        // Build summary data
        $summaryRows  = [];
        $totalInvoices = 0;
        $grandAmount   = 0;
        $grandPaid     = 0;

        foreach ($grouped as $group) {
            $type        = $group->first()->paymentType;
            $totalAmount = $group->sum('amount');
            $totalPaid   = $group->sum(fn ($inv) => $inv->payments->sum('amount'));

            $summaryRows[] = [
                'name'         => $type->name,
                'cycle'        => $type->cycle,
                'invoiceCount' => $group->count(),
                'totalAmount'  => (int) $totalAmount,
                'totalPaid'    => (int) $totalPaid,
                'totalSisa'    => (int) ($totalAmount - $totalPaid),
                'pctLunas'     => $totalAmount > 0 ? round($totalPaid / $totalAmount * 100, 1) : 0,
            ];

            $totalInvoices += $group->count();
            $grandAmount   += $totalAmount;
            $grandPaid     += $totalPaid;
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // remove default sheet

        // Sheet 1: Rekap
        $this->buildSummarySheet($spreadsheet, $academicYear->name, $summaryRows, [
            'invoiceCount' => $totalInvoices,
            'totalAmount'  => (int) $grandAmount,
            'totalPaid'    => (int) $grandPaid,
            'totalSisa'    => (int) ($grandAmount - $grandPaid),
        ]);

        // Per-type sheets
        foreach ($grouped as $group) {
            $type = $group->first()->paymentType;

            $invoiceRows = $group->sortBy('student.name')->values()->map(function ($inv, $i) {
                $paid      = $inv->payments->sum('amount');
                $classroom = $inv->student->classrooms->first()?->name ?? '-';

                return [
                    'no'        => $i + 1,
                    'nis'       => $inv->student->nis ?? '-',
                    'name'      => $inv->student->name ?? '-',
                    'classroom' => $classroom,
                    'amount'    => (int) $inv->amount,
                    'paid'      => (int) $paid,
                    'sisa'      => (int) ($inv->amount - $paid),
                    'status'    => $inv->status,
                ];
            })->values()->toArray();

            $this->buildPaymentTypeSheet($spreadsheet, $type->name, $type->cycle, $academicYear->name, $invoiceRows);
        }

        return $spreadsheet;
    }

    // ── Summary Sheet ────────────────────────────────────────────────────────────

    private function buildSummarySheet(Spreadsheet $spreadsheet, string $yearName, array $rows, array $totals): void
    {
        $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Rekap');
        $spreadsheet->addSheet($sheet);
        $sheet->getTabColor()->setARGB('FF1E40AF');

        // Title
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'LAPORAN KEUANGAN');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E40AF']],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(36);

        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'Tahun Ajaran: ' . $yearName);
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1D4ED8']],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(22);

        $sheet->mergeCells('A3:H3');
        $sheet->setCellValue('A3', 'Digenerate: ' . now()->translatedFormat('d F Y, H:i'));
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF64748B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(18);
        $sheet->getRowDimension(4)->setRowHeight(10);

        // Headers
        $headers = ['No', 'Jenis Tagihan', 'Siklus', 'Jumlah Siswa', 'Total Tagihan (Rp)', 'Total Terbayar (Rp)', 'Total Sisa (Rp)', '% Lunas'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValue(chr(65 + $i) . '5', $header);
        }
        $sheet->getStyle('A5:H5')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF334155']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF475569']]],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(22);

        // Data rows
        $rowNum = 6;
        foreach ($rows as $i => $row) {
            $bgArgb = $i % 2 === 1 ? 'FFF8FAFC' : 'FFFFFFFF';

            $sheet->setCellValue("A{$rowNum}", $i + 1);
            $sheet->setCellValue("B{$rowNum}", $row['name']);
            $sheet->setCellValue("C{$rowNum}", $this->cycleLabel[$row['cycle']] ?? $row['cycle']);
            $sheet->setCellValue("D{$rowNum}", $row['invoiceCount']);
            $sheet->setCellValue("E{$rowNum}", $row['totalAmount']);
            $sheet->setCellValue("F{$rowNum}", $row['totalPaid']);
            $sheet->setCellValue("G{$rowNum}", $row['totalSisa']);
            $sheet->setCellValue("H{$rowNum}", $row['pctLunas'] . '%');

            foreach (['E', 'F', 'G'] as $col) {
                $sheet->getStyle("{$col}{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
            }

            $sheet->getStyle("A{$rowNum}:H{$rowNum}")->applyFromArray([
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgArgb]],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
            ]);
            $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $pct = (float) $row['pctLunas'];
            $pctColor = $pct >= 80 ? 'FF166534' : ($pct >= 50 ? 'FF92400E' : 'FF991B1B');
            $sheet->getStyle("H{$rowNum}")->getFont()->getColor()->setARGB($pctColor);

            $sheet->getRowDimension($rowNum)->setRowHeight(20);
            $rowNum++;
        }

        // Total row
        $sheet->mergeCells("A{$rowNum}:C{$rowNum}");
        $sheet->setCellValue("A{$rowNum}", 'TOTAL');
        $sheet->setCellValue("D{$rowNum}", $totals['invoiceCount']);
        $sheet->setCellValue("E{$rowNum}", $totals['totalAmount']);
        $sheet->setCellValue("F{$rowNum}", $totals['totalPaid']);
        $sheet->setCellValue("G{$rowNum}", $totals['totalSisa']);
        $overallPct = $totals['totalAmount'] > 0
            ? round($totals['totalPaid'] / $totals['totalAmount'] * 100, 1)
            : 0;
        $sheet->setCellValue("H{$rowNum}", $overallPct . '%');

        foreach (['E', 'F', 'G'] as $col) {
            $sheet->getStyle("{$col}{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
        }

        $sheet->getStyle("A{$rowNum}:H{$rowNum}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF1E40AF']]],
        ]);
        $sheet->getRowDimension($rowNum)->setRowHeight(24);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(32);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(22);
        $sheet->getColumnDimension('F')->setWidth(22);
        $sheet->getColumnDimension('G')->setWidth(22);
        $sheet->getColumnDimension('H')->setWidth(12);
    }

    // ── Per-type Sheet ───────────────────────────────────────────────────────────

    private function buildPaymentTypeSheet(
        Spreadsheet $spreadsheet,
        string $typeName,
        string $cycle,
        string $yearName,
        array $invoices,
    ): void {
        $safe  = mb_substr(preg_replace('/[\/\\\?\*\[\]:]+/', '-', $typeName), 0, 31);
        $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $safe);
        $spreadsheet->addSheet($sheet);

        $cycleColor = ['monthly' => 'FF0E7490', 'yearly' => 'FF7C3AED', 'once' => 'FF0F766E'];
        $tabColors  = ['monthly' => 'FF0891B2', 'yearly' => 'FF7C3AED', 'once' => 'FF0F766E'];
        $headerBg   = $cycleColor[$cycle] ?? 'FF334155';

        $sheet->getTabColor()->setARGB($tabColors[$cycle] ?? 'FF64748B');

        // Title
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', strtoupper($typeName));
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 15, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $headerBg]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(34);

        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2',
            'Tahun Ajaran: ' . $yearName .
            '   |   Siklus: ' . ($this->cycleLabel[$cycle] ?? $cycle)
        );
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $this->lighten($headerBg, 20)]],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(8);

        // Headers
        $headers = ['No', 'NIS', 'Nama Siswa', 'Kelas', 'Tagihan (Rp)', 'Terbayar (Rp)', 'Sisa (Rp)', 'Status'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue(chr(65 + $i) . '4', $h);
        }
        $sheet->getStyle('A4:H4')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF334155']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF475569']]],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(22);

        // Data rows
        $statusLabel = ['paid' => 'Lunas', 'partial' => 'Sebagian', 'unpaid' => 'Belum Bayar'];
        $statusColor = ['paid' => 'FF166534', 'partial' => 'FF92400E', 'unpaid' => 'FF991B1B'];
        $statusBg    = ['paid' => 'FFdcfce7', 'partial' => 'FFFEF3C7', 'unpaid' => 'FFFEE2E2'];

        $rowNum      = 5;
        $totalAmount = 0;
        $totalPaid   = 0;
        $totalSisa   = 0;

        foreach ($invoices as $i => $inv) {
            $bgArgb = $i % 2 === 1 ? 'FFF8FAFC' : 'FFFFFFFF';

            $sheet->setCellValue("A{$rowNum}", $inv['no']);
            $sheet->setCellValue("B{$rowNum}", $inv['nis']);
            $sheet->setCellValue("C{$rowNum}", $inv['name']);
            $sheet->setCellValue("D{$rowNum}", $inv['classroom']);
            $sheet->setCellValue("E{$rowNum}", $inv['amount']);
            $sheet->setCellValue("F{$rowNum}", $inv['paid']);
            $sheet->setCellValue("G{$rowNum}", $inv['sisa']);
            $sheet->setCellValue("H{$rowNum}", $statusLabel[$inv['status']] ?? $inv['status']);

            foreach (['E', 'F', 'G'] as $col) {
                $sheet->getStyle("{$col}{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
            }

            $sheet->getStyle("A{$rowNum}:H{$rowNum}")->applyFromArray([
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgArgb]],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
            ]);
            $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $status = $inv['status'];
            $sheet->getStyle("H{$rowNum}")->applyFromArray([
                'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => $statusColor[$status] ?? 'FF334155']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $statusBg[$status] ?? 'FFF1F5F9']],
            ]);

            $totalAmount += $inv['amount'];
            $totalPaid   += $inv['paid'];
            $totalSisa   += $inv['sisa'];

            $sheet->getRowDimension($rowNum)->setRowHeight(19);
            $rowNum++;
        }

        // Total row
        $sheet->mergeCells("A{$rowNum}:D{$rowNum}");
        $sheet->setCellValue("A{$rowNum}", 'TOTAL (' . count($invoices) . ' siswa)');
        $sheet->setCellValue("E{$rowNum}", $totalAmount);
        $sheet->setCellValue("F{$rowNum}", $totalPaid);
        $sheet->setCellValue("G{$rowNum}", $totalSisa);

        $pct = $totalAmount > 0 ? round($totalPaid / $totalAmount * 100, 1) : 0;
        $sheet->setCellValue("H{$rowNum}", $pct . '% lunas');

        foreach (['E', 'F', 'G'] as $col) {
            $sheet->getStyle("{$col}{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
        }

        $sheet->getStyle("A{$rowNum}:H{$rowNum}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF1E40AF']]],
        ]);
        $sheet->getRowDimension($rowNum)->setRowHeight(24);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(15);
    }

    private function lighten(string $argb, int $offset): string
    {
        $r = min(255, hexdec(substr($argb, 2, 2)) + $offset);
        $g = min(255, hexdec(substr($argb, 4, 2)) + $offset);
        $b = min(255, hexdec(substr($argb, 6, 2)) + $offset);
        return 'FF' . sprintf('%02X%02X%02X', $r, $g, $b);
    }
}
