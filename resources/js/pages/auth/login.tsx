import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEventHandler } from 'react';

interface LoginProps {
    status?: string;
}

const FONT_HEADING = "'Lora', serif";
const FONT_BODY = "'Public Sans', sans-serif";

export default function Login({ status }: LoginProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/login', {
            onFinish: () => reset('password'),
        });
    };

    return (
        <>
            <Head title="Iniciar sesión">
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
                            Prodenya
                        </h1>
                    </div>

                    <p
                        className="mt-6 text-sm text-[#2B2F36]/70"
                        style={{ fontFamily: FONT_BODY }}
                    >
                        Ingresá con tu usuario para acceder al sistema.
                    </p>

                    {status && (
                        <div className="mt-4 rounded-md border border-[#6B8F71]/30 bg-[#6B8F71]/10 px-4 py-3 text-sm font-medium text-[#4A6B52]">
                            {status}
                        </div>
                    )}

                    <form onSubmit={submit} className="mt-6 space-y-4">
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
                                value={data.email}
                                autoComplete="username"
                                autoFocus
                                onChange={(e) =>
                                    setData('email', e.target.value)
                                }
                                className="block w-full rounded-md border border-[#D5D1C6] bg-white px-3.5 py-2.5 text-sm text-[#2B2F36] placeholder-[#2B2F36]/30 transition outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20"
                                placeholder="nombre@organismo.gob.ar"
                            />
                            {errors.email && (
                                <p className="mt-1.5 text-sm text-[#B1553F]">
                                    {errors.email}
                                </p>
                            )}
                        </div>

                        <div>
                            <label
                                htmlFor="password"
                                className="mb-1.5 block text-sm font-medium text-[#2B2F36]"
                                style={{ fontFamily: FONT_BODY }}
                            >
                                Contraseña
                            </label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                value={data.password}
                                autoComplete="current-password"
                                onChange={(e) =>
                                    setData('password', e.target.value)
                                }
                                className="block w-full rounded-md border border-[#D5D1C6] bg-white px-3.5 py-2.5 text-sm text-[#2B2F36] placeholder-[#2B2F36]/30 transition outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20"
                                placeholder="••••••••"
                            />
                            {errors.password && (
                                <p className="mt-1.5 text-sm text-[#B1553F]">
                                    {errors.password}
                                </p>
                            )}
                        </div>

                        <label
                            className="flex items-center gap-2 text-sm text-[#2B2F36]/80"
                            style={{ fontFamily: FONT_BODY }}
                        >
                            <input
                                type="checkbox"
                                name="remember"
                                checked={data.remember}
                                onChange={(e) =>
                                    setData('remember', e.target.checked)
                                }
                                className="h-4 w-4 rounded border-[#D5D1C6] text-[#2C4A5E] focus:ring-[#2C4A5E]/30"
                            />
                            Recordarme
                        </label>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full rounded-md bg-[#2C4A5E] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#233B4B] focus:ring-2 focus:ring-[#2C4A5E]/40 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                            style={{ fontFamily: FONT_BODY }}
                        >
                            {processing ? 'Ingresando…' : 'Ingresar'}
                        </button>
                    </form>

                    <p
                        className="mt-6 text-center text-sm text-[#2B2F36]/70"
                        style={{ fontFamily: FONT_BODY }}
                    >
                        ¿No tenés una cuenta?{' '}
                        <Link
                            href="/register"
                            className="font-medium text-[#2C4A5E] underline-offset-2 hover:underline"
                        >
                            Registrate
                        </Link>
                    </p>
                </div>
            </div>
        </>
    );
}
