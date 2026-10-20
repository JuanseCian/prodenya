<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Concerns\HasTeams;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string|null $profesion
 * @property string $email
 * @property string|null $avatar_path
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property int|null $rol_id
 * @property bool $activo
 * @property Carbon|null $ultimo_acceso
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property int|null $current_team_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Rol|null $rol
 * @property-read Team|null $currentTeam
 * @property-read Collection<int, Team> $ownedTeams
 * @property-read Collection<int, Membership> $teamMemberships
 * @property-read Collection<int, Team> $teams
 */
// rol_id y activo NO son fillable a propósito: no deben poder asignarse
// por mass assignment (por ejemplo desde un formulario de registro).
#[Fillable(['name', 'email', 'password', 'dni', 'telefono', 'profesion', 'current_team_id', 'avatar_path'])]
#[Hidden(['password', 'avatar_path', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    protected $table = 'users';

    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_APROBADO = 'aprobado';
    public const ESTADO_RECHAZADO = 'rechazado';
    public const ESTADO_SUSPENDIDO = 'suspendido';
    public const ESTADO_ARCHIVADO = 'archivado';
    /** @var array<int, string> */
    protected $appends = ['avatar'];

    /** @use HasFactory<UserFactory> */
    use HasFactory, HasTeams, MustVerifyEmailTrait, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'activo' => 'boolean',
            'ultimo_acceso' => 'datetime',
            'aprobado_en' => 'datetime',
            'rechazado_en' => 'datetime',
            'suspendido_en' => 'datetime',
            'archivado_en' => 'datetime',
        ];
    }

    public function estaHabilitado(): bool
    {
        return $this->activo
            && $this->estado_cuenta === self::ESTADO_APROBADO
            && $this->rol_id !== null;
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    /**
     * true si el usuario está activo y su rol es alguno de los indicados.
     */
    public function tieneRol(string ...$roles): bool
    {
        $activo = $this->getAttribute('activo');
        $estado = $this->getAttribute('estado_cuenta');

        // Legacy databases may not have the post-pull `activo` column yet.
        return ($activo === null || $activo)
            && ($estado === null || $estado === self::ESTADO_APROBADO)
            && in_array($this->rol?->nombre, $roles, true);
    }

    public function esAdmin(): bool
    {
        return $this->tieneRol('admin');
    }

    /**
     * Get the public URL for the user's avatar without exposing its storage path.
     */
    protected function avatar(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->avatar_path
            ? Storage::disk('public')->url($this->avatar_path)
            : null);
    }
}
