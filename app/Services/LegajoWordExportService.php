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
use PhpOffice\PhpWord\SimpleType\Jc;

class LegajoWordExportService
{
    private const COLOR_PRIMARY = '173247';

    private const COLOR_SECONDARY = '2C4A5E';

    private const COLOR_MUTED = '5B6B66';

    private const COLOR_BORDER = 'C9D6D1';

    private const COLOR_LABEL_BG = 'EDF3EF';

    private const COLOR_ZEBRA = 'F3F7F5';

    private const ESTILO_ETIQUETA = ['bold' => true, 'color' => '173247'];

    private const ESTILO_TABLA = ['borderSize' => 6, 'borderColor' => 'C9D6D1', 'cellMargin' => 90];

    private const ESTILO_CABECERA_TABLA = ['bgColor' => '2C4A5E'];

    private const ESTILO_TEXTO_CABECERA = ['bold' => true, 'color' => 'FFFFFF', 'size' => 9];

    private const COLORES_NIVEL_RIESGO = [
        'Bajo' => '2E7D4F',
        'Medio' => 'B8860B',
        'Alto' => 'C0392B',
        'Critico' => '922B21',
    ];

    private const COLORES_ESTADO = [
        'Abierto' => '2E7D4F',
        'Cerrado' => '6B7280',
        'Archivado' => '34495E',
    ];

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
        $phpWord->addTitleStyle(1, ['size' => 22, 'bold' => true, 'color' => self::COLOR_PRIMARY], ['alignment' => Jc::CENTER, 'spaceAfter' => 120]);
        $phpWord->addTitleStyle(2, ['size' => 13, 'bold' => true, 'color' => self::COLOR_SECONDARY], [
            'spaceBefore' => 240,
            'spaceAfter' => 160,
            'borderBottomSize' => 8,
            'borderBottomColor' => self::COLOR_BORDER,
        ]);

        $section = $phpWord->addSection([
            'marginTop' => 850,
            'marginBottom' => 850,
            'marginLeft' => 900,
            'marginRight' => 900,
            'headerHeight' => 850,
            'footerHeight' => 700,
        ]);

        $this->agregarEncabezadoPie($section, $legajo);
        $this->agregarCaratula($section, $legajo);
        $section->addPageBreak();
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

    private function agregarEncabezadoPie(Section $section, Legajo $legajo): void
    {
        $logo = public_path('assets/img/municipalidad_logo.png');

        $header = $section->addHeader();
        $tablaEncabezado = $header->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $fila = $tablaEncabezado->addRow();

        $celdaLogo = $fila->addCell(1600, ['valign' => 'center', 'borderBottomSize' => 6, 'borderBottomColor' => self::COLOR_BORDER]);
        if (is_file($logo)) {
            $celdaLogo->addImage($logo, ['width' => 36, 'height' => 36]);
        }

        $celdaTexto = $fila->addCell(8500, ['valign' => 'center', 'borderBottomSize' => 6, 'borderBottomColor' => self::COLOR_BORDER]);
        $celdaTexto->addText('PRODENYA · Promoción y Protección de Derechos', ['bold' => true, 'color' => self::COLOR_PRIMARY, 'size' => 9], ['alignment' => Jc::END]);
        $celdaTexto->addText("Legajo N.º {$legajo->numero_legajo}", ['color' => self::COLOR_MUTED, 'size' => 8], ['alignment' => Jc::END]);

        $footer = $section->addFooter();
        $tablaPie = $footer->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $filaPie = $tablaPie->addRow();

        $celdaPieIzq = $filaPie->addCell(6600, ['valign' => 'center', 'borderTopSize' => 6, 'borderTopColor' => self::COLOR_BORDER]);
        $celdaPieIzq->addText('Municipalidad de San Nicolás de los Arroyos — Documento confidencial de uso interno', ['color' => self::COLOR_MUTED, 'size' => 8]);

        $celdaPieDer = $filaPie->addCell(3500, ['valign' => 'center', 'borderTopSize' => 6, 'borderTopColor' => self::COLOR_BORDER]);
        $celdaPieDer->addPreserveText('Página {PAGE} de {NUMPAGES}', ['color' => self::COLOR_MUTED, 'size' => 8], ['alignment' => Jc::END]);
    }

