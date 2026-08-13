<?php
namespace App\Http\Controllers;
use App\Models\Subscription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\{Request,Response};
use Illuminate\Support\Str;
use Illuminate\View\View;
class MembershipController extends Controller
{
    public function show(Request $r):View{$subscription=$r->user()->subscription()->with('history.changedBy')->firstOrFail();abort_unless($subscription->isApproved(),403);return view('memberships.show',compact('subscription'));}
    public function certificate(Request $r):Response
    {
        $s=$r->user()->subscription()->firstOrFail();abort_unless($s->isApproved()&&$r->user()->isMember(),403);$date=$s->reviewed_at??$s->updated_at;$months=[1=>'enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];$institutional=$s->isInstitutional();
        return Pdf::loadView('applications.certificate',['article'=>$institutional?'la':'el','salutation'=>'','name'=>$s->displayName(),'role'=>$institutional?'Institución miembro':'Miembro profesional','memberNoun'=>$institutional?'institución':'profesional','registered'=>$institutional?'registrada':'registrado','interestedPhrase'=>$institutional?'de la institución interesada':'de la persona interesada','researchLine'=>$institutional?($s->institution_type==='Otra'?$s->other_institution_type:$s->institution_type):implode(', ',$s->research_areas??[]),'city'=>'Portoviejo','day'=>$date->day,'month'=>$months[$date->month],'year'=>$date->year])->setPaper('a4')->download('certificacion-rimis-'.Str::slug($s->displayName()).'.pdf');
    }
}
