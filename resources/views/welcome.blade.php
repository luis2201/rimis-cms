<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-seo-meta :seo="$seo" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800|libre-baskerville:400,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white font-sans antialiased text-slate-900">
    <div class="home-hero relative overflow-hidden bg-[var(--rimis-charcoal)] text-white">
        <div class="home-grid absolute inset-0 opacity-40"></div>
        <div class="home-orb home-orb-cyan"></div>
        <div class="home-orb home-orb-coral"></div>
        <div class="relative z-20"><x-public-header :menus="$publicMenus" /></div>

        <main class="relative z-10 mx-auto grid min-h-[680px] w-full max-w-7xl items-center gap-16 px-5 py-16 sm:px-8 lg:grid-cols-[1.05fr_.95fr] lg:py-24">
            <section>
                <div class="inline-flex items-center gap-3 rounded-full border border-white/15 bg-white/[.06] px-4 py-2 text-xs font-bold uppercase tracking-[.22em] text-cyan-100 backdrop-blur">
                    <span class="home-pulse h-2 w-2 rounded-full bg-[var(--rimis-primary)]"></span>
                    Red de Investigación Multidisciplinaria
                </div>
                <h1 class="mt-7 max-w-3xl text-5xl font-extrabold leading-[1.04] tracking-[-.045em] sm:text-6xl lg:text-7xl">
                    Investigación que conecta
                    <span class="home-gradient-text block">ideas con impacto.</span>
                </h1>
                <p class="mt-7 max-w-2xl text-lg leading-8 text-slate-300">
                    Un ecosistema para articular investigadores, compartir conocimiento y convertir la colaboración multidisciplinaria en soluciones para el futuro.
                </p>
                <div class="mt-9 flex flex-wrap gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="home-button-primary">Acceder a mi panel <i class="fa-solid fa-arrow-right ml-2"></i></a>
                    @else
                        <a href="{{ route('subscriptions.index') }}" class="home-button-primary">Formulario de Suscripción <i class="fa-solid fa-arrow-right ml-2"></i></a>
                        <a href="{{ route('news.index') }}" class="home-button-secondary">Explorar actualidad</a>
                    @endauth
                </div>
                <div class="mt-12 flex flex-wrap gap-x-8 gap-y-4 border-t border-white/10 pt-6 text-sm text-slate-400">
                    <span><i class="fa-solid fa-circle-nodes mr-2 text-[var(--rimis-primary)]"></i>Colaboración científica</span>
                    <span><i class="fa-solid fa-shield-halved mr-2 text-[var(--rimis-primary)]"></i>Comunidad verificada</span>
                    <span><i class="fa-solid fa-globe mr-2 text-[var(--rimis-primary)]"></i>Visión multidisciplinaria</span>
                </div>
            </section>

            <section class="home-research-visual relative hidden min-h-[500px] items-center justify-center lg:flex" aria-label="Investigación multidisciplinaria conectando ideas con impacto">
                <div class="home-research-glow"></div>
                <img
                    src="{{ asset('images/research-impact-hero.png') }}"
                    alt="Red multidisciplinaria de conocimiento conectando ciencia, tecnología y sociedad"
                    class="relative z-10 w-full max-w-[620px]"
                >
            </section>
        </main>
    </div>

    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto grid w-full max-w-7xl md:grid-cols-4">
            <a href="{{ route('news.index') }}" class="editorial-quick-link"><span>01</span><strong>Noticias</strong><i class="fa-solid fa-arrow-right"></i></a>
            <a href="{{ route('events.index') }}" class="editorial-quick-link"><span>02</span><strong>Eventos</strong><i class="fa-solid fa-arrow-right"></i></a>
            <a href="{{ route('calls.index') }}" class="editorial-quick-link"><span>03</span><strong>Convocatorias</strong><i class="fa-solid fa-arrow-right"></i></a>
            <a href="{{ route('bulletins.index') }}" class="editorial-quick-link"><span>04</span><strong>Boletines</strong><i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </section>

    <section class="bg-[var(--rimis-surface)]">
        <div class="mx-auto w-full max-w-7xl px-5 py-20 sm:px-8 lg:py-24">
            <div class="grid gap-7 border-b border-slate-300 pb-7 md:grid-cols-[1fr_auto] md:items-end">
                <div>
                    <span class="editorial-kicker text-[var(--rimis-coral)]">Actualidad</span>
                    <h2 class="editorial-serif mt-3 text-4xl font-bold tracking-[-.035em] text-slate-900 sm:text-5xl">Lo último de RIMIS</h2>
                </div>
                <a href="{{ route('news.index') }}" class="editorial-arrow-link">Ver todas las noticias <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="mt-10 grid gap-x-7 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($recentNews as $item)
                    <article class="latest-news-card group">
                        <a href="{{ route('news.show', $item->slug) }}" class="block">
                            <div class="latest-news-card-image">
                                @if($item->featuredImage)
                                    <img src="{{ $item->featuredImage->publicUrl() }}" alt="{{ $item->title }}">
                                @else
                                    <div class="editorial-news-art flex h-full items-center justify-center"><i class="fa-regular fa-newspaper relative z-10 text-5xl text-white/80"></i></div>
                                @endif
                                <span>{{ $item->category?->name ?? 'Actualidad' }}</span>
                            </div>
                            <time datetime="{{ $item->published_at?->toDateString() }}" class="latest-news-card-date">
                                {{ $item->published_at?->translatedFormat('d F Y') }}
                            </time>
                            <h3>{{ $item->title }}</h3>
                            @if($item->excerpt)
                                <p>{{ Str::limit($item->excerpt, 125) }}</p>
                            @endif
                            <strong>Leer noticia <i class="fa-solid fa-arrow-right"></i></strong>
                        </a>
                    </article>
                @empty
                    <div class="editorial-empty sm:col-span-2 lg:col-span-3"><span>Actualidad</span><h3>Próximamente compartiremos historias, hallazgos y perspectivas de nuestra comunidad.</h3></div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="bg-white">
        <div class="mx-auto grid w-full max-w-7xl gap-14 px-5 py-20 sm:px-8 lg:grid-cols-[.8fr_1.2fr] lg:py-28">
            <div>
                <span class="editorial-kicker text-[var(--rimis-primary-dark)]">Agenda RIMIS</span>
                <h2 class="editorial-heading mt-4">Encuentros para compartir conocimiento.</h2>
                <p class="mt-6 max-w-lg text-lg leading-8 text-slate-600">Conferencias, diálogos y espacios multidisciplinarios para conectar personas e ideas.</p>
                <a href="{{ route('events.index') }}" class="editorial-arrow-link mt-8">Explorar eventos <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="border-t-2 border-slate-900">
                @forelse($upcomingEvents as $event)
                    <a href="{{ route('events.show', $event->slug) }}" class="editorial-event-row group">
                        <time><strong>{{ $event->starts_at->format('d') }}</strong><span>{{ $event->starts_at->translatedFormat('M Y') }}</span></time>
                        <span class="flex-1"><small>{{ $event->modalityLabel() }}{{ $event->location ? ' · '.$event->location : '' }}</small><strong>{{ $event->title }}</strong></span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                @empty
                    <div class="editorial-empty border-0 px-0"><span>Agenda</span><h3>Los próximos encuentros de la red aparecerán aquí.</h3></div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="institution-partners border-y border-slate-200 bg-[var(--rimis-surface)]">
        <div class="mx-auto w-full max-w-7xl px-5 py-16 sm:px-8 lg:py-20">
            <div class="mx-auto max-w-2xl text-center">
                <span class="editorial-kicker text-[var(--rimis-coral)]">Colaboración institucional</span>
                <h2 class="editorial-serif mt-3 text-3xl font-bold tracking-[-.035em] text-slate-900 sm:text-4xl">Comunidades Científicas</h2>
                <p class="mt-4 text-base leading-7 text-slate-600">Organizaciones que colaboran para fortalecer la investigación, la divulgación científica y su impacto en la sociedad.</p>
            </div>
            <div class="mt-10 grid grid-cols-2 gap-4 lg:grid-cols-4">
                <a href="https://www.itsup.edu.ec/home" target="_blank" rel="noopener noreferrer" class="institution-partner-card">
                    <img src="{{ asset('images/itsup.png') }}" alt="Instituto Superior Tecnológico Portoviejo">
                    <span>ITSUP <i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                </a>
                <a href="https://editorial.itsup.edu.ec/index.php/itsup" target="_blank" rel="noopener noreferrer" class="institution-partner-card">
                    <img src="{{ asset('images/sinapsis.png') }}" alt="Sinapsis Editorial">
                    <span>Sinapsis Editorial <i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                </a>
                <a href="https://revistas.itsup.edu.ec/index.php/Higia" target="_blank" rel="noopener noreferrer" class="institution-partner-card">
                    <img src="{{ asset('images/higia.jpg') }}" alt="Higia">
                    <span>Higia <i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                </a>
                <a href="https://ceish.itsup.edu.ec/" target="_blank" rel="noopener noreferrer" class="institution-partner-card">
                    <img src="{{ asset('images/ceish.png') }}" alt="Comité de Ética de Investigación en Seres Humanos ITSUP">
                    <span>CEISH-ITSUP <i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                </a>
            </div>
        </div>
    </section>

    <section class="mx-auto w-full max-w-7xl px-5 py-20 sm:px-8 lg:py-28">
        <div class="grid gap-12 lg:grid-cols-2">
            <div class="editorial-dark-panel">
                <span class="editorial-kicker text-cyan-200">Oportunidades</span>
                <h2 class="editorial-serif mt-5 text-4xl font-bold leading-tight sm:text-5xl">Convocatorias para transformar ideas en acción.</h2>
                <div class="mt-10 border-t border-white/20">
                    @forelse($openCalls as $call)
                        <a href="{{ route('calls.show', $call->slug) }}" class="editorial-dark-row"><span><small>Cierra {{ $call->closes_at->format('d/m/Y') }}</small><strong>{{ $call->title }}</strong></span><i class="fa-solid fa-arrow-right"></i></a>
                    @empty
                        <p class="py-7 text-slate-300">No hay convocatorias abiertas en este momento.</p>
                    @endforelse
                </div>
                <a href="{{ route('calls.index') }}" class="editorial-button-light mt-9">Ver convocatorias</a>
            </div>

            <div class="border-t-2 border-slate-900 pt-6">
                <div class="flex items-end justify-between gap-5"><div><span class="editorial-kicker text-[var(--rimis-coral)]">Biblioteca</span><h2 class="editorial-serif mt-4 text-4xl font-bold">Boletines recientes</h2></div><a href="{{ route('bulletins.index') }}" class="editorial-circle-link"><i class="fa-solid fa-arrow-right"></i></a></div>
                <div class="mt-9 grid gap-5 sm:grid-cols-3">
                    @forelse($recentBulletins as $bulletin)
                        <a href="{{ route('bulletins.show', $bulletin->slug) }}" class="group">
                            <div class="overflow-hidden bg-slate-900">@if($bulletin->coverImage)<img src="{{ $bulletin->coverImage->publicUrl() }}" alt="{{ $bulletin->title }}" class="aspect-[3/4] w-full object-cover transition duration-500 group-hover:scale-105">@else<div class="editorial-news-art flex aspect-[3/4] items-center justify-center"><i class="fa-solid fa-file-lines text-5xl text-white/80"></i></div>@endif</div>
                            <small class="editorial-kicker mt-4 block text-[var(--rimis-primary-dark)]">{{ $bulletin->published_at->format('d/m/Y') }}</small>
                            <strong class="editorial-serif mt-2 block leading-snug">{{ $bulletin->title }}</strong>
                        </a>
                    @empty
                        <div class="editorial-empty sm:col-span-3"><span>Biblioteca</span><h3>Los boletines publicados estarán disponibles aquí.</h3></div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-[var(--rimis-charcoal)] text-slate-400">
        <div class="mx-auto grid w-full max-w-7xl gap-10 px-5 py-12 sm:px-8 md:grid-cols-[1fr_auto] md:items-center">
            <div><img src="{{ asset('images/logo_rimis.png') }}" alt="RIMIS" class="h-12 w-auto"><p class="mt-4 max-w-md text-sm">Red de Investigación Multidisciplinaria.</p></div>
            <div class="flex flex-wrap gap-5 text-sm">@foreach(($publicMenus->get('footer')?->rootItems ?? collect()) as $item)<a href="{{ $item->url }}" target="{{ $item->target }}" class="transition hover:text-white">{{ $item->label }}</a>@endforeach</div>
        </div>
    </footer>
</body>
</html>