    private function agregarCaratula(Section $section, Legajo $legajo): void
    {
        $persona = $legajo->persona;

        $section->addTextBreak(2);
        $section->addTitle("Legajo N.º {$legajo->numero_legajo}", 1);

        $nombreCompleto = $persona ? "{$persona->apellido}, {$persona->nombre}" : 'Sin persona asociada';
        $section->addText($nombreCompleto, ['size' => 15, 'bold' => true, 'color' => self::COLOR_SECONDARY], ['alignment' => Jc::CENTER, 'spaceAfter' => 160]);

        $colorEstado = self::COLORES_ESTADO[$legajo->estado] ?? self::COLOR_MUTED;
        $nivelRiesgo = $legajo->nivel_riesgo === 'Critico' ? 'Crítico' : $legajo->nivel_riesgo;
        $colorNivel = self::COLORES_NIVEL_RIESGO[$legajo->nivel_riesgo] ?? self::COLOR_MUTED;

        $badges = $section->addTextRun(['alignment' => Jc::CENTER, 'spaceAfter' => 320]);
        $badges->addText('ESTADO: ', ['color' => self::COLOR_MUTED, 'size' => 10]);
        $badges->addText(mb_strtoupper($legajo->estado), ['bold' => true, 'color' => $colorEstado, 'size' => 10]);
        $badges->addText('      ·      NIVEL DE RIESGO: ', ['color' => self::COLOR_MUTED, 'size' => 10]);
        $badges->addText(mb_strtoupper($nivelRiesgo), ['bold' => true, 'color' => $colorNivel, 'size' => 10]);

        $tabla = $section->addTable(self::ESTILO_TABLA);
        $this->agregarFilaDato($tabla, 'Número de legajo', (string) $legajo->numero_legajo, 0);
        $this->agregarFilaDato($tabla, 'Apellido y nombre', $nombreCompleto, 1);
        $this->agregarFilaDato($tabla, 'DNI', $persona?->dni ?? '—', 2);
        $this->agregarFilaDato($tabla, 'Fecha de nacimiento', $persona?->fecha_nacimiento?->format('d/m/Y') ?? '—', 3);
        $this->agregarFilaDato($tabla, 'Edad', $persona?->fecha_nacimiento ? "{$persona->fecha_nacimiento->age} años" : '—', 4);
        $this->agregarFilaDato($tabla, 'Fecha de apertura', $legajo->fecha_apertura?->format('d/m/Y') ?? '—', 5);
        $this->agregarFilaDato($tabla, 'Fecha de cierre', $legajo->fecha_cierre?->format('d/m/Y') ?? '—', 6);

        $section->addTextBreak(1);
        $generadoEl = now()->format('d/m/Y').' a las '.now()->format('H:i');
        $section->addText("Documento generado el {$generadoEl} por el sistema PRODENYA.", ['italic' => true, 'color' => self::COLOR_MUTED, 'size' => 8], ['alignment' => Jc::CENTER]);
    }

    private function agregarDatosLegajo(Section $section, Legajo $legajo): void
    {
        $section->addTitle('Datos del legajo', 2);

        $nivelRiesgo = $legajo->nivel_riesgo === 'Critico' ? 'Crítico' : $legajo->nivel_riesgo;
        $colorNivel = self::COLORES_NIVEL_RIESGO[$legajo->nivel_riesgo] ?? self::COLOR_MUTED;

        $tabla = $section->addTable(self::ESTILO_TABLA);
        $this->agregarFilaDato($tabla, 'Nivel de riesgo', $nivelRiesgo, 0, $colorNivel);
        $this->agregarFilaDato($tabla, 'Origen de la derivación', $legajo->origen_derivacion, 1);
        $this->agregarFilaDato($tabla, 'Profesional asignado', $legajo->profesional?->name ?? '—', 2);
        $this->agregarFilaDato($tabla, 'Problemáticas', $legajo->problematicas->isNotEmpty() ? $legajo->problematicas->pluck('nombre')->implode(', ') : '—', 3);

        $section->addTextBreak(1);
        $section->addText('Observaciones', self::ESTILO_ETIQUETA);

        $cajaObservaciones = $section->addTable(['borderSize' => 6, 'borderColor' => self::COLOR_BORDER, 'cellMargin' => 120]);
        $cajaObservaciones->addRow()->addCell(9200, ['bgColor' => 'FAFCFB'])->addText($legajo->observaciones ?: '—');

        $section->addTextBreak(1);
    }

