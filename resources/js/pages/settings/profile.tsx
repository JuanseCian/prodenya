import { Form, Head, Link, usePage } from '@inertiajs/react';
import type { InertiaLinkProps } from '@inertiajs/react';
import {
    ArrowUpRight,
    BriefcaseBusiness,
    CalendarDays,
    Camera,
    Check,
    Clock3,
    Copy,
    ImageOff,
    Mail,
    Palette,
    ShieldCheck,
    UserRound,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useEffect, useState } from 'react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import InputError from '@/components/input-error';
import SettingsSection from '@/components/settings-section';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useUnsavedChanges } from '@/hooks/use-unsaved-changes';
import { edit as editAppearance } from '@/routes/appearance';
import { edit } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import { send } from '@/routes/verification';
import type { Auth } from '@/types';

type PageProps = {
    auth: Auth;
};

type AccountDetails = {
    team: string | null;
    role: string | null;
    createdAt: string | null;
    lastAccess: string | null;
};

function formatDate(value: string | null, withTime = false): string {
    if (!value) {
        return 'No registrado';
    }

    return new Date(value).toLocaleString('es-AR', {
        dateStyle: 'medium',
        ...(withTime ? { timeStyle: 'short' } : {}),
    });
}

function AccountFact({
    icon: Icon,
    label,
    value,
}: {
    icon: LucideIcon;
    label: string;
    value: string;
}) {
    return (
        <div className="rounded-2xl border border-border bg-surface-soft p-4 transition-colors hover:border-primary/30">
            <div className="flex items-center gap-2 text-primary">
                <Icon className="size-4" aria-hidden="true" />
                <p className="text-[length:calc(0.65rem_+_2px)] font-bold tracking-[0.13em] uppercase">
                    {label}
                </p>
            </div>
            <p className="mt-3 text-sm font-semibold text-foreground">
                {value}
            </p>
        </div>
    );
}

function QuickLink({
    href,
    icon: Icon,
    title,
    description,
}: {
    href: NonNullable<InertiaLinkProps['href']>;
    icon: LucideIcon;
    title: string;
    description: string;
}) {
    return (
        <Link
            href={href}
            className="group flex items-start gap-3 rounded-2xl border border-border bg-surface-soft p-4 transition-[border-color,background-color,transform] hover:-translate-y-0.5 hover:border-primary/35 hover:bg-accent"
        >
            <span className="flex size-9 shrink-0 items-center justify-center rounded-xl bg-secondary text-primary">
                <Icon className="size-4" aria-hidden="true" />
            </span>
            <span className="min-w-0">
                <span className="flex items-center gap-1 text-sm font-semibold text-foreground">
                    {title}
                    <ArrowUpRight className="size-3.5 text-primary opacity-0 transition-opacity group-hover:opacity-100" />
                </span>
                <span className="mt-1 block text-xs leading-5 text-muted-foreground">
                    {description}
                </span>
            </span>
        </Link>
    );
}

