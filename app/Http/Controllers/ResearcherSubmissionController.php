<?php

namespace App\Http\Controllers;

use App\Models\Bulletin;
use App\Models\CallForProposal;
use App\Models\Event;
use App\Services\ContentSubmissionWorkflowService;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResearcherSubmissionController extends Controller
{
    public function __construct(private ContentSubmissionWorkflowService $workflow, private HtmlSanitizer $sanitizer) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->isMember(), 403);
        $items = collect([
            ...Event::where('user_id', $request->user()->id)->where('origin', Event::ORIGIN_RESEARCHER)->get()->map(fn ($m) => $this->item($m, 'event')),
            ...Bulletin::where('user_id', $request->user()->id)->where('origin', Bulletin::ORIGIN_RESEARCHER)->get()->map(fn ($m) => $this->item($m, 'bulletin')),
            ...CallForProposal::where('user_id', $request->user()->id)->where('origin', CallForProposal::ORIGIN_RESEARCHER)->get()->map(fn ($m) => $this->item($m, 'call')),
        ])->sortByDesc('updated_at');
        $counts = $items->countBy('review_status');
        if ($request->filled('type')) $items = $items->where('type', $request->string('type')->toString());
        if ($request->filled('review_status')) $items = $items->where('review_status', $request->string('review_status')->toString());
        return view('researcher.submissions.index', compact('items', 'counts'));
    }

    public function eventCreate(): View { return $this->form('event', new Event); }
    public function bulletinCreate(): View { return $this->form('bulletin', new Bulletin); }
    public function callCreate(): View { return $this->form('call', new CallForProposal); }
    public function eventStore(Request $r): RedirectResponse { return $this->store($r, 'event', new Event); }
    public function bulletinStore(Request $r): RedirectResponse { return $this->store($r, 'bulletin', new Bulletin); }
    public function callStore(Request $r): RedirectResponse { return $this->store($r, 'call', new CallForProposal); }
    public function eventEdit(Event $event): View { return $this->edit($event, 'event'); }
    public function bulletinEdit(Bulletin $bulletin): View { return $this->edit($bulletin, 'bulletin'); }
    public function callEdit(CallForProposal $call): View { return $this->edit($call, 'call'); }
    public function eventUpdate(Request $r, Event $event): RedirectResponse { return $this->update($r, $event, 'event'); }
    public function bulletinUpdate(Request $r, Bulletin $bulletin): RedirectResponse { return $this->update($r, $bulletin, 'bulletin'); }
    public function callUpdate(Request $r, CallForProposal $call): RedirectResponse { return $this->update($r, $call, 'call'); }
    public function eventDestroy(Request $r, Event $event): RedirectResponse { return $this->destroy($r, $event); }
    public function bulletinDestroy(Request $r, Bulletin $bulletin): RedirectResponse { return $this->destroy($r, $bulletin); }
    public function callDestroy(Request $r, CallForProposal $call): RedirectResponse { return $this->destroy($r, $call); }
    public function eventSubmit(Request $r, Event $event): RedirectResponse { return $this->submit($r, $event, 'event'); }
    public function bulletinSubmit(Request $r, Bulletin $bulletin): RedirectResponse { return $this->submit($r, $bulletin, 'bulletin'); }
    public function callSubmit(Request $r, CallForProposal $call): RedirectResponse { return $this->submit($r, $call, 'call'); }

    public function eventDownload(Request $r, Event $event): StreamedResponse { $r->user()->can('downloadSubmissionFile', $event) ?: abort(403); return $this->download($event->attachment_path, $event->attachment_original_name); }
    public function bulletinDownload(Request $r, Bulletin $bulletin): StreamedResponse { $r->user()->can('downloadSubmissionFile', $bulletin) ?: abort(403); return $this->download($bulletin->pdf_path, $bulletin->pdf_original_name); }
    public function callDownload(Request $r, CallForProposal $call): StreamedResponse { $r->user()->can('downloadSubmissionFile', $call) ?: abort(403); return $this->download($call->bases_pdf_path, $call->bases_pdf_original_name); }

    private function form(string $type, Model $model): View { return view('researcher.submissions.form', compact('type', 'model')); }
    private function edit(Model $model, string $type): View { $this->authorize('updateSubmission', $model); return $this->form($type, $model); }

    private function store(Request $request, string $type, Model $model): RedirectResponse
    {
        $this->authorize('createSubmission', $model::class);
        $data = $this->validateDraft($request, $type, $model);
        $data += ['user_id' => $request->user()->id, 'origin' => $model::ORIGIN_RESEARCHER, 'review_status' => $model::REVIEW_DRAFT, 'status' => $model::STATUS_DRAFT, 'published_at' => null, 'submitted_at' => null, 'review_started_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'review_notes' => null];
        $this->putFile($request, $type, $data);
        $created = $model::create($data);
        $created->reviewHistory()->create(['new_status' => $model::REVIEW_DRAFT, 'comments' => 'Borrador creado.', 'changed_by' => $request->user()->id]);
        return redirect()->route("researcher.submissions.$type.edit", $created)->with('success', 'Aporte guardado como borrador.');
    }

    private function update(Request $request, Model $model, string $type): RedirectResponse
    {
        $this->authorize('updateSubmission', $model);
        $data = $this->validateDraft($request, $type, $model);
        $this->putFile($request, $type, $data, $model);
        $model->update($data);
        return back()->with('success', 'Borrador actualizado.');
    }

    private function destroy(Request $request, Model $model): RedirectResponse
    {
        $this->authorize('deleteSubmission', $model); $model->delete();
        return redirect()->route('researcher.submissions.index')->with('success', 'Borrador eliminado.');
    }

    private function submit(Request $request, Model $model, string $type): RedirectResponse
    {
        $this->authorize('submitSubmission', $model);
        validator($model->toArray(), $this->submissionRules($type))->validate();
        $this->workflow->submit($model, $request->user()->id);
        return redirect()->route('researcher.submissions.index')->with('success', 'Aporte enviado a revisión.');
    }

    private function validateDraft(Request $request, string $type, Model $model): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('title')), 'registration_enabled' => $request->boolean('registration_enabled')]);
        $base = ['title' => ['required','string','max:255'], 'slug' => ['required','string','max:255',Rule::unique($model->getTable())->ignore($model->exists ? $model : null)], 'summary' => ['nullable','string','max:1000'], 'description' => ['required','string']];
        $rules = match ($type) {
            'event' => $base + ['starts_at'=>['required','date'],'ends_at'=>['required','date','after_or_equal:starts_at'],'modality'=>['required',Rule::in(['in_person','virtual','hybrid'])],'location'=>['nullable','string','max:255'],'organizer'=>['nullable','string','max:255'],'responsible_name'=>['nullable','string','max:255'],'contact_email'=>['nullable','email','max:255'],'contact_phone'=>['nullable','string','max:50'],'website_url'=>['nullable','url','max:2048'],'attachment'=>['nullable','file','mimes:pdf,jpg,jpeg,png,webp','max:10240']],
            'bulletin' => ['title'=>$base['title'],'slug'=>$base['slug'],'description'=>['nullable','string','max:2000'],'pdf'=>[$model->exists?'nullable':'required','file','mimes:pdf','max:10240']],
            default => $base + ['opens_at'=>['required','date'],'closes_at'=>['required','date','after_or_equal:opens_at'],'bases_pdf'=>[$model->exists?'nullable':'required','file','mimes:pdf','max:10240'],'registration_enabled'=>['required','boolean'],'registration_url'=>['nullable','url','max:2048']],
        };
        $data = $request->validate($rules);
        if (isset($data['description'])) $data['description'] = $this->sanitizer->clean($data['description']);
        unset($data['attachment'], $data['pdf'], $data['bases_pdf']);
        return $data;
    }

    private function submissionRules(string $type): array
    {
        return match ($type) {
            'event' => ['title'=>'required','slug'=>'required','description'=>'required','starts_at'=>'required|date','ends_at'=>'required|date','modality'=>'required','attachment_path'=>'nullable'],
            'bulletin' => ['title'=>'required','slug'=>'required','pdf_path'=>'required'],
            default => ['title'=>'required','slug'=>'required','description'=>'required','opens_at'=>'required|date','closes_at'=>'required|date','bases_pdf_path'=>'required'],
        };
    }

    private function putFile(Request $request, string $type, array &$data, ?Model $model = null): void
    {
        $input = ['event'=>'attachment','bulletin'=>'pdf','call'=>'bases_pdf'][$type]; $file = $request->file($input); if (! $file) return;
        $pathField = ['event'=>'attachment_path','bulletin'=>'pdf_path','call'=>'bases_pdf_path'][$type];
        if ($model?->{$pathField}) Storage::disk('local')->delete($model->{$pathField});
        $data[$pathField] = $file->store("researcher-submissions/$type", 'local');
        if ($type === 'event') { $data['attachment_original_name']=$file->getClientOriginalName(); $data['attachment_mime_type']=$file->getMimeType(); $data['attachment_size']=$file->getSize(); }
        elseif ($type === 'bulletin') { $data['pdf_original_name']=$file->getClientOriginalName(); $data['pdf_size']=$file->getSize(); }
        else { $data['bases_pdf_original_name']=$file->getClientOriginalName(); $data['bases_pdf_size']=$file->getSize(); }
    }
    private function download(?string $path, ?string $name): StreamedResponse { abort_unless($path && Storage::disk('local')->exists($path), 404); return Storage::disk('local')->download($path, $name); }
    private function item(Model $m, string $type): array { return ['id'=>$m->id,'title'=>$m->title ?: 'Sin título','type'=>$type,'type_label'=>['event'=>'Evento','bulletin'=>'Boletín','call'=>'Convocatoria'][$type],'review_status'=>$m->review_status,'status_label'=>$m->reviewStatusLabel(),'publication_label'=>$m->isPublished()?'Publicado':($m->isApprovedForPublication()?'Aprobado — temporalmente no publicado':'Borrador'),'published_at'=>$m->published_at,'public_url'=>$m->isPublished()?route($type==='event'?'events.show':($type==='bulletin'?'bulletins.show':'calls.show'),$m->slug):null,'updated_at'=>$m->updated_at,'editable'=>$m->isEditableByResearcher()]; }
}
