<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\MediaFile;
use App\Support\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventController extends Controller
{
    public function __construct(private HtmlSanitizer $sanitizer)
    {
    }

    public function publicIndex(): View
    {
        $events = Event::published()
            ->with('featuredImage')
            ->orderByRaw('starts_at < ? asc', [now()])
            ->orderBy('starts_at')
            ->paginate(12);

        return view('events.index', compact('events'));
    }

    public function publicShow(string $slug): View
    {
        $event = Event::published()->with(['featuredImage', 'author'])->where('slug', $slug)->firstOrFail();

        return view('events.show', compact('event'));
    }

    public function downloadAttachment(Event $event): StreamedResponse
    {
        abort_unless($event->isPublished() && ($event->isStaffContent() || $event->isApprovedForPublication()) && $event->attachment_path && Storage::disk('local')->exists($event->attachment_path), 404);
        return Storage::disk('local')->download($event->attachment_path, $event->attachment_original_name);
    }

    public function index(Request $request): View
    {
        $events = Event::with('author')
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('origin'), fn ($query) => $query->where('origin', $request->string('origin')))
            ->when($request->filled('review_status'), fn ($query) => $query->where('review_status', $request->string('review_status')))
            ->orderByDesc('starts_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.events.index', compact('events'));
    }

    public function create(): View
    {
        return view('admin.events.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateEvent($request);
        $validated += [
            'user_id' => $request->user()->id,
            'status' => Event::STATUS_DRAFT,
            'published_at' => null,
            'origin' => Event::ORIGIN_STAFF,
            'review_status' => Event::REVIEW_NOT_REQUIRED,
        ];
        $event = Event::create($validated);

        return redirect()->route('admin.events.edit', $event)->with('success', 'Evento creado como borrador.');
    }

    public function edit(Event $event): View
    {
        return view('admin.events.edit', array_merge($this->formData(), compact('event')));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $event->update($this->validateEvent($request, $event));
        if ($event->isResearcherSubmission()) {
            $event->reviewHistory()->create(['previous_status' => 'editorial:content', 'new_status' => 'editorial:content', 'comments' => 'Edición administrativa del contenido.', 'changed_by' => $request->user()->id]);
        }

        return back()->with('success', 'Evento actualizado correctamente.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Evento eliminado correctamente.');
    }

    public function publish(Event $event): RedirectResponse
    {
        abort_if($event->isResearcherSubmission() && ! $event->isApprovedForPublication(), 409, 'El aporte debe estar aprobado antes de publicarse.');
        $event->publish();

        return back()->with('success', 'Evento publicado correctamente.');
    }

    public function unpublish(Event $event): RedirectResponse
    {
        $event->unpublish();

        return back()->with('success', 'Evento despublicado correctamente.');
    }

    private function validateEvent(Request $request, ?Event $event = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('title'))]);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('events')->ignore($event)],
            'summary' => ['nullable', 'string', 'max:1000'],
            'description' => ['required', 'string'],
            'featured_image_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'modality' => ['required', Rule::in([Event::MODALITY_IN_PERSON, Event::MODALITY_VIRTUAL, Event::MODALITY_HYBRID])],
            'location' => ['nullable', 'string', 'max:255'],
            'organizer' => ['nullable', 'string', 'max:255'],
            'responsible_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'website_url' => ['nullable', 'url', 'max:2048'],
        ]);
        $validated['description'] = $this->sanitizer->clean($validated['description']);

        return $validated;
    }

    private function formData(): array
    {
        return ['mediaImages' => MediaFile::where('file_type', 'image')->where('status', true)->latest()->get()];
    }
}
