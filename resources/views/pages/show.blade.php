<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo-meta :seo="$seo" type="article" />
    <link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
    <x-public-header :menus="$publicMenus" />
    @if($page->show_title || $page->content)
    <main class="mx-auto max-w-4xl px-5 {{ $page->blocks->isNotEmpty() ? 'pb-5 pt-10' : 'py-14' }}">
        <article class="{{ $page->show_title ? 'rounded-2xl bg-white p-7 shadow-sm sm:p-12' : 'page-public-content py-4 leading-8 text-slate-700' }}">
            @if($page->show_title)
            <span class="text-sm font-bold uppercase tracking-wider text-[var(--rimis-primary)]">RIMIS</span>
            <h1 class="mt-3 text-4xl font-bold text-slate-900">{{ $page->title }}</h1>
            @if($page->excerpt)<p class="mt-5 text-xl leading-8 text-slate-500">{{ $page->excerpt }}</p>@endif
            @endif
            @if($page->content)<div class="page-public-content {{ $page->show_title ? 'mt-10 border-t border-slate-100 pt-8' : '' }} leading-8 text-slate-700">{!! $page->content !!}</div>@endif
        </article>
    </main>
    @endif
    @foreach($page->blocks as $block)
        @include('pages._block')
    @endforeach
</body>
</html>
