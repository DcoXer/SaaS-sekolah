<?php

namespace App\Services;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;

class SubjectService
{
    public function getAll(): Collection
    {
        return Subject::orderBy('grade')->orderBy('name')->get();
    }

    public function getByGrade(int $grade): Collection
    {
        return Subject::where('grade', $grade)->orderBy('name')->get();
    }

    public function getById(Subject $subject): Subject
    {
        return $subject;
    }

    public function create(array $data): Subject
    {
        return Subject::create([
            'name'  => $data['name'],
            'grade' => $data['grade'],
        ]);
    }

    public function update(Subject $subject, array $data): Subject
    {
        $subject->update([
            'name'  => $data['name'],
            'grade' => $data['grade'],
        ]);

        return $subject->fresh();
    }

    public function delete(Subject $subject): void
    {
        $subject->delete();
    }
}