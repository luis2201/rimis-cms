<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $call->title }} | RIMIS</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="bg-slate-50 font-sans text-slate-800">
    <x-public-header :menus="$publicMenus" />
    <main class="mx-auto max-w-6xl px-5 py-12">
        <article class="overflow-hidden rounded-2xl bg-white shadow-sm">
            @if($call->featuredImage)
                <img src="{{ $call->featuredImage->publicUrl() }}" class="max-h-[560px] w-full object-cover" alt="{{ $call->title }}">
            @endif
            <div class="grid gap-10 p-7 sm:p-12 lg:grid-cols-[1fr_320px]">
                <div>
                    <span class="text-sm font-bold uppercase tracking-wider text-[var(--rimis-primary-dark)]">Convocatoria · {{ $call->operationalStatusLabel() }}</span>
                    <h1 class="mt-3 text-4xl font-bold text-slate-900">{{ $call->title }}</h1>
                    @if($call->summary)
                        <p class="mt-5 text-xl leading-8 text-slate-500">{{ $call->summary }}</p>
                    @endif
                    <div class="page-public-content mt-8 border-t pt-8 leading-8">{!! $call->description !!}</div>
                </div>
                <aside class="space-y-5">
                    <div class="rounded-xl bg-[var(--rimis-charcoal)] p-6 text-white"><div class="flex items-center justify-between"><h2 class="text-lg font-bold">Plazos</h2><span class="rounded-full bg-white/10 px-3 py-1 text-xs font-bold">{{ $call->operationalStatusLabel() }}</span></div><p class="mt-4 text-sm text-slate-300">Apertura</p><p class="font-semibold">{{ $call->opens_at->format('d/m/Y · H:i') }}</p><p class="mt-3 text-sm text-slate-300">Cierre</p><p class="font-semibold">{{ $call->closes_at->format('d/m/Y · H:i') }}</p></div>
                    <div class="rounded-xl border border-slate-200 p-6"><h2 class="text-lg font-bold">Bases</h2><p class="mt-2 text-sm text-slate-500">{{ $call->bases_pdf_original_name }} · {{ $call->formattedBasesSize() }}</p><a href="{{ route('calls.download', $call) }}" class="mt-5 block rounded-lg border border-[var(--rimis-primary)] px-4 py-3 text-center font-semibold text-[var(--rimis-primary-dark)] hover:bg-slate-50"><i class="fa-solid fa-file-pdf mr-2"></i>Descargar bases PDF</a></div>
                    <div class="rounded-xl border border-slate-200 p-6">
                        <h2 class="text-lg font-bold">Inscripciones</h2>
                        @if($call->acceptsRegistrations())
                            <p class="mt-2 text-sm text-slate-500">Las inscripciones se encuentran habilitadas.</p>
                            <a href="{{ $call->registration_url }}" target="_blank" rel="noopener noreferrer" class="mt-5 block rounded-lg bg-[var(--rimis-primary)] px-4 py-3 text-center font-semibold text-white hover:bg-[var(--rimis-primary-dark)]">Inscribirse <i class="fa-solid fa-arrow-up-right-from-square ml-1"></i></a>
                        @elseif($call->operationalStatus() === 'upcoming')
                            <p class="mt-2 text-sm text-slate-500">Las inscripciones estarán disponibles desde la fecha de apertura.</p>
                        @else
                            <p class="mt-2 text-sm text-slate-500">Las inscripciones no están disponibles.</p>
                        @endif
                    </div>
                </aside>
            </div>
        </article>
    </main>
</body></html>
