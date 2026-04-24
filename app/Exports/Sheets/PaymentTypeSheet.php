<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PaymentTypeSheet implements WithTitle, WithEvents
{
    public function __construct(
        private string $typeName,
        private string $cycle,
        private string $academicYearName,
        private array  $invoices, // [{no, nis, name, classroom, amount, paid, sisa, status}]
    ) {}

    public function title(): string
    {
        // Sheet name max 31 chars, no special characters
        $safe = preg_replace('/[\/\\\?\*\[\]:]+/', '-', $this->typeName);
        return mb_substr($safe, 0, 31);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $cycleLabel = ['monthly' => 'Bulanan', 'yearly' => 'Tahunan', 'once' => 'Sekali'];
                $cycleColor = [
                    'monthly' => 'FF0E7490', // cyan
                    'yearly'  => 'FF7C3AED', // purple
                    'once'    => 'FF0F766E', // teal
                ];
                $headerBg = $cycleColor[$this->cycle] ?? 'FF334155';

                // ── Title block ─────────────────────────────────────────
                $sheet->mergeCells('A1:H1');
                $sheet->setCellValue('A1', strtoupper($this->typeName));
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 15, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $headerBg]],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(34);

                $sheet->mergeCells('A2:H2');
                $sheet->setCellValue('A2',
                    'Tahun Ajaran: ' . $this->academicYearName .
                    '   |   Siklus: ' . ($cycleLabel[$this->cycle] ?? $this->cycle)
                );
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $this->shadeColor($headerBg, 20)]],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(20);

                // ── Gap ─────────────────────────────────────────────────
                $sheet->getRowDimension(3)->setRowHeight(8);

                // ── Headers ─────────────────────────────────────────────
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

                // ── Data rows ────────────────────────────────────────────
                $statusLabel = ['paid' => 'Lunas', 'partial' => 'Sebagian', 'unpaid' => 'Belum Bayar'];
                $statusColor = ['paid' => 'FF166534', 'partial' => 'FF92400E', 'unpaid' => 'FF991B1B'];
                $statusBg    = ['paid' => 'FFdcfce7', 'partial' => 'FFFEF3C7', 'unpaid' => 'FFFEE2E2'];

                $rowNum = 5;
                $totalAmount = 0;
                $totalPaid   = 0;
                $totalSisa   = 0;

                foreach ($this->invoices as $i => $inv) {
                    $isEven = $i % 2 === 1;
                    $bgArgb = $isEven ? 'FFF8FAFC' : 'FFFFFFFF';

                    $sheet->setCellValue("A{$rowNum}", $i + 1);
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

                    // Status badge cell
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

                // ── Total row ────────────────────────────────────────────
                $sheet->mergeCells("A{$rowNum}:D{$rowNum}");
                $sheet->setCellValue("A{$rowNum}", 'TOTAL (' . count($this->invoices) . ' siswa)');
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

                // ── Column widths ─────────────────────────────────────────
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(12);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(18);
                $sheet->getColumnDimension('H')->setWidth(15);

                // ── Tab color ─────────────────────────────────────────────
                $tabColors = ['monthly' => 'FF0891B2', 'yearly' => 'FF7C3AED', 'once' => 'FF0F766E'];
                $sheet->getTabColor()->setARGB($tabColors[$this->cycle] ?? 'FF64748B');
            },
        ];
    }

    /** Lighten an ARGB hex color by adding a fixed offset */
    private function shadeColor(string $argb, int $offset): string
    {
        $r = min(255, hexdec(substr($argb, 2, 2)) + $offset);
        $g = min(255, hexdec(substr($argb, 4, 2)) + $offset);
        $b = min(255, hexdec(substr($argb, 6, 2)) + $offset);
        return 'FF' . sprintf('%02X%02X%02X', $r, $g, $b);
    }
}
