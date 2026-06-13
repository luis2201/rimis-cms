<?php

namespace App\Http\Controllers;

use App\Models\CallForProposal;
use App\Models\MediaFile;
use App\Support\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CallForProposalController extends Controller
{
    public function __construct(private HtmlSanitizer $sanitizer)
    {
    }

    public function publicIndex(Request $request): View
    {
        $calls = CallForProposal::published()
            ->with('featuredImage')
            ->when($request->input('state') === 'open', fn ($query) => $query->where('opens_at', '<=', now())->where('closes_at', '>=', now()))
            ->orderByRaw('closes_at < ? asc', [now()])
            ->orderBy('closes_at')
            ->paginate(12)
            ->withQueryString();

        return view('calls.index', compact('calls'));
    }

    public function publicShow(string $slug): View
    {
        $call = CallForProposal::published()->with(['featuredImage', 'author'])->where('slug', $slug)->firstOrFail();

        return view('calls.show', compact('call'));
    }

    public function download(CallForProposal $call): StreamedResponse
    {
        abort_unless($call->isPublished() && Storage::disk('local')->exists($call->bases_pdf_path), 404);

        return Storage::disk('local')->download($call->bases_pdf_path, $call->bases_pdf_original_name);
    }

    public function index(Request $request): View
    {
        $calls = CallForProposal::with('author')
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderByDesc('opens_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.calls.index', compact('calls'));
    }

    public function create(): View
    {
        return view('admin.calls.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCall($request);
        $file = $validated['bases_pdf'];
        unset($validated['bases_pdf']);
        $validated += [
            'user_id' => $request->user()->id,
            'bases_pdf_path' => $file->store('calls', 'local'),
            'bases_pdf_original_name' => $file->getClientOriginalName(),
            'bases_pdf_size' => $file->getSize(),
            'status' => CallForProposal::STATUS_DRAFT,
            'published_at' => null,
        ];
        $call = CallForProposal::create($validated);

        return redirect()->route('admin.calls.edit', $call)->with('success', 'Convocatoria creada como borrador.');
    }

    public function edit(CallForProposal $call): View
    {
        return view('admin.calls.edit', array_merge($this->formData(), compact('call')));
    }

    public function update(Request $request, CallForProposal $call): RedirectResponse
    {
        $validated = $this->validateCall($request, $call);
        $file = $validated['bases_pdf'] ?? null;
        unset($validated['bases_pdf']);

        if ($file) {
            Storage::disk('local')->delete($call->bases_pdf_path);
            $validated += [
                'bases_pdf_path' => $file->store('calls', 'local'),
                'bases_pdf_original_name' => $file->getClientOriginalName(),
                'bases_pdf_size' => $file->getSize(),
            ];
        }

        $call->update($validated);

        return back()->with('success', 'Convocatoria actualizada correctamente.');
    }

    public function destroy(CallForProposal $call): RedirectResponse
    {
        $call->delete();

        return redirect()->route('admin.calls.index')->with('success', 'Convocatoria eliminada correctamente.');
    }

    public function publish(CallForProposal $call): RedirectResponse
    {
        $call->publish();

        return back()->with('success', 'Convocatoria publicada correctamente.');
    }

    public function unpublish(CallForProposal $call): RedirectResponse
    {
        $call->unpublish();

        return back()->with('success', 'Convocatoria despublicada correctamente.');
    }

    private function validateCall(Request $request, ?CallForProposal $call = null): array
    {
        $request->merge([
            'slug' => Str::slug($request->input('slug') ?: $request->input('title')),
            'registration_enabled' => $request->boolean('registration_enabled'),
        ]);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('calls')->ignore($call)],
            'summary' => ['nullable', 'string', 'max:1000'],
            'description' => ['required', 'string'],
            'featured_image_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'opens_at' => ['required', 'date'],
            'closes_at' => ['required', 'date', 'after_or_equal:opens_at'],
            'bases_pdf' => [$call ? 'nullable' : 'required', 'file', 'mimes:pdf', 'max:20480'],
            'registration_enabled' => ['required', 'boolean'],
            'registration_url' => ['nullable', 'required_if:registration_enabled,1', 'url', 'max:2048'],
        ]);
        $validated['description'] = $this->sanitizer->clean($validated['description']);

        return $validated;
    }

    private function formData(): array
    {
        return ['mediaImages' => MediaFile::where('file_type', 'image')->where('status', true)->latest()->get()];
    }
}
