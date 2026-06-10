<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\PpdbRegistration;
use App\Models\PpdbSetting;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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
        // Fix #3: Cegah duplikat — no_kk + nama yang sama di batch PPDB yang sama
        $duplicate = PpdbRegistration::where('ppdb_setting_id', $setting->id)
            ->where('no_kk', $data['no_kk'])
            ->where('full_name', $data['full_name'])
            ->exists();

        if ($duplicate) {
            throw new \RuntimeException(
                'Data pendaftar ini sudah pernah dikirimkan. Silakan cek status via halaman Cek Status Pendaftaran.'
            );
        }

        // Simpan file dokumen sebelum masuk transaction
        // Fix #9: pakai subfolder UUID per pendaftar supaya tidak flat di satu folder
        $subfolder = 'ppdb/' . Str::uuid();
        $stored = ['photo' => null, 'document_kk' => null, 'document_akta' => null];
        foreach (['photo', 'document_kk', 'document_akta'] as $field) {
            if (isset($files[$field]) && $files[$field] instanceof UploadedFile) {
                // Foto disimpan di public disk (digunakan untuk review di halaman operator)
                // Dokumen KK & Akta disimpan di local disk (tidak bisa diakses langsung via URL)
                $disk = $field === 'photo' ? 'public' : 'local';
                $stored[$field] = $files[$field]->store($subfolder, $disk);
            }
        }

        // Fix #1: Bungkus dalam transaction + retry jika nomor tabrakan (race condition)
        return DB::transaction(function () use ($setting, $data, $stored) {
            $attempts = 0;

            while (true) {
                $number = 'PPDB-' . strtoupper(Str::random(8));

                try {
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
                        // Kontak utama
                        'parent_name'         => $data['father_name'],
                        'parent_phone'        => $data['parent_phone'],
                        'parent_email'        => $data['parent_email'] ?? null,
                        // Dokumen
                        'photo'               => $stored['photo'],
                        'document_kk'         => $stored['document_kk'],
                        'document_akta'       => $stored['document_akta'],
                        'status'              => 'pending',
                    ]);
                } catch (UniqueConstraintViolationException $e) {
                    // Nomor tabrakan karena race condition — coba lagi
                    if (++$attempts >= 5) {
                        throw new \RuntimeException('Gagal membuat nomor pendaftaran unik. Silakan coba lagi.');
                    }
                }
            }
        });
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
        // Guard status — hanya pending atau waitlisted yang bisa diterima
        if (! in_array($reg->status, ['pending', 'waitlisted'])) {
            throw new \RuntimeException(
                'Hanya pendaftar berstatus pending atau daftar tunggu yang dapat diterima.'
            );
        }

        // Fix #2: Enforce kuota dengan lock agar tidak bisa melebihi quota secara concurrent
        DB::transaction(function () use ($reg, $userId) {
            $setting = PpdbSetting::lockForUpdate()->findOrFail($reg->ppdb_setting_id);

            $acceptedCount = PpdbRegistration::where('ppdb_setting_id', $setting->id)
                ->where('status', 'accepted')
                ->count();

            if ($acceptedCount >= $setting->quota) {
                throw new \RuntimeException(
                    "Kuota PPDB sudah penuh ({$setting->quota} siswa). Tidak dapat menerima pendaftar baru."
                );
            }

            $reg->update([
                'status'      => 'accepted',
                'notes'       => null,
                'reviewed_at' => now(),
                'reviewed_by' => $userId,
            ]);

            // Auto-generate invoice uang masuk jika sudah dikonfigurasi
            $this->generateInvoiceForRegistration($reg, $setting);
        });
    }

    private function generateInvoiceForRegistration(PpdbRegistration $reg, PpdbSetting $setting): void
    {
        // Hanya buat invoice jika uang_masuk_amount sudah diset
        if (! $setting->uang_masuk_amount) {
            return;
        }

        // Hindari duplikat
        if (Invoice::where('ppdb_registration_id', $reg->id)->exists()) {
            return;
        }

        $academicYear = AcademicYear::where('status', 'active')->first();
        if (! $academicYear) return;

        Invoice::create([
            'student_id'           => null,
            'ppdb_registration_id' => $reg->id,
            'payment_type_id'      => null,
            'academic_year_id'     => $academicYear->id,
            'amount'               => $setting->uang_masuk_amount,
            'status'               => 'unpaid',
            'due_date'             => $setting->announcement_date ?? now()->addDays(14),
        ]);
    }

    public function reject(PpdbRegistration $reg, int $userId, string $notes): void
    {
        // Fix #4: Guard status — hanya pending atau waitlisted yang bisa ditolak
        if (! in_array($reg->status, ['pending', 'waitlisted'])) {
            throw new \RuntimeException(
                'Hanya pendaftar berstatus pending atau daftar tunggu yang dapat ditolak.'
            );
        }

        $reg->update([
            'status'      => 'rejected',
            'notes'       => $notes,
            'reviewed_at' => now(),
            'reviewed_by' => $userId,
        ]);
    }

    public function waitlist(PpdbRegistration $reg, int $userId): void
    {
        // Fix #4: Guard status — hanya pending yang bisa dipindah ke daftar tunggu
        if ($reg->status !== 'pending') {
            throw new \RuntimeException(
                'Hanya pendaftar berstatus pending yang dapat dipindahkan ke daftar tunggu.'
            );
        }

        $reg->update([
            'status'      => 'waitlisted',
            'reviewed_at' => now(),
            'reviewed_by' => $userId,
        ]);
    }

    public function getStats(PpdbSetting $setting): array
    {
        // Fix #13: satu query groupBy menggantikan 5 COUNT terpisah
        $counts = $setting->registrations()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total'      => $counts->sum(),
            'pending'    => $counts->get('pending', 0),
            'accepted'   => $counts->get('accepted', 0),
            'rejected'   => $counts->get('rejected', 0),
            'waitlisted' => $counts->get('waitlisted', 0),
        ];
    }
}
