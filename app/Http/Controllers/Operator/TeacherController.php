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

    public function store(StoreTeacherRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()->back()->with('success', 'Data guru berhasil ditambahkan.');
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