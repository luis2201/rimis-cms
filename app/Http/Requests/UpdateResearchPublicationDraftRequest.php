<?php
namespace App\Http\Requests;
use App\Http\Requests\Concerns\ResearchPublicationRules;
use Illuminate\Foundation\Http\FormRequest;
class UpdateResearchPublicationDraftRequest extends FormRequest { use ResearchPublicationRules; public function authorize():bool{return $this->user()->can('update',$this->route('publication'));} public function rules():array{return $this->draftRules($this->route('publication'));} }
