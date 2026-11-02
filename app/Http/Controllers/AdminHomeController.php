<?php

namespace App\Http\Controllers;

use App\Models\Legajo;
use App\Models\MailMessage;
use App\Models\User;
use Illuminate\View\View;

class AdminHomeController extends Controller
{
    public function __invoke(): View
    {
        $legajosActivos = Legajo::whereHas('persona', fn ($query) => $query->where('activo', true))->count();
        $legajosRegistrados = Legajo::count();
        $usuariosActivos = User::where(function ($query) {
            $query->where('activo', true)->orWhereNull('activo');
        })->count();
        $usuariosPendientes = User::where('estado_cuenta', User::ESTADO_PENDIENTE)->count();
        $mensajesNoLeidos = MailMessage::where('is_read', false)->count();

        $legajosRecientes = Legajo::query()
            ->with('persona')
            ->latest('creado_en')
            ->take(5)
            ->get(['id', 'persona_id', 'numero_legajo', 'estado', 'creado_en']);

        return view('backend.home.home', compact(
            'legajosActivos',
            'legajosRegistrados',
            'usuariosActivos',
            'usuariosPendientes',
            'mensajesNoLeidos',
            'legajosRecientes',
        ));
    }
}
