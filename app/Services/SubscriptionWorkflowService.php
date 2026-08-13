<?php
namespace App\Services;
use App\Models\{Subscription,User};
use App\Notifications\SubscriptionStatusNotification;
use Illuminate\Support\Facades\{DB,Hash,Log,Notification};
use Illuminate\Support\Str;
class SubscriptionWorkflowService
{
    public function startReview(Subscription $s,User $actor):Subscription{return $this->transition($s,$actor,[Subscription::STATUS_SUBMITTED],Subscription::STATUS_UNDER_REVIEW,'Suscripción en revisión.','under_review');}
    public function reject(Subscription $s,User $actor,string $notes):Subscription{return $this->transition($s,$actor,[Subscription::STATUS_UNDER_REVIEW],Subscription::STATUS_REJECTED,$notes,'rejected');}
    public function approve(Subscription $s,User $actor,?string $notes=null):Subscription
    {
        $password=Str::random(10).'9!';
        $result=DB::transaction(function()use($s,$actor,$notes,$password){
            $locked=Subscription::lockForUpdate()->findOrFail($s->id);
            abort_unless($locked->status===Subscription::STATUS_UNDER_REVIEW,409,'La suscripción no está en revisión.');
            abort_if(User::where('email',$locked->email)->exists(),409,'Ya existe una cuenta con este correo.');
            $user=User::create(['name'=>$locked->displayName(),'email'=>$locked->email,'password'=>Hash::make($password),'must_change_password'=>true,'is_active'=>true]);
            $user->markEmailAsVerified();
            $user->assignRole($locked->isProfessional()?'INVESTIGADOR':'INSTITUCIONAL');
            $previous=$locked->status;
            $locked->update(['status'=>Subscription::STATUS_APPROVED,'reviewed_at'=>now(),'reviewed_by'=>$actor->id,'review_notes'=>$notes,'user_id'=>$user->id,'public_slug'=>$this->uniqueSlug($locked->displayName())]);
            $locked->history()->create(['previous_status'=>$previous,'new_status'=>Subscription::STATUS_APPROVED,'comments'=>$notes?:'Suscripción aprobada.','changed_by'=>$actor->id]);
            return $locked->fresh(['user']);
        });
        $this->notify($result,'approved',$password);
        return $result;
    }
    private function transition(Subscription $s,User $actor,array $from,string $to,string $notes,string $event):Subscription
    {
        $result=DB::transaction(function()use($s,$actor,$from,$to,$notes){$locked=Subscription::lockForUpdate()->findOrFail($s->id);abort_unless(in_array($locked->status,$from,true),409,'Transición no permitida.');$previous=$locked->status;$locked->update(['status'=>$to,'review_started_at'=>$to===Subscription::STATUS_UNDER_REVIEW?now():$locked->review_started_at,'reviewed_at'=>$to===Subscription::STATUS_REJECTED?now():null,'reviewed_by'=>$actor->id,'review_notes'=>$notes]);$locked->history()->create(['previous_status'=>$previous,'new_status'=>$to,'comments'=>$notes,'changed_by'=>$actor->id]);return $locked->fresh();});
        $this->notify($result,$event);return $result;
    }
    private function uniqueSlug(string $name):string{$base=Str::slug($name)?:'miembro-rimis';$slug=$base;$i=2;while(Subscription::where('public_slug',$slug)->exists())$slug=$base.'-'.$i++;return $slug;}
    private function notify(Subscription $s,string $event,?string $password=null):void{try{Notification::route('mail',$s->email)->notify(new SubscriptionStatusNotification($s,$event,$password));}catch(\Throwable $e){Log::warning('No se pudo notificar la suscripción.',['subscription_id'=>$s->id,'event'=>$event,'error'=>$e->getMessage()]);}}
}
