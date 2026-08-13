<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\SubscriptionWorkflowService;
use Illuminate\Http\{RedirectResponse,Request};
use Illuminate\View\View;
class SubscriptionController extends Controller
{
    public function __construct(private SubscriptionWorkflowService $workflow){}
    public function index(Request $r):View{$this->authorizeAccess($r,'subscriptions.view');$subscriptions=Subscription::with('reviewer')->when($r->filled('status'),fn($q)=>$q->where('status',$r->status))->when($r->filled('type'),fn($q)=>$q->where('type',$r->type))->when($r->filled('search'),fn($q)=>$q->where(fn($x)=>$x->where('email','like','%'.$r->search.'%')->orWhere('first_names','like','%'.$r->search.'%')->orWhere('last_names','like','%'.$r->search.'%')->orWhere('institution_name','like','%'.$r->search.'%')))->orderByRaw("CASE status WHEN 'submitted' THEN 1 WHEN 'under_review' THEN 2 ELSE 3 END")->latest()->paginate(15)->withQueryString();return view('admin.subscriptions.index',compact('subscriptions'));}
    public function show(Request $r,Subscription $subscription):View{$this->authorizeAccess($r,'subscriptions.view');$subscription->load(['reviewer','history.changedBy','user']);return view('admin.subscriptions.show',compact('subscription'));}
    public function startReview(Request $r,Subscription $subscription):RedirectResponse{$this->authorizeAccess($r,'subscriptions.review');$this->workflow->startReview($subscription,$r->user());return back()->with('success','Revisión iniciada.');}
    public function approve(Request $r,Subscription $subscription):RedirectResponse{$this->authorizeAccess($r,'subscriptions.approve');$data=$r->validate(['review_notes'=>['nullable','string','max:5000']]);$this->workflow->approve($subscription,$r->user(),$data['review_notes']??null);return back()->with('success','Suscripción aprobada y credenciales enviadas.');}
    public function reject(Request $r,Subscription $subscription):RedirectResponse{$this->authorizeAccess($r,'subscriptions.reject');$data=$r->validate(['review_notes'=>['required','string','max:5000']]);$this->workflow->reject($subscription,$r->user(),$data['review_notes']);return back()->with('success','Suscripción rechazada.');}
    private function authorizeAccess(Request $r,string $permission):void{abort_unless($r->user()->can($permission)&&!$r->user()->isMember(),403);}
}
