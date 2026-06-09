<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\Teacher;
use App\Services\TeacherService;
use Inertia\Inertia;
use Inertia\Response;

class TeacherController extends Controller
{
    public function __construct(private TeacherService $service) {}

    public function index(): Response
    {
        return Inertia::render('Operator/Teacher/Index', [
            'teachers' => $this->service->getAll(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Operator/Teacher/Create');
    }

    public function bulkGenerateAccounts()
    {
        $credentials = $this->service->bulkGenerateAccounts();

        return response()->streamDownload(function () use ($credentials) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['NIP', 'Nama Guru', 'Email', 'Password']);
            foreach ($credentials as $cred) {
                fputcsv($handle, [
                    $cred['nip'],
                    $cred['name'],
                    $cred['email'],
                    $cred['password'],
                ]);
            }
            fclose($handle);
        }, 'akun_guru.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function store(StoreTeacherRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()->route('operator.teachers.index')->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function show(Teacher $teacher): Response
    {
        return Inertia::render('Operator/Teacher/Show', [
            'teacher' => $this->service->getById($teacher),
        ]);
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        $this->service->update($teacher, $request->validated());

        return redirect()->back()->with('success', 'Data guru berhasil diupdate.');
    }

    public function destroy(Teacher $teacher)
    {
        $this->service->delete($teacher);

        return redirect()->back()->with('success', 'Data guru berhasil dihapus.');
    }
}