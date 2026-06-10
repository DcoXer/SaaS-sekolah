<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\PpdbRegistration;
use Illuminate\Support\Facades\Storage;

class PpdbDocumentController extends Controller
{
    public function show(PpdbRegistration $registration, string $type)
    {
        abort_unless(in_array($type, ['document_kk', 'document_akta']), 404);

        $path = $registration->{$type};

        abort_if(!$path, 404);
        abort_if(!Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}
