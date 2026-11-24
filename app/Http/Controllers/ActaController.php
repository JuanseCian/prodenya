<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActaRequest;
use App\Models\Acta;
use App\Models\AuditoriaEvento;
use App\Models\Legajo;
use App\Models\Persona;
use App\Services\ActaDocumentoService;
use App\Services\ActaService;
use App\Support\Barrios;
use App\Support\Escuelas;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ActaController extends Controller
{
    private const MIME_DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

    public function index(): Response
    {
        return Inertia::render('actas/index', [
            'tipos' => $this->tipos(),
            'sinLegajo' => Acta::query()->with('creadoPor')->whereNull('legajo_id')->latest('id')->limit(5)->get()
                ->map(fn (Acta $acta) => $this->resumen($acta))->all(),
            'sinLegajoTotal' => Acta::query()->whereNull('legajo_id')->count(),
            'recientes' => Acta::query()->with(['creadoPor', 'legajo'])->whereNotNull('legajo_id')->latest('id')->limit(10)->get()
                ->map(fn (Acta $acta) => $this->resumen($acta))->all(),
            'status' => session('status'),
        ]);
    }

    /**
     * Actas guardadas sin legajo, para revisarlas y vincularlas.
     */
    public function pendientes(Request $request): Response
    {
        $busqueda = trim((string) $request->query('buscar'));
        $tipo = (string) $request->query('tipo');

        $actas = Acta::query()
            ->with('creadoPor')
            ->whereNull('legajo_id')
            ->when(array_key_exists($tipo, Acta::TIPOS), fn ($query) => $query->where('tipo', $tipo))
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $digitos = preg_replace('/\D/', '', $busqueda);

                $query->where(fn ($porTexto) => $porTexto
                    ->where('titulo', 'like', "%{$busqueda}%")
                    ->when($digitos !== '', fn ($porDni) => $porDni->orWhere('datos', 'like', "%{$digitos}%")));
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Acta $acta) => $this->resumen($acta) + [
                'creado_en' => $acta->creado_en?->toIso8601String(),
                'firmas' => $acta->firmas ?? [],
                'secciones' => $this->detalle($acta),
            ]);

        return Inertia::render('actas/pendientes', [
            'actas' => $actas,
            'total' => Acta::query()->whereNull('legajo_id')->count(),
            'filtros' => ['buscar' => $busqueda, 'tipo' => array_key_exists($tipo, Acta::TIPOS) ? $tipo : ''],
            'tipos' => collect(Acta::TIPOS)->map(fn (array $config, string $clave) => [
                'tipo' => $clave,
                'label' => isset($config['auh']) ? "AUH · {$config['label']}" : $config['label'],
            ])->values()->all(),
            'status' => session('status'),
        ]);
    }

    public function create(Request $request, string $tipo, ActaService $actas): Response
    {
        $legajo = $request->filled('legajo') ? Legajo::with('persona.barrio')->find($request->integer('legajo')) : null;
        $config = Acta::TIPOS[$tipo];

        return Inertia::render('actas/create', [
            'tipo' => $tipo,
            'tipoLabel' => $config['label'],
            'grupo' => $config['grupo'],
            'esAuh' => isset($config['auh']),
            'tipoAuh' => $config['auh'] ?? null,
            'firmantes' => Acta::firmantesDe($tipo),
            'legajo' => $legajo ? $this->legajoResumen($legajo) : null,
            'datos' => $actas->datosIniciales($tipo, $legajo, $request->user()),
            'barrios' => Barrios::nombres(),
            'escuelas' => Escuelas::nombres(),
        ]);
    }

    /**
     * Personas del sistema por DNI o nombre, con su legajo si lo tienen.
     */
    public function buscarPersonas(Request $request): JsonResponse
    {
        $termino = trim((string) $request->query('q'));

        if (mb_strlen($termino) < 2) {
            return response()->json([]);
        }

        $personas = Persona::query()
            ->with(['barrio', 'localidad', 'legajos' => fn ($query) => $query->latest('fecha_apertura')])
            ->where(function ($query) use ($termino) {
                $digitos = preg_replace('/\D/', '', $termino);

                if ($digitos !== '' && $digitos === preg_replace('/[\s.]/', '', $termino)) {
                    $query->where('dni', 'like', "{$digitos}%");

                    return;
                }

                foreach (preg_split('/[\s,]+/', $termino, -1, PREG_SPLIT_NO_EMPTY) as $palabra) {
                    $query->where(fn ($porPalabra) => $porPalabra
                        ->where('apellido', 'like', "%{$palabra}%")
                        ->orWhere('nombre', 'like', "%{$palabra}%"));
                }
            })
            ->orderBy('apellido')
            ->limit(8)
            ->get();

        return response()->json($personas->map(fn (Persona $persona) => [
            'id' => $persona->id,
            'nombre' => "{$persona->apellido}, {$persona->nombre}",
            'dni' => (string) ($persona->dni ?? ''),
            'fecha_nacimiento' => $persona->fecha_nacimiento?->toDateString() ?? '',
            'domicilio' => trim(implode(' ', array_filter([$persona->direccion_calle, $persona->direccion_numero, $persona->direccion_piso_depto]))),
            'barrio' => $persona->barrio?->nombre ?? '',
            'telefono' => (string) ($persona->telefono ?? ''),
            'legajo' => ($legajo = $persona->legajos->first()) ? $this->legajoResumen($legajo) : null,
        ])->values());
    }

    public function store(StoreActaRequest $request, string $tipo, ActaService $actas): RedirectResponse
    {
        $legajo = $request->filled('legajo_id') ? Legajo::find($request->integer('legajo_id')) : null;
        $rutas = [];

        try {
            $acta = DB::transaction(fn () => $actas->registrar(
                $tipo,
                $request->datos(),
                $request->firmas(),
                $request->user(),
                $legajo,
                $request->integer('persona_id') ?: null,
                null,
                $rutas,
            ));
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($rutas);
            report($exception);

            return back()->withErrors(['acta' => 'No se pudo guardar el acta. Intentá nuevamente.']);
        }

        return redirect()->route('actas.index')->with(
            'status',
            match (true) {
                $legajo === null => "Se guardó «{$acta->etiqueta()}». Podés vincularla a un legajo cuando corresponda.",
                Acta::esAuh($tipo) => "Se guardó «{$acta->etiqueta()}» en el legajo {$legajo->numero_legajo}.",
                default => "Se guardó «{$acta->etiqueta()}» y quedó registrada como intervención en el legajo {$legajo->numero_legajo}.",
            },
        );
    }

    /**
     * Descarga el Word sin guardar el acta (para imprimir y firmar en papel).
     */
    public function borrador(StoreActaRequest $request, string $tipo, ActaDocumentoService $documentos): BinaryFileResponse
    {
        $legajo = $request->filled('legajo_id') ? Legajo::find($request->integer('legajo_id')) : null;

        return response()
            ->download(
                $documentos->generar($tipo, $request->datos(), [], $legajo?->numero_legajo),
                Str::slug(Acta::TIPOS[$tipo]['label']).'-borrador.docx',
                ['Content-Type' => self::MIME_DOCX],
            )
            ->deleteFileAfterSend();
    }

    public function descargar(Request $request, Acta $acta): StreamedResponse
    {
        abort_unless($request->user()->tieneRol(...Acta::ROLES), 403);
        abort_unless(Storage::disk('local')->exists($acta->ruta_archivo), 404);

        AuditoriaEvento::registrar('acta.descargada', $acta);

        return Storage::disk('local')->download($acta->ruta_archivo, $acta->nombre_archivo, ['Content-Type' => $acta->mime_type]);
    }

    public function vincular(Request $request, Acta $acta, ActaService $actas): RedirectResponse
    {
        abort_unless($request->user()->tieneRol(...Acta::ROLES), 403);

        $datos = $request->validate(
            ['legajo_id' => ['required', 'integer', 'exists:legajos,id']],
            ['legajo_id.required' => 'Elegí el legajo.', 'legajo_id.exists' => 'El legajo elegido no existe.'],
        );

        if ($acta->legajo_id !== null) {
            return back()->withErrors(['legajo_id' => 'Esta acta ya está vinculada a un legajo.']);
        }

        $legajo = Legajo::findOrFail($datos['legajo_id']);
        $rutas = [];

        try {
            DB::transaction(fn () => $actas->vincular($acta, $legajo, $request->user(), $rutas));
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($rutas);
            report($exception);

            return back()->withErrors(['legajo_id' => 'No se pudo vincular el acta. Intentá nuevamente.']);
        }

        return back()->with('status', Acta::esAuh($acta->tipo)
            ? "«{$acta->etiqueta()}» quedó vinculada al legajo {$legajo->numero_legajo}."
            : "«{$acta->etiqueta()}» quedó vinculada al legajo {$legajo->numero_legajo} como intervención.");
    }

    /**
     * Contenido del acta en secciones legibles, sin campos vacíos.
     *
     * @return array<int, array{titulo: string, campos: array<int, array{label: string, valor: string}>, texto: bool}>
     */
    private function detalle(Acta $acta): array
    {
        $d = $acta->datos ?? [];
        $fecha = fn (?string $valor) => filled($valor) ? CarbonImmutable::parse($valor)->format('d/m/Y') : null;

        if (Acta::esAuh($acta->tipo)) {
            $responsable = $d['responsable'] ?? [];
            $grupo = collect($d['grupo_familiar'] ?? [])->filter(fn ($miembro) => filled($miembro['nombre'] ?? null));

            return $this->secciones([
                'Datos' => ['Fecha' => $fecha($d['fecha'] ?? null), 'Ciudad' => $d['ciudad'] ?? null, 'Provincia' => $d['provincia'] ?? null],
                'Niño/a' => ['Nombre' => $d['nino_nombre'] ?? null, 'DNI' => $d['nino_dni'] ?? null],
                'Responsable' => [
                    'Nombre' => $responsable['nombre'] ?? null,
                    'DNI' => $responsable['dni'] ?? null,
                    'CUIL' => $responsable['cuil'] ?? null,
                    'Fecha de nacimiento' => $fecha($responsable['fecha_nacimiento'] ?? null),
                    'Nacionalidad' => $responsable['nacionalidad'] ?? null,
                    'Domicilio' => collect([$responsable['domicilio'] ?? null, $responsable['barrio'] ?? null, $responsable['localidad'] ?? null])->filter()->implode(', ') ?: null,
                    'Teléfono' => $responsable['telefono'] ?? null,
                ],
                'No convive' => [
                    'Nombre' => $d['no_conviviente_nombre'] ?? null,
                    'DNI' => $d['no_conviviente_dni'] ?? null,
                    'Desde el año' => $d['no_conviviente_desde'] ?? null,
                ],
                'Grupo familiar' => $grupo->mapWithKeys(fn ($miembro) => [
                    $miembro['nombre'] => collect([$miembro['vinculo'] ?? null, filled($miembro['dni'] ?? null) ? "DNI {$miembro['dni']}" : null])->filter()->implode(' · ') ?: '—',
                ])->all(),
            ], [
                'Referencia' => $d['referencia_detalle'] ?? null,
                'Motivos de intervención' => $d['motivos'] ?? null,
            ]);
        }

        $persona = match ($acta->tipo) {
            'telefonica' => 'Comunicación con',
            'citacion' => 'Citado/a',
            'constancia' => 'Asistió',
            'domiciliaria' => 'Entrevistado/a',
            default => 'Compareciente',
        };

        return $this->secciones([
            'Datos' => [
                'Fecha' => $fecha($d['fecha'] ?? null),
                'Hora' => $d['hora'] ?? null,
                'Operador/a' => $d['operador'] ?? null,
                'Día de la citación' => $fecha($d['citacion_dia'] ?? null),
                'Horario' => $d['citacion_horario'] ?? null,
            ],
            $persona => [
                'Nombre' => $d['compareciente_nombre'] ?? null,
                'DNI' => $d['compareciente_dni'] ?? null,
                'Domicilio' => collect([$d['compareciente_domicilio'] ?? null, $d['compareciente_barrio'] ?? null])->filter()->implode(', ') ?: null,
                'Teléfono' => $d['compareciente_telefono'] ?? null,
                'Vínculo' => $d['vinculo'] ?? null,
            ],
            'Niño/a' => [
                'Nombre' => $d['nino_nombre'] ?? null,
                'DNI' => $d['nino_dni'] ?? null,
                'Fecha de nacimiento' => $fecha($d['nino_fecha_nacimiento'] ?? null),
                'Escuela' => $d['nino_escuela'] ?? null,
            ],
        ], [
            'Manifestación / relato' => $d['relato'] ?? null,
            'Estrategias a seguir' => $d['estrategias'] ?? null,
        ]);
    }

    /**
     * @param  array<string, array<string, string|null>>  $grupos
     * @param  array<string, string|null>  $textos
     * @return array<int, array{titulo: string, campos: array<int, array{label: string, valor: string}>, texto: bool}>
     */
    private function secciones(array $grupos, array $textos): array
    {
        $secciones = collect($grupos)
            ->map(fn (array $campos, string $titulo) => [
                'titulo' => $titulo,
                'campos' => collect($campos)
                    ->filter(fn ($valor) => filled($valor))
                    ->map(fn ($valor, string $label) => ['label' => $label, 'valor' => (string) $valor])
                    ->values()
                    ->all(),
                'texto' => false,
            ]);

        foreach ($textos as $titulo => $texto) {
            if (filled($texto)) {
                $secciones->push(['titulo' => $titulo, 'campos' => [['label' => $titulo, 'valor' => (string) $texto]], 'texto' => true]);
            }
        }

        return $secciones->filter(fn (array $seccion) => $seccion['campos'] !== [])->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function resumen(Acta $acta): array
    {
        return [
            'id' => $acta->id,
            'tipo' => $acta->tipo,
            'etiqueta' => $acta->etiqueta(),
            'titulo' => $acta->titulo,
            'fecha' => $acta->fecha?->toDateString(),
            'firmado' => $acta->firmado,
            'requiere_firma' => Acta::firmantesDe($acta->tipo) !== [],
            'creado_por' => $acta->creadoPor?->name,
            'legajo' => $acta->legajo ? $this->legajoResumen($acta->legajo) : null,
            'descarga_url' => route('actas.descargar', $acta),
            'vincular_url' => route('actas.vincular', $acta),
        ];
    }

    /**
     * @return array{id: int, numero_legajo: string, url: string}
     */
    private function legajoResumen(Legajo $legajo): array
    {
        return [
            'id' => $legajo->id,
            'numero_legajo' => $legajo->numero_legajo,
            'url' => route('legajos.show', $legajo),
        ];
    }

    /**
     * @return array<int, array{grupo: string, tipos: array<int, array{tipo: string, label: string, descripcion: string, firma: bool}>}>
     */
    private function tipos(): array
    {
        return collect(Acta::TIPOS)
            ->map(fn (array $config, string $tipo) => [
                'tipo' => $tipo,
                'label' => $config['label'],
                'descripcion' => $config['descripcion'],
                'grupo' => $config['grupo'],
                'firma' => Acta::firmantesDe($tipo) !== [],
            ])
            ->groupBy('grupo')
            ->map(fn ($tipos, string $grupo) => ['grupo' => $grupo, 'tipos' => $tipos->map(fn ($tipo) => collect($tipo)->except('grupo')->all())->values()->all()])
            ->values()
            ->all();
    }
}
