<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Models\{ResearcherProfile,Subscription};
use App\Notifications\SubscriptionStatusNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
class SubscriptionController extends Controller
{
    public function index():View{return view('subscriptions.index');}
    public function create(string $type):View{abort_unless(in_array($type,[Subscription::TYPE_PROFESSIONAL,Subscription::TYPE_INSTITUTIONAL],true),404);return view('subscriptions.create',['type'=>$type,'areas'=>ResearcherProfile::RESEARCH_AREAS,'institutionTypes'=>Subscription::INSTITUTION_TYPES]);}
    public function store(StoreSubscriptionRequest $request,string $type):RedirectResponse
    {
        $subscription=Subscription::create($request->validated()+['type'=>$type,'status'=>Subscription::STATUS_SUBMITTED,'submitted_at'=>now()]);
        $subscription->history()->create(['new_status'=>Subscription::STATUS_SUBMITTED,'comments'=>'Suscripción enviada.']);
        try { Notification::route('mail',$subscription->email)->notify(new SubscriptionStatusNotification($subscription,'submitted')); }
        catch (\Throwable $e) { Log::warning('No se pudo confirmar la suscripción por correo.',['subscription_id'=>$subscription->id,'error'=>$e->getMessage()]); }
        return redirect()->route('subscriptions.sent');
    }
    public function sent():View{return view('subscriptions.sent');}
}
