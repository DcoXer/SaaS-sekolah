<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\PaymentType;
use Illuminate\Database\Eloquent\Collection;

class PaymentTypeService
{
    public function getByAcademicYear(AcademicYear $academicYear): Collection
    {
        return PaymentType::where('academic_year_id', $academicYear->id)
            ->orderBy('created_at')
            ->get();
    }

    public function create(array $data): PaymentType
    {
        return PaymentType::create([
            'academic_year_id' => $data['academic_year_id'],
            'name'             => $data['name'],
            'cycle'            => $data['cycle'],
            'amount'           => $data['amount'],
            'due_date'         => $data['due_date'],
            'grade'            => $data['grade'] ?? null,
            'is_exam_related'  => $data['is_exam_related'] ?? false,
            'is_active'        => true,
        ]);
    }

    public function update(PaymentType $paymentType, array $data): PaymentType
    {
        $paymentType->update([
            'name'            => $data['name'],
            'amount'          => $data['amount'],
            'due_date'        => $data['due_date'],
            'grade'           => $data['grade'] ?? null,
            'is_exam_related' => $data['is_exam_related'] ?? false,
            'is_active'       => $data['is_active'] ?? true,
        ]);

        return $paymentType->fresh();
    }

    public function delete(PaymentType $paymentType): void
    {
        $paymentType->delete();
    }

    /**
     * Generate SPP bulanan untuk seluruh tahun ajaran.
     * Mengembalikan hanya PaymentType yang BARU dibuat (bukan yang sudah ada),
     * agar controller hanya generate invoice untuk bulan yang baru.
     */
    public function generateMonthlySpp(AcademicYear $academicYear, int $amount): Collection
    {
        $start = $academicYear->start_date->copy();
        $end   = $academicYear->end_date->copy();

        $current  = $start->copy()->startOfMonth();
        $newTypes = new Collection();

        while ($current->lte($end)) {
            $name    = 'SPP ' . $current->isoFormat('MMMM YYYY');
            $dueDate = $current->copy()->endOfMonth();

            [$paymentType, $created] = PaymentType::firstOrCreate(
                [
                    'academic_year_id' => $academicYear->id,
                    'name'             => $name,
                    'cycle'            => 'monthly',
                ],
                [
                    'amount'          => $amount,
                    'due_date'        => $dueDate,
                    'grade'           => null,
                    'is_exam_related' => false,
                    'is_active'       => true,
                ]
            );

            if ($created) {
                $newTypes->push($paymentType);
            }

            $current->addMonth();
        }

        return $newTypes;
    }
}