    private function agregarDatosPersona(Section $section, Legajo $legajo): void
    {
        $persona = $legajo->persona;

        $section->addTitle('Datos de la persona', 2);

        if (! $persona) {
            $section->addText('No hay datos de persona asociados.', ['italic' => true, 'color' => self::COLOR_MUTED]);
            $section->addTextBreak(1);

            return;
        }

        $direccion = trim(implode(' ', array_filter([
            $persona->direccion_calle,
            $persona->direccion_numero,
            $persona->direccion_piso_depto ? "({$persona->direccion_piso_depto})" : null,
        ])));

        $tabla = $section->addTable(self::ESTILO_TABLA);
        $this->agregarFilaDato($tabla, 'Género', $persona->genero ?: '—', 0);
        $this->agregarFilaDato($tabla, 'Dirección', $direccion !== '' ? $direccion : '—', 1);
        $this->agregarFilaDato($tabla, 'Localidad', $persona->localidad?->nombre ?? '—', 2);
        $this->agregarFilaDato($tabla, 'Barrio', $persona->barrio?->nombre ?? '—', 3);
        $this->agregarFilaDato($tabla, 'Teléfono', $persona->telefono ?: '—', 4);
        $this->agregarFilaDato($tabla, 'Email', $persona->email ?: '—', 5);

        $section->addTextBreak(1);
    }

    private function agregarIntervenciones(Section $section, Legajo $legajo): void
    {
        $section->addTitle('Intervenciones', 2);

        if ($legajo->intervenciones->isEmpty()) {
            $section->addText('No se registraron intervenciones.', ['italic' => true, 'color' => self::COLOR_MUTED]);
            $section->addTextBreak(1);

            return;
        }

        $tabla = $section->addTable(self::ESTILO_TABLA);
        $this->agregarFilaEncabezado($tabla, [
            ['Fecha', 1600],
            ['Hora', 1200],
            ['Tipo', 2000],
            ['Profesional', 2000],
            ['Detalle', 3200],
        ]);

        foreach ($legajo->intervenciones as $indice => $intervencion) {
            /** @var Intervencion $intervencion */
            $fila = $tabla->addRow();
            $fondo = $this->colorFila($indice);
            $fila->addCell(1600, ['bgColor' => $fondo])->addText($intervencion->fecha_intervencion?->format('d/m/Y') ?? '—');
            $fila->addCell(1200, ['bgColor' => $fondo])->addText((string) ($intervencion->hora_intervencion ?? '—'));
            $fila->addCell(2000, ['bgColor' => $fondo])->addText($intervencion->tipo?->nombre ?? '—');
            $fila->addCell(2000, ['bgColor' => $fondo])->addText($intervencion->profesional?->name ?? '—');
            $fila->addCell(3200, ['bgColor' => $fondo])->addText($intervencion->detalle ?: '—');
        }

        $section->addTextBreak(1);
    }

