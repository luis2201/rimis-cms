@props(['items'])

@foreach($items->where('is_active', true) as $item)
    <div class="group relative">
        <a href="{{ $item->url }}" target="{{ $item->target }}" class="block rounded-lg px-3 py-2 text-sm font-semibold text-white transition hover:bg-white/10">
            @if($item->icon)
                <i class="{{ $item->icon }} mr-1.5" aria-hidden="true"></i>
            @endif
            {{ $item->label }}
        </a>
        @if($item->children->where('is_active', true)->isNotEmpty())
            <div class="invisible absolute left-0 z-20 min-w-52 rounded-lg border border-white/10 bg-[var(--rimis-charcoal)] p-2 opacity-0 shadow-xl transition group-hover:visible group-hover:opacity-100">
                <x-public-menu-items :items="$item->children" />
            </div>
        @endif
    </div>
@endforeach
