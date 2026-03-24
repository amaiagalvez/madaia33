<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Comunidad de Vecinos') }}</title>

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
                Comunidad de Vecinos
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
                    Bienvenida
                </p>

                <div
                    class="space-y-5 transition duration-700 starting:translate-y-4 starting:opacity-0">
                    <h1
                        class="font-['Space_Grotesk'] text-4xl leading-tight font-bold sm:text-5xl lg:text-6xl">
                        Un hogar compartido,
                        <span class="text-emerald-300">ordenado y cercano</span>
                    </h1>
                    <p class="max-w-2xl text-base leading-relaxed text-slate-300 sm:text-lg">
                        Somos una comunidad de vecinos grande y activa, construida con el compromiso
                        diario de
                        quienes vivimos aqui. En esta portada encontraras un resumen visual de
                        nuestro entorno,
                        parte de nuestra historia y un acceso directo al portal del vecino.
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
                        <dt class="text-slate-300">Viviendas</dt>
                        <dd class="mt-1 text-xl font-semibold text-white">320+</dd>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <dt class="text-slate-300">Portales</dt>
                        <dd class="mt-1 text-xl font-semibold text-white">6</dd>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <dt class="text-slate-300">Zonas comunes</dt>
                        <dd class="mt-1 text-xl font-semibold text-white">14</dd>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <dt class="text-slate-300">Anos de historia</dt>
                        <dd class="mt-1 text-xl font-semibold text-white">30</dd>
                    </div>
                </dl>
            </div>

            <div class="lg:col-span-5">
                <article
                    class="overflow-hidden rounded-[2rem] border border-white/10 bg-slate-900/70 shadow-2xl shadow-black/20 transition duration-700 starting:translate-y-6 starting:opacity-0">
                    <img src="https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?auto=format&fit=crop&w=1200&q=80"
                        alt="Vista principal de la comunidad" class="h-80 w-full object-cover"
                        loading="lazy">
                    <div class="space-y-3 p-6">
                        <p class="text-xs uppercase tracking-[0.2em] text-emerald-200">Comunidad
                            viva</p>
                        <h2 class="font-['Space_Grotesk'] text-2xl font-semibold text-white">
                            Espacios cuidados para convivir mejor
                        </h2>
                        <p class="text-sm leading-relaxed text-slate-300">
                            Jardines, zonas de encuentro y servicios compartidos que conectan a los
                            vecinos en su dia a dia.
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
                        Crecimiento con identidad de barrio
                    </h2>
                    <p class="max-w-3xl text-base leading-relaxed text-slate-300">
                        Nuestra comunidad nacio como un proyecto residencial familiar y, con el paso
                        de los anos,
                        se convirtio en un referente local por su organizacion y participacion
                        vecinal. Hoy seguimos
                        mejorando las zonas comunes, impulsando actividades y cuidando un entorno
                        seguro y acogedor.
                    </p>
                </div>

                <ol class="space-y-3 text-sm lg:col-span-5">
                    <li class="rounded-xl border border-white/10 bg-slate-900/60 p-4">
                        <span class="text-emerald-200">1996</span>
                        <p class="mt-1 text-slate-200">Inicio del primer bloque y creacion de la
                            comunidad.</p>
                    </li>
                    <li class="rounded-xl border border-white/10 bg-slate-900/60 p-4">
                        <span class="text-emerald-200">2008</span>
                        <p class="mt-1 text-slate-200">Ampliacion de zonas verdes y nuevas areas
                            comunes.</p>
                    </li>
                    <li class="rounded-xl border border-white/10 bg-slate-900/60 p-4">
                        <span class="text-emerald-200">2024</span>
                        <p class="mt-1 text-slate-200">Digitalizacion de gestiones y puesta en
                            marcha del portal del vecino.</p>
                    </li>
                </ol>
            </div>
        </section>

        <section id="galeria" class="mx-auto w-full max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-amber-100">Galeria</p>
                    <h2
                        class="font-['Space_Grotesk'] text-3xl font-semibold text-white sm:text-4xl">
                        Rincones de la comunidad
                    </h2>
                </div>
                <p class="max-w-md text-sm text-slate-300">
                    Una seleccion de imagenes de nuestras zonas comunes y espacios de convivencia.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                <figure
                    class="group overflow-hidden rounded-2xl border border-white/10 bg-slate-900/60">
                    <img src="https://images.unsplash.com/photo-1460317442991-0ec209397118?auto=format&fit=crop&w=900&q=80"
                        alt="Zona ajardinada"
                        class="h-64 w-full object-cover transition duration-500 group-hover:scale-105"
                        loading="lazy">
                    <figcaption class="p-4 text-sm text-slate-300">Jardines y paseos interiores
                    </figcaption>
                </figure>

                <figure
                    class="group overflow-hidden rounded-2xl border border-white/10 bg-slate-900/60">
                    <img src="https://images.unsplash.com/photo-1494526585095-c41746248156?auto=format&fit=crop&w=900&q=80"
                        alt="Fachada residencial"
                        class="h-64 w-full object-cover transition duration-500 group-hover:scale-105"
                        loading="lazy">
                    <figcaption class="p-4 text-sm text-slate-300">Arquitectura residencial
                    </figcaption>
                </figure>

                <figure
                    class="group overflow-hidden rounded-2xl border border-white/10 bg-slate-900/60">
                    <img src="https://images.unsplash.com/photo-1459535653751-d571815e906b?auto=format&fit=crop&w=900&q=80"
                        alt="Zona comun de encuentro"
                        class="h-64 w-full object-cover transition duration-500 group-hover:scale-105"
                        loading="lazy">
                    <figcaption class="p-4 text-sm text-slate-300">Espacios de encuentro vecinal
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

    <footer class="border-t border-white/10 py-6">
        <div
            class="mx-auto flex w-full max-w-6xl flex-col gap-2 px-4 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <p>Comunidad de Vecinos · {{ date('Y') }}</p>
            <p>Convivencia, transparencia y cuidado del entorno.</p>
        </div>
    </footer>
</body>

</html>
