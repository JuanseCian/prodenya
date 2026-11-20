import { Form, Head } from '@inertiajs/react';
import { Check, KeyRound, Monitor, ShieldCheck } from 'lucide-react';
import { useRef, useState } from 'react';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import InputError from '@/components/input-error';
import type { Props as ManagePasskeysProps } from '@/components/manage-passkeys';
import ManagePasskeys from '@/components/manage-passkeys';
import type { Props as ManageTwoFactorProps } from '@/components/manage-two-factor';
import ManageTwoFactor from '@/components/manage-two-factor';
import PasswordInput from '@/components/password-input';
import SettingsSection from '@/components/settings-section';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { useUnsavedChanges } from '@/hooks/use-unsaved-changes';
import { edit } from '@/routes/security';

type Session = {
    id: string;
    ip_address: string | null;
    user_agent: string;
    last_active_at: string;
    is_current: boolean;
};

type Props = {
    passwordRules: string;
    passwordRequirements: string;
    canManageSessions: boolean;
    sessions: Session[];
} & ManagePasskeysProps &
    ManageTwoFactorProps;

export default function Security(props: Props) {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);
    const [showRevokeForm, setShowRevokeForm] = useState(false);
    const [passwordDirty, setPasswordDirty] = useState(false);
    const otherSessions = props.sessions.filter(
        (session) => !session.is_current,
    );

    useUnsavedChanges(passwordDirty);

    return (
        <>
            <Head title="Seguridad y contraseña" />

            <div className="space-y-6 sm:space-y-8">
                <SettingsSection
                    icon={KeyRound}
                    eyebrow="Acceso principal"
                    title="Cambiar contraseña"
                    description="Usá una contraseña única que no compartas con otras personas."
                >
                    <Form
                        {...SecurityController.update.form()}
                        options={{ preserveScroll: true }}
                        resetOnError={[
                            'password',
                            'password_confirmation',
                            'current_password',
                        ]}
                        resetOnSuccess
                        onSuccess={() => setPasswordDirty(false)}
                        onError={(errors) => {
                            if (errors.password) {
                                passwordInput.current?.focus();
                            }

                            if (errors.current_password) {
                                currentPasswordInput.current?.focus();
                            }
                        }}
                        className="max-w-2xl space-y-5"
                    >
                        {({ errors, processing, recentlySuccessful }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="current_password">
                                        Contraseña actual
                                    </Label>
                                    <PasswordInput
                                        id="current_password"
                                        ref={currentPasswordInput}
                                        name="current_password"
                                        autoComplete="current-password"
                                        placeholder="Ingresá tu contraseña actual"
                                        className="h-11"
                                        onChange={() => setPasswordDirty(true)}
                                    />
                                    <InputError
                                        message={errors.current_password}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="password">
                                        Nueva contraseña
                                    </Label>
                                    <PasswordInput
                                        id="password"
                                        ref={passwordInput}
                                        name="password"
                                        autoComplete="new-password"
                                        placeholder="Ingresá una nueva contraseña"
                                        passwordrules={props.passwordRules}
                                        className="h-11"
                                        onChange={() => setPasswordDirty(true)}
                                    />
                                    <InputError message={errors.password} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="password_confirmation">
                                        Confirmar contraseña
                                    </Label>
                                    <PasswordInput
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        autoComplete="new-password"
                                        placeholder="Repetí la nueva contraseña"
                                        passwordrules={props.passwordRules}
                                        className="h-11"
                                        onChange={() => setPasswordDirty(true)}
                                    />
                                    <InputError
                                        message={errors.password_confirmation}
                                    />
                                </div>
                                <p className="rounded-lg border border-border bg-surface-soft p-3.5 text-xs leading-relaxed text-muted-foreground">
                                    Requisitos de contraseña:{' '}
                                    <span className="font-semibold text-foreground">
                                        {props.passwordRequirements}
                                    </span>
                                </p>
                                <div className="flex flex-wrap items-center gap-3">
                                    <Button
                                        disabled={processing}
                                        data-test="update-password-button"
                                    >
                                        {processing
                                            ? 'Guardando...'
                                            : 'Guardar contraseña'}
                                    </Button>
                                    {recentlySuccessful && (
                                        <span
                                            className="flex items-center gap-1 text-sm font-medium text-success"
                                            role="status"
                                        >
                                            <Check className="size-4" />{' '}
                                            Contraseña actualizada
                                        </span>
                                    )}
                                </div>
                            </>
                        )}
                    </Form>
                </SettingsSection>

                {props.canManageSessions && (
                    <SettingsSection
                        icon={Monitor}
                        eyebrow="Actividad"
                        title="Sesiones y accesos"
                        description="Revisá dónde está abierta tu cuenta y cerrá los accesos que no reconozcas."
                    >
                        <div className="space-y-3">
                            {props.sessions.map((session) => (
                                <div
                                    key={session.id}
                                    className="flex flex-col gap-3 rounded-xl border border-border bg-surface-soft p-4 transition hover:border-primary/50 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div className="flex min-w-0 items-start gap-3">
                                        <Monitor
                                            className="mt-0.5 size-5 shrink-0 text-primary"
                                            aria-hidden="true"
                                        />
                                        <div className="min-w-0">
                                            <p className="font-medium">
                                                {session.is_current
                                                    ? 'Sesión actual'
                                                    : 'Sesión activa'}
                                            </p>
                                            <p
                                                className="truncate text-sm text-muted-foreground"
                                                title={session.user_agent}
                                            >
                                                {session.user_agent}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                {session.ip_address ??
                                                    'IP no registrada'}{' '}
                                                · Última actividad:{' '}
                                                {new Date(
                                                    session.last_active_at,
                                                ).toLocaleString('es-AR')}
                                            </p>
                                        </div>
                                    </div>
                                    {session.is_current && (
                                        <span className="w-fit rounded-full bg-secondary px-2.5 py-1 text-xs font-semibold text-secondary-foreground">
                                            Este dispositivo
                                        </span>
                                    )}
                                </div>
                            ))}
                        </div>

                        {otherSessions.length > 0 && !showRevokeForm && (
                            <Button
                                type="button"
                                variant="outline"
                                className="mt-5"
                                onClick={() => setShowRevokeForm(true)}
                            >
                                Cerrar las demás sesiones
                            </Button>
                        )}

                        {showRevokeForm && (
                            <Form
                                {...SecurityController.destroyOtherSessions.form()}
                                options={{ preserveScroll: true }}
                                resetOnSuccess
                                onSuccess={() => setShowRevokeForm(false)}
                                className="mt-5 space-y-4 rounded-xl border border-warning/40 bg-warning/10 p-4"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div>
                                            <p className="font-semibold text-foreground">
                                                Confirmá tu identidad
                                            </p>
                                            <p className="mt-1 text-sm leading-6 text-muted-foreground">
                                                Ingresá tu contraseña actual
                                                para cerrar{' '}
                                                {otherSessions.length === 1
                                                    ? 'la otra sesión'
                                                    : 'las demás sesiones'}
                                                .
                                            </p>
                                        </div>
                                        <div className="max-w-sm space-y-2">
                                            <Label htmlFor="revoke_current_password">
                                                Contraseña actual
                                            </Label>
                                            <PasswordInput
                                                id="revoke_current_password"
                                                name="current_password"
                                                autoComplete="current-password"
                                            />
                                            <InputError
                                                message={
                                                    errors.current_password
                                                }
                                            />
                                        </div>
                                        <div className="flex flex-wrap gap-2">
                                            <Button
                                                type="submit"
                                                variant="destructive"
                                                disabled={processing}
                                            >
                                                {processing
                                                    ? 'Cerrando...'
                                                    : 'Confirmar cierre'}
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                onClick={() =>
                                                    setShowRevokeForm(false)
                                                }
                                            >
                                                Cancelar
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </Form>
                        )}
                    </SettingsSection>
                )}

                <SettingsSection
                    icon={ShieldCheck}
                    eyebrow="Defensa adicional"
                    title="Protecciones adicionales"
                    description="Administrá los métodos de seguridad disponibles para tu cuenta."
                >
                    <div className="space-y-8">
                        <ManageTwoFactor
                            canManageTwoFactor={props.canManageTwoFactor}
                            requiresConfirmation={props.requiresConfirmation}
                            twoFactorEnabled={props.twoFactorEnabled}
                        />
                        <ManagePasskeys
                            canManagePasskeys={props.canManagePasskeys}
                            passkeys={props.passkeys}
                        />
                    </div>
                </SettingsSection>
            </div>
        </>
    );
}

Security.layout = {
    breadcrumbs: [
        {
            title: 'Seguridad y contraseña',
            href: edit(),
        },
    ],
};
