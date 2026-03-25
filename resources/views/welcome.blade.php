<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Comunidad Madaya') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|space-grotesk:500,700"
        rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    <div aria-hidden="true" class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -left-20 top-0 h-96 w-96 rounded-full bg-emerald-500/20 blur-3xl">
        </div>
        <div
            class="absolute right-0 top-40 h-[28rem] w-[28rem] rounded-full bg-amber-500/15 blur-3xl">
        </div>
        <div
            class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.08),transparent_55%)]">
        </div>
    </div>

    <header class="sticky top-0 z-20 border-b border-white/10 bg-slate-950/80 backdrop-blur-xl">
        <div
            class="mx-auto flex w-full max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}"
                class="font-['Space_Grotesk'] text-lg font-semibold tracking-tight">
                Comunidad Madaya · Labeaga
            </a>

            <nav class="hidden items-center gap-6 text-sm text-slate-300 md:flex">
                <a href="#historia" class="transition hover:text-white">Historia</a>
                <a href="#galeria" class="transition hover:text-white">Galeria</a>
                <a href="#portal" class="transition hover:text-white">Portal</a>
            </nav>

            @if (Route::has('login'))
                @auth
                    <a href="{{ route('dashboard') }}"
                        class="rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-medium text-white transition hover:bg-white hover:text-slate-900">
                        Ir al portal
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="rounded-full border border-emerald-300/40 bg-emerald-300/10 px-4 py-2 text-sm font-medium text-emerald-100 transition hover:bg-emerald-200 hover:text-slate-900">
                        Acceso vecinos
                    </a>
                @endauth
            @endif
        </div>
    </header>

    <main>
        <section
            class="mx-auto grid w-full max-w-6xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-12 lg:gap-14 lg:px-8 lg:py-24">
            <div class="space-y-8 lg:col-span-7">
                <p
                    class="inline-flex rounded-full border border-amber-200/25 bg-amber-100/10 px-3 py-1 text-xs font-medium uppercase tracking-[0.2em] text-amber-100">
                    Urretxu · Alto Urola
                </p>

                <div
                    class="space-y-5 transition duration-700 starting:translate-y-4 starting:opacity-0">
                    <h1
                        class="font-['Space_Grotesk'] text-4xl leading-tight font-bold sm:text-5xl lg:text-6xl">
                        Madaya: de la forja industrial
                        <span class="text-emerald-300">a una comunidad residencial viva</span>
                    </h1>
                    <p class="max-w-2xl text-base leading-relaxed text-slate-300 sm:text-lg">
                        Este lugar nacio junto a la calle Labeaga como sede de Madaya
                        (Manufactura de Accesorios de Automovilismo y Aviacion), empresa fundada por
                        Madariaga y Unceta y vinculada al mecanizado y la transformacion de acero.
                        Tras su cierre en los anos 80, el entorno se reconvirtio en viviendas,
                        conservando su nombre y su identidad.
                    </p>
                </div>

                <div
                    class="flex flex-wrap gap-3 transition delay-150 duration-700 starting:translate-y-4 starting:opacity-0">
                    <a href="#portal"
                        class="rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-emerald-200">
                        Entrar al portal
                    </a>
                    <a href="#historia"
                        class="rounded-full border border-white/20 px-5 py-2.5 text-sm font-semibold text-white transition hover:border-white hover:bg-white/10">
                        Conocer la historia
                    </a>
                </div>

                <dl class="grid grid-cols-2 gap-3 pt-4 text-sm sm:grid-cols-4">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <dt class="text-slate-300">Origen</dt>
                        <dd class="mt-1 text-xl font-semibold text-white">Industrial</dd>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <dt class="text-slate-300">Sector historico</dt>
                        <dd class="mt-1 text-xl font-semibold text-white">Acero y forja</dd>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <dt class="text-slate-300">Ubicacion</dt>
                        <dd class="mt-1 text-xl font-semibold text-white">Labeaga</dd>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <dt class="text-slate-300">Reconversion</dt>
                        <dd class="mt-1 text-xl font-semibold text-white">Anos 80</dd>
                    </div>
                </dl>
            </div>

            <div class="lg:col-span-5">
                <article
                    class="overflow-hidden rounded-[2rem] border border-white/10 bg-slate-900/70 shadow-2xl shadow-black/20 transition duration-700 starting:translate-y-6 starting:opacity-0">
                    <img src="https://www.euskadi.eus/contenidos/informacion/madaya_fondo_pascual/es_madaya/images/madaya44.jpg"
                        alt="Foto historica de Fundiciones y Forja Madaya"
                        class="h-80 w-full object-cover" loading="lazy">
                    <div class="space-y-3 p-6">
                        <p class="text-xs uppercase tracking-[0.2em] text-emerald-200">Memoria y
                            presente</p>
                        <h2 class="font-['Space_Grotesk'] text-2xl font-semibold text-white">
                            Un nuevo barrio sobre una huella industrial
                        </h2>
                        <p class="text-sm leading-relaxed text-slate-300">
                            La antigua zona productiva de Urretxu evoluciono hacia un entorno de
                            convivencia,
                            manteniendo el nombre Madaya como parte de su historia local.
                        </p>
                    </div>
                </article>
            </div>
        </section>

        <section id="historia" class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 lg:px-8 lg:py-10">
            <div
                class="grid gap-6 rounded-3xl border border-white/10 bg-white/5 p-6 lg:grid-cols-12 lg:p-10">
                <div class="space-y-4 lg:col-span-7">
                    <p class="text-xs uppercase tracking-[0.2em] text-amber-100">Historia del lugar
                    </p>
                    <h2
                        class="font-['Space_Grotesk'] text-3xl font-semibold text-white sm:text-4xl">
                        Del auge de la forja a la vida residencial
                    </h2>
                    <p class="max-w-3xl text-base leading-relaxed text-slate-300">
                        Madaya fue una destacada empresa de mecanizado y transformacion de acero en
                        Urretxu, situada en Labeaga junto a la via principal y cerca del rio. Su
                        actividad formo parte del tejido industrial del Alto Urola, en un entorno
                        proximo a otras fabricas historicas como Genaro Berriochoa. Tras su cierre
                        en
                        la decada de los 80, el area se transformo en zona residencial, conservando
                        el nombre como legado colectivo.
                    </p>
                </div>

                <ol class="space-y-3 text-sm lg:col-span-5">
                    <li class="rounded-xl border border-white/10 bg-slate-900/60 p-4">
                        <span class="text-emerald-200">Fundacion industrial</span>
                        <p class="mt-1 text-slate-200">
                            Madariaga y Unceta impulsan Madaya como referente local en
                            automovilismo,
                            aviacion y transformacion de acero.
                        </p>
                    </li>
                    <li class="rounded-xl border border-white/10 bg-slate-900/60 p-4">
                        <span class="text-emerald-200">Decada de los 80</span>
                        <p class="mt-1 text-slate-200">
                            Cierre de la actividad fabril en un contexto de declive de la forja
                            tradicional en Urretxu.
                        </p>
                    </li>
                    <li class="rounded-xl border border-white/10 bg-slate-900/60 p-4">
                        <span class="text-emerald-200">Etapa residencial</span>
                        <p class="mt-1 text-slate-200">
                            Reconstruccion urbana de Labeaga en viviendas, manteniendo Madaya como
                            nombre identitario.
                        </p>
                    </li>
                </ol>
            </div>
        </section>

        <section class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <article class="rounded-2xl border border-white/10 bg-white/5 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-amber-100">Ubicacion</p>
                    <h3 class="mt-2 font-['Space_Grotesk'] text-xl font-semibold text-white">
                        Labeaga, junto a la via y el rio</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-300">
                        Un punto historico de actividad industrial de Urretxu, conectado al eje
                        principal del municipio.
                    </p>
                </article>

                <article class="rounded-2xl border border-white/10 bg-white/5 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-amber-100">Actividad</p>
                    <h3 class="mt-2 font-['Space_Grotesk'] text-xl font-semibold text-white">
                        Mecanizado, acero y forja</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-300">
                        Madaya formo parte del auge productivo del Alto Urola, ligado al trabajo
                        metalurgico y la transformacion de materiales.
                    </p>
                </article>

                <article class="rounded-2xl border border-white/10 bg-white/5 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-amber-100">Evolucion</p>
                    <h3 class="mt-2 font-['Space_Grotesk'] text-xl font-semibold text-white">Del
                        taller al barrio</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-300">
                        El cierre de la empresa dio paso a nuevas estructuras urbanas y a una gran
                        comunidad vecinal.
                    </p>
                </article>
            </div>
        </section>

        <section id="galeria" class="mx-auto w-full max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-amber-100">Galeria</p>
                    <h2
                        class="font-['Space_Grotesk'] text-3xl font-semibold text-white sm:text-4xl">
                        Imagenes de ayer y de hoy
                    </h2>
                </div>
                <p class="max-w-md text-sm text-slate-300">
                    Una galeria con referencias historicas y actuales de Madaya, Labeaga y su
                    entorno.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                <figure
                    class="group overflow-hidden rounded-2xl border border-white/10 bg-slate-900/60">
                    <img src="https://www.euskadi.eus/contenidos/informacion/madaya_fondo_pascual/es_madaya/images/madaya43.jpg"
                        alt="Fotografia historica de Fundiciones y Forja Madaya"
                        class="h-64 w-full object-cover transition duration-500 group-hover:scale-105"
                        loading="lazy">
                    <figcaption class="space-y-2 p-4 text-sm text-slate-300">
                        <p>Archivo historico de Fundiciones y Forja Madaya.</p>
                        <a href="https://www.euskadi.eus/fondo-pascual-madaya/web01-a2kulonz/es/madaya3.html"
                            target="_blank" rel="noopener noreferrer"
                            class="inline-flex text-xs font-medium text-emerald-200 underline underline-offset-4 transition hover:text-emerald-100">
                            Ver fuente historica
                        </a>
                    </figcaption>
                </figure>

                <figure
                    class="group overflow-hidden rounded-2xl border border-white/10 bg-slate-900/60">
                    <img src="https://s3.ppllstatics.com/diariovasco/www/multimedia/202106/26/media/cortadas/65574194--1248x1898.jpg"
                        alt="Chimenea y composicion floral entre Liceo y Madaya en Urretxu"
                        class="h-64 w-full object-cover transition duration-500 group-hover:scale-105"
                        loading="lazy">
                    <figcaption class="space-y-2 p-4 text-sm text-slate-300">
                        <p>La chimenea preservada como recuerdo del pasado industrial de la zona.
                        </p>
                        <a href="https://www.diariovasco.com/alto-urola/urretxu/xxxiii-edicion-certamen-20210626222529-ntvo.html"
                            target="_blank" rel="noopener noreferrer"
                            class="inline-flex text-xs font-medium text-emerald-200 underline underline-offset-4 transition hover:text-emerald-100">
                            Ver referencia en Diario Vasco
                        </a>
                    </figcaption>
                </figure>

                <figure
                    class="group overflow-hidden rounded-2xl border border-white/10 bg-slate-900/60">
                    <img src="https://images.unsplash.com/photo-1459535653751-d571815e906b?auto=format&fit=crop&w=900&q=80"
                        alt="Vista urbana residencial actual"
                        class="h-64 w-full object-cover transition duration-500 group-hover:scale-105"
                        loading="lazy">
                    <figcaption class="space-y-2 p-4 text-sm text-slate-300">
                        <p>Referencia visual del presente residencial y comercial de la zona Madaya.
                        </p>
                        <a href="https://es.wallapop.com/item/local-comercial-en-venta-en-urretxu-1237053961"
                            target="_blank" rel="noopener noreferrer"
                            class="inline-flex text-xs font-medium text-emerald-200 underline underline-offset-4 transition hover:text-emerald-100">
                            Ver referencia actual
                        </a>
                    </figcaption>
                </figure>
            </div>
        </section>

        <section id="portal" class="mx-auto w-full max-w-6xl px-4 pb-20 sm:px-6 lg:px-8">
            <div
                class="rounded-3xl border border-emerald-200/20 bg-gradient-to-r from-emerald-400/20 via-emerald-300/10 to-transparent p-6 sm:p-8 lg:p-10">
                <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                    <div class="space-y-3">
                        <p class="text-xs uppercase tracking-[0.2em] text-emerald-100">Portal del
                            vecino</p>
                        <h2
                            class="font-['Space_Grotesk'] text-3xl font-semibold text-white sm:text-4xl">
                            Todo en un solo lugar
                        </h2>
                        <p class="max-w-2xl text-sm leading-relaxed text-slate-200 sm:text-base">
                            Accede a comunicados, incidencias, reservas de zonas comunes y gestiones
                            habituales
                            de la comunidad desde tu area privada.
                        </p>
                    </div>

                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('dashboard') }}"
                                class="inline-flex items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-900 transition hover:bg-amber-200">
                                Abrir mi portal
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="inline-flex items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-900 transition hover:bg-amber-200">
                                Acceder ahora
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </section>
    </main>

    @php
        $footerLogo = file_exists(storage_path('app/public/amaia.png'))
            ? 'amaia.png'
            : (file_exists(storage_path('app/public/a.png'))
                ? 'a.png'
                : null);
    @endphp

    <footer class="border-t border-white/10 py-6">
        <div
            class="mx-auto flex w-full max-w-6xl flex-col gap-2 px-4 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <p>Comunidad Madaya · {{ date('Y') }}</p>
            <p>Convivencia, memoria y cuidado del entorno.</p>
        </div>

        @if ($footerLogo)
            <div class="mx-auto mt-4 flex w-full max-w-6xl justify-end px-4 sm:px-6 lg:px-8">
                <img src="{{ asset('storage/' . $footerLogo) }}" alt="Logo Amaia"
                    class="h-7 w-auto opacity-50 transition hover:opacity-70" loading="lazy">
            </div>
        @endif
    </footer>
</body>

</html>
