import { Form, Head, Link, usePage } from '@inertiajs/react';
import {
    AlertTriangle,
    BriefcaseBusiness,
    CalendarDays,
    Camera,
    Check,
    Clock3,
    Copy,
    Download,
    ImageOff,
    Mail,
    ShieldCheck,
    UserRound,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import InputError from '@/components/input-error';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useUnsavedChanges } from '@/hooks/use-unsaved-changes';
import { edit } from '@/routes/profile';
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

    return (
        <>
            <Head title="Cuenta" />

            <div className="space-y-6 sm:space-y-8">
                <section className="relative overflow-hidden rounded-2xl border border-[#cfe3d7] bg-gradient-to-br from-[#173247] via-[#205064] to-[#347461] p-5 text-white shadow-[0_18px_40px_rgba(23,50,71,0.16)] sm:p-7" aria-labelledby="account-hero-title">
                    <div className="pointer-events-none absolute -right-20 -top-24 size-64 rounded-full bg-[#9ce0bf]/15 blur-3xl" aria-hidden="true" />
                    <div className="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex min-w-0 items-center gap-4">
                            <Avatar className="size-20 shrink-0 rounded-2xl border-2 border-white/35 shadow-lg sm:size-24">
                                <AvatarImage src={auth.user.avatar} alt={`Foto de ${auth.user.name}`} />
                                <AvatarFallback className="rounded-2xl bg-white/15 text-2xl text-white">
                                    {initials}
                                </AvatarFallback>
                            </Avatar>
                            <div className="min-w-0">
                                <p className="mb-1 text-xs font-bold tracking-[0.16em] text-[#bcebd1] uppercase">Cuenta institucional</p>
                                <h1 id="account-hero-title" className="truncate text-2xl font-semibold tracking-[-0.03em] sm:text-3xl">{auth.user.name}</h1>
                                <p className="mt-2 flex items-center gap-2 truncate text-sm text-white/75">
                                    <Mail className="size-4 shrink-0" aria-hidden="true" />
                                    {auth.user.email}
                                </p>
                            </div>
                        </div>
                        <div className="flex shrink-0 items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-2 text-xs font-semibold text-white/90">
                            <span className="size-2 rounded-full bg-[#9ce0bf] shadow-[0_0_0_4px_rgba(156,224,191,0.16)]" aria-hidden="true" />
                            Cuenta activa
                        </div>
                    </div>
                </section>

                <section
                    className="rounded-2xl border border-[#d7e5dd] bg-white p-5 shadow-[0_12px_32px_rgba(23,50,71,0.06)] sm:p-7"
                    aria-labelledby="personal-title"
                >
                    <div className="mb-7 flex items-start gap-3 border-b border-[#e5eee8] pb-5">
                        <span className="flex size-10 shrink-0 items-center justify-center rounded-xl bg-[#e7f4eb] text-[#4f916d]" aria-hidden="true">
                            <UserRound className="size-5" />
                        </span>
                        <div>
                            <h2 id="personal-title" className="font-semibold text-[#173247]">Información personal</h2>
                            <p className="mt-1 text-sm leading-5 text-[#71828a]">Actualizá los datos que utilizás para ingresar al sistema.</p>
                        </div>
                    </div>

                    <div className="grid gap-8 xl:grid-cols-[220px_1fr]">
                        <div className="space-y-4">
                            <div className="flex flex-col items-center gap-3 rounded-2xl border border-[#d7e5dd] bg-[#f8fcf9] p-5 text-center shadow-sm">
                                <Avatar className="size-24 rounded-2xl">
                                    <AvatarImage
                                        src={avatarPreview ?? auth.user.avatar}
                                        alt={`Foto de ${auth.user.name}`}
                                    />
                                    <AvatarFallback className="rounded-2xl bg-[#e1f2e7] text-xl text-[#397461]">
                                        {initials}
                                    </AvatarFallback>
                                </Avatar>
                                <div>
                                    <p className="text-sm font-semibold text-[#173247]">
                                        Foto de perfil
                                    </p>
                                    <p className="mt-1 text-xs leading-5 text-[#819197]">
                                        JPG, PNG o WEBP · máximo 2 MB
                                    </p>
                                </div>

                                <Form
                                    action="/settings/profile/avatar"
                                    method="post"
                                    encType="multipart/form-data"
                                    options={{ preserveScroll: true }}
                                    className="w-full"
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            <Label
                                                htmlFor="avatar"
                                                className="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-[#cfe3d7] bg-white px-3 py-2.5 text-sm font-semibold text-[#315b52] shadow-sm transition hover:bg-[#eef8f2]"
                                            >
                                                <Camera className="size-4" />
                                                Cambiar foto
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
                                                className="mt-2 w-full rounded-xl bg-[#173247] shadow-sm hover:bg-[#24546a]"
                                                disabled={processing}
                                            >
                                                {processing
                                                    ? 'Guardando...'
                                                    : 'Guardar foto'}
                                            </Button>
                                            <InputError
                                                className="mt-2 text-left"
                                                message={errors.avatar}
                                            />
                                        </>
                                    )}
                                </Form>

                                {auth.user.avatar && (
                                    <Form
                                        action="/settings/profile/avatar"
                                        method="delete"
                                        options={{ preserveScroll: true }}
                                        className="w-full"
                                    >
                                        {({ processing }) => (
                                            <Button
                                                type="submit"
                                                variant="ghost"
                                                size="sm"
                                                 className="w-full rounded-xl text-[#a25862] hover:bg-[#fff2f3] hover:text-[#8c4c58]"
                                                disabled={processing}
                                            >
                                                <ImageOff className="size-4" />
                                                Eliminar foto
                                            </Button>
                                        )}
                                    </Form>
                                )}
                            </div>
                        </div>

                        <Form
                            {...ProfileController.update.form()}
                            options={{ preserveScroll: true }}
                            onSuccess={() => setProfileDirty(false)}
                            className="space-y-5"
                        >
                            {({ processing, errors, recentlySuccessful }) => (
                                <>
                                    <div className="grid gap-5 sm:grid-cols-2">
                                        <div className="grid gap-2 sm:col-span-2">
                                            <Label htmlFor="name" className="text-sm font-semibold text-[#31505d]">
                                                Nombre y apellido
                                            </Label>
                                            <Input
                                                id="name"
                                                name="name"
                                                defaultValue={auth.user.name}
                                                required
                                                autoComplete="name"
                                                className="h-11 rounded-xl border-[#d7e5dd] bg-[#fbfdfc] focus-visible:border-[#78b58f] focus-visible:ring-[#c7e7d2]"
                                                onChange={() =>
                                                    setProfileDirty(true)
                                                }
                                            />
                                            <InputError message={errors.name} />
                                        </div>

                                        <div className="grid gap-2 sm:col-span-2">
                                            <Label htmlFor="email" className="text-sm font-semibold text-[#31505d]">
                                                Correo electrónico
                                            </Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                name="email"
                                                defaultValue={auth.user.email}
                                                required
                                                autoComplete="email"
                                                className="h-11 rounded-xl border-[#d7e5dd] bg-[#fbfdfc] focus-visible:border-[#78b58f] focus-visible:ring-[#c7e7d2]"
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
                                            <div className="rounded-xl border border-[#f1dfb4] bg-[#fff9e8] p-4 text-sm leading-6 text-[#765d27]">
                                                Tu correo todavía no está
                                                verificado.{' '}
                                                <Link
                                                    href={send()}
                                                    as="button"
                                                        className="font-semibold underline decoration-[#c49a3b] underline-offset-4"
                                                >
                                                    Reenviar correo de
                                                    verificación
                                                </Link>
                                                {status ===
                                                    'verification-link-sent' && (
                                                    <p className="mt-2 font-medium text-green-700">
                                                        Se envió un nuevo enlace
                                                        de verificación.
                                                    </p>
                                                )}
                                            </div>
                                        )}

                                    <div className="grid gap-3 rounded-2xl border border-[#deece3] bg-[#f5faf7] p-4 sm:grid-cols-2">
                                        <div className="rounded-xl border border-[#e2eee7] bg-white p-3.5">
                                            <div className="flex items-center gap-2 text-[#5d9b7a]"><BriefcaseBusiness className="size-4" /><p className="text-xs font-bold tracking-wide uppercase">Equipo</p></div>
                                            <p className="mt-2 text-sm font-semibold text-[#173247]">{account.team ?? 'Sin equipo asignado'}</p>
                                        </div>
                                        <div className="rounded-xl border border-[#e2eee7] bg-white p-3.5">
                                            <div className="flex items-center gap-2 text-[#5d9b7a]"><ShieldCheck className="size-4" /><p className="text-xs font-bold tracking-wide uppercase">Rol</p></div>
                                            <p className="mt-2 text-sm font-semibold text-[#173247]">{account.role ?? 'Sin rol asignado'}</p>
                                        </div>
                                        <div className="rounded-xl border border-[#e2eee7] bg-white p-3.5">
                                            <div className="flex items-center gap-2 text-[#5d9b7a]"><Copy className="size-4" /><p className="text-xs font-bold tracking-wide uppercase">Código de usuario</p></div>
                                            <p className="mt-2 font-mono text-sm font-semibold text-[#173247]">{auth.user.id}</p>
                                        </div>
                                        <div className="rounded-xl border border-[#e2eee7] bg-white p-3.5">
                                            <div className="flex items-center gap-2 text-[#5d9b7a]"><CalendarDays className="size-4" /><p className="text-xs font-bold tracking-wide uppercase">Cuenta creada</p></div>
                                            <p className="mt-2 text-sm font-semibold text-[#173247]">{account.createdAt ? new Date(account.createdAt).toLocaleDateString('es-AR') : 'No registrado'}</p>
                                        </div>
                                        <div className="rounded-xl border border-[#e2eee7] bg-white p-3.5 sm:col-span-2">
                                            <div className="flex items-center gap-2 text-[#5d9b7a]"><Clock3 className="size-4" /><p className="text-xs font-bold tracking-wide uppercase">Último acceso</p></div>
                                            <p className="mt-2 text-sm font-semibold text-[#173247]">{account.lastAccess ? new Date(account.lastAccess).toLocaleString('es-AR') : 'No registrado'}</p>
                                        </div>
                                        <p className="flex items-start gap-2 px-1 pt-1 text-xs leading-5 text-[#71828a] sm:col-span-2">
                                            <ShieldCheck className="mt-0.5 size-4 shrink-0 text-[#5d9b7a]" />
                                            El equipo y el rol solo pueden ser administrados desde la gestión institucional.
                                        </p>
                                    </div>

                                    <div className="flex flex-wrap items-center gap-3">
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
                                                className="flex items-center gap-1 text-sm font-medium text-[#397461]"
                                                role="status"
                                            >
                                                <Check className="size-4" />{' '}
                                                Cambios guardados
                                            </span>
                                        )}
                                    </div>
                                </>
                            )}
                        </Form>
                    </div>
                </section>

                <section
                    className="rounded-2xl border border-[#d7e5dd] bg-white p-5 shadow-[0_12px_32px_rgba(23,50,71,0.06)] sm:p-7"
                    aria-labelledby="privacy-title"
                >
                    <div className="mb-6 flex items-start gap-3 border-b border-[#e5eee8] pb-5">
                        <span className="flex size-10 shrink-0 items-center justify-center rounded-xl bg-[#fff3dc] text-[#ad7b2c]" aria-hidden="true">
                            <AlertTriangle className="size-5" />
                        </span>
                        <div>
                            <h2 id="privacy-title" className="font-semibold text-[#173247]">Privacidad y cuenta</h2>
                            <p className="mt-1 text-sm leading-5 text-[#71828a]">Gestioná el acceso a tus datos y las acciones sensibles de tu cuenta.</p>
                        </div>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        <div className="flex flex-col rounded-2xl border border-[#dcebe2] bg-[#f7fbf8] p-5">
                            <span className="mb-4 flex size-10 items-center justify-center rounded-xl bg-[#e1f2e7] text-[#4f916d]" aria-hidden="true">
                                <Download className="size-5" />
                            </span>
                            <h3 className="font-semibold text-[#173247]">Exportar mis datos</h3>
                            <p className="mt-2 flex-1 text-sm leading-6 text-[#71828a]">Prepará una copia de la información asociada a tu cuenta. El formato de exportación será definido institucionalmente.</p>
                            <Button type="button" variant="outline" disabled className="mt-5 w-full rounded-xl border-[#cfe3d7] bg-white text-[#49616d]">
                                Disponible próximamente
                            </Button>
                        </div>

                        <div className="flex flex-col rounded-2xl border border-[#f0dfc2] bg-[#fffaf0] p-5">
                            <span className="mb-4 flex size-10 items-center justify-center rounded-xl bg-[#fff0cc] text-[#ad7b2c]" aria-hidden="true">
                                <AlertTriangle className="size-5" />
                            </span>
                            <h3 className="font-semibold text-[#5d4825]">Solicitar baja</h3>
                            <p className="mt-2 flex-1 text-sm leading-6 text-[#806c4b]">La solicitud requiere un circuito institucional de revisión. Tus registros operativos y de auditoría se conservarán.</p>
                            <Button type="button" variant="outline" disabled className="mt-5 w-full rounded-xl border-[#e7d3a9] bg-white text-[#806c4b]">
                                Solicitud no disponible
                            </Button>
                        </div>
                    </div>
                </section>
            </div>
        </>
    );
}

Profile.layout = {
    breadcrumbs: [
        {
            title: 'Mi cuenta',
            href: edit(),
        },
    ],
};
