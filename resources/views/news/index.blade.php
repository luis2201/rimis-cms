<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Noticias | RIMIS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-800">
    <x-public-header :menus="$publicMenus" />

    <main class="mx-auto max-w-6xl px-5 py-12">
        <div class="mb-10 flex flex-wrap items-end justify-between gap-5">
            <div>
                <h1 class="text-4xl font-bold">Noticias</h1>
                <p class="mt-3 text-slate-500">Explora las novedades de RIMIS por área temática.</p>
            </div>
            <a href="{{ route('news.all') }}" class="rounded-lg bg-[var(--rimis-primary)] px-5 py-3 font-semibold text-white transition hover:bg-[var(--rimis-primary-dark)]">Ver todas las noticias</a>
        </div>

        @if($featured)
            <a href="{{ route('news.show', $featured->slug) }}" class="mb-12 grid overflow-hidden rounded-2xl bg-[var(--rimis-charcoal)] text-white shadow-lg md:grid-cols-2">
                @if($featured->featuredImage)<img src="{{ $featured->featuredImage->publicUrl() }}" class="h-full min-h-72 w-full object-cover" alt="{{ $featured->title }}">@endif
                <div class="p-8"><span class="text-sm font-bold uppercase text-[var(--rimis-primary)]">Destacada</span><h2 class="mt-3 text-3xl font-bold">{{ $featured->title }}</h2><p class="mt-4 text-slate-300">{{ $featured->excerpt }}</p></div>
            </a>
        @endif

        <section>
            <h2 class="text-2xl font-bold text-slate-900">Categorías</h2>
            <div class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @forelse($categories as $category)
                    <a href="{{ route('news.category', $category) }}" class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-[var(--rimis-primary)] hover:shadow-lg">
                        <div class="flex items-start justify-between gap-4"><span class="rounded-lg bg-[var(--rimis-primary)]/10 p-3 text-[var(--rimis-primary-dark)]"><i class="fa-solid fa-folder-open text-xl"></i></span><span class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-500">{{ $category->news_count }} noticias</span></div>
                        <h3 class="mt-5 text-xl font-bold text-slate-900 group-hover:text-[var(--rimis-primary-dark)]">{{ $category->name }}</h3>
                        @if($category->description)<p class="mt-3 text-sm leading-6 text-slate-600">{{ $category->description }}</p>@endif
                    </a>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-slate-500 md:col-span-2 lg:col-span-3">Las categorías aparecerán aquí cuando estén disponibles.</div>
                @endforelse
            </div>
        </section>

        @if($recentNews->isNotEmpty())
            <section class="mt-14">
                <div class="flex items-center justify-between gap-4"><h2 class="text-2xl font-bold text-slate-900">Noticias recientes</h2><a href="{{ route('news.all') }}" class="font-semibold text-[var(--rimis-primary-dark)]">Ver todas <i class="fa-solid fa-arrow-right ml-1"></i></a></div>
                <div class="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($recentNews as $item)
                        @include('news._card', ['item' => $item])
                    @endforeach
                </div>
            </section>
        @endif
    </main>
</body>
</html>