    private function agregarDerivaciones(Section $section, Legajo $legajo): void
    {
        $section->addTitle('Derivaciones', 2);

        if ($legajo->derivaciones->isEmpty()) {
            $section->addText('No se registraron derivaciones.', ['italic' => true, 'color' => self::COLOR_MUTED]);
            $section->addTextBreak(1);

            return;
        }

        $tabla = $section->addTable(self::ESTILO_TABLA);
        $this->agregarFilaEncabezado($tabla, [
            ['Estado', 1800],
            ['Modo', 1800],
            ['Motivo', 2600],
            ['Destinatario', 1800],
            ['Fecha límite', 1800],
        ]);

        foreach ($legajo->derivaciones as $indice => $derivacion) {
            /** @var DerivacionCaso $derivacion */
            $fila = $tabla->addRow();
            $fondo = $this->colorFila($indice);
            $fila->addCell(1800, ['bgColor' => $fondo])->addText($derivacion->estado);
            $fila->addCell(1800, ['bgColor' => $fondo])->addText($derivacion->modo);
            $fila->addCell(2600, ['bgColor' => $fondo])->addText($derivacion->motivo ?: '—');
            $fila->addCell(1800, ['bgColor' => $fondo])->addText($derivacion->destinatario?->name ?? '—');
            $fila->addCell(1800, ['bgColor' => $fondo])->addText($derivacion->fecha_limite?->format('d/m/Y') ?? '—');
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

        $filas = $vinculos->map(function (VinculoFamiliar $vinculo) use ($legajo) {
            $otraPersona = $vinculo->persona_id === $legajo->persona_id
                ? $vinculo->personaRelacionada
                : $vinculo->persona;

            if (! $otraPersona) {
                return null;
            }

            return [$vinculo->tipo_vinculo, "{$otraPersona->apellido}, {$otraPersona->nombre}"];
        })->filter()->values();

        if ($filas->isEmpty()) {
            $section->addText('No se registraron vínculos familiares.', ['italic' => true, 'color' => self::COLOR_MUTED]);
            $section->addTextBreak(1);

            return;
        }

        $tabla = $section->addTable(self::ESTILO_TABLA);
        $this->agregarFilaEncabezado($tabla, [
            ['Tipo de vínculo', 3200],
            ['Persona vinculada', 6000],
        ]);

        foreach ($filas as $indice => $datos) {
            $fila = $tabla->addRow();
            $fondo = $this->colorFila($indice);
            $fila->addCell(3200, ['bgColor' => $fondo])->addText($datos[0]);
            $fila->addCell(6000, ['bgColor' => $fondo])->addText($datos[1]);
        }

        $section->addTextBreak(1);
    }

    private function agregarResponsables(Section $section, Legajo $legajo): void
    {
        $section->addTitle('Responsables del caso', 2);

        if ($legajo->responsablesActivos->isEmpty()) {
            $section->addText('No hay responsables activos asignados.', ['italic' => true, 'color' => self::COLOR_MUTED]);

            return;
        }

        $tabla = $section->addTable(self::ESTILO_TABLA);
        $this->agregarFilaEncabezado($tabla, [
            ['Nombre', 6400],
            ['Rol', 2800],
        ]);

        foreach ($legajo->responsablesActivos as $indice => $responsable) {
            $fila = $tabla->addRow();
            $fondo = $this->colorFila($indice);
            $fila->addCell(6400, ['bgColor' => $fondo])->addText($responsable->usuario?->name ?? '—');

            $celdaRol = $fila->addCell(2800, ['bgColor' => $fondo]);
            if ($responsable->es_principal) {
                $celdaRol->addText('Principal', ['bold' => true, 'color' => self::COLOR_PRIMARY]);
            } else {
                $celdaRol->addText('Colaborador', ['color' => self::COLOR_MUTED]);
            }
        }
    }

    private function agregarFilaDato(Table $tabla, string $etiqueta, string $valor, int $indice, ?string $colorValor = null): void
    {
        $fondo = $this->colorFila($indice);
        $fila = $tabla->addRow();
        $fila->addCell(2600, ['bgColor' => self::COLOR_LABEL_BG])->addText($etiqueta, self::ESTILO_ETIQUETA);

        $estiloValor = $colorValor ? ['bold' => true, 'color' => $colorValor] : null;
        $fila->addCell(6600, ['bgColor' => $fondo])->addText($valor, $estiloValor);
    }

    /** @param  array<int, array{0: string, 1: int}>  $columnas */
    private function agregarFilaEncabezado(Table $tabla, array $columnas): void
    {
        $fila = $tabla->addRow(null, ['tblHeader' => true]);

        foreach ($columnas as [$titulo, $ancho]) {
            $fila->addCell($ancho, self::ESTILO_CABECERA_TABLA)->addText($titulo, self::ESTILO_TEXTO_CABECERA);
        }
    }

    private function colorFila(int $indice): string
    {
        return $indice % 2 === 0 ? 'FFFFFF' : self::COLOR_ZEBRA;
    }
}
