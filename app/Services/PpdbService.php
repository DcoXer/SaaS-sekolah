<?php

namespace App\Services;

use App\Models\PpdbRegistration;
use App\Models\PpdbSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class PpdbService
{
    public function getSetting(): ?PpdbSetting
    {
        return PpdbSetting::latest()->first();
    }

    public function upsertSetting(array $data): PpdbSetting
    {
        $setting = PpdbSetting::latest()->first();

        if ($setting) {
            $setting->update($data);
        } else {
            $setting = PpdbSetting::create($data);
        }

        return $setting;
    }

    public function register(PpdbSetting $setting, array $data, array $files): PpdbRegistration
    {
        $number = 'PPDB-' . strtoupper(Str::random(8));
        while (PpdbRegistration::where('registration_number', $number)->exists()) {
            $number = 'PPDB-' . strtoupper(Str::random(8));
        }

        $stored = ['photo' => null, 'document_kk' => null, 'document_akta' => null];
        foreach (['photo', 'document_kk', 'document_akta'] as $field) {
            if (isset($files[$field]) && $files[$field] instanceof UploadedFile) {
                $stored[$field] = $files[$field]->store('ppdb', 'public');
            }
        }

        return PpdbRegistration::create([
            'ppdb_setting_id'     => $setting->id,
            'registration_number' => $number,
            // Data siswa
            'full_name'           => $data['full_name'],
            'nik_siswa'           => $data['nik_siswa'] ?? null,
            'no_kk'               => $data['no_kk'],
            'birth_place'         => $data['birth_place'],
            'birth_date'          => $data['birth_date'],
            'gender'              => $data['gender'],
            'religion'            => $data['religion'] ?? null,
            'previous_school'     => $data['previous_school'] ?? null,
            // Alamat
            'province'            => $data['province'],
            'regency'             => $data['regency'],
            'district'            => $data['district'],
            'village'             => $data['village'],
            'address'             => $data['address'],
            // Data ayah
            'father_name'         => $data['father_name'],
            'father_nik'          => $data['father_nik'],
            'father_phone'        => $data['father_phone'] ?? null,
            // Data ibu
            'mother_name'         => $data['mother_name'],
            'mother_nik'          => $data['mother_nik'],
            'mother_phone'        => $data['mother_phone'] ?? null,
            // Kontak utama (parent_name = ayah, parent_phone = no WA utama)
            'parent_name'         => $data['father_name'],
            'parent_phone'        => $data['parent_phone'],
            'parent_email'        => $data['parent_email'] ?? null,
            // Dokumen
            'photo'               => $stored['photo'],
            'document_kk'         => $stored['document_kk'],
            'document_akta'       => $stored['document_akta'],
            'status'              => 'pending',
        ]);
    }

    public function getRegistrations(PpdbSetting $setting, array $filters = [])
    {
        $query = $setting->registrations()->with('reviewer')->latest();

        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $query->where(function ($qb) use ($q) {
                $qb->where('full_name', 'like', "%{$q}%")
                   ->orWhere('registration_number', 'like', "%{$q}%")
                   ->orWhere('parent_phone', 'like', "%{$q}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate(20)->withQueryString();
    }

    public function accept(PpdbRegistration $reg, int $userId): void
    {
        $reg->update([
            'status'      => 'accepted',
            'notes'       => null,
            'reviewed_at' => now(),
            'reviewed_by' => $userId,
        ]);
    }

    public function reject(PpdbRegistration $reg, int $userId, string $notes): void
    {
        $reg->update([
            'status'      => 'rejected',
            'notes'       => $notes,
            'reviewed_at' => now(),
            'reviewed_by' => $userId,
        ]);
    }

    public function waitlist(PpdbRegistration $reg, int $userId): void
    {
        $reg->update([
            'status'      => 'waitlisted',
            'reviewed_at' => now(),
            'reviewed_by' => $userId,
        ]);
    }

    public function getStats(PpdbSetting $setting): array
    {
        $regs = $setting->registrations();
        return [
            'total'      => $regs->count(),
            'pending'    => (clone $regs)->where('status', 'pending')->count(),
            'accepted'   => (clone $regs)->where('status', 'accepted')->count(),
            'rejected'   => (clone $regs)->where('status', 'rejected')->count(),
            'waitlisted' => (clone $regs)->where('status', 'waitlisted')->count(),
        ];
    }
}
