@php($data = $block->data)

@if($block->type === 'hero')
    <section class="page-block-hero" @if(!empty($data['image_id']) && $media->has($data['image_id'])) style="background-image: linear-gradient(rgba(21,29,36,.72), rgba(21,29,36,.72)), url('{{ $media[$data['image_id']]->publicUrl() }}')" @endif>
        <div class="mx-auto max-w-5xl px-5 py-20 text-center text-white">
            @if(!empty($data['title']))<h2 class="text-4xl font-bold sm:text-5xl">{{ $data['title'] }}</h2>@endif
            @if(!empty($data['subtitle']))<p class="mx-auto mt-5 max-w-3xl text-lg text-slate-200">{{ $data['subtitle'] }}</p>@endif
        </div>
    </section>
@elseif($block->type === 'text')
    <section class="page-block-section"><div class="mx-auto max-w-4xl px-5">@if(!empty($data['title']))<h2 class="page-block-title">{{ $data['title'] }}</h2>@endif<div class="page-public-content">{!! $data['content'] ?? '' !!}</div></div></section>
@elseif($block->type === 'image' && !empty($data['image_id']) && $media->has($data['image_id']))
    <section class="page-block-section"><div class="mx-auto max-w-5xl px-5">@if(!empty($data['title']))<h2 class="page-block-title">{{ $data['title'] }}</h2>@endif<img class="w-full rounded-2xl shadow-lg" src="{{ $media[$data['image_id']]->publicUrl() }}" alt="{{ $media[$data['image_id']]->alt_text }}"></div></section>
@elseif($block->type === 'text_image')
    <section class="page-block-section"><div class="mx-auto grid max-w-6xl items-center gap-10 px-5 lg:grid-cols-2">
        <div class="{{ ($data['image_position'] ?? 'right') === 'left' ? 'lg:order-2' : '' }}">@if(!empty($data['title']))<h2 class="page-block-title">{{ $data['title'] }}</h2>@endif<div class="page-public-content">{!! $data['content'] ?? '' !!}</div></div>
        @if(!empty($data['image_id']) && $media->has($data['image_id']))<img class="w-full rounded-2xl shadow-lg {{ ($data['image_position'] ?? 'right') === 'left' ? 'lg:order-1' : '' }}" src="{{ $media[$data['image_id']]->publicUrl() }}" alt="{{ $media[$data['image_id']]->alt_text }}">@endif
    </div></section>
@elseif($block->type === 'gallery')
    <section class="page-block-section"><div class="mx-auto max-w-6xl px-5">@if(!empty($data['title']))<h2 class="page-block-title text-center">{{ $data['title'] }}</h2>@endif<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">@foreach($data['image_ids'] ?? [] as $id)@if($media->has($id))<a href="{{ $media[$id]->publicUrl() }}" target="_blank"><img class="page-block-gallery-image" src="{{ $media[$id]->publicUrl() }}" alt="{{ $media[$id]->alt_text }}"></a>@endif @endforeach</div></div></section>
@elseif($block->type === 'cards')
    <section class="page-block-section"><div class="mx-auto max-w-6xl px-5">@if(!empty($data['title']))<h2 class="page-block-title text-center">{{ $data['title'] }}</h2>@endif<div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">@foreach($data['items'] ?? [] as $item)<article class="rounded-xl border border-slate-100 bg-white p-6 shadow-sm"><h3 class="text-xl font-bold text-slate-900">{{ $item[0] ?? '' }}</h3><p class="mt-3 text-slate-600">{{ $item[1] ?? '' }}</p>@if(!empty($item[2]))<a class="mt-5 inline-block font-semibold text-[var(--rimis-primary-dark)]" href="{{ $item[2] }}">Ver más <i class="fa-solid fa-arrow-right ml-1"></i></a>@endif</article>@endforeach</div></div></section>
@elseif($block->type === 'video' && $block->videoEmbedUrl())
    <section class="page-block-section"><div class="mx-auto max-w-5xl px-5">@if(!empty($data['title']))<h2 class="page-block-title text-center">{{ $data['title'] }}</h2>@endif<div class="aspect-video overflow-hidden rounded-2xl shadow-lg"><iframe class="h-full w-full" src="{{ $block->videoEmbedUrl() }}" title="{{ $data['title'] ?? 'Video' }}" allowfullscreen loading="lazy"></iframe></div></div></section>
@elseif($block->type === 'buttons')
    <section class="page-block-section"><div class="mx-auto max-w-4xl px-5 text-center">@if(!empty($data['title']))<h2 class="page-block-title">{{ $data['title'] }}</h2>@endif<div class="flex flex-wrap justify-center gap-3">@foreach($data['items'] ?? [] as $item)<a href="{{ $item[1] ?? '#' }}" class="{{ ($item[2] ?? '') === 'outline' ? 'page-block-button-outline' : 'page-block-button' }}">{{ $item[0] ?? 'Ver más' }}</a>@endforeach</div></div></section>
@elseif($block->type === 'faq')
    <section class="page-block-section"><div class="mx-auto max-w-4xl px-5">@if(!empty($data['title']))<h2 class="page-block-title text-center">{{ $data['title'] }}</h2>@endif<div class="space-y-3">@foreach($data['items'] ?? [] as $item)<details class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><summary class="cursor-pointer font-bold text-slate-900">{{ $item[0] ?? '' }}</summary><p class="mt-3 leading-7 text-slate-600">{{ $item[1] ?? '' }}</p></details>@endforeach</div></div></section>
@elseif($block->type === 'html')
    <section class="page-block-section"><div class="page-public-content mx-auto max-w-6xl px-5">{!! $data['content'] ?? '' !!}</div></section>
@elseif($block->type === 'dynamic_list')
    @php($source = $data['source'] ?? 'published_pages')
    @php($listKey = $source === 'news_category' ? 'news_category_'.($data['category_id'] ?? '') : $source)
    @php($items = ($dynamicLists[$listKey] ?? collect())->take($data['limit'] ?? 6))
    @php($isNewsList = in_array($source, ['recent_news', 'featured_news', 'news_category'], true))
    <section class="page-block-section"><div class="mx-auto max-w-6xl px-5">@if(!empty($data['title']))<h2 class="page-block-title text-center">{{ $data['title'] }}</h2>@endif<div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        @foreach($items as $item)
            @if($source === 'media_images')
                <img class="page-block-gallery-image" src="{{ $item->publicUrl() }}" alt="{{ $item->alt_text }}">
            @elseif($isNewsList)
                <article class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    @if($item->featuredImage)<a href="{{ route('news.show', $item->slug) }}"><img class="h-48 w-full object-cover" src="{{ $item->featuredImage->publicUrl() }}" alt="{{ $item->title }}"></a>@endif
                    <div class="p-6">@if($item->category)<small class="font-semibold text-[var(--rimis-primary-dark)]">{{ $item->category->name }}</small>@endif<h3 class="mt-2 text-xl font-bold text-slate-900"><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h3><p class="mt-3 text-slate-600">{{ $item->excerpt }}</p></div>
                </article>
            @else
                <a href="{{ route('pages.show', $item->slug) }}" class="rounded-xl border border-slate-100 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"><h3 class="text-xl font-bold text-slate-900">{{ $item->title }}</h3><p class="mt-3 text-slate-600">{{ $item->excerpt }}</p></a>
            @endif
        @endforeach
    </div></div></section>
@endif
