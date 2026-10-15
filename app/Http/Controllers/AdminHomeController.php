<?php

namespace App\Http\Controllers;

use App\Models\MailMessage;
use App\Models\Persona;
use App\Models\User;
use Illuminate\View\View;

class AdminHomeController extends Controller
{
    public function __invoke(): View
    {
        $legajosActivos = Persona::where('activo', true)
            ->whereNotNull('numero_legajo')
            ->count();
        $legajosRegistrados = Persona::whereNotNull('numero_legajo')->count();
        $usuariosActivos = User::where(function ($query) {
            $query->where('activo', true)->orWhereNull('activo');
        })->count();
        $mensajesNoLeidos = MailMessage::where('is_read', false)->count();

        $legajosRecientes = Persona::query()
            ->whereNotNull('numero_legajo')
            ->latest('creado_en')
            ->take(5)
            ->get(['id', 'nombre', 'apellido', 'numero_legajo', 'estado', 'creado_en']);

        return view('backend.home.home', compact(
            'legajosActivos',
            'legajosRegistrados',
            'usuariosActivos',
            'mensajesNoLeidos',
            'legajosRecientes',
        ));
    }
}
