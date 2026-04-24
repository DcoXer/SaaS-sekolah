<?php

namespace App\Services;

use App\Models\LetterTemplate;
use App\Models\LetterType;
use Illuminate\Database\Eloquent\Collection;

class LetterTemplateService
{
    // Semua placeholder yang tersedia di sistem
    public const AVAILABLE_PLACEHOLDERS = [
        '{{student.name}}'        => 'Nama Siswa',
        '{{student.nis}}'         => 'NIS Siswa',
        '{{classroom.name}}'      => 'Nama Kelas',
        '{{academic_year.name}}'  => 'Tahun Ajaran',
        '{{letter.date}}'         => 'Tanggal Surat',
        '{{letter.number}}'       => 'Nomor Surat',
        '{{principal.name}}'      => 'Nama Kepala Sekolah',
        '{{principal.nip}}'       => 'NIP Kepala Sekolah',
        '{{school.name}}'         => 'Nama Sekolah',
        '{{school.address}}'      => 'Alamat Sekolah',
        '{{school.phone}}'        => 'Telepon Sekolah',
        '{{barcode}}'             => 'Barcode Verifikasi',
    ];

    public function getByLetterType(LetterType $letterType): Collection
    {
        return LetterTemplate::where('letter_type_id', $letterType->id)
                              ->where('is_active', true)
                              ->get();
    }

    public function getAll(): Collection
    {
        return LetterTemplate::with('letterType')->get();
    }

    public function create(array $data): LetterTemplate
    {
        return LetterTemplate::create([
            'letter_type_id'         => $data['letter_type_id'],
            'name'                   => $data['name'],
            'content'                => $data['content'],
            'available_placeholders' => $data['available_placeholders'] ?? array_keys(self::AVAILABLE_PLACEHOLDERS),
            'is_active'              => true,
        ]);
    }

    public function update(LetterTemplate $template, array $data): LetterTemplate
    {
        $template->update([
            'name'                   => $data['name'],
            'content'                => $data['content'],
            'available_placeholders' => $data['available_placeholders'] ?? array_keys(self::AVAILABLE_PLACEHOLDERS),
            'is_active'              => $data['is_active'] ?? true,
        ]);

        return $template->fresh();
    }

    public function delete(LetterTemplate $template): void
    {
        $template->delete();
    }
}