<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentAccountsExport implements WithMultipleSheets
{
    public function __construct(private array $grouped) {}

    public function sheets(): array
    {
        $sheets = [];
        foreach ($this->grouped as $sheetName => $rows) {
            $sheets[] = new StudentAccountsSheet($sheetName, $rows);
        }
        return $sheets;
    }
}
