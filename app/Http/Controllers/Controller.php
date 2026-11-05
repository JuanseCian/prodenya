<?php

namespace App\Http\Controllers;

use App\Models\Legajo;
use App\Models\User;

abstract class Controller
{
    protected function authorizeLegajoSection(User $user, Legajo $legajo, string $section): void
    {
        abort_unless(
            $user->puedeVerSeccionLegajo($section),
            403,
            'No tenés permiso para acceder a esta sección del legajo.',
        );
    }

    protected function authorizeCompleteLegajo(User $user, Legajo $legajo): void
    {
        abort_unless(
            $user->tieneRol('admin', 'coordinador', 'profesional', 'administrativo'),
            403,
            'No tenés permiso para exportar este legajo.',
        );

        foreach (array_keys(Legajo::SECCIONES) as $section) {
            $this->authorizeLegajoSection($user, $legajo, $section);
        }
    }

    protected function puedeAccederLegajo(User $user, Legajo $legajo, string ...$secciones): bool
    {
        foreach ($secciones as $seccion) {
            if ($user->puedeVerSeccionLegajo($seccion)) {
                return true;
            }
        }

        return false;
    }
}
