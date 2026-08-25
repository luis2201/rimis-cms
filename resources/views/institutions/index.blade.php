<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Instituciones de la Red RIMIS</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="bg-slate-50 text-slate-800">@include('public.partials.nav')
<main class="mx-auto max-w-7xl px-6 py-12">
    <h1 class="text-4xl font-bold">Instituciones de la Red RIMIS</h1>
    <p class="mt-3 text-slate-600">Consulta las instituciones miembros por nombre, país o tipo.</p>

    <form class="mt-8 grid gap-3 rounded-xl border bg-white p-5 md:grid-cols-4">
        <input name="search" value="{{ request('search') }}" class="rounded border p-3" placeholder="Nombre de la institución">
        <select name="country" class="rounded border p-3"><option value="">Todos los países</option>@foreach($filters['countries'] as $value)<option value="{{ $value }}" @selected(request('country') === $value)>{{ $value }}</option>@endforeach</select>
        <select name="institution_type" class="rounded border p-3"><option value="">Todos los tipos</option>@foreach($filters['types'] as $value)<option value="{{ $value }}" @selected(request('institution_type') === $value)>{{ $value === 'Otra' ? 'Otra' : $value }}</option>@endforeach</select>
        <button class="rounded bg-teal-700 px-4 py-3 font-semibold text-white">Filtrar instituciones</button>
    </form>

    <div class="mt-10 grid gap-5 md:grid-cols-3">@forelse($institutions as $institution)
        <article class="rounded-xl border bg-white p-5">@if($institution->institution_logo_path)<img src="{{ Storage::disk('public')->url($institution->institution_logo_path) }}" alt="Logotipo de {{ $institution->institution_name }}" class="mb-4 h-20 w-20 rounded-lg object-contain">@endif<h2 class="text-xl font-bold"><a href="{{ route('institutions.show',$institution->public_slug) }}">{{ $institution->institution_name }}</a></h2><p>{{ $institution->city }}, {{ $institution->country }}</p><p>{{ $institution->institution_type === 'Otra' ? $institution->other_institution_type : $institution->institution_type }}</p></article>
    @empty<p>No se encontraron instituciones con los filtros seleccionados.</p>@endforelse</div>
    <div class="mt-8">{{ $institutions->links() }}</div>
</main>@include('public.partials.footer')</body></html>
