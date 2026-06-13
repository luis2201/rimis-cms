<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <x-seo-meta :seo="$seo" />

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[var(--rimis-charcoal)] font-sans antialiased text-white">
        <div class="min-h-screen bg-[radial-gradient(circle_at_top_right,rgba(31,157,173,0.35),transparent_38%),radial-gradient(circle_at_bottom_left,rgba(178,47,56,0.25),transparent_34%)]">
            <x-public-header :menus="$publicMenus" />

            <main class="mx-auto grid w-full max-w-7xl items-center gap-12 px-5 py-16 sm:px-8 lg:grid-cols-2 lg:py-28">
                <section>
                    <span class="inline-flex rounded-full border border-[var(--rimis-primary)]/50 bg-[var(--rimis-primary)]/10 px-4 py-2 text-sm font-semibold text-cyan-100">
                        Red de Investigación Multidisciplinaria
                    </span>
                    <h1 class="mt-6 text-4xl font-bold leading-tight sm:text-5xl lg:text-6xl">
                        Conectamos conocimiento, investigación e innovación.
                    </h1>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
                        RIMIS impulsa la colaboración científica y facilita la gestión de proyectos, publicaciones y oportunidades para investigadores.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-lg bg-[var(--rimis-primary)] px-6 py-3 font-semibold text-white transition hover:bg-[var(--rimis-primary-dark)]">
                                Acceder a mi panel
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="rounded-lg bg-[var(--rimis-primary)] px-6 py-3 font-semibold text-white transition hover:bg-[var(--rimis-primary-dark)]">
                                Crear una cuenta
                            </a>
                            <a href="{{ route('login') }}" class="rounded-lg border border-white/30 px-6 py-3 font-semibold text-white transition hover:bg-white/10">
                                Ya tengo una cuenta
                            </a>
                        @endauth
                    </div>
                </section>

                <section class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-2xl backdrop-blur sm:p-8">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl bg-white/10 p-5">
                            <p class="text-3xl font-bold text-[var(--rimis-primary)]">CMS</p>
                            <p class="mt-2 text-sm text-slate-300">Gestión centralizada de contenidos científicos.</p>
                        </div>
                        <div class="rounded-xl bg-white/10 p-5">
                            <p class="text-3xl font-bold text-[var(--rimis-coral)]">RIMIS</p>
                            <p class="mt-2 text-sm text-slate-300">Un espacio para investigadores y administradores.</p>
                        </div>
                        <div class="rounded-xl bg-white/10 p-5 sm:col-span-2">
                            <p class="font-semibold">Plataforma en construcción</p>
                            <p class="mt-2 text-sm text-slate-300">Próximamente: investigaciones, artículos, eventos, convocatorias y notificaciones.</p>
                        </div>
                    </div>
                </section>
            </main>

            @if($recentNews->isNotEmpty())
                <section class="mx-auto w-full max-w-7xl px-5 pb-16 sm:px-8">
                    <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
                        <div><span class="text-sm font-bold uppercase tracking-wider text-[var(--rimis-primary)]">Actualidad</span><h2 class="mt-2 text-3xl font-bold">Noticias recientes</h2></div>
                        <a href="{{ route('news.index') }}" class="rounded-lg border border-white/30 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10">Explorar noticias</a>
                    </div>
                    <div class="grid gap-5 md:grid-cols-3">
                        @foreach($recentNews as $item)
                            <article class="overflow-hidden rounded-xl border border-white/10 bg-white/5 shadow-lg transition hover:-translate-y-1 hover:bg-white/10">
                                @if($item->featuredImage)<a href="{{ route('news.show', $item->slug) }}"><img src="{{ $item->featuredImage->publicUrl() }}" class="h-44 w-full object-cover" alt="{{ $item->title }}"></a>@endif
                                <div class="p-5">@if($item->category)<span class="text-sm font-semibold text-cyan-200">{{ $item->category->name }}</span>@endif<h3 class="mt-2 text-xl font-bold"><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h3><p class="mt-3 text-sm leading-6 text-slate-300">{{ $item->excerpt }}</p></div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mx-auto flex w-full max-w-7xl flex-wrap items-center justify-between gap-4 border-t border-white/10 px-5 py-8 text-sm text-slate-400 sm:px-8">
                <div class="flex flex-wrap gap-4">
                    @foreach(($publicMenus->get('footer')?->rootItems ?? collect()) as $item)
                        <a href="{{ $item->url }}" target="{{ $item->target }}" class="transition hover:text-white">
                            @if($item->icon)<i class="{{ $item->icon }} mr-1" aria-hidden="true"></i>@endif
                            {{ $item->label }}
                        </a>
                    @endforeach
                </div>
                <div class="flex gap-3">
                    @foreach(($publicMenus->get('social')?->rootItems ?? collect()) as $item)
                        <a href="{{ $item->url }}" target="{{ $item->target }}" class="transition hover:text-white" aria-label="{{ $item->label }}">
                            @if($item->icon)<i class="{{ $item->icon }} mr-1" aria-hidden="true"></i>@endif
                            {{ $item->label }}
                        </a>
                    @endforeach
                </div>
            </footer>
        </div>
    </body>
</html>
