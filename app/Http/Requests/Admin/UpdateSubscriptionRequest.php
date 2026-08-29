<?php

namespace App\Http\Requests\Admin;

use App\Models\ResearcherProfile;
use App\Models\Subscription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('subscriptions.edit') && ! $this->user()->isMember();
    }

    protected function prepareForValidation(): void
    {
        $clean = fn ($value) => $value === null ? null : preg_replace('/[^0-9+]/', '', trim((string) $value));
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->email)),
            'requester_email' => $this->filled('requester_email') ? mb_strtolower(trim((string) $this->requester_email)) : null,
            'national_id' => $clean($this->national_id),
            'contact_phone' => $clean($this->contact_phone),
            'main_phone' => $clean($this->main_phone),
            'mobile_phone' => $clean($this->mobile_phone),
        ]);
    }

    public function rules(): array
    {
        /** @var Subscription $subscription */
        $subscription = $this->route('subscription');
        $ignore = fn (string $column) => Rule::unique('subscriptions', $column)->ignore($subscription->id);
        $email = ['required', 'email:rfc', 'max:255', $ignore('email'), $ignore('requester_email'), Rule::unique('users', 'email')->ignore($subscription->user_id)];

        if ($subscription->isProfessional()) {
            return [
                'first_names'=>['required','string','max:150'], 'last_names'=>['required','string','max:150'], 'email'=>$email,
                'national_id'=>['required','string','min:6','max:30',$ignore('national_id')],
                'orcid'=>['nullable','regex:/^\d{4}-\d{4}-\d{4}-[\dX]{4}$/'],
                'undergraduate_title'=>['required','string','max:255'], 'affiliated_institution'=>['required','string','max:255'],
                'postgraduate_titles'=>['nullable','string','max:3000'],
                'research_areas'=>['required','array','min:1'], 'research_areas.*'=>['required',Rule::in(ResearcherProfile::RESEARCH_AREAS)],
                'other_research_area'=>[Rule::requiredIf(in_array('Otra',(array)$this->research_areas,true)),'nullable','string','max:255'],
                'scientific_communities'=>['nullable','string','max:3000'], 'research_activity'=>['required','string','max:5000'],
                'teaching_functions'=>['required','string','max:5000'], 'current_research_functions'=>['required','string','max:5000'],
                'personal_photo'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
                'country'=>['required','string','max:100'], 'city'=>['required','string','max:150'],
                'contact_phone'=>['required','string','min:7','max:30',$ignore('contact_phone'),$ignore('main_phone'),$ignore('mobile_phone')],
            ];
        }

        return [
            'institution_name'=>['required','string','max:255'], 'principal_authority_name'=>['required','string','max:255'],
            'foundation_year'=>['required','integer','min:1800','max:'.now()->year],
            'institution_logo'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
            'institution_type'=>['required',Rule::in(Subscription::INSTITUTION_TYPES)],
            'other_institution_type'=>[Rule::requiredIf($this->institution_type==='Otra'),'nullable','string','max:150'],
            'country'=>['required','string','max:100'], 'city'=>['required','string','max:150'], 'email'=>$email,
            'requester_name'=>['required','string','max:255'], 'requester_position'=>['required','string','max:255'],
            'requester_email'=>['required','email:rfc','max:255','different:email',$ignore('email'),$ignore('requester_email'),Rule::unique('users','email')->ignore($subscription->user_id)],
            'main_phone'=>['nullable','string','min:7','max:30','different:mobile_phone',$ignore('contact_phone'),$ignore('main_phone'),$ignore('mobile_phone')],
            'mobile_phone'=>['required','string','min:7','max:30','different:main_phone',$ignore('contact_phone'),$ignore('main_phone'),$ignore('mobile_phone')],
        ];
    }
}
