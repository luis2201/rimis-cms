<?php
namespace App\Http\Controllers;
use App\Models\ResearchPublication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
class PublicResearchPublicationController extends Controller
{
 public function index(Request $r):View{$publications=ResearchPublication::publiclyVisible()->with(['authors','coverImage'])->search($r->input('search'))->byYear($r->input('year'))->byType($r->input('type'))->byResearchArea($r->input('research_area'))->byInstitution($r->input('institution'))->byResearchLine($r->input('research_line'))->when($r->filled('author'),fn($q)=>$q->whereHas('authors',fn($a)=>$a->where('author_name','like','%'.$r->string('author').'%')))->when($r->filled('keyword'),fn($q)=>$q->where('keywords','like','%'.mb_strtolower($r->string('keyword')).'%'))->when($r->input('order')==='oldest',fn($q)=>$q->oldest('published_at'))->when($r->input('order')==='title',fn($q)=>$q->orderBy('title'))->when($r->input('order')==='year',fn($q)=>$q->orderByDesc('year'))->when(!$r->filled('order'),fn($q)=>$q->latest('published_at'))->paginate(12)->withQueryString();return view('research-publications.index',compact('publications'));}
 public function show(string $slug):View{$publication=ResearchPublication::publiclyVisible()->with(['authors.user.subscription','coverImage','author.subscription'])->where('slug',$slug)->firstOrFail();$related=ResearchPublication::publiclyVisible()->whereKeyNot($publication->id)->where(fn($q)=>$q->where('research_area',$publication->research_area)->orWhere('research_line',$publication->research_line))->limit(4)->get();return view('research-publications.show',compact('publication','related'));}
 public function pdf(string $slug):StreamedResponse{$p=ResearchPublication::publiclyVisible()->where('slug',$slug)->firstOrFail();abort_unless($p->canExposePdfPublicly()&&Storage::disk('local')->exists($p->pdf_path),404);return Storage::disk('local')->download($p->pdf_path,$p->pdf_original_name);}
}
