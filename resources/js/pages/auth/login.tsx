import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface LoginProps {
    status?: string;
}

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
                {/*
                  Reset defensivo: fuerza html/body/#app a ocupar el 100% del
                  viewport y anula cualquier max-width o dark-mode heredado
                  de un layout padre, para que esta página siempre se vea
                  a pantalla completa sin importar dónde se monte.
                */}
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
                className="flex items-center justify-center bg-gradient-to-br from-[#F5E9FB] via-[#FDF3E7] to-[#E6F5EE] px-4 py-10 sm:px-6"
            >
                {/* Blobs decorativos pastel */}
                <div className="pointer-events-none absolute -top-24 -left-16 h-72 w-72 rounded-full bg-[#D8C9F0] opacity-50 blur-3xl sm:-top-32 sm:-left-24 sm:h-96 sm:w-96" />
                <div className="pointer-events-none absolute top-1/3 -right-20 h-80 w-80 rounded-full bg-[#BFE8DA] opacity-50 blur-3xl sm:-right-32 sm:h-[28rem] sm:w-[28rem]" />
                <div className="pointer-events-none absolute -bottom-28 left-1/4 h-72 w-72 rounded-full bg-[#FBD9BD] opacity-40 blur-3xl sm:-bottom-40 sm:h-96 sm:w-96" />

                {/* Card */}
                <div className="relative w-full max-w-[22rem] rounded-[1.75rem] border border-white/60 bg-white/70 p-6 shadow-xl shadow-[#D8C9F0]/30 backdrop-blur-xl sm:max-w-sm sm:rounded-[2rem] sm:p-10">
                    <div className="text-center">
                        <span
                            className="text-[0.65rem] font-semibold tracking-[0.2em] text-[#9B87C4] uppercase sm:text-xs sm:tracking-[0.25em]"
                            style={{ fontFamily: "'Inter', sans-serif" }}
                        >
                            Sistema de gestión
                        </span>
                        <h1
                            className="mt-2 text-2xl font-semibold text-[#4A3F55] sm:text-3xl"
                            style={{ fontFamily: "'Poppins', sans-serif" }}
                        >
                            Prodenya
                        </h1>
                        <p
                            className="mt-3 text-sm text-[#4A3F55]/70"
                            style={{ fontFamily: "'Inter', sans-serif" }}
                        >
                            Ingresá con tu usuario para acceder al sistema.
                        </p>
                    </div>

                    {status && (
                        <div className="mt-6 rounded-xl bg-[#BFE8DA]/50 px-4 py-3 text-center text-sm font-medium text-[#3E7A63]">
                            {status}
                        </div>
                    )}

                    <form onSubmit={submit} className="mt-8 space-y-4">
                        <div>
                            <label
                                htmlFor="email"
                                className="mb-1.5 block text-sm font-medium text-[#4A3F55]"
                                style={{ fontFamily: "'Inter', sans-serif" }}
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
                                onChange={(e) => setData('email', e.target.value)}
                                className="block w-full rounded-xl border border-[#D8C9F0]/60 bg-white/80 px-4 py-2.5 text-sm text-[#4A3F55] placeholder-[#4A3F55]/30 shadow-sm outline-none transition focus:border-[#B49BDE] focus:ring-4 focus:ring-[#D8C9F0]/40"
                                placeholder="nombre@organismo.gob.ar"
                            />
                            {errors.email && (
                                <p className="mt-1.5 text-sm text-[#D97A6C]">{errors.email}</p>
                            )}
                        </div>

                        <div>
                            <label
                                htmlFor="password"
                                className="mb-1.5 block text-sm font-medium text-[#4A3F55]"
                                style={{ fontFamily: "'Inter', sans-serif" }}
                            >
                                Contraseña
                            </label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                value={data.password}
                                autoComplete="current-password"
                                onChange={(e) => setData('password', e.target.value)}
                                className="block w-full rounded-xl border border-[#D8C9F0]/60 bg-white/80 px-4 py-2.5 text-sm text-[#4A3F55] placeholder-[#4A3F55]/30 shadow-sm outline-none transition focus:border-[#B49BDE] focus:ring-4 focus:ring-[#D8C9F0]/40"
                                placeholder="••••••••"
                            />
                            {errors.password && (
                                <p className="mt-1.5 text-sm text-[#D97A6C]">{errors.password}</p>
                            )}
                        </div>

                        <div className="flex items-center justify-between pt-1">
                            <label className="flex items-center gap-2 text-sm text-[#4A3F55]/80">
                                <input
                                    type="checkbox"
                                    name="remember"
                                    checked={data.remember}
                                    onChange={(e) => setData('remember', e.target.checked)}
                                    className="h-4 w-4 rounded border-[#D8C9F0] text-[#B49BDE] focus:ring-[#D8C9F0]"
                                />
                                Recordarme
                            </label>
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="mt-2 w-full rounded-full bg-gradient-to-r from-[#C9B6E4] to-[#F6C6A6] px-4 py-2.5 text-sm font-semibold text-[#4A3F55] shadow-md shadow-[#D8C9F0]/50 transition hover:brightness-105 focus:outline-none focus:ring-4 focus:ring-[#D8C9F0]/50 disabled:cursor-not-allowed disabled:opacity-60"
                            style={{ fontFamily: "'Inter', sans-serif" }}
                        >
                            {processing ? 'Ingresando…' : 'Ingresar'}
                        </button>
                    </form>
                </div>
            </div>
        </>
    );
}
