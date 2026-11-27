<?php

namespace App\Http\Requests;

use App\Models\Acta;
use App\Models\Intervencion;
use App\Models\Legajo;
use App\Models\Rol;
use App\Models\TipoIntervencion;
use App\Models\User;
use App\Support\ValidacionActa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Se usa tanto al crear como al editar una intervención.
 */
class GuardarIntervencionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $legajo = $this->route('legajo');

        return $this->user()?->tieneRol('admin', 'operador', 'coordinador', 'profesional')
            && $legajo instanceof Legajo
            && $this->user()->puedeVerSeccionLegajo('intervenciones');
    }

    private ?string $tipoActaResuelto = null;

    private bool $tipoActaCalculado = false;

    /**
     * Tipo de acta que genera el tipo de intervención elegido, si genera alguno.
     */
    public function tipoActa(): ?string
    {
        if (! $this->tipoActaCalculado) {
            $nombre = TipoIntervencion::query()->whereKey($this->integer('tipo_intervencion_id'))->value('nombre');
            $this->tipoActaResuelto = Acta::tipoDesdeIntervencion($nombre);
            $this->tipoActaCalculado = true;
        }

        return $this->tipoActaResuelto;
    }

    /**
     * Al crear una intervención de tipo acta se completa la hoja del acta, que reemplaza al detalle y las firmas.
     */
    public function generaActa(): bool
    {
        return $this->isMethod('post') && $this->tipoActa() !== null;
    }

    /**
     * Al editar una intervención cuya acta todavía no tiene firmas, se corrige la hoja del
     * acta (que vuelve a reemplazar al detalle) en lugar del texto libre.
     */
    public function actaEditable(): ?Acta
    {
        $intervencion = $this->route('intervencion');

        if (! $this->isMethod('put') || ! $intervencion instanceof Intervencion || ! is_array($this->input('acta'))) {
            return null;
        }

        $acta = $intervencion->acta;

        return $acta && ! Acta::esAuh($acta->tipo) && empty($acta->firmas) ? $acta : null;
    }

    private function tipoActualId(): ?int
    {
        $intervencion = $this->route('intervencion');

        return $intervencion instanceof Intervencion ? (int) $intervencion->tipo_intervencion_id : null;
    }

    private function intervencionConActa(): bool
    {
        $intervencion = $this->route('intervencion');

        return $intervencion instanceof Intervencion && $intervencion->acta()->exists();
    }

    protected function prepareForValidation(): void
    {
        if ($this->generaActa() && is_array($this->input('acta'))) {
            $this->merge(['acta' => ValidacionActa::prepararFirmas($this->tipoActa(), $this->input('acta'))]);
        }
    }

    public function rules(): array
    {
        $reglas = $this->reglasBase();

        if ($this->generaActa()) {
            $reglas['detalle'] = ['nullable', 'string', 'max:10000'];
            $reglas['firma_nombre'] = ['nullable', 'string', 'max:150'];
            $reglas['firma_aclaracion'] = ['nullable', 'string', 'max:150'];
            $reglas['acta'] = ['nullable', 'array'];
            $reglas += ValidacionActa::reglas($this->tipoActa(), 'acta.');
        }

        if ($acta = $this->actaEditable()) {
            $reglas['detalle'] = ['nullable', 'string', 'max:10000'];
            $reglas['acta'] = ['array'];
            $reglas += ValidacionActa::reglas($acta->tipo, 'acta.');
        }

        if ($this->isMethod('put') && $this->intervencionConActa()) {
            // El detalle de una intervención con acta sale del acta: no se edita aparte.
            $reglas['detalle'] = ['nullable', 'string', 'max:10000'];
        }

        if ($this->isMethod('put')) {
            // Al editar no se cambian las firmas: se conservan las que ya tiene la intervención.
            $reglas['firma_nombre'] = ['nullable', 'string', 'max:150'];
            $reglas['firma_aclaracion'] = ['nullable', 'string', 'max:150'];
        }

        return $reglas;
    }

    /**
     * @return array<string, mixed>
     */
    private function reglasBase(): array
    {
        return [
            'fecha_intervencion' => ['required', 'date', 'before_or_equal:today', 'after:2000-01-01'],
            'hora_intervencion' => ['required', 'date_format:H:i'],
            // Solo tipos visibles; al editar se acepta además el tipo que ya tenía (aunque esté oculto).
            'tipo_intervencion_id' => [
                'required',
                Rule::exists(TipoIntervencion::class, 'id')->where(fn ($query) => $query
                    ->where('activo', true)
                    ->when($this->tipoActualId(), fn ($actual, int $id) => $actual->orWhere('id', $id))),
            ],
            'profesional_id' => [
                'required',
                Rule::exists(User::class, 'id')
                    ->where('activo', true)
                    ->where('estado_cuenta', User::ESTADO_APROBADO)
                    ->where('rol_id', Rol::query()->where('nombre', 'profesional')->value('id')),
            ],
            'detalle' => ['required', 'string', 'max:10000'],
            'firma_nombre' => ['required', 'string', 'max:150'],
            'firma_aclaracion' => ['required', 'string', 'max:150'],
            'firma_responsable_dibujo' => ['nullable', 'string', 'max:3000000', 'regex:/^data:image\/png;base64,/'],
            'firma_responsable_archivo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,pdf', 'max:5120'],
            'firma_entrevistado_nombre' => ['nullable', 'string', 'max:150'],
            'firma_entrevistado_aclaracion' => ['nullable', 'string', 'max:150'],
            'firma_entrevistado_dibujo' => ['nullable', 'string', 'max:3000000', 'regex:/^data:image\/png;base64,/'],
            'firma_entrevistado_archivo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,pdf', 'max:5120'],
            'archivos' => ['nullable', 'array', 'max:10'],
            'archivos.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'legajos_destino_ids' => ['nullable', 'array', 'max:10'],
            'legajos_destino_ids.*' => [
                'integer',
                'distinct',
                Rule::exists(Legajo::class, 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        $tipoMensajes = $this->generaActa() ? $this->tipoActa() : $this->actaEditable()?->tipo;

        return ($tipoMensajes ? ValidacionActa::mensajes($tipoMensajes, 'acta.') : []) + [
            'fecha_intervencion.required' => 'Ingresá la fecha de la intervención.',
            'fecha_intervencion.date' => 'La fecha de la intervención no es válida.',
            'fecha_intervencion.before_or_equal' => 'La fecha de la intervención no puede ser posterior a hoy.',
            'fecha_intervencion.after' => 'La fecha de la intervención no puede ser anterior al año 2000.',
            'hora_intervencion.required' => 'Ingresá el horario de la intervención.',
            'hora_intervencion.date_format' => 'El horario debe tener el formato HH:MM.',
            'tipo_intervencion_id.required' => 'Seleccioná el tipo de intervención.',
            'tipo_intervencion_id.exists' => 'El tipo de intervención seleccionado no es válido o está inactivo.',
            'profesional_id.required' => 'Seleccioná el profesional que realizó la intervención.',
            'profesional_id.exists' => 'El profesional seleccionado no es válido o está inactivo.',
            'detalle.required' => 'Ingresá el detalle o la evolución de la intervención.',
            'detalle.max' => 'El detalle no puede superar los 10.000 caracteres.',
            'firma_nombre.required' => 'Ingresá el nombre de quien firma la intervención.',
            'firma_nombre.max' => 'El nombre de la firma no puede superar los 150 caracteres.',
            'firma_aclaracion.required' => 'Ingresá la aclaración de la firma.',
            'firma_aclaracion.max' => 'La aclaración no puede superar los 150 caracteres.',
            'firma_responsable_dibujo.regex' => 'La firma dibujada no tiene un formato válido.',
            'firma_responsable_archivo.mimes' => 'La firma debe ser PNG, JPG o PDF.',
            'firma_responsable_archivo.max' => 'El archivo de firma no puede superar los 5 MB.',
            'firma_entrevistado_nombre.max' => 'El nombre del entrevistado no puede superar los 150 caracteres.',
            'firma_entrevistado_aclaracion.max' => 'La aclaración del entrevistado no puede superar los 150 caracteres.',
            'firma_entrevistado_dibujo.regex' => 'La firma del entrevistado no tiene un formato válido.',
            'firma_entrevistado_archivo.mimes' => 'La firma del entrevistado debe ser PNG, JPG o PDF.',
            'firma_entrevistado_archivo.max' => 'El archivo de firma del entrevistado no puede superar los 5 MB.',
            'archivos.max' => 'Podés adjuntar hasta 10 archivos por intervención.',
            'archivos.*.file' => 'Uno de los adjuntos no es un archivo válido.',
            'archivos.*.mimes' => 'Los adjuntos deben ser PDF, JPG o PNG.',
            'archivos.*.max' => 'Cada adjunto puede pesar hasta 10 MB.',
            'archivos.*.uploaded' => 'No se pudo subir uno de los archivos (probablemente supera el límite del servidor, upload_max_filesize en php.ini).',
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->isMethod('put')) {
                $this->validarTipoAlEditar($validator);
            }

            if ($acta = $this->actaEditable()) {
                ValidacionActa::validarFirmas($validator, $acta->tipo, (array) $this->input('acta', []), 'acta.');

                return;
            }

            if ($this->generaActa()) {
                ValidacionActa::validarFirmas($validator, $this->tipoActa(), (array) $this->input('acta', []), 'acta.');

                return;
            }

            // Al editar, las firmas ya registradas se conservan: no se vuelven a pedir.
            if ($this->isMethod('put')) {
                return;
            }

            if (! $this->filled('firma_responsable_dibujo') && ! $this->hasFile('firma_responsable_archivo')) {
                $validator->errors()->add('firma_responsable_digital', 'Dibujá la firma o subí un archivo de firma.');
            }

            $entrevistadoCompleto = $this->filled('firma_entrevistado_nombre')
                || $this->filled('firma_entrevistado_aclaracion')
                || $this->filled('firma_entrevistado_dibujo')
                || $this->hasFile('firma_entrevistado_archivo');

            if (! $entrevistadoCompleto) {
                return;
            }

            if (! $this->filled('firma_entrevistado_nombre')) {
                $validator->errors()->add('firma_entrevistado_nombre', 'Ingresá el nombre del entrevistado que firma.');
            }

            if (! $this->filled('firma_entrevistado_aclaracion')) {
                $validator->errors()->add('firma_entrevistado_aclaracion', 'Ingresá la aclaración del entrevistado.');
            }

            if (! $this->filled('firma_entrevistado_dibujo') && ! $this->hasFile('firma_entrevistado_archivo')) {
                $validator->errors()->add('firma_entrevistado_digital', 'Dibujá la firma del entrevistado o subí un archivo.');
            }
        });
    }

    /**
     * Una intervención con acta conserva su tipo, y un acta solo se genera al registrar una intervención nueva.
     */
    private function validarTipoAlEditar(Validator $validator): void
    {
        $intervencion = $this->route('intervencion');

        if (! $intervencion instanceof Intervencion) {
            return;
        }

        $cambiaTipo = (int) $this->input('tipo_intervencion_id') !== (int) $intervencion->tipo_intervencion_id;

        if ($this->intervencionConActa() && $cambiaTipo) {
            $validator->errors()->add('tipo_intervencion_id', 'Esta intervención tiene un acta: no se puede cambiar su tipo.');
        } elseif (! $this->intervencionConActa() && $this->tipoActa() !== null) {
            $validator->errors()->add('tipo_intervencion_id', 'Para hacer un acta, registrá una nueva intervención de ese tipo.');
        }
    }
}
