<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Documento extends Model
{
    protected $table = 'documentos';

    const CREATED_AT = 'fecha_subida';
    const UPDATED_AT = null;

    protected $fillable = [
        'persona_id',
        'legajo_id',
        'tipo_documento_id',
        'nombre_archivo',
        'ruta_archivo',
        'tamanio_bytes',
        'subido_por',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function legajo(): BelongsTo
    {
        return $this->belongsTo(Legajo::class, 'legajo_id');
    }

    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id');
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'subido_por');
    }

    public function descargas(): HasMany
    {
        return $this->hasMany(AuditoriaDescarga::class, 'documento_id');
    }

    /**
     * Registra una descarga del documento. Usar en el controller
     * antes de enviar el archivo al navegador.
     */
    public function registrarDescarga(Usuario $usuario): AuditoriaDescarga
    {
        return $this->descargas()->create([
            'usuario_id' => $usuario->id,
            'ip_origen' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
