import { Form, Head } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import InputError from '@/components/input-error';
import { email } from '@/routes/password';

const FONT_HEADING = "'Lora', serif";
const FONT_BODY = "'Public Sans', sans-serif";

export default function ForgotPassword({ status }: { status?: string }) {
    return (
        <>
            <Head title="Recuperar contraseña">
                <style>{`
                    html, body, #app {
                        width: 100% !important;
                        max-width: none !important;
                        min-height: 100% !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        background: none !important;
                    }
                `}</style>
            </Head>

            <div
                style={{
                    position: 'fixed',
                    inset: 0,
                    width: '100vw',
                    height: '100dvh',
                    overflowY: 'auto',
                }}
                className="flex items-center justify-center bg-[#F6F5F1] px-4 py-10 sm:px-6"
            >
                <div className="w-full max-w-sm rounded-lg border border-[#E3E0D8] bg-white p-8 shadow-sm sm:p-10">
                    <div className="border-b border-[#E3E0D8] pb-6 text-center">
                        <span
                            className="text-[0.65rem] font-semibold tracking-[0.2em] text-[#6B8F71] uppercase"
                            style={{ fontFamily: FONT_BODY }}
                        >
                            Sistema de gestión
                        </span>
                        <h1
                            className="mt-2 text-2xl font-medium text-[#2C4A5E] sm:text-3xl"
                            style={{ fontFamily: FONT_HEADING }}
                        >
                            Recuperar contraseña
                        </h1>
                    </div>

                    <p
                        className="mt-6 text-sm text-[#2B2F36]/70"
                        style={{ fontFamily: FONT_BODY }}
                    >
                        Ingresá tu correo electrónico y te enviaremos un enlace
                        para restablecer tu contraseña.
                    </p>

                    {status && (
                        <div className="mt-4 rounded-md border border-[#6B8F71]/30 bg-[#6B8F71]/10 px-4 py-3 text-sm font-medium text-[#4A6B52]">
                            {status}
                        </div>
                    )}

                    <Form {...email.form()} className="mt-6">
                        {({ processing, errors }) => (
                            <>
                                <div>
                                    <label
                                        htmlFor="email"
                                        className="mb-1.5 block text-sm font-medium text-[#2B2F36]"
                                        style={{ fontFamily: FONT_BODY }}
                                    >
                                        Correo electrónico
                                    </label>
                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        autoComplete="off"
                                        autoFocus
                                        placeholder="nombre@organismo.gob.ar"
                                        className="block w-full rounded-md border border-[#D5D1C6] bg-white px-3.5 py-2.5 text-sm text-[#2B2F36] placeholder-[#2B2F36]/30 transition outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20"
                                    />
                                    <InputError message={errors.email} />
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    data-test="email-password-reset-link-button"
                                    className="mt-6 flex w-full items-center justify-center gap-2 rounded-md bg-[#2C4A5E] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#233B4B] focus:ring-2 focus:ring-[#2C4A5E]/40 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                                    style={{ fontFamily: FONT_BODY }}
                                >
                                    {processing && (
                                        <LoaderCircle className="h-4 w-4 animate-spin" />
                                    )}
                                    Enviar enlace de recuperación
                                </button>
                            </>
                        )}
                    </Form>

                    <div
                        className="mt-6 space-x-1 text-center text-sm text-[#2B2F36]/70"
                        style={{ fontFamily: FONT_BODY }}
                    >
                        <span>¿Ya te acordaste?</span>
                        <a
                            href="/login"
                            className="font-medium text-[#2C4A5E] underline decoration-[#2C4A5E]/30 underline-offset-2 hover:decoration-[#2C4A5E]"
                        >
                            Volver al inicio de sesión
                        </a>
                    </div>
                </div>
            </div>
        </>
    );
}
