<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SummarySheet implements WithTitle, WithEvents
{
    public function __construct(
        private string $academicYearName,
        private array  $rows,   // [name, cycle, invoiceCount, totalAmount, totalPaid, totalSisa, pctLunas]
        private array  $totals, // [invoiceCount, totalAmount, totalPaid, totalSisa]
    ) {}

    public function title(): string
    {
        return 'Rekap';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $cols  = 'ABCDEFGH';

                // ── Title block ─────────────────────────────────────────
                $sheet->mergeCells('A1:H1');
                $sheet->setCellValue('A1', 'LAPORAN KEUANGAN');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E40AF']],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(36);

                $sheet->mergeCells('A2:H2');
                $sheet->setCellValue('A2', 'Tahun Ajaran: ' . $this->academicYearName);
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1D4ED8']],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(22);

                $sheet->mergeCells('A3:H3');
                $sheet->setCellValue('A3', 'Digenerate: ' . now()->translatedFormat('d F Y, H:i'));
                $sheet->getStyle('A3')->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF64748B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(18);

                // ── Gap row ─────────────────────────────────────────────
                $sheet->getRowDimension(4)->setRowHeight(10);

                // ── Headers ─────────────────────────────────────────────
                $headers = ['No', 'Jenis Tagihan', 'Siklus', 'Jumlah Siswa', 'Total Tagihan (Rp)', 'Total Terbayar (Rp)', 'Total Sisa (Rp)', '% Lunas'];
                foreach ($headers as $i => $header) {
                    $col = chr(65 + $i);
                    $sheet->setCellValue("{$col}5", $header);
                }
                $sheet->getStyle('A5:H5')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF334155']],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF475569']]],
                ]);
                $sheet->getRowDimension(5)->setRowHeight(22);

                // ── Data rows ────────────────────────────────────────────
                $cycleLabel = ['monthly' => 'Bulanan', 'yearly' => 'Tahunan', 'once' => 'Sekali'];
                $rowNum = 6;
                foreach ($this->rows as $i => $row) {
                    $isEven = $i % 2 === 1;
                    $bgArgb = $isEven ? 'FFF8FAFC' : 'FFFFFFFF';

                    $sheet->setCellValue("A{$rowNum}", $i + 1);
                    $sheet->setCellValue("B{$rowNum}", $row['name']);
                    $sheet->setCellValue("C{$rowNum}", $cycleLabel[$row['cycle']] ?? $row['cycle']);
                    $sheet->setCellValue("D{$rowNum}", $row['invoiceCount']);
                    $sheet->setCellValue("E{$rowNum}", $row['totalAmount']);
                    $sheet->setCellValue("F{$rowNum}", $row['totalPaid']);
                    $sheet->setCellValue("G{$rowNum}", $row['totalSisa']);
                    $sheet->setCellValue("H{$rowNum}", $row['pctLunas'] . '%');

                    // Format currency
                    foreach (['E', 'F', 'G'] as $col) {
                        $sheet->getStyle("{$col}{$rowNum}")
                            ->getNumberFormat()
                            ->setFormatCode('#,##0');
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

                    // Color % lunas
                    $pct = (float) $row['pctLunas'];
                    $pctColor = $pct >= 80 ? 'FF166534' : ($pct >= 50 ? 'FF92400E' : 'FF991B1B');
                    $sheet->getStyle("H{$rowNum}")->getFont()->getColor()->setARGB($pctColor);

                    $sheet->getRowDimension($rowNum)->setRowHeight(20);
                    $rowNum++;
                }

                // ── Total row ────────────────────────────────────────────
                $sheet->mergeCells("A{$rowNum}:C{$rowNum}");
                $sheet->setCellValue("A{$rowNum}", 'TOTAL');
                $sheet->setCellValue("D{$rowNum}", $this->totals['invoiceCount']);
                $sheet->setCellValue("E{$rowNum}", $this->totals['totalAmount']);
                $sheet->setCellValue("F{$rowNum}", $this->totals['totalPaid']);
                $sheet->setCellValue("G{$rowNum}", $this->totals['totalSisa']);
                $overallPct = $this->totals['totalAmount'] > 0
                    ? round($this->totals['totalPaid'] / $this->totals['totalAmount'] * 100, 1)
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

                // ── Column widths ─────────────────────────────────────────
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(32);
                $sheet->getColumnDimension('C')->setWidth(12);
                $sheet->getColumnDimension('D')->setWidth(16);
                $sheet->getColumnDimension('E')->setWidth(22);
                $sheet->getColumnDimension('F')->setWidth(22);
                $sheet->getColumnDimension('G')->setWidth(22);
                $sheet->getColumnDimension('H')->setWidth(12);

                // ── Sheet tab color ───────────────────────────────────────
                $sheet->getTabColor()->setARGB('FF1E40AF');
            },
        ];
    }
}
