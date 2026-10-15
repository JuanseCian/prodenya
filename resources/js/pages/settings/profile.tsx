import { Form, Head, Link, usePage } from '@inertiajs/react';
import { Camera, Check, ImageOff, ShieldCheck, UserRound } from 'lucide-react';
import { useEffect, useState } from 'react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/heading';
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
            <Head title="Mi cuenta" />

            <div className="space-y-8">
                <Heading
                    title="Mi cuenta"
                    description="Administrá tu información personal y las opciones de seguridad de tu cuenta."
                />

                <section
                    className="rounded-xl border bg-card p-5 shadow-sm sm:p-7"
                    aria-labelledby="personal-title"
                >
                    <div className="mb-7 flex items-start gap-3">
                        <span
                            className="rounded-lg bg-primary/10 p-2 text-primary"
                            aria-hidden="true"
                        >
                            <UserRound className="size-5" />
                        </span>
                        <div>
                            <h2 id="personal-title" className="font-semibold">
                                Información personal
                            </h2>
                            <p className="text-sm text-muted-foreground">
                                Actualizá los datos que utilizás para ingresar
                                al sistema.
                            </p>
                        </div>
                    </div>

                    <div className="grid gap-8 xl:grid-cols-[220px_1fr]">
                        <div className="space-y-4">
                            <div className="flex flex-col items-center gap-3 rounded-xl border border-dashed p-5 text-center">
                                <Avatar className="size-24 rounded-2xl">
                                    <AvatarImage
                                        src={avatarPreview ?? auth.user.avatar}
                                        alt={`Foto de ${auth.user.name}`}
                                    />
                                    <AvatarFallback className="rounded-2xl bg-primary/10 text-xl text-primary">
                                        {initials}
                                    </AvatarFallback>
                                </Avatar>
                                <div>
                                    <p className="text-sm font-medium">
                                        Foto de perfil
                                    </p>
                                    <p className="mt-1 text-xs text-muted-foreground">
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
                                                className="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-md border px-3 py-2 text-sm font-medium hover:bg-muted"
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
                                                className="mt-2 w-full"
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
                                                className="w-full text-destructive hover:text-destructive"
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
                                            <Label htmlFor="name">
                                                Nombre y apellido
                                            </Label>
                                            <Input
                                                id="name"
                                                name="name"
                                                defaultValue={auth.user.name}
                                                required
                                                autoComplete="name"
                                                onChange={() =>
                                                    setProfileDirty(true)
                                                }
                                            />
                                            <InputError message={errors.name} />
                                        </div>

                                        <div className="grid gap-2 sm:col-span-2">
                                            <Label htmlFor="email">
                                                Correo electrónico
                                            </Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                name="email"
                                                defaultValue={auth.user.email}
                                                required
                                                autoComplete="email"
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
                                            <div className="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                                                Tu correo todavía no está
                                                verificado.{' '}
                                                <Link
                                                    href={send()}
                                                    as="button"
                                                    className="font-semibold underline underline-offset-4"
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

                                    <div className="grid gap-3 rounded-lg bg-muted/40 p-4 sm:grid-cols-2">
                                        <div>
                                            <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                                Equipo
                                            </p>
                                            <p className="mt-1 text-sm">
                                                {account.team ??
                                                    'Sin equipo asignado'}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                                Rol
                                            </p>
                                            <p className="mt-1 text-sm">
                                                {account.role ??
                                                    'Sin rol asignado'}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                                Cuenta creada
                                            </p>
                                            <p className="mt-1 text-sm">
                                                {account.createdAt
                                                    ? new Date(
                                                          account.createdAt,
                                                      ).toLocaleDateString(
                                                          'es-AR',
                                                      )
                                                    : 'No registrado'}
                                            </p>
                                        </div>
                                        <p className="flex items-center gap-2 text-xs text-muted-foreground sm:col-span-2">
                                            <ShieldCheck className="size-4" />{' '}
                                            El equipo y el rol solo pueden ser
                                            administrados desde la gestión
                                            institucional.
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
                                                className="flex items-center gap-1 text-sm text-green-700"
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
