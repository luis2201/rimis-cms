<?php

namespace App\Http\Requests;

use App\Models\ResearcherProfile;
use App\Models\Subscription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $clean = fn ($value) => $value === null ? null : preg_replace('/[^0-9+]/', '', trim((string) $value));
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->email)),
            'national_id' => $clean($this->national_id), 'ruc' => $clean($this->ruc),
            'contact_phone' => $clean($this->contact_phone), 'main_phone' => $clean($this->main_phone),
            'mobile_phone' => $clean($this->mobile_phone),
        ]);
    }

    public function rules(): array
    {
        $type = $this->route('type');
        abort_unless(in_array($type, [Subscription::TYPE_PROFESSIONAL, Subscription::TYPE_INSTITUTIONAL], true), 404);
        $email = ['required','email:rfc','max:255','unique:subscriptions,email','unique:users,email'];
        if ($type === Subscription::TYPE_PROFESSIONAL) {
            return [
                'first_names'=>['required','string','max:150'],'last_names'=>['required','string','max:150'],'email'=>$email,
                'national_id'=>['required','string','min:6','max:30','unique:subscriptions,national_id'],
                'orcid'=>['nullable','regex:/^\d{4}-\d{4}-\d{4}-[\dX]{4}$/'],
                'undergraduate_title'=>['required','string','max:255'],'postgraduate_titles'=>['nullable','string','max:3000'],
                'research_areas'=>['required','array','min:1'],'research_areas.*'=>['required',Rule::in(ResearcherProfile::RESEARCH_AREAS)],
                'other_research_area'=>[Rule::requiredIf(in_array('Otra',(array)$this->research_areas,true)),'nullable','string','max:255'],
                'scientific_communities'=>['nullable','string','max:3000'],'research_activity'=>['required','string','max:5000'],
                'country'=>['required','string','max:100'],'city'=>['required','string','max:150'],
                'contact_phone'=>['required','string','min:7','max:30','unique:subscriptions,contact_phone','unique:subscriptions,main_phone','unique:subscriptions,mobile_phone'],
            ];
        }
        return [
            'institution_name'=>['required','string','max:255'],'ruc'=>['required','string','min:8','max:30','unique:subscriptions,ruc'],
            'rector_name'=>['required','string','max:255'],'institution_type'=>['required',Rule::in(Subscription::INSTITUTION_TYPES)],
            'other_institution_type'=>[Rule::requiredIf($this->institution_type==='Otra'),'nullable','string','max:150'],
            'country'=>['required','string','max:100'],'city'=>['required','string','max:150'],'email'=>$email,
            'main_phone'=>['nullable','string','min:7','max:30','different:mobile_phone','unique:subscriptions,contact_phone','unique:subscriptions,main_phone','unique:subscriptions,mobile_phone'],
            'mobile_phone'=>['required','string','min:7','max:30','different:main_phone','unique:subscriptions,contact_phone','unique:subscriptions,main_phone','unique:subscriptions,mobile_phone'],
        ];
    }
}
