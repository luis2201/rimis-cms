<?php

namespace App\Http\Controllers;

use App\Models\Bulletin;
use App\Models\MediaFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulletinController extends Controller
{
    public function publicIndex(): View
    {
        $bulletins = Bulletin::published()->with('coverImage')->latest('published_at')->paginate(12);

        return view('bulletins.index', compact('bulletins'));
    }

    public function publicShow(string $slug): View
    {
        $bulletin = Bulletin::published()->with('coverImage')->where('slug', $slug)->firstOrFail();

        return view('bulletins.show', compact('bulletin'));
    }

    public function download(Bulletin $bulletin): StreamedResponse
    {
        abort_unless($bulletin->isPublished() && ($bulletin->isStaffContent() || $bulletin->isApprovedForPublication()) && Storage::disk('local')->exists($bulletin->pdf_path), 404);

        return Storage::disk('local')->download($bulletin->pdf_path, $bulletin->pdf_original_name);
    }

    public function index(Request $request): View
    {
        $bulletins = Bulletin::with('author')
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('origin'), fn ($query) => $query->where('origin', $request->string('origin')))
            ->when($request->filled('review_status'), fn ($query) => $query->where('review_status', $request->string('review_status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.bulletins.index', compact('bulletins'));
    }

    public function create(): View
    {
        return view('admin.bulletins.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBulletin($request);
        $file = $validated['pdf'];
        unset($validated['pdf']);
        $validated += [
            'user_id' => $request->user()->id,
            'pdf_path' => $file->store('bulletins', 'local'),
            'pdf_original_name' => $file->getClientOriginalName(),
            'pdf_size' => $file->getSize(),
            'status' => Bulletin::STATUS_DRAFT,
            'published_at' => null,
            'origin' => Bulletin::ORIGIN_STAFF,
            'review_status' => Bulletin::REVIEW_NOT_REQUIRED,
        ];
        $bulletin = Bulletin::create($validated);

        return redirect()->route('admin.bulletins.edit', $bulletin)->with('success', 'Boletín creado como borrador.');
    }

    public function edit(Bulletin $bulletin): View
    {
        return view('admin.bulletins.edit', array_merge($this->formData(), compact('bulletin')));
    }

    public function update(Request $request, Bulletin $bulletin): RedirectResponse
    {
        $validated = $this->validateBulletin($request, $bulletin);
        $file = $validated['pdf'] ?? null;
        unset($validated['pdf']);

        if ($file) {
            Storage::disk('local')->delete($bulletin->pdf_path);
            $validated += [
                'pdf_path' => $file->store('bulletins', 'local'),
                'pdf_original_name' => $file->getClientOriginalName(),
                'pdf_size' => $file->getSize(),
            ];
        }

        $bulletin->update($validated);
        if ($bulletin->isResearcherSubmission()) {
            $bulletin->reviewHistory()->create(['previous_status' => 'editorial:content', 'new_status' => 'editorial:content', 'comments' => 'Edición administrativa del contenido.', 'changed_by' => $request->user()->id]);
        }

        return back()->with('success', 'Boletín actualizado correctamente.');
    }

    public function destroy(Bulletin $bulletin): RedirectResponse
    {
        $bulletin->delete();

        return redirect()->route('admin.bulletins.index')->with('success', 'Boletín eliminado correctamente.');
    }

    public function publish(Bulletin $bulletin): RedirectResponse
    {
        abort_if($bulletin->isResearcherSubmission() && ! $bulletin->isApprovedForPublication(), 409, 'El aporte debe estar aprobado antes de publicarse.');
        $bulletin->publish();

        return back()->with('success', 'Boletín publicado correctamente.');
    }

    public function unpublish(Bulletin $bulletin): RedirectResponse
    {
        $bulletin->unpublish();

        return back()->with('success', 'Boletín despublicado correctamente.');
    }

    private function validateBulletin(Request $request, ?Bulletin $bulletin = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('title'))]);

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('bulletins')->ignore($bulletin)],
            'description' => ['nullable', 'string', 'max:2000'],
            'cover_image_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'pdf' => [$bulletin ? 'nullable' : 'required', 'file', 'mimes:pdf', 'max:20480'],
        ]);
    }

    private function formData(): array
    {
        return ['mediaImages' => MediaFile::where('file_type', 'image')->where('status', true)->latest()->get()];
    }
}
