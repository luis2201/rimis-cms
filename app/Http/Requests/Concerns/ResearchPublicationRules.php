<?php
namespace App\Http\Requests\Concerns;
use App\Models\ResearchPublication;
use Illuminate\Validation\Rule;
trait ResearchPublicationRules
{
 protected function draftRules(?ResearchPublication $publication=null,bool $admin=false):array{return [
  'title'=>['required','string','max:500'],'abstract'=>['nullable','string','max:15000'],'publication_type'=>['nullable',Rule::in(array_keys(ResearchPublication::TYPE_LABELS))],'publication_date'=>['nullable','date'],'research_area'=>['nullable','string','max:150'],'research_line'=>['nullable','string','max:255'],'institution'=>['nullable','string','max:255'],'journal_name'=>['nullable','string','max:255'],'publisher'=>['nullable','string','max:255'],'volume'=>['nullable','string','max:50'],'issue'=>['nullable','string','max:50'],'pages'=>['nullable','string','max:100'],'doi'=>['nullable','regex:/^10\.\d{4,9}\/\S+$/i','max:255'],'issn'=>['nullable','string','max:50'],'isbn'=>['nullable','string','max:50'],'external_url'=>['nullable','url','starts_with:http://,https://','max:2048'],'keywords_text'=>['nullable','string','max:1000'],'citation_text'=>['nullable','string','max:5000'],'license_type'=>['nullable',Rule::in(array_keys(ResearchPublication::LICENSE_LABELS))],'pdf'=>['nullable','file','mimes:pdf','mimetypes:application/pdf','max:20480'],'pdf_distribution_authorized'=>['nullable','boolean'],'authors'=>['nullable','array','max:30'],'authors.*.author_name'=>['required_with:authors','string','max:255'],'authors.*.institution'=>['nullable','string','max:255'],'authors.*.orcid'=>['nullable','regex:/^\d{4}-\d{4}-\d{4}-[\dX]{4}$/'],'authors.*.email'=>['nullable','email','max:255'],'authors.*.is_corresponding'=>['nullable','boolean'],
 ]+($admin?['cover_image_id'=>['nullable','exists:media_files,id'],'pdf_public'=>['nullable','boolean'],'seo_title'=>['nullable','string','max:255'],'seo_description'=>['nullable','string','max:500']]:[]);}
}
