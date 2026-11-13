import { usePasskeyRegister } from '@laravel/passkeys/react';
import { KeyRound, ShieldCheck } from 'lucide-react';
import { useState } from 'react';
import { getDefaultPasskeyName } from '@/components/passkey-register';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import security from '@/routes/security';

type Props = {
    enabled: boolean;
    passwordConfirmed: boolean;
};

export default function PasskeyOnboardingModal({
    enabled,
    passwordConfirmed,
}: Props) {
    const [isOpen, setIsOpen] = useState(enabled);
    const [isComplete, setIsComplete] = useState(false);
    const { register, isLoading, error, isSupported } = usePasskeyRegister({
        onSuccess: () => setIsComplete(true),
    });

    const dismiss = () => {
        setIsOpen(false);
    };

    const handleOpenChange = (nextOpen: boolean) => {
        if (nextOpen) {
            setIsOpen(true);

            return;
        }

        if (isLoading) {
            return;
        }

        if (isComplete) {
            setIsOpen(false);

            return;
        }

        dismiss();
    };

    const handleRegister = async () => {
        if (!passwordConfirmed) {
            window.location.assign(security.edit.url());

            return;
        }

        await register(getDefaultPasskeyName());
    };

    if (!enabled || !isSupported) {
        return null;
    }

    return (
        <Dialog open={isOpen} onOpenChange={handleOpenChange}>
            <DialogContent
                className="max-w-[min(30rem,calc(100%-2rem))] gap-0 overflow-hidden rounded-[1.35rem] border-[#DCE7E1] bg-[#FCFBF7] p-0 text-[#173247] shadow-[0_24px_80px_rgba(23,50,71,0.2)] sm:max-w-[30rem]"
                onEscapeKeyDown={(event) => {
                    if (isLoading) {
                        event.preventDefault();
                    }
                }}
                onPointerDownOutside={(event) => {
                    if (isLoading) {
                        event.preventDefault();
                    }
                }}
            >
                <div className="px-6 pt-7 pb-6 sm:px-8 sm:pt-8">
                    <DialogHeader className="gap-4 text-left">
                        <div className="flex size-12 items-center justify-center rounded-2xl border border-[#CDE2D5] bg-[#EAF5ED] text-[#39795B]">
                            <KeyRound
                                className="size-6"
                                strokeWidth={1.8}
                                aria-hidden="true"
                            />
                        </div>
                        <div className="space-y-3">
                            <DialogTitle className="max-w-[22rem] text-[1.55rem] leading-[1.12] tracking-[-0.035em] text-[#173247] sm:text-[1.7rem]">
                                {isComplete
                                    ? 'Llave de acceso activada'
                                    : 'Accedé de forma más rápida y segura'}
                            </DialogTitle>
                            <DialogDescription className="text-[0.95rem] leading-7 text-[#536B76]">
                                {isComplete
                                    ? 'Ya podés utilizar tu llave de acceso para ingresar a Prodenya.'
                                    : 'Protegé tu cuenta de Prodenya utilizando una llave de acceso. Podrás iniciar sesión con tu huella digital, reconocimiento facial o el método de desbloqueo de tu dispositivo, sin necesidad de escribir tu contraseña.'}
                            </DialogDescription>
                        </div>
                    </DialogHeader>

                    {isComplete ? (
                        <div className="mt-6 flex items-start gap-3 rounded-xl border border-[#CDE2D5] bg-[#F1F8F2] p-4 text-sm leading-6 text-[#3E6650]">
                            <ShieldCheck
                                className="mt-0.5 size-5 shrink-0 text-[#39795B]"
                                aria-hidden="true"
                            />
                            <span>
                                Tu cuenta ya está protegida con este
                                dispositivo.
                            </span>
                        </div>
                    ) : (
                        <>
                            <p className="mt-5 text-sm leading-6 text-[#6B7E86]">
                                La configuración es sencilla y solo te llevará
                                unos instantes.
                            </p>
                            <p className="mt-3 text-xs leading-5 text-[#73858C]">
                                También podés configurar tus llaves de acceso
                                desde{' '}
                                <a
                                    href={security.edit.url()}
                                    className="font-semibold text-[#39795B] underline-offset-4 hover:underline"
                                >
                                    Perfil → Seguridad
                                </a>
                                .
                            </p>

                            {error && (
                                <div
                                    className="mt-5 rounded-xl border border-[#EBC9C3] bg-[#FFF3F0] px-4 py-3 text-sm leading-6 text-[#9A493D]"
                                    role="alert"
                                >
                                    No pudimos registrar la llave de acceso.
                                    Revisá el aviso de tu dispositivo e intentá
                                    nuevamente.
                                </div>
                            )}
                        </>
                    )}
                </div>

                <DialogFooter className="border-t border-[#E6ECE8] bg-[#F8F8F4] px-6 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                    {isComplete ? (
                        <Button
                            type="button"
                            className="w-full rounded-lg bg-[#2C4A5E] px-5 text-white hover:bg-[#1F394B] sm:ml-auto sm:w-auto"
                            onClick={() => setIsOpen(false)}
                        >
                            Continuar
                        </Button>
                    ) : (
                        <>
                            <Button
                                type="button"
                                variant="ghost"
                                className="order-2 rounded-lg px-4 text-[#536B76] hover:bg-[#EDF2EF] hover:text-[#2C4A5E] sm:order-1"
                                onClick={dismiss}
                                disabled={isLoading}
                            >
                                Ahora no
                            </Button>
                            <Button
                                type="button"
                                className="order-1 w-full rounded-lg bg-[#2C4A5E] px-5 text-white shadow-[0_6px_14px_rgba(44,74,94,0.18)] hover:bg-[#1F394B] sm:order-2 sm:w-auto"
                                onClick={handleRegister}
                                disabled={isLoading}
                            >
                                {isLoading
                                    ? 'Registrando...'
                                    : 'Activar llave de acceso'}
                            </Button>
                        </>
                    )}
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
