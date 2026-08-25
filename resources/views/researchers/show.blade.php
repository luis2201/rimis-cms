<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $profile->displayName() }} | RIMIS</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="bg-slate-50 text-slate-800">@include('public.partials.nav')
<main class="mx-auto max-w-4xl px-6 py-12">
    <a href="{{ route('researchers.index') }}" class="text-teal-700">← Investigadores</a>
    <div class="mt-6 rounded-xl border bg-white p-6">
        @if($profile->personal_photo_path)<img src="{{ Storage::disk('public')->url($profile->personal_photo_path) }}" alt="{{ $profile->displayName() }}" class="h-32 w-32 rounded-full object-cover">@endif
        <h1 class="mt-4 text-4xl font-bold">{{ $profile->displayName() }}</h1>
        <p><strong>Título:</strong> {{ $profile->undergraduate_title }}</p>
        <p><strong>Institución afiliada:</strong> {{ $profile->affiliated_institution ?: '—' }}</p>
        @if($profile->postgraduate_titles)<p><strong>Posgrados:</strong> {{ $profile->postgraduate_titles }}</p>@endif
        <p><strong>Áreas:</strong> {{ implode(', ', $profile->research_areas ?? []) }}</p>
        <p><strong>Funciones de docencia:</strong> {{ $profile->teaching_functions }}</p>
        <p><strong>Funciones de investigación:</strong> {{ $profile->current_research_functions }}</p>
        <p><strong>Actividad investigativa:</strong> {{ $profile->research_activity }}</p>
        <p><strong>Ubicación:</strong> {{ $profile->city }}, {{ $profile->country }}</p>
        @if($profile->orcid)<p><strong>ORCID:</strong> {{ $profile->orcid }}</p>@endif
    </div>
</main>@include('public.partials.footer')</body></html>
