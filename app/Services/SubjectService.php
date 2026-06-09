<?php

namespace App\Services;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;

class SubjectService
{
    public function getAll(): Collection
    {
        return Subject::orderBy('name')->get();
    }

    public function create(array $data): Subject
    {
        return Subject::create(['name' => $data['name']]);
    }

    public function update(Subject $subject, array $data): Subject
    {
        $subject->update(['name' => $data['name']]);

        return $subject->fresh();
    }

    public function delete(Subject $subject): void
    {
        $subject->delete();
    }
}
