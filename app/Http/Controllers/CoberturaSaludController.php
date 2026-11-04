<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCoberturaSaludRequest;
use App\Models\CoberturaSaludArchivo;
use App\Models\Legajo;
use App\Services\CoberturaSaludService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CoberturaSaludController extends Controller
{
    public function store(
        StoreCoberturaSaludRequest $request,
        Legajo $legajo,
        CoberturaSaludService $service,
    ): RedirectResponse {
        $this->authorizeLegajoSection($request->user(), $legajo, 'persona');

        abort_unless($legajo->persona, 404);

        try {
            $service->reemplazar(
                $legajo->persona,
                $request->validated(),
                $request->file('credencial'),
                (array) $request->file('constancias', []),
                $request->user(),
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'cobertura' => 'No se pudo guardar la cobertura. Intentá nuevamente.',
            ]);
        }

        return back()->with('status', 'La cobertura de salud se guardó correctamente.');
    }

    public function download(
        Request $request,
        Legajo $legajo,
        CoberturaSaludArchivo $archivo,
    ): StreamedResponse {
        $this->authorizeLegajoSection($request->user(), $legajo, 'persona');

        $archivo->load('cobertura');
        abort_unless(
            $archivo->cobertura?->persona_id === $legajo->persona_id,
            404,
        );
        abort_unless(Storage::disk('local')->exists($archivo->ruta_archivo), 404);

        $legajo->registrarAcceso($request->user(), 'descarga_cobertura_salud');

        return Storage::disk('local')->download($archivo->ruta_archivo, $archivo->nombre_archivo, [
            'Content-Type' => $archivo->mime_type,
        ]);
    }
}
