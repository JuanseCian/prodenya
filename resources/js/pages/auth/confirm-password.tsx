import { Form, Head } from '@inertiajs/react';
import { ShieldCheck } from 'lucide-react';
import {
    index as confirmOptions,
    store as confirmStore,
} from '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyConfirmationController';
import InputError from '@/components/input-error';
import PasskeyVerify from '@/components/passkey-verify';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/password/confirm';

export default function ConfirmPassword() {
    return (
        <>
            <Head title="Confirmar identidad" />

            <section className="overflow-hidden rounded-[1.5rem] border border-border bg-card shadow-[0_24px_60px_-38px_rgba(32,56,70,0.55)]">
                <div className="border-b border-border/80 bg-surface-soft px-5 py-5 sm:px-7 sm:py-6">
                    <div className="flex items-start gap-3.5">
                        <span className="flex size-10 shrink-0 items-center justify-center rounded-xl bg-secondary text-primary ring-1 ring-primary/10">
                            <ShieldCheck className="size-[1.1rem]" />
                        </span>
                        <div>
                            <p className="text-[0.65rem] font-bold tracking-[0.16em] text-primary uppercase">
                                Acción sensible
                            </p>
                            <h2 className="mt-1 text-base font-semibold text-card-foreground sm:text-lg">
                                Comprobá que sos vos
                            </h2>
                            <p className="mt-1 text-sm leading-6 text-muted-foreground">
                                Necesitamos una confirmación adicional antes de
                                continuar. Esto protege tus datos y permisos.
                            </p>
                        </div>
                    </div>
                </div>

                <div className="space-y-6 px-5 py-5 sm:px-7 sm:py-7">
                    <PasskeyVerify
                        routes={{
                            options: confirmOptions(),
                            submit: confirmStore(),
                        }}
                        label="Confirmar con huella o rostro"
                        loadingLabel="Esperando confirmación..."
                        separator="O ingresá tu contraseña"
                        buttonClassName="h-11 rounded-xl"
                    />

                    <Form {...store.form()} resetOnSuccess={['password']}>
                        {({ processing, errors }) => (
                            <div className="space-y-5">
                                <div className="grid gap-2">
                                    <Label htmlFor="password">
                                        Contraseña actual
                                    </Label>
                                    <PasswordInput
                                        id="password"
                                        name="password"
                                        placeholder="Ingresá tu contraseña"
                                        autoComplete="current-password"
                                        autoFocus
                                        className="h-11 rounded-xl bg-background"
                                    />
                                    <InputError message={errors.password} />
                                </div>

                                <p className="rounded-xl border border-primary/15 bg-secondary/60 p-3.5 text-xs leading-5 text-muted-foreground">
                                    Solo se usará para confirmar esta acción y
                                    no se guardará en el navegador.
                                </p>

                                <Button
                                    className="h-11 w-full rounded-xl"
                                    disabled={processing}
                                    data-test="confirm-password-button"
                                >
                                    {processing && <Spinner />}
                                    Confirmar identidad
                                </Button>
                            </div>
                        )}
                    </Form>
                </div>
            </section>
        </>
    );
}

ConfirmPassword.layout = {
    title: 'Confirmar identidad',
    description:
        'Verificá tu identidad para continuar con una acción protegida.',
};
