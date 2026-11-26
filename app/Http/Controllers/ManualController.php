<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ManualController extends Controller
{
    public function download(): BinaryFileResponse
    {
        $path = public_path('documents/manual-usuario-prodenya.pdf');

        abort_unless(is_file($path), 404);

        return response()->download($path, 'manual-usuario-prodenya.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
