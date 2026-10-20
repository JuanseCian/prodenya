import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEventHandler } from 'react';

const FONT_HEADING = "'Lora', serif";
const FONT_BODY = "'Public Sans', sans-serif";

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        dni: '',
        telefono: '',
        profesion: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/register', {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <>
            <Head title="Crear cuenta">
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
                        Creá tu cuenta para acceder al sistema.
                    </p>

                    <form onSubmit={submit} className="mt-6 space-y-4">
                        <div>
                            <label
                                htmlFor="name"
                                className="mb-1.5 block text-sm font-medium text-[#2B2F36]"
                                style={{ fontFamily: FONT_BODY }}
                            >
                                Nombre completo
                            </label>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                value={data.name}
                                autoComplete="name"
                                autoFocus
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                                className="block w-full rounded-md border border-[#D5D1C6] bg-white px-3.5 py-2.5 text-sm text-[#2B2F36] placeholder-[#2B2F36]/30 transition outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20"
                                placeholder="Nombre y apellido"
                            />
                            {errors.name && (
                                <p className="mt-1.5 text-sm text-[#B1553F]">
                                    {errors.name}
                                </p>
                            )}
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label htmlFor="dni" className="mb-1.5 block text-sm font-medium text-[#2B2F36]" style={{ fontFamily: FONT_BODY }}>DNI</label>
                                <input id="dni" type="text" name="dni" value={data.dni} inputMode="numeric" onChange={(e) => setData('dni', e.target.value)} className="block w-full rounded-md border border-[#D5D1C6] bg-white px-3.5 py-2.5 text-sm text-[#2B2F36] outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20" placeholder="Sin puntos" required />
                                {errors.dni && <p className="mt-1.5 text-sm text-[#B1553F]">{errors.dni}</p>}
                            </div>
                            <div>
                                <label htmlFor="telefono" className="mb-1.5 block text-sm font-medium text-[#2B2F36]" style={{ fontFamily: FONT_BODY }}>Teléfono</label>
                                <input id="telefono" type="tel" name="telefono" value={data.telefono} onChange={(e) => setData('telefono', e.target.value)} className="block w-full rounded-md border border-[#D5D1C6] bg-white px-3.5 py-2.5 text-sm text-[#2B2F36] outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20" placeholder="Opcional" />
                                {errors.telefono && <p className="mt-1.5 text-sm text-[#B1553F]">{errors.telefono}</p>}
                            </div>
                        </div>

                        <div>
                            <label htmlFor="profesion" className="mb-1.5 block text-sm font-medium text-[#2B2F36]" style={{ fontFamily: FONT_BODY }}>Profesión</label>
                            <input id="profesion" type="text" name="profesion" value={data.profesion} onChange={(e) => setData('profesion', e.target.value)} className="block w-full rounded-md border border-[#D5D1C6] bg-white px-3.5 py-2.5 text-sm text-[#2B2F36] outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20" placeholder="Opcional" />
                            {errors.profesion && <p className="mt-1.5 text-sm text-[#B1553F]">{errors.profesion}</p>}
                        </div>

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
                                autoComplete="new-password"
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

                        <div>
                            <label
                                htmlFor="password_confirmation"
                                className="mb-1.5 block text-sm font-medium text-[#2B2F36]"
                                style={{ fontFamily: FONT_BODY }}
                            >
                                Confirmar contraseña
                            </label>
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                value={data.password_confirmation}
                                autoComplete="new-password"
                                onChange={(e) =>
                                    setData(
                                        'password_confirmation',
                                        e.target.value,
                                    )
                                }
                                className="block w-full rounded-md border border-[#D5D1C6] bg-white px-3.5 py-2.5 text-sm text-[#2B2F36] placeholder-[#2B2F36]/30 transition outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20"
                                placeholder="••••••••"
                            />
                            {errors.password_confirmation && (
                                <p className="mt-1.5 text-sm text-[#B1553F]">
                                    {errors.password_confirmation}
                                </p>
                            )}
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full rounded-md bg-[#2C4A5E] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#233B4B] focus:ring-2 focus:ring-[#2C4A5E]/40 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                            style={{ fontFamily: FONT_BODY }}
                        >
                            {processing ? 'Creando cuenta…' : 'Crear cuenta'}
                        </button>
                    </form>

                    <p
                        className="mt-6 text-center text-sm text-[#2B2F36]/70"
                        style={{ fontFamily: FONT_BODY }}
                    >
                        ¿Ya tenés una cuenta?{' '}
                        <Link
                            href="/login"
                            className="font-medium text-[#2C4A5E] underline-offset-2 hover:underline"
                        >
                            Iniciar sesión
                        </Link>
                    </p>
                </div>
            </div>
        </>
    );
}
