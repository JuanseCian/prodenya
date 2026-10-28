<?php

namespace App\Services;

use App\Models\DerivacionCaso;
use App\Models\Intervencion;
use App\Models\Legajo;
use App\Models\VinculoFamiliar;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class LegajoWordExportService
{
    private const ESTILO_ETIQUETA = ['bold' => true];

    private const ESTILO_TABLA = ['borderSize' => 6, 'borderColor' => 'CCCCCC', 'cellMargin' => 80];

    private const ESTILO_CABECERA = ['bgColor' => 'EDF3EF'];

    public function generar(Legajo $legajo): string
    {
        $legajo->loadMissing([
            'persona.localidad',
            'persona.barrio',
            'problematicas',
            'profesional',
            'intervenciones.tipo',
            'intervenciones.profesional',
            'derivaciones.destinatario',
            'responsablesActivos.usuario',
        ]);

        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(10);
        $phpWord->addTitleStyle(1, ['size' => 18, 'bold' => true, 'color' => '173247']);
        $phpWord->addTitleStyle(2, ['size' => 13, 'bold' => true, 'color' => '2C4A5E']);

        $section = $phpWord->addSection();

        $this->agregarCaratula($section, $legajo);
        $this->agregarDatosLegajo($section, $legajo);
        $this->agregarDatosPersona($section, $legajo);
        $this->agregarIntervenciones($section, $legajo);
        $this->agregarDerivaciones($section, $legajo);
        $this->agregarVinculos($section, $legajo);
        $this->agregarResponsables($section, $legajo);

        $ruta = tempnam(sys_get_temp_dir(), 'legajo_').'.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($ruta);

        return $ruta;
    }

    private function agregarCaratula(Section $section, Legajo $legajo): void
    {
        $persona = $legajo->persona;

        $section->addTitle("Legajo {$legajo->numero_legajo}", 1);

        $tabla = $section->addTable(self::ESTILO_TABLA);
        $this->agregarFilaDato($tabla, 'Número de legajo', $legajo->numero_legajo);
        $this->agregarFilaDato($tabla, 'Apellido y nombre', $persona ? "{$persona->apellido}, {$persona->nombre}" : '—');
        $this->agregarFilaDato($tabla, 'DNI', $persona?->dni ?? '—');
        $this->agregarFilaDato($tabla, 'Fecha de nacimiento', $persona?->fecha_nacimiento?->format('d/m/Y') ?? '—');
        $this->agregarFilaDato($tabla, 'Edad', $persona?->fecha_nacimiento ? "{$persona->fecha_nacimiento->age} años" : '—');
        $this->agregarFilaDato($tabla, 'Estado del legajo', $legajo->estado);
        $this->agregarFilaDato($tabla, 'Fecha de apertura', $legajo->fecha_apertura?->format('d/m/Y') ?? '—');
        $this->agregarFilaDato($tabla, 'Fecha de cierre', $legajo->fecha_cierre?->format('d/m/Y') ?? '—');

        $section->addTextBreak(1);
    }

    private function agregarDatosLegajo(Section $section, Legajo $legajo): void
    {
        $section->addTitle('Datos del legajo', 2);

        $tabla = $section->addTable(self::ESTILO_TABLA);
        $this->agregarFilaDato($tabla, 'Nivel de riesgo', $legajo->nivel_riesgo === 'Critico' ? 'Crítico' : $legajo->nivel_riesgo);
        $this->agregarFilaDato($tabla, 'Origen de la derivación', $legajo->origen_derivacion);
        $this->agregarFilaDato($tabla, 'Profesional asignado', $legajo->profesional?->name ?? '—');
        $this->agregarFilaDato($tabla, 'Problemáticas', $legajo->problematicas->isNotEmpty() ? $legajo->problematicas->pluck('nombre')->implode(', ') : '—');

        $section->addText('Observaciones', self::ESTILO_ETIQUETA);
        $section->addText($legajo->observaciones ?: '—');
        $section->addTextBreak(1);
    }

    private function agregarDatosPersona(Section $section, Legajo $legajo): void
    {
        $persona = $legajo->persona;

        $section->addTitle('Datos de la persona', 2);

        if (! $persona) {
            $section->addText('No hay datos de persona asociados.');
            $section->addTextBreak(1);

            return;
        }

        $direccion = trim(implode(' ', array_filter([
            $persona->direccion_calle,
            $persona->direccion_numero,
            $persona->direccion_piso_depto ? "({$persona->direccion_piso_depto})" : null,
        ])));

        $tabla = $section->addTable(self::ESTILO_TABLA);
        $this->agregarFilaDato($tabla, 'Género', $persona->genero ?: '—');
        $this->agregarFilaDato($tabla, 'Dirección', $direccion !== '' ? $direccion : '—');
        $this->agregarFilaDato($tabla, 'Localidad', $persona->localidad?->nombre ?? '—');
        $this->agregarFilaDato($tabla, 'Barrio', $persona->barrio?->nombre ?? '—');
        $this->agregarFilaDato($tabla, 'Teléfono', $persona->telefono ?: '—');
        $this->agregarFilaDato($tabla, 'Email', $persona->email ?: '—');

        $section->addTextBreak(1);
    }

    private function agregarIntervenciones(Section $section, Legajo $legajo): void
    {
        $section->addTitle('Intervenciones', 2);

        if ($legajo->intervenciones->isEmpty()) {
            $section->addText('No se registraron intervenciones.');
            $section->addTextBreak(1);

            return;
        }

        $tabla = $section->addTable(self::ESTILO_TABLA);
        $this->agregarFilaEncabezado($tabla, ['Fecha', 'Hora', 'Tipo', 'Profesional', 'Detalle']);

        foreach ($legajo->intervenciones as $intervencion) {
            /** @var Intervencion $intervencion */
            $fila = $tabla->addRow();
            $fila->addCell(1600)->addText($intervencion->fecha_intervencion?->format('d/m/Y') ?? '—');
            $fila->addCell(1200)->addText((string) ($intervencion->hora_intervencion ?? '—'));
            $fila->addCell(2000)->addText($intervencion->tipo?->nombre ?? '—');
            $fila->addCell(2000)->addText($intervencion->profesional?->name ?? '—');
            $fila->addCell(3200)->addText($intervencion->detalle ?: '—');
        }

        $section->addTextBreak(1);
    }

    private function agregarDerivaciones(Section $section, Legajo $legajo): void
    {
        $section->addTitle('Derivaciones', 2);

        if ($legajo->derivaciones->isEmpty()) {
            $section->addText('No se registraron derivaciones.');
            $section->addTextBreak(1);

            return;
        }

        $tabla = $section->addTable(self::ESTILO_TABLA);
        $this->agregarFilaEncabezado($tabla, ['Estado', 'Modo', 'Motivo', 'Destinatario', 'Fecha límite']);

        foreach ($legajo->derivaciones as $derivacion) {
            /** @var DerivacionCaso $derivacion */
            $fila = $tabla->addRow();
            $fila->addCell(1800)->addText($derivacion->estado);
            $fila->addCell(1800)->addText($derivacion->modo);
            $fila->addCell(2600)->addText($derivacion->motivo ?: '—');
            $fila->addCell(1800)->addText($derivacion->destinatario?->name ?? '—');
            $fila->addCell(1800)->addText($derivacion->fecha_limite?->format('d/m/Y') ?? '—');
        }

        $section->addTextBreak(1);
    }

    private function agregarVinculos(Section $section, Legajo $legajo): void
    {
        $section->addTitle('Vínculos familiares', 2);

        $vinculos = VinculoFamiliar::query()
            ->with(['persona', 'personaRelacionada'])
            ->whereNull('desvinculado_en')
            ->where(function ($query) use ($legajo) {
                $query->where('persona_id', $legajo->persona_id)
                    ->orWhere('persona_relacionada_id', $legajo->persona_id);
            })
            ->get();

        if ($vinculos->isEmpty()) {
            $section->addText('No se registraron vínculos familiares.');
            $section->addTextBreak(1);

            return;
        }

        foreach ($vinculos as $vinculo) {
            $otraPersona = $vinculo->persona_id === $legajo->persona_id
                ? $vinculo->personaRelacionada
                : $vinculo->persona;

            if (! $otraPersona) {
                continue;
            }

            $section->addListItem("{$vinculo->tipo_vinculo}: {$otraPersona->apellido}, {$otraPersona->nombre}");
        }

        $section->addTextBreak(1);
    }

    private function agregarResponsables(Section $section, Legajo $legajo): void
    {
        $section->addTitle('Responsables del caso', 2);

        if ($legajo->responsablesActivos->isEmpty()) {
            $section->addText('No hay responsables activos asignados.');

            return;
        }

        foreach ($legajo->responsablesActivos as $responsable) {
            $principal = $responsable->es_principal ? ' (principal)' : '';
            $section->addListItem(($responsable->usuario?->name ?? '—').$principal);
        }
    }

    private function agregarFilaDato(Table $tabla, string $etiqueta, string $valor): void
    {
        $fila = $tabla->addRow();
        $fila->addCell(2600)->addText($etiqueta, self::ESTILO_ETIQUETA);
        $fila->addCell(6600)->addText($valor);
    }

    /** @param  array<int, string>  $titulos */
    private function agregarFilaEncabezado(Table $tabla, array $titulos): void
    {
        $fila = $tabla->addRow();

        foreach ($titulos as $titulo) {
            $fila->addCell(1800, self::ESTILO_CABECERA)->addText($titulo, self::ESTILO_ETIQUETA);
        }
    }
}
