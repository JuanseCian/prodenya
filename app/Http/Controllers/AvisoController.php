<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaEvento;
use App\Models\Aviso;
use App\Models\User;
use App\Notifications\AvisoPublicadoNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class AvisoController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $estado = (string) $request->query('estado', '');

        $avisos = Aviso::query()
            ->with('creadoPor:id,name')
            ->when($buscar !== '', fn ($query) => $query->where(function ($query) use ($buscar) {
                $query->where('titulo', 'like', "%{$buscar}%")
                    ->orWhere('mensaje', 'like', "%{$buscar}%");
            }))
            ->when($estado === 'activo', fn ($query) => $query->where('activo', true))
            ->when($estado === 'inactivo', fn ($query) => $query->where('activo', false))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.avisos.index', [
            'avisos' => $avisos,
            'buscar' => $buscar,
            'estado' => $estado,
        ]);
    }

    public function create(): View
    {
        return view('backend.avisos.form', ['aviso' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate($this->rules());

        $aviso = Aviso::create([
            ...$datos,
            'creado_por' => $request->user()->id,
        ]);

        AuditoriaEvento::registrar('aviso_publicado', $aviso, null, $aviso->only(['titulo', 'mensaje', 'url']));
        $this->notificarUsuarios($aviso, $request->user());

        return redirect()->route('admin.avisos.index')->with('success', 'Aviso publicado y notificado a los usuarios.');
    }

    public function edit(Aviso $aviso): View
    {
        return view('backend.avisos.form', ['aviso' => $aviso]);
    }

    public function update(Request $request, Aviso $aviso): RedirectResponse
    {
        $datos = $request->validate($this->rules());
        $antes = $aviso->only(['titulo', 'mensaje', 'url']);

        $aviso->update($datos);
        AuditoriaEvento::registrar('aviso_actualizado', $aviso, $antes, $aviso->only(['titulo', 'mensaje', 'url']));

        return redirect()->route('admin.avisos.index')->with('success', 'Aviso actualizado correctamente.');
    }

    public function desactivar(Aviso $aviso): RedirectResponse
    {
        $aviso->update(['activo' => false]);
        AuditoriaEvento::registrar('aviso_desactivado', $aviso);

        return back()->with('success', 'Aviso desactivado correctamente.');
    }

    public function reactivar(Aviso $aviso): RedirectResponse
    {
        $aviso->update(['activo' => true]);
        AuditoriaEvento::registrar('aviso_reactivado', $aviso);

        return back()->with('success', 'Aviso reactivado correctamente.');
    }

    /** @return array<string, array<int, string>> */
    private function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:190'],
            'mensaje' => ['required', 'string', 'max:2000'],
            'url' => ['nullable', 'url', 'max:255'],
        ];
    }

    private function notificarUsuarios(Aviso $aviso, User $autor): void
    {
        $destinatarios = User::query()
            ->where('activo', true)
            ->where('estado_cuenta', User::ESTADO_APROBADO)
            ->whereNotNull('rol_id')
            ->where('id', '!=', $autor->id)
            ->get(['id', 'name', 'email']);

        Notification::send($destinatarios, new AvisoPublicadoNotification($aviso));
    }
}
