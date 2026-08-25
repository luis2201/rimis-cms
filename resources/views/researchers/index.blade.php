<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Investigadores de la Red RIMIS</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="bg-slate-50 text-slate-800">@include('public.partials.nav')
<main class="mx-auto max-w-7xl px-6 py-12">
    <h1 class="text-4xl font-bold">Investigadores de la Red RIMIS</h1>
    <p class="mt-3 text-slate-600">Consulta investigadores por nombre, institución afiliada, país o área de investigación.</p>

    <form class="mt-8 grid gap-3 rounded-xl border bg-white p-5 md:grid-cols-2 lg:grid-cols-5">
        <input name="search" value="{{ request('search') }}" class="rounded border p-3" placeholder="Nombre o título">
        <select name="institution" class="rounded border p-3"><option value="">Todas las instituciones</option>@foreach($filters['institutions'] as $value)<option value="{{ $value }}" @selected(request('institution') === $value)>{{ $value }}</option>@endforeach</select>
        <select name="country" class="rounded border p-3"><option value="">Todos los países</option>@foreach($filters['countries'] as $value)<option value="{{ $value }}" @selected(request('country') === $value)>{{ $value }}</option>@endforeach</select>
        <select name="research_area" class="rounded border p-3"><option value="">Todas las áreas</option>@foreach($filters['areas'] as $value)<option value="{{ $value }}" @selected(request('research_area') === $value)>{{ $value }}</option>@endforeach</select>
        <button class="rounded bg-teal-700 px-4 py-3 font-semibold text-white">Filtrar investigadores</button>
    </form>

    <div class="mt-10 grid gap-6 md:grid-cols-3">@forelse($profiles as $profile)
        <article class="rounded-xl bg-white p-6 shadow">@if($profile->personal_photo_path)<img src="{{ Storage::disk('public')->url($profile->personal_photo_path) }}" alt="{{ $profile->displayName() }}" class="h-20 w-20 rounded-full object-cover">@else<div class="flex h-20 w-20 items-center justify-center rounded-full bg-teal-700 text-2xl text-white">{{ mb_substr($profile->first_names,0,1).mb_substr($profile->last_names,0,1) }}</div>@endif<h2 class="mt-4 text-xl font-bold">{{ $profile->displayName() }}</h2><p>{{ $profile->undergraduate_title }}</p>@if($profile->affiliated_institution)<p class="mt-2 text-sm font-semibold text-slate-600"><i class="fa-solid fa-building-columns mr-1 text-teal-700"></i>{{ $profile->affiliated_institution }}</p>@endif<p class="mt-2 text-sm text-teal-700">{{ implode(', ',$profile->research_areas??[]) }}</p><a class="mt-4 inline-block font-semibold text-teal-700" href="{{ route('researchers.show',$profile->public_slug) }}">Ver perfil</a></article>
    @empty<p>No se encontraron investigadores con los filtros seleccionados.</p>@endforelse</div>
    <div class="mt-8">{{ $profiles->links() }}</div>
</main>@include('public.partials.footer')</body></html>
