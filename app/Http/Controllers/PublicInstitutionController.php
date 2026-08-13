<?php
namespace App\Http\Controllers;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\View\View;
class PublicInstitutionController extends Controller
{
    public function index(Request $r):View{$institutions=Subscription::where('type',Subscription::TYPE_INSTITUTIONAL)->where('status',Subscription::STATUS_APPROVED)->whereHas('user',fn($q)=>$q->where('is_active',true))->when($r->filled('search'),fn($q)=>$q->where('institution_name','like','%'.$r->search.'%'))->paginate(12)->withQueryString();return view('institutions.index',compact('institutions'));}
    public function show(string $slug):View{$institution=Subscription::where('type',Subscription::TYPE_INSTITUTIONAL)->where('status',Subscription::STATUS_APPROVED)->where('public_slug',$slug)->whereHas('user',fn($q)=>$q->where('is_active',true))->firstOrFail();return view('institutions.show',compact('institution'));}
}
