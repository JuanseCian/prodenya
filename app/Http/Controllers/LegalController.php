<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LegalController extends Controller
{
    public function terminos(): View
    {
        return view('legal.terminos');
    }

    public function contacto(): View
    {
        return view('legal.informacion', [
            'eyebrow' => 'PRODENYA · Contacto institucional',
            'title' => 'Contacto institucional',
            'intro' => 'Canales oficiales para realizar consultas sobre el sistema y la gestión institucional.',
            'sections' => [
                ['title' => 'Ubicación', 'body' => 'Alte. Brown y José Luis Lazarte, San Nicolás de los Arroyos.'],
                ['title' => 'Correo institucional', 'body' => 'prodenya.sn@gmail.com'],
                ['title' => 'Horario de atención', 'body' => 'De 08:00 a 14:00.'],
            ],
        ]);
    }

    public function privacidad(): View
    {
        return view('legal.informacion', [
            'eyebrow' => 'PRODENYA · Privacidad',
            'title' => 'Privacidad y tratamiento de datos',
            'intro' => 'PRODENYA es un sistema de uso institucional destinado a la promoción y protección de derechos.',
            'sections' => [
                ['title' => 'Uso institucional', 'body' => 'El acceso y la información disponible están destinados exclusivamente a las funciones autorizadas por la institución y el rol de cada persona usuaria.'],
                ['title' => 'Confidencialidad', 'body' => 'Las personas usuarias deben proteger las credenciales y mantener la confidencialidad de la información a la que acceden.'],
                ['title' => 'Consultas', 'body' => 'Para consultas sobre el tratamiento de datos, escribí a prodenya.sn@gmail.com.'],
            ],
        ]);
    }

    public function accesibilidad(): View
    {
        return view('legal.informacion', [
            'eyebrow' => 'PRODENYA · Accesibilidad',
            'title' => 'Accesibilidad',
            'intro' => 'Trabajamos para que la información y las funciones de PRODENYA puedan utilizarse de forma clara y accesible.',
            'sections' => [
                ['title' => 'Necesitás ayuda', 'body' => 'Si encontrás una barrera de acceso o necesitás una alternativa para realizar una gestión, comunicate con el equipo institucional.'],
                ['title' => 'Canal de contacto', 'body' => 'Escribí a prodenya.sn@gmail.com indicando la pantalla o función que necesitás utilizar.'],
            ],
        ]);
    }

    public function ubicacion(): RedirectResponse
    {
        return redirect()->away('https://www.google.com/maps/search/?'.http_build_query([
            'api' => 1,
            'query' => 'Alte. Brown y José Luis Lazarte, San Nicolás de los Arroyos',
        ]));
    }
}
