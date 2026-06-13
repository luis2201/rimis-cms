@props(['seo', 'type' => 'website'])

<title>{{ $seo['title'] }}</title>
@if($seo['description'])<meta name="description" content="{{ $seo['description'] }}">@endif
@if($seo['keywords'])<meta name="keywords" content="{{ $seo['keywords'] }}">@endif
<meta name="robots" content="{{ $seo['robots'] }}">
<link rel="canonical" href="{{ $seo['canonical_url'] }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $seo['title'] }}">
@if($seo['description'])<meta property="og:description" content="{{ $seo['description'] }}">@endif
<meta property="og:url" content="{{ $seo['canonical_url'] }}">
@if($seo['image_url'])<meta property="og:image" content="{{ $seo['image_url'] }}">@endif
<meta name="twitter:card" content="{{ $seo['twitter_card'] }}">
<meta name="twitter:title" content="{{ $seo['title'] }}">
@if($seo['description'])<meta name="twitter:description" content="{{ $seo['description'] }}">@endif
@if($seo['image_url'])<meta name="twitter:image" content="{{ $seo['image_url'] }}">@endif
<script type="application/ld+json">
{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => $type === 'article' ? 'Article' : 'WebSite',
    'headline' => $seo['title'],
    'name' => $seo['title'],
    'description' => $seo['description'],
    'url' => $seo['canonical_url'],
    'image' => $seo['image_url'],
]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
