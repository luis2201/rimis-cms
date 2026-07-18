<?php
namespace App\Http\Requests;
use App\Http\Requests\Concerns\ResearchPublicationRules;
use Illuminate\Foundation\Http\FormRequest;
class StoreResearchPublicationDraftRequest extends FormRequest { use ResearchPublicationRules; public function authorize():bool{return $this->user()->can('create',\App\Models\ResearchPublication::class);} public function rules():array{return $this->draftRules();} }
