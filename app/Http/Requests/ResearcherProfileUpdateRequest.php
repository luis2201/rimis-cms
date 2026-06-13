<?php

namespace App\Http\Requests;

use App\Models\ResearcherProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResearcherProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('INVESTIGADOR');
    }

    public function rules(): array
    {
        return [
            'country' => ['required', Rule::in(ResearcherProfile::COUNTRIES)],
            'salutation' => ['required', Rule::in(ResearcherProfile::SALUTATIONS)],
            'academic_title' => ['required', 'string', 'max:150'],
            'profession' => ['required', 'string', 'max:150'],
            'research_area' => ['required', Rule::in(ResearcherProfile::RESEARCH_AREAS)],
            'institution' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'cv' => [
                $this->user()->researcherProfile?->cv_path ? 'nullable' : 'required',
                'file',
                'mimes:pdf',
                'max:5120',
            ],
        ];
    }
}
