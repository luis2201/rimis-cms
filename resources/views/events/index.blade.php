<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Eventos | RIMIS</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="bg-slate-50 font-sans text-slate-800">
    <x-public-header :menus="$publicMenus" />
    <main class="mx-auto max-w-6xl px-5 py-12">
        <div class="mb-10"><span class="text-sm font-bold uppercase tracking-widest text-[var(--rimis-primary-dark)]">Agenda RIMIS</span><h1 class="mt-2 text-4xl font-bold">Eventos</h1><p class="mt-3 text-slate-500">Consulta fechas, ubicación y datos de contacto de nuestros próximos eventos.</p></div>
        <div class="grid gap-7 md:grid-cols-2 lg:grid-cols-3">
            @forelse($events as $event)
                <article class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    @if($event->featuredImage)
                        <a href="{{ route('events.show', $event->slug) }}"><img src="{{ $event->featuredImage->publicUrl() }}" class="aspect-[16/9] w-full object-cover" alt="{{ $event->title }}"></a>
                    @else
                        <div class="flex aspect-[16/9] items-center justify-center bg-[var(--rimis-charcoal)]"><i class="fa-regular fa-calendar text-6xl text-[var(--rimis-primary)]"></i></div>
                    @endif
                    <div class="p-6">
                        <div class="flex items-center gap-3 text-sm font-semibold text-[var(--rimis-primary-dark)]"><time>{{ $event->starts_at->format('d/m/Y · H:i') }}</time><span class="rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-600">{{ $event->modalityLabel() }}</span></div>
                        <h2 class="mt-3 text-xl font-bold"><a href="{{ route('events.show', $event->slug) }}">{{ $event->title }}</a></h2>
                        @if($event->summary)
                            <p class="mt-3 text-sm leading-6 text-slate-600">{{ Str::limit($event->summary, 150) }}</p>
                        @endif
                        @if($event->location)
                            <p class="mt-4 text-sm text-slate-500"><i class="fa-solid fa-location-dot mr-2 text-[var(--rimis-primary)]"></i>{{ $event->location }}</p>
                        @endif
                        <a href="{{ route('events.show', $event->slug) }}" class="mt-5 inline-block font-semibold text-[var(--rimis-primary-dark)]">Ver información <i class="fa-solid fa-arrow-right ml-1"></i></a>
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-slate-500 md:col-span-2 lg:col-span-3">No hay eventos publicados todavía.</div>
            @endforelse
        </div>
        <div class="mt-10">{{ $events->links() }}</div>
    </main>
</body></html>
