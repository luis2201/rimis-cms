<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
    </url>
@foreach($pages as $page)
    <url>
        <loc>{{ route('pages.show', $page->slug) }}</loc>
        <lastmod>{{ $page->updated_at->toAtomString() }}</lastmod>
    </url>
@endforeach
@foreach($news as $item)
    <url>
        <loc>{{ route('news.show', $item->slug) }}</loc>
        <lastmod>{{ $item->updated_at->toAtomString() }}</lastmod>
    </url>
@endforeach
@foreach($bulletins as $item)
    <url>
        <loc>{{ route('bulletins.show', $item->slug) }}</loc>
        <lastmod>{{ $item->updated_at->toAtomString() }}</lastmod>
    </url>
@endforeach
@foreach($events as $item)
    <url>
        <loc>{{ route('events.show', $item->slug) }}</loc>
        <lastmod>{{ $item->updated_at->toAtomString() }}</lastmod>
    </url>
@endforeach
@foreach($calls as $item)
    <url>
        <loc>{{ route('calls.show', $item->slug) }}</loc>
        <lastmod>{{ $item->updated_at->toAtomString() }}</lastmod>
    </url>
@endforeach
@foreach($researchPublications as $item)
    <url><loc>{{ route('research-publications.show', $item->slug) }}</loc><lastmod>{{ $item->updated_at->toAtomString() }}</lastmod></url>
@endforeach
    <url><loc>{{ route('researchers.index') }}</loc><lastmod>{{ now()->toAtomString() }}</lastmod></url>
@foreach($researcherProfiles as $profile)
    <url><loc>{{ route('researchers.show', $profile->public_slug) }}</loc><lastmod>{{ $profile->updated_at->toAtomString() }}</lastmod></url>
@endforeach
    <url><loc>{{ route('institutions.index') }}</loc><lastmod>{{ now()->toAtomString() }}</lastmod></url>
@foreach($institutionProfiles as $profile)
    <url><loc>{{ route('institutions.show', $profile->public_slug) }}</loc><lastmod>{{ $profile->updated_at->toAtomString() }}</lastmod></url>
@endforeach
</urlset>
