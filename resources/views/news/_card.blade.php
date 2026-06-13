<article class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
    @if($item->featuredImage)<a href="{{ route('news.show', $item->slug) }}"><img src="{{ $item->featuredImage->publicUrl() }}" class="h-48 w-full object-cover" alt="{{ $item->title }}"></a>@endif
    <div class="p-6">
        @if($item->category)<a href="{{ route('news.category', $item->category) }}" class="text-sm font-semibold text-[var(--rimis-primary-dark)]">{{ $item->category->name }}</a>@endif
        <h2 class="mt-2 text-xl font-bold text-slate-900"><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h2>
        @if($item->excerpt)<p class="mt-3 text-sm leading-6 text-slate-600">{{ $item->excerpt }}</p>@endif
        <a href="{{ route('news.show', $item->slug) }}" class="mt-5 inline-block font-semibold text-[var(--rimis-primary-dark)]">Leer noticia <i class="fa-solid fa-arrow-right ml-1"></i></a>
    </div>
</article>
