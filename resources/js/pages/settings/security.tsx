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
                <div>
                    <p className="mb-2 text-xs font-bold tracking-[0.16em] text-[#5d9b7a] uppercase">Protección de acceso</p>
                    <h1 className="text-2xl font-semibold tracking-[-0.03em] text-[#173247] sm:text-3xl">Seguridad</h1>
                    <p className="mt-2 text-sm leading-6 text-[#63757e] sm:text-base">Protegé tu cuenta y revisá los accesos activos.</p>
                </div>

                <section
                    className="rounded-2xl border border-[#d7e5dd] bg-white p-5 shadow-[0_12px_32px_rgba(23,50,71,0.06)] sm:p-7"
                    aria-labelledby="password-title"
                >
                    <div className="mb-7 flex items-start gap-3 border-b border-[#e5eee8] pb-5">
                        <span className="flex size-10 shrink-0 items-center justify-center rounded-xl bg-[#e7f4eb] text-[#4f916d]" aria-hidden="true">
                            <KeyRound className="size-5" />
                        </span>
                        <div>
                            <h2 id="password-title" className="font-semibold text-[#173247]">Cambiar contraseña</h2>
                            <p className="mt-1 text-sm leading-5 text-[#71828a]">Usá una contraseña única que no compartas con otras personas.</p>
                        </div>
                    </div>

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
                        className="max-w-xl space-y-5"
                    >
                        {({ errors, processing, recentlySuccessful }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="current_password" className="text-sm font-semibold text-[#31505d]">
                                        Contraseña actual
                                    </Label>
                                    <PasswordInput
                                        id="current_password"
                                        ref={currentPasswordInput}
                                        name="current_password"
                                        autoComplete="current-password"
                                        placeholder="Ingresá tu contraseña actual"
                                        className="h-11 rounded-xl border-[#d7e5dd] bg-[#fbfdfc] focus-visible:border-[#78b58f] focus-visible:ring-[#c7e7d2]"
                                        onChange={() => setPasswordDirty(true)}
                                    />
                                    <InputError
                                        message={errors.current_password}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="password" className="text-sm font-semibold text-[#31505d]">
                                        Nueva contraseña
                                    </Label>
                                    <PasswordInput
                                        id="password"
                                        ref={passwordInput}
                                        name="password"
                                        autoComplete="new-password"
                                        placeholder="Ingresá una nueva contraseña"
                                        passwordrules={props.passwordRules}
                                        className="h-11 rounded-xl border-[#d7e5dd] bg-[#fbfdfc] focus-visible:border-[#78b58f] focus-visible:ring-[#c7e7d2]"
                                        onChange={() => setPasswordDirty(true)}
                                    />
                                    <InputError message={errors.password} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="password_confirmation" className="text-sm font-semibold text-[#31505d]">
                                        Confirmar contraseña
                                    </Label>
                                    <PasswordInput
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        autoComplete="new-password"
                                        placeholder="Repetí la nueva contraseña"
                                        passwordrules={props.passwordRules}
                                        className="h-11 rounded-xl border-[#d7e5dd] bg-[#fbfdfc] focus-visible:border-[#78b58f] focus-visible:ring-[#c7e7d2]"
                                        onChange={() => setPasswordDirty(true)}
                                    />
                                    <InputError
                                        message={errors.password_confirmation}
                                    />
                                </div>
                                <p className="rounded-xl border border-[#e1eee7] bg-[#f5faf7] p-3.5 text-xs leading-relaxed text-[#71828a]">
                                    Requisitos de contraseña:{' '}
                                    <span className="font-semibold text-[#31505d]">
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
                                            className="flex items-center gap-1 text-sm font-medium text-[#397461]"
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
                </section>

                {props.canManageSessions && (
                    <section
                        className="rounded-2xl border border-[#d7e5dd] bg-white p-5 shadow-[0_12px_32px_rgba(23,50,71,0.06)] sm:p-7"
                        aria-labelledby="sessions-title"
                    >
                        <div className="mb-6 flex items-start gap-3 border-b border-[#e5eee8] pb-5">
                            <span className="flex size-10 shrink-0 items-center justify-center rounded-xl bg-[#e7f4eb] text-[#4f916d]" aria-hidden="true">
                                <Monitor className="size-5" />
                            </span>
                            <div>
                                <h2 id="sessions-title" className="font-semibold text-[#173247]">Sesiones y accesos</h2>
                                <p className="mt-1 text-sm leading-5 text-[#71828a]">Estos son los accesos reales registrados por el sistema.</p>
                            </div>
                        </div>

                        <div className="space-y-3">
                            {props.sessions.map((session) => (
                                <div
                                    key={session.id}
                                    className="flex flex-col gap-3 rounded-xl border border-[#e1eee7] bg-[#fbfdfc] p-4 transition hover:border-[#cfe3d7] sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div className="flex min-w-0 items-start gap-3">
                                        <Monitor
                                                className="mt-0.5 size-5 shrink-0 text-[#5d9b7a]"
                                            aria-hidden="true"
                                        />
                                        <div className="min-w-0">
                                            <p className="font-medium">
                                                {session.is_current
                                                    ? 'Sesión actual'
                                                    : 'Sesión activa'}
                                            </p>
                                            <p
                                                className="truncate text-sm text-[#63757e]"
                                                title={session.user_agent}
                                            >
                                                {session.user_agent}
                                            </p>
                                            <p className="text-xs text-[#819197]">
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
                                        <span className="w-fit rounded-full bg-[#e1f2e7] px-2.5 py-1 text-xs font-semibold text-[#397461]">
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
                                className="mt-5 rounded-xl border-[#cfe3d7] text-[#315b52] hover:bg-[#eef8f2]"
                                onClick={() => setShowRevokeForm(true)}
                            >
                                Cerrar las demás sesiones
                            </Button>
                        )}

                        {showRevokeForm && (
                            <Form
                                action="/settings/sessions/other"
                                method="delete"
                                options={{ preserveScroll: true }}
                                resetOnSuccess
                                onSuccess={() => setShowRevokeForm(false)}
                                className="mt-5 space-y-4 rounded-2xl border border-[#f1dfb4] bg-[#fff9e8] p-4"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div>
                                            <p className="font-semibold text-[#5d4825]">
                                                Confirmá tu identidad
                                            </p>
                                            <p className="mt-1 text-sm leading-6 text-[#806c4b]">
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
                    </section>
                )}

                <section
                    className="rounded-2xl border border-[#d7e5dd] bg-white p-5 shadow-[0_12px_32px_rgba(23,50,71,0.06)] sm:p-7"
                    aria-labelledby="advanced-security-title"
                >
                    <div className="mb-6 flex items-start gap-3 border-b border-[#e5eee8] pb-5">
                        <span className="flex size-10 shrink-0 items-center justify-center rounded-xl bg-[#e7f4eb] text-[#4f916d]" aria-hidden="true">
                            <ShieldCheck className="size-5" />
                        </span>
                        <div>
                            <h2 id="advanced-security-title" className="font-semibold text-[#173247]">Protecciones adicionales</h2>
                            <p className="mt-1 text-sm leading-5 text-[#71828a]">Administrá los métodos de seguridad disponibles para tu cuenta.</p>
                        </div>
                    </div>
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
                </section>
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
