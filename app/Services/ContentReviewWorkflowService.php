<?php
namespace App\Services;
use App\Exceptions\InvalidApplicationTransition;
use App\Models\User;
use App\Notifications\ContentSubmissionStatusNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
class ContentReviewWorkflowService
{
    public function startReview(Model $m, User $actor): Model { return $this->reviewTransition($m,$actor,'submissions.review',[$m::REVIEW_SUBMITTED],$m::REVIEW_UNDER_REVIEW,['review_started_at'=>now(),'reviewed_by'=>$actor->id,'reviewed_at'=>null],'Revisión iniciada.','under_review'); }
    public function observe(Model $m, User $actor, string $notes): Model { return $this->reviewTransition($m,$actor,'submissions.observe',[$m::REVIEW_UNDER_REVIEW],$m::REVIEW_OBSERVED,['reviewed_at'=>now(),'reviewed_by'=>$actor->id,'review_notes'=>$notes,'status'=>$m::STATUS_DRAFT,'published_at'=>null],$notes,'observed'); }
    public function approve(Model $m, User $actor, ?string $notes=null): Model { return $this->reviewTransition($m,$actor,'submissions.approve',[$m::REVIEW_UNDER_REVIEW],$m::REVIEW_APPROVED,['reviewed_at'=>now(),'reviewed_by'=>$actor->id,'review_notes'=>$notes,'status'=>$m::STATUS_DRAFT,'published_at'=>null],$notes ?: 'Aporte aprobado.','approved'); }
    public function reject(Model $m, User $actor, string $notes): Model { return $this->reviewTransition($m,$actor,'submissions.reject',[$m::REVIEW_UNDER_REVIEW],$m::REVIEW_REJECTED,['reviewed_at'=>now(),'reviewed_by'=>$actor->id,'review_notes'=>$notes,'status'=>$m::STATUS_DRAFT,'published_at'=>null],$notes,'rejected'); }
    public function publish(Model $m, User $actor): Model { return $this->publicationTransition($m,$actor,$m::STATUS_DRAFT,$m::STATUS_PUBLISHED,['published_at'=>now()],'Aporte publicado.','published'); }
    public function unpublish(Model $m, User $actor): Model { return $this->publicationTransition($m,$actor,$m::STATUS_PUBLISHED,$m::STATUS_DRAFT,['published_at'=>null],'Aporte despublicado.','unpublished'); }
    private function reviewTransition(Model $m, User $actor, string $permission, array $from, string $to, array $changes, string $comments, string $event): Model
    {
        $this->guard($m,$actor,$permission);
        return $this->transition($m,$actor,function(Model $locked) use($from,$to,$changes,$comments,$actor){ if(!in_array($locked->review_status,$from,true)) throw new InvalidApplicationTransition('La transición editorial no está permitida desde el estado actual.'); $previous=$locked->review_status; $locked->update($changes+['review_status'=>$to]); $locked->reviewHistory()->create(['previous_status'=>$previous,'new_status'=>$to,'comments'=>$comments,'changed_by'=>$actor->id]); },$event,$changes['review_notes'] ?? null);
    }
    private function publicationTransition(Model $m, User $actor, string $from, string $to, array $changes, string $comments, string $event): Model
    {
        $this->guard($m,$actor,'submissions.publish');
        return $this->transition($m,$actor,function(Model $locked) use($from,$to,$changes,$comments,$actor){ if(!$locked->isApprovedForPublication() || $locked->status!==$from) throw new InvalidApplicationTransition('El aporte no puede cambiar de publicación desde el estado actual.'); $locked->update($changes+['status'=>$to]); $locked->reviewHistory()->create(['previous_status'=>'publication:'.$from,'new_status'=>'publication:'.$to,'comments'=>$comments,'changed_by'=>$actor->id]); },$event);
    }
    private function transition(Model $m, User $actor, callable $operation, string $event, ?string $notes=null): Model
    {
        $result=DB::transaction(function() use($m,$operation){ $locked=$m->newQuery()->with('author')->lockForUpdate()->findOrFail($m->getKey()); $operation($locked); return $locked->fresh(['author','reviewer','reviewHistory.changedBy']); });
        try { $result->author?->notify(new ContentSubmissionStatusNotification($result,$this->typeLabel($result),$event,$notes)); } catch(Throwable $e) { Log::warning('No se pudo notificar el cambio editorial.',['type'=>$result::class,'id'=>$result->id,'error'=>$e->getMessage()]); }
        return $result;
    }
    private function guard(Model $m, User $actor, string $permission): void { if(!$actor->can($permission) || $actor->hasRole('INVESTIGADOR') || !$m->isResearcherSubmission() || (int)$m->user_id===(int)$actor->id) abort(403); }
    private function typeLabel(Model $m): string { return match(class_basename($m)){ 'Event'=>'Evento','Bulletin'=>'Boletín','ResearchPublication'=>'Investigación',default=>'Convocatoria' }; }
}
