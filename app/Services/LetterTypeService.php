<?php

namespace App\Services;

use App\Models\LetterType;
use Illuminate\Database\Eloquent\Collection;

class LetterTypeService
{
    public function getAll(): Collection
    {
        return LetterType::orderBy('category')->orderBy('name')->get();
    }

    public function getActive(): Collection
    {
        return LetterType::where('is_active', true)
                         ->orderBy('category')
                         ->orderBy('name')
                         ->get();
    }

    public function create(array $data): LetterType
    {
        return LetterType::create([
            'name'        => $data['name'],
            'category'    => $data['category'],
            'description' => $data['description'] ?? null,
            'is_active'   => true,
        ]);
    }

    public function update(LetterType $letterType, array $data): LetterType
    {
        $letterType->update([
            'name'        => $data['name'],
            'category'    => $data['category'],
            'description' => $data['description'] ?? null,
            'is_active'   => $data['is_active'] ?? true,
        ]);

        return $letterType->fresh();
    }

    public function delete(LetterType $letterType): void
    {
        $letterType->delete();
    }
}