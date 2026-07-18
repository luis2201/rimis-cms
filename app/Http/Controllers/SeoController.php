<?php

namespace App\Http\Controllers;

use App\Models\Bulletin;
use App\Models\CallForProposal;
use App\Models\ResearchPublication;
use App\Models\ResearcherProfile;
use App\Models\Event;
use App\Models\MediaFile;
use App\Models\News;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Support\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SeoController extends Controller
{
    public function edit(SeoService $seo): View
    {
        $settings = SiteSetting::firstOrCreate(['id' => 1], ['site_name' => 'RIMIS']);
        $mediaImages = MediaFile::where('file_type', 'image')->where('status', true)->latest()->get();

        return view('admin.seo.edit', [
            'settings' => $settings,
            'mediaImages' => $mediaImages,
            'preview' => $seo->global(),
            'suggestions' => $seo->globalSuggestions($settings),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'meta_title' => ['nullable', 'string', 'max:60'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'string', 'max:2048'],
            'seo_index' => ['required', 'boolean'],
            'twitter_card' => ['required', 'in:summary,summary_large_image'],
        ]);

        SiteSetting::firstOrCreate(['id' => 1], ['site_name' => 'RIMIS'])->update($validated);

        return back()->with('success', 'Configuración SEO global actualizada.');
    }

    public function sitemap(): Response
    {
        return response()->view('seo.sitemap', [
            'pages' => Page::published()->where('seo_index', true)->latest('updated_at')->get(),
            'news' => News::published()->where('seo_index', true)->latest('updated_at')->get(),
            'bulletins' => Bulletin::published()->latest('updated_at')->get(),
            'events' => Event::published()->latest('updated_at')->get(),
            'calls' => CallForProposal::published()->latest('updated_at')->get(),
            'researchPublications' => ResearchPublication::publiclyVisible()->latest('updated_at')->get(),
            'researcherProfiles' => ResearcherProfile::publiclyVisible()->latest('updated_at')->get(),
        ])
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $index = SiteSetting::find(1)?->seo_index ?? true;
        $content = $index
            ? "User-agent: *\nAllow: /\nSitemap: ".route('seo.sitemap')."\n"
            : "User-agent: *\nDisallow: /\n";

        return response($content)->header('Content-Type', 'text/plain');
    }
}
