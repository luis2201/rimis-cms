<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'RIMIS') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[var(--rimis-charcoal)] px-4 py-10">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(31,157,173,0.38),transparent_38%),radial-gradient(circle_at_bottom_left,rgba(178,47,56,0.25),transparent_34%)]"></div>

            <div {{ $attributes->class(['relative w-full', 'max-w-5xl' => $attributes->has('wide'), 'max-w-md' => ! $attributes->has('wide')]) }}>
                <a href="{{ url('/') }}" class="mx-auto block w-fit rounded-xl px-4 py-2 transition hover:bg-white/5" aria-label="Volver al inicio de RIMIS">
                    <img src="{{ asset('images/logo_rimis.png') }}" alt="RIMIS" class="h-20 w-auto object-contain">
                </a>

                <div class="mt-6 overflow-hidden rounded-2xl border border-white/10 bg-white shadow-2xl">
                    <div class="h-1.5 bg-gradient-to-r from-[var(--rimis-primary)] via-[var(--rimis-coral)] to-[var(--rimis-primary)]"></div>
                    <div class="px-6 py-7 sm:px-8">
                        {{ $slot }}
                    </div>
                </div>

                <p class="mt-6 text-center text-xs text-slate-400">
                    Red de Investigación Multidisciplinaria
                </p>
            </div>
        </div>
    </body>
</html>
