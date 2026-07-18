<?php
namespace App\Http\Controllers;
use App\Models\{Bulletin,CallForProposal,Event};
use Illuminate\Http\Request;
use Illuminate\View\View;
class DashboardController extends Controller
{
 public function __invoke(Request $request):View{$user=$request->user();if($user->can('dashboard.view'))return view('dashboard.admin');if($user->can('dashboard.researcher')){$submissionCounts=collect([Event::class,Bulletin::class,CallForProposal::class])->flatMap(fn($model)=>$model::where('user_id',$user->id)->where('origin',$model::ORIGIN_RESEARCHER)->pluck('review_status'))->countBy();return view('dashboard.researcher',compact('submissionCounts'));}if($user->can('dashboard.basic'))return view('dashboard.user');abort(403);}
}
