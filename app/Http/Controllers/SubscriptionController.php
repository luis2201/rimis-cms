<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Models\{ResearcherProfile,Subscription};
use App\Notifications\SubscriptionStatusNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
class SubscriptionController extends Controller
{
    public function index():View{return view('subscriptions.index');}
    public function create(string $type):View{abort_unless(in_array($type,[Subscription::TYPE_PROFESSIONAL,Subscription::TYPE_INSTITUTIONAL],true),404);return view('subscriptions.create',['type'=>$type,'areas'=>ResearcherProfile::RESEARCH_AREAS,'institutionTypes'=>Subscription::INSTITUTION_TYPES]);}
    public function store(StoreSubscriptionRequest $request,string $type):RedirectResponse
    {
        $data=$request->safe()->except(['personal_photo','institution_logo']);
        $storedPaths=[];
        try {
            if($request->hasFile('personal_photo')) $data['personal_photo_path']=$storedPaths[]=$request->file('personal_photo')->store('subscriptions/professional-photos','public');
            if($request->hasFile('institution_logo')) $data['institution_logo_path']=$storedPaths[]=$request->file('institution_logo')->store('subscriptions/institution-logos','public');
            $subscription=Subscription::create($data+['type'=>$type,'status'=>Subscription::STATUS_SUBMITTED,'submitted_at'=>now()]);
        } catch (\Throwable $e) {
            foreach($storedPaths as $path) Storage::disk('public')->delete($path);
            throw $e;
        }
        $subscription->history()->create(['new_status'=>Subscription::STATUS_SUBMITTED,'comments'=>'Suscripción enviada.']);
        try { Notification::route('mail',$subscription->email)->notify(new SubscriptionStatusNotification($subscription,'submitted')); }
        catch (\Throwable $e) { Log::warning('No se pudo confirmar la suscripción por correo.',['subscription_id'=>$subscription->id,'error'=>$e->getMessage()]); }
        return redirect()->route('subscriptions.sent');
    }
    public function sent():View{return view('subscriptions.sent');}
}
