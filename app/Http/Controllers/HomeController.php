<?php
namespace App\Http\Controllers;
use App\Models\{Bulletin,CallForProposal,Event,News};
use App\Support\SeoService;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
class HomeController extends Controller
{
 public function __invoke(SeoService $seo):View{return view('welcome',['seo'=>$seo->global(),'recentNews'=>Schema::hasTable('news')?News::published()->with(['category','featuredImage'])->latest('published_at')->limit(6)->get():collect(),'upcomingEvents'=>Schema::hasTable('events')?Event::published()->with('featuredImage')->where('ends_at','>=',now())->orderBy('starts_at')->limit(3)->get():collect(),'openCalls'=>Schema::hasTable('calls')?CallForProposal::published()->with('featuredImage')->where('opens_at','<=',now())->where('closes_at','>=',now())->orderBy('closes_at')->limit(3)->get():collect(),'recentBulletins'=>Schema::hasTable('bulletins')?Bulletin::published()->with('coverImage')->latest('published_at')->limit(3)->get():collect()]);}
}
