<?php

namespace App\Http\Requests;

use App\Models\TipoIntervencion;
use App\Models\User;
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
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_intervencion' => ['required', 'date', 'before_or_equal:today', 'after:2000-01-01'],
            'hora_intervencion' => ['required', 'date_format:H:i'],
            'tipo_intervencion_id' => ['required', Rule::exists(TipoIntervencion::class, 'id')->where('activo', true)],
            'profesional_id' => ['required', Rule::exists(User::class, 'id')->where('activo', true)],
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
        ];
    }

    public function messages(): array
    {
        return [
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
            if ($this->isMethod('post') && ! $this->filled('firma_responsable_dibujo') && ! $this->hasFile('firma_responsable_archivo')) {
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
}