export default function Profile({
    mustVerifyEmail,
    status,
    account,
}: {
    mustVerifyEmail: boolean;
    status?: string;
    account: AccountDetails;
}) {
    const { auth } = usePage<PageProps>().props;
    const [avatarPreview, setAvatarPreview] = useState<string | null>(null);
    const [profileDirty, setProfileDirty] = useState(false);

    useUnsavedChanges(profileDirty);

    useEffect(() => {
        return () => {
            if (avatarPreview?.startsWith('blob:')) {
                URL.revokeObjectURL(avatarPreview);
            }
        };
    }, [avatarPreview]);

    const initials = auth.user.name
        .split(' ')
        .map((part) => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
    const isVerified = !mustVerifyEmail || auth.user.email_verified_at !== null;

    return (
        <>
            <Head title="Mi perfil" />

            <div className="space-y-6 sm:space-y-8">
                <section className="relative overflow-hidden rounded-[1.75rem] bg-brand-strong text-white shadow-[0_24px_60px_-32px_rgba(23,50,71,0.75)] dark:bg-[#173247]">
                    <div className="absolute -top-20 -right-14 size-56 rounded-full border border-white/10" />
                    <div className="absolute -right-2 -bottom-28 size-64 rounded-full border border-primary-foreground/10" />
                    <div className="relative p-5 sm:p-7 lg:p-8">
                        <div className="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                            <div className="flex min-w-0 items-end gap-4 sm:gap-5">
                                <Avatar className="size-20 shrink-0 rounded-[1.35rem] border-4 border-white/20 bg-white/10 shadow-xl sm:size-24">
                                    <AvatarImage
                                        src={auth.user.avatar}
                                        alt={`Foto de ${auth.user.name}`}
                                    />
                                    <AvatarFallback className="rounded-[1rem] bg-white/15 text-2xl font-semibold text-white">
                                        {initials}
                                    </AvatarFallback>
                                </Avatar>
                                <div className="min-w-0 pb-1">
                                    <div className="mb-2 flex flex-wrap items-center gap-2">
                                        <span className="rounded-full bg-white/10 px-2.5 py-1 text-[length:calc(0.65rem_+_2px)] font-bold tracking-[0.13em] text-white/80 uppercase ring-1 ring-white/15">
                                            Perfil personal
                                        </span>
                                        <span className="inline-flex items-center gap-1.5 rounded-full bg-success/20 px-2.5 py-1 text-[length:calc(0.65rem_+_2px)] font-semibold text-[#b9f0cf]">
                                            <span className="size-1.5 rounded-full bg-[#9ce0bf]" />
                                            Activa
                                        </span>
                                    </div>
                                    <h1 className="truncate text-2xl font-semibold tracking-[-0.04em] sm:text-3xl">
                                        {auth.user.name}
                                    </h1>
                                    <p className="mt-2 flex items-center gap-2 truncate text-sm text-white/65">
                                        <Mail className="size-4 shrink-0" />
                                        {auth.user.email}
                                    </p>
                                </div>
                            </div>
                            <Link
                                href={editSecurity()}
                                className="inline-flex w-fit items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-3.5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-white/15"
                            >
                                <ShieldCheck className="size-4 text-[#9ce0bf]" />
                                Revisar seguridad
                                <ArrowUpRight className="size-3.5" />
                            </Link>
                        </div>
                    </div>
                </section>

                <SettingsSection
                    icon={UserRound}
                    eyebrow="Identidad"
                    title="Información personal"
                    description="Estos datos identifican tu cuenta dentro de la plataforma."
                >
                    <div className="grid gap-8 xl:grid-cols-[minmax(0,1fr)_18rem]">
                        <Form
                            {...ProfileController.update.form()}
                            options={{ preserveScroll: true }}
                            onSuccess={() => setProfileDirty(false)}
                            className="order-2 space-y-5 xl:order-1"
                        >
                            {({ processing, errors, recentlySuccessful }) => (
                                <>
                                    <div className="grid gap-5 sm:grid-cols-2">
                                        <div className="grid gap-2 sm:col-span-2">
                                            <Label htmlFor="name">
                                                Nombre y apellido
                                            </Label>
                                            <Input
                                                id="name"
                                                name="name"
                                                defaultValue={auth.user.name}
                                                required
                                                autoComplete="name"
                                                className="h-11 bg-background"
                                                onChange={() =>
                                                    setProfileDirty(true)
                                                }
                                            />
                                            <InputError message={errors.name} />
                                        </div>
                                        <div className="grid gap-2 sm:col-span-2">
                                            <div className="flex items-center justify-between gap-3">
                                                <Label htmlFor="email">
                                                    Correo electrónico
                                                </Label>
                                                {isVerified ? (
                                                    <span className="inline-flex items-center gap-1 text-xs font-semibold text-success">
                                                        <Check className="size-3.5" />
                                                        Verificado
                                                    </span>
                                                ) : (
                                                    <span className="text-xs font-semibold text-warning">
                                                        Pendiente de
                                                        verificación
                                                    </span>
                                                )}
                                            </div>
                                            <Input
                                                id="email"
                                                type="email"
                                                name="email"
                                                defaultValue={auth.user.email}
                                                required
                                                autoComplete="email"
                                                className="h-11 bg-background"
                                                onChange={() =>
                                                    setProfileDirty(true)
                                                }
                                            />
                                            <InputError
                                                message={errors.email}
                                            />
                                        </div>
                                    </div>

                                    {mustVerifyEmail &&
                                        auth.user.email_verified_at ===
                                            null && (
                                            <div className="rounded-2xl border border-warning/30 bg-warning/10 p-4 text-sm leading-6 text-foreground">
                                                <p>
                                                    Verificá tu correo para
                                                    mantener protegida tu cuenta
                                                    y recibir comunicaciones
                                                    importantes.
                                                </p>
                                                <Link
                                                    href={send()}
                                                    as="button"
                                                    className="mt-2 font-semibold text-primary underline underline-offset-4"
                                                >
                                                    Reenviar correo de
                                                    verificación
                                                </Link>
                                                {status ===
                                                    'verification-link-sent' && (
                                                    <p
                                                        className="mt-2 font-medium text-success"
                                                        role="status"
                                                    >
                                                        Se envió un nuevo enlace
                                                        de verificación.
                                                    </p>
                                                )}
                                            </div>
                                        )}

                                    <div className="flex flex-wrap items-center gap-3 border-t border-border pt-5">
                                        <Button
                                            disabled={processing}
                                            data-test="update-profile-button"
                                        >
                                            {processing
                                                ? 'Guardando...'
                                                : 'Guardar cambios'}
                                        </Button>
                                        {recentlySuccessful && (
                                            <span
                                                className="flex items-center gap-1 text-sm font-medium text-success"
                                                role="status"
                                            >
                                                <Check className="size-4" />
                                                Cambios guardados
                                            </span>
                                        )}
                                    </div>
                                </>
                            )}
                        </Form>

                        <div className="order-1 xl:order-2">
                            <div className="rounded-2xl border border-border bg-surface-soft p-4">
                                <div className="flex items-center gap-3">
                                    <Avatar className="size-12 rounded-xl">
                                        <AvatarImage
                                            src={
                                                avatarPreview ??
                                                auth.user.avatar
                                            }
                                            alt={`Foto de ${auth.user.name}`}
                                        />
                                        <AvatarFallback className="rounded-xl bg-secondary font-semibold text-secondary-foreground">
                                            {initials}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div className="min-w-0">
                                        <p className="text-sm font-semibold text-foreground">
                                            Foto de perfil
                                        </p>
                                        <p className="mt-0.5 text-xs text-muted-foreground">
                                            JPG, PNG o WEBP · máximo 2 MB
                                        </p>
                                    </div>
                                </div>

                                <Form
                                    {...ProfileController.updateAvatar.form()}
                                    encType="multipart/form-data"
                                    options={{ preserveScroll: true }}
                                    className="mt-4"
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            <Label
                                                htmlFor="avatar"
                                                className="inline-flex h-10 w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-input bg-background px-3 text-sm font-semibold text-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                                            >
                                                <Camera className="size-4" />
                                                Elegir imagen
                                            </Label>
                                            <Input
                                                id="avatar"
                                                name="avatar"
                                                type="file"
                                                accept="image/jpeg,image/png,image/webp"
                                                className="sr-only"
                                                disabled={processing}
                                                onChange={(event) => {
                                                    const file =
                                                        event.target.files?.[0];
                                                    setAvatarPreview(
                                                        file
                                                            ? URL.createObjectURL(
                                                                  file,
                                                              )
                                                            : null,
                                                    );
                                                }}
                                            />
                                            <Button
                                                type="submit"
                                                size="sm"
                                                className="mt-2 w-full rounded-xl"
                                                disabled={processing}
                                            >
                                                {processing
                                                    ? 'Guardando...'
                                                    : 'Guardar foto'}
                                            </Button>
                                            <InputError
                                                className="mt-2"
                                                message={errors.avatar}
                                            />
                                        </>
                                    )}
                                </Form>

                                {auth.user.avatar && (
                                    <Form
                                        {...ProfileController.destroyAvatar.form()}
                                        options={{ preserveScroll: true }}
                                        className="mt-2"
                                    >
                                        {({ processing }) => (
                                            <Button
                                                type="submit"
                                                variant="ghost"
                                                size="sm"
                                                className="w-full rounded-xl text-destructive hover:bg-destructive/10 hover:text-destructive"
                                                disabled={processing}
                                            >
                                                <ImageOff className="size-4" />
                                                Quitar foto
                                            </Button>
                                        )}
                                    </Form>
                                )}
                            </div>
                        </div>
                    </div>
                </SettingsSection>

                <SettingsSection
                    icon={BriefcaseBusiness}
                    eyebrow="Contexto de trabajo"
                    title="Tu cuenta en Prodenya"
                    description="Información operativa que se asigna desde la gestión institucional."
                >
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <AccountFact
                            icon={BriefcaseBusiness}
                            label="Equipo"
                            value={account.team ?? 'Sin equipo asignado'}
                        />
                        <AccountFact
                            icon={ShieldCheck}
                            label="Rol"
                            value={account.role ?? 'Sin rol asignado'}
                        />
                        <AccountFact
                            icon={Copy}
                            label="Identificador"
                            value={String(auth.user.id)}
                        />
                        <AccountFact
                            icon={CalendarDays}
                            label="Alta"
                            value={formatDate(account.createdAt)}
                        />
                        <div className="rounded-2xl border border-border bg-surface-soft p-4 sm:col-span-2 lg:col-span-4">
                            <div className="flex items-center gap-2 text-primary">
                                <Clock3 className="size-4" />
                                <p className="text-[length:calc(0.65rem_+_2px)] font-bold tracking-[0.13em] uppercase">
                                    Último acceso
                                </p>
                            </div>
                            <p className="mt-3 text-sm font-semibold text-foreground">
                                {formatDate(account.lastAccess, true)}
                            </p>
                        </div>
                    </div>
                    <div className="mt-5 flex items-start gap-2 rounded-2xl border border-primary/15 bg-secondary/60 p-4 text-xs leading-5 text-muted-foreground">
                        <ShieldCheck className="mt-0.5 size-4 shrink-0 text-primary" />
                        <p>
                            El equipo y el rol se administran desde la gestión
                            institucional para mantener consistencia en tus
                            permisos.
                        </p>
                    </div>
                </SettingsSection>

                <SettingsSection
                    icon={ShieldCheck}
                    eyebrow="Accesos rápidos"
                    title="Protegé tu experiencia"
                    description="Revisá las opciones que mantienen tu cuenta segura y adaptada a tu forma de trabajar."
                >
                    <div className="grid gap-3 md:grid-cols-2">
                        <QuickLink
                            href={editSecurity()}
                            icon={ShieldCheck}
                            title="Seguridad y accesos"
                            description="Contraseña, sesiones activas, 2FA y llaves de acceso."
                        />
                        <QuickLink
                            href={editAppearance()}
                            icon={Palette}
                            title="Preferencias visuales"
                            description="Elegí el modo de visualización que acompaña tu jornada."
                        />
                    </div>
                </SettingsSection>
            </div>
        </>
    );
}

Profile.layout = {
    breadcrumbs: [
        {
            title: 'Mi perfil',
            href: edit(),
        },
    ],
};
