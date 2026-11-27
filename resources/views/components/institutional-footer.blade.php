<footer class="site-footer-institutional border-t border-[#304753] bg-[#173247] text-white" data-site-footer>
    <div class="mx-auto w-full max-w-[1440px] px-5 py-10 sm:px-8 lg:py-12">
        <div class="grid gap-10 lg:grid-cols-[1.35fr_repeat(3,minmax(0,1fr))] lg:gap-12">
            <div class="max-w-sm">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3" aria-label="Ir al inicio de PRODENYA">
                    <span class="flex size-12 items-center justify-center rounded-xl border border-white/20 bg-white/10 p-2">
                        <img src="{{ asset('assets/img/SN.png') }}" alt="Municipalidad de San Nicolás" class="size-full object-contain">
                    </span>
                    <span>
                        <strong class="block text-base font-bold tracking-[0.14em]">PRODENYA</strong>
                        <span class="mt-1 block text-xs text-[#b8cbd2]">Municipalidad de San Nicolás de los Arroyos</span>
                    </span>
                </a>
                <p class="mt-5 text-sm leading-6 text-[#b8cbd2]">
                    Sistema de Promoción y Protección de los Derechos de Niños, Niñas y Adolescentes.
                </p>
            </div>

            <div>
                <h2 class="text-xs font-bold tracking-[0.14em] text-[#9ccfb2] uppercase">Institución</h2>
                <nav class="mt-4 flex flex-col items-start gap-3 text-sm text-[#dce8ec]" aria-label="Información institucional">
                    <a href="{{ route('legal.terminos') }}" class="transition-colors hover:text-white">Aviso legal</a>
                    <a href="{{ route('legal.privacidad') }}" class="transition-colors hover:text-white">Privacidad</a>
                    <a href="{{ route('legal.accesibilidad') }}" class="transition-colors hover:text-white">Accesibilidad</a>
                </nav>
            </div>

            <div>
                <h2 class="text-xs font-bold tracking-[0.14em] text-[#9ccfb2] uppercase">Contacto</h2>
                <address class="mt-4 flex flex-col gap-3 text-sm leading-5 not-italic text-[#dce8ec]">
                    <a href="{{ route('legal.ubicacion') }}" class="transition-colors hover:text-white">Alte. Brown y José Luis Lazarte</a>
                    <a href="mailto:prodenya.sn@gmail.com" class="break-all transition-colors hover:text-white">prodenya.sn@gmail.com</a>
                    <span class="text-[#b8cbd2]">Atención: 08:00 a 14:00</span>
                </address>
            </div>

            <div>
                <h2 class="text-xs font-bold tracking-[0.14em] text-[#9ccfb2] uppercase">Acceso</h2>
                <nav class="mt-4 flex flex-col items-start gap-3 text-sm text-[#dce8ec]" aria-label="Accesos del sistema">
                    @auth
                        <a href="{{ route('home') }}" class="transition-colors hover:text-white">Ir al panel</a>
                    @else
                        <a href="{{ route('login') }}" class="transition-colors hover:text-white">Ingresar</a>
                    @endauth
                    <a href="{{ route('legal.contacto') }}" class="transition-colors hover:text-white">Contacto institucional</a>
                    <a href="{{ route('legal.ubicacion') }}" class="transition-colors hover:text-white">Ver ubicación</a>
                </nav>
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-3 border-t border-white/10 pt-5 text-xs text-[#9fb5bd] sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ now()->year }} PRODENYA · Municipalidad de San Nicolás de los Arroyos</p>
            <p>Uso institucional · Información protegida</p>
        </div>
    </div>
</footer>
