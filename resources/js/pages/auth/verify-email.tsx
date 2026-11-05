// Components
import { Form, Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

export default function VerifyEmail({ status }: { status?: string }) {
    return (
        <>
            <Head title="Verificación de email" />

            {status === 'verification-link-sent' && (
                <div
                    role="status"
                    className="mb-4 text-center text-sm font-medium text-green-600"
                >
                    Se envió un nuevo enlace de verificación a la dirección de
                    correo que proporcionaste al registrarte.
                </div>
            )}

            <Form {...send.form()} className="space-y-6 text-center">
                {({ processing }) => (
                    <>
                        <Button disabled={processing} variant="secondary">
                            {processing && <Spinner />}
                            Reenviar email de verificación
                        </Button>

                        <button
                            type="button"
                            onClick={() => router.post(logout.url())}
                            className="mx-auto block text-sm text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                        >
                            Cerrar sesión
                        </button>
                    </>
                )}
            </Form>
        </>
    );
}

VerifyEmail.layout = {
    title: 'Verificación de email',
    description:
        'Por favor verificá tu dirección de email haciendo clic en el enlace que te acabamos de enviar.',
};
