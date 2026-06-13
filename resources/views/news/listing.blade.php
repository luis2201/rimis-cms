<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $category?->name ?? 'Todas las noticias' }} | RIMIS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-800">
    <x-public-header :menus="$publicMenus" />
    <main class="mx-auto max-w-6xl px-5 py-12">
        <div class="mb-8">
            <a href="{{ route('news.index') }}" class="text-sm font-semibold text-[var(--rimis-primary-dark)]"><i class="fa-solid fa-arrow-left mr-1"></i> Volver a categorías</a>
            <h1 class="mt-5 text-4xl font-bold">{{ $category?->name ?? 'Todas las noticias' }}</h1>
            @if($category?->description)<p class="mt-3 max-w-3xl text-slate-500">{{ $category->description }}</p>@endif
        </div>
        <nav class="mb-8 flex flex-wrap gap-2" aria-label="Categorías de noticias">
            <a href="{{ route('news.all') }}" class="rounded-full border px-4 py-2 text-sm {{ !$category ? 'border-[var(--rimis-primary)] bg-[var(--rimis-primary)] text-white' : 'border-slate-300 bg-white' }}">Todas</a>
            @foreach($categories as $option)
                <a href="{{ route('news.category', $option) }}" class="rounded-full border px-4 py-2 text-sm {{ $category?->is($option) ? 'border-[var(--rimis-primary)] bg-[var(--rimis-primary)] text-white' : 'border-slate-300 bg-white' }}">{{ $option->name }} ({{ $option->news_count }})</a>
            @endforeach
        </nav>
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse($news as $item)
                @include('news._card', ['item' => $item])
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-slate-500 md:col-span-2 lg:col-span-3">No hay noticias publicadas en esta selección.</div>
            @endforelse
        </div>
        <div class="mt-10">{{ $news->links() }}</div>
    </main>
</body>
</html>
