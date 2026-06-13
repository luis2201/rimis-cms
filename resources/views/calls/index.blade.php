<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Convocatorias | RIMIS</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="bg-slate-50 font-sans text-slate-800">
    <x-public-header :menus="$publicMenus" />
    <main class="mx-auto max-w-6xl px-5 py-12">
        <div class="mb-10 flex flex-wrap items-end justify-between gap-5"><div><span class="text-sm font-bold uppercase tracking-widest text-[var(--rimis-primary-dark)]">Oportunidades RIMIS</span><h1 class="mt-2 text-4xl font-bold">Convocatorias</h1><p class="mt-3 text-slate-500">Consulta bases, plazos y enlaces de inscripción.</p></div><div class="flex gap-2"><a href="{{ route('calls.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold {{ request('state') ? 'bg-white text-slate-600' : 'bg-[var(--rimis-primary)] text-white' }}">Todas</a><a href="{{ route('calls.index', ['state' => 'open']) }}" class="rounded-lg px-4 py-2 text-sm font-semibold {{ request('state') === 'open' ? 'bg-[var(--rimis-primary)] text-white' : 'bg-white text-slate-600' }}">Abiertas</a></div></div>
        <div class="grid gap-7 md:grid-cols-2 lg:grid-cols-3">
            @forelse($calls as $call)
                <article class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    @if($call->featuredImage)
                        <a href="{{ route('calls.show', $call->slug) }}"><img src="{{ $call->featuredImage->publicUrl() }}" class="aspect-[16/9] w-full object-cover" alt="{{ $call->title }}"></a>
                    @else
                        <div class="flex aspect-[16/9] items-center justify-center bg-[var(--rimis-charcoal)]"><i class="fa-solid fa-bullhorn text-6xl text-[var(--rimis-primary)]"></i></div>
                    @endif
                    <div class="p-6">
                        <div class="flex items-center justify-between gap-3"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $call->operationalStatusPublicClasses() }}">{{ $call->operationalStatusLabel() }}</span><small class="text-slate-500">Cierra {{ $call->closes_at->format('d/m/Y') }}</small></div>
                        <h2 class="mt-4 text-xl font-bold"><a href="{{ route('calls.show', $call->slug) }}">{{ $call->title }}</a></h2>
                        @if($call->summary)
                            <p class="mt-3 text-sm leading-6 text-slate-600">{{ Str::limit($call->summary, 150) }}</p>
                        @endif
                        <a href="{{ route('calls.show', $call->slug) }}" class="mt-5 inline-block font-semibold text-[var(--rimis-primary-dark)]">Ver convocatoria <i class="fa-solid fa-arrow-right ml-1"></i></a>
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-slate-500 md:col-span-2 lg:col-span-3">No hay convocatorias disponibles.</div>
            @endforelse
        </div>
        <div class="mt-10">{{ $calls->links() }}</div>
    </main>
</body></html>
