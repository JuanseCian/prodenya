<?php

namespace App\Http\Requests;

use App\Models\TipoIntervencion;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'tipo_intervencion_id' => ['required', Rule::exists(TipoIntervencion::class, 'id')->where('activo', true)],
            'profesional_id' => ['required', Rule::exists(User::class, 'id')->where('activo', true)],
            'detalle' => ['required', 'string', 'max:10000'],
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
            'tipo_intervencion_id.required' => 'Seleccioná el tipo de intervención.',
            'tipo_intervencion_id.exists' => 'El tipo de intervención seleccionado no es válido o está inactivo.',
            'profesional_id.required' => 'Seleccioná el profesional que realizó la intervención.',
            'profesional_id.exists' => 'El profesional seleccionado no es válido o está inactivo.',
            'detalle.required' => 'Ingresá el detalle o la evolución de la intervención.',
            'detalle.max' => 'El detalle no puede superar los 10.000 caracteres.',
            'archivos.max' => 'Podés adjuntar hasta 10 archivos por intervención.',
            'archivos.*.file' => 'Uno de los adjuntos no es un archivo válido.',
            'archivos.*.mimes' => 'Los adjuntos deben ser PDF, JPG o PNG.',
            'archivos.*.max' => 'Cada adjunto puede pesar hasta 10 MB.',
            'archivos.*.uploaded' => 'No se pudo subir uno de los archivos (probablemente supera el límite del servidor, upload_max_filesize en php.ini).',
        ];
    }
}
