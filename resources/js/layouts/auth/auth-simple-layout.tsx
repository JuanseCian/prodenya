import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="min-h-svh bg-background text-foreground">
            <main className="mx-auto flex min-h-svh w-full max-w-xl flex-col px-5 py-6 sm:px-8 sm:py-8">
                <div className="flex flex-1 items-center justify-center py-10 sm:py-14">
                    <div className="w-full">
                        <div className="mb-8">
                            <h1 className="text-3xl font-semibold tracking-[-0.045em] text-foreground sm:text-4xl">
                                {title}
                            </h1>
                            <p className="mt-3 max-w-md text-sm leading-6 text-muted-foreground sm:text-base">
                                {description}
                            </p>
                        </div>

                        {children}

                        <p className="mt-8 text-center text-xs leading-5 text-muted-foreground">
                            Nunca compartas tu contraseña ni códigos de
                            recuperación con otras personas.
                        </p>
                    </div>
                </div>
            </main>
        </div>
    );
}
