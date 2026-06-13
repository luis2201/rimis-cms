<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $event->title }} | RIMIS</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="bg-slate-50 font-sans text-slate-800">
    <x-public-header :menus="$publicMenus" />
    <main class="mx-auto max-w-6xl px-5 py-12">
        <article class="overflow-hidden rounded-2xl bg-white shadow-sm">
            @if($event->featuredImage)
                <img src="{{ $event->featuredImage->publicUrl() }}" class="max-h-[560px] w-full object-cover" alt="{{ $event->title }}">
            @endif
            <div class="grid gap-10 p-7 sm:p-12 lg:grid-cols-[1fr_320px]">
                <div>
                    <span class="text-sm font-bold uppercase tracking-wider text-[var(--rimis-primary-dark)]">Evento · {{ $event->modalityLabel() }}</span>
                    <h1 class="mt-3 text-4xl font-bold text-slate-900">{{ $event->title }}</h1>
                    @if($event->summary)
                        <p class="mt-5 text-xl leading-8 text-slate-500">{{ $event->summary }}</p>
                    @endif
                    <div class="page-public-content mt-8 border-t pt-8 leading-8">{!! $event->description !!}</div>
                </div>
                <aside class="space-y-5">
                    <div class="rounded-xl bg-[var(--rimis-charcoal)] p-6 text-white"><h2 class="text-lg font-bold">Fecha y hora</h2><p class="mt-4 text-sm text-slate-300">Inicio</p><p class="font-semibold">{{ $event->starts_at->format('d/m/Y · H:i') }}</p><p class="mt-3 text-sm text-slate-300">Fin</p><p class="font-semibold">{{ $event->ends_at->format('d/m/Y · H:i') }}</p></div>
                    <div class="rounded-xl border border-slate-200 p-6">
                        <h2 class="text-lg font-bold">Información</h2>
                        <dl class="mt-4 space-y-4 text-sm">
                            @if($event->location)<div><dt class="font-semibold text-slate-500">Ubicación o plataforma</dt><dd class="mt-1">{{ $event->location }}</dd></div>@endif
                            @if($event->organizer)<div><dt class="font-semibold text-slate-500">Organizador</dt><dd class="mt-1">{{ $event->organizer }}</dd></div>@endif
                            @if($event->responsible_name)<div><dt class="font-semibold text-slate-500">Responsable</dt><dd class="mt-1">{{ $event->responsible_name }}</dd></div>@endif
                            @if($event->contact_email)<div><dt class="font-semibold text-slate-500">Correo</dt><dd class="mt-1"><a class="text-[var(--rimis-primary-dark)] hover:underline" href="mailto:{{ $event->contact_email }}">{{ $event->contact_email }}</a></dd></div>@endif
                            @if($event->contact_phone)<div><dt class="font-semibold text-slate-500">Teléfono</dt><dd class="mt-1"><a class="text-[var(--rimis-primary-dark)] hover:underline" href="tel:{{ preg_replace('/[^0-9+]/', '', $event->contact_phone) }}">{{ $event->contact_phone }}</a></dd></div>@endif
                        </dl>
                        @if($event->website_url)
                            <a href="{{ $event->website_url }}" target="_blank" rel="noopener noreferrer" class="mt-6 block rounded-lg bg-[var(--rimis-primary)] px-4 py-3 text-center font-semibold text-white hover:bg-[var(--rimis-primary-dark)]">Más información <i class="fa-solid fa-arrow-up-right-from-square ml-1"></i></a>
                        @endif
                    </div>
                </aside>
            </div>
        </article>
    </main>
</body></html>
