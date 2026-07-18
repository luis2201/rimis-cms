<?php
namespace App\Http\Requests;
use App\Http\Requests\Concerns\ResearchPublicationRules;
use Illuminate\Foundation\Http\FormRequest;
class AdminUpdateResearchPublicationRequest extends FormRequest { use ResearchPublicationRules; public function authorize():bool{return $this->user()->can('updateEditorial',$this->route('publication'));} public function rules():array{return $this->draftRules($this->route('publication'),true);} }
