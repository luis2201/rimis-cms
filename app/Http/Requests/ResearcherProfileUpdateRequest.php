<?php

namespace App\Http\Requests;

use App\Models\ResearcherProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResearcherProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['USUARIO', 'INVESTIGADOR']);
    }

    public function rules(): array
    {
        return [
            'country' => ['required', Rule::in(ResearcherProfile::COUNTRIES)],
            'salutation' => ['required', Rule::in(ResearcherProfile::SALUTATIONS)],
            'academic_title' => ['required', 'string', 'max:150'],
            'profession' => ['required', 'string', 'max:150'],
            'research_area' => ['required', Rule::in(ResearcherProfile::RESEARCH_AREAS)],
            'research_line' => ['nullable', 'string', 'max:255'],
            'institution' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'cv' => [
                $this->user()->researcherProfile?->cv_path ? 'nullable' : 'required',
                'file',
                'mimes:pdf',
                'max:5120',
            ],
            'public_bio' => ['nullable', 'string', 'max:3000'],
            'orcid' => ['nullable', 'regex:/^\d{4}-\d{4}-\d{4}-[\dX]{4}$/'],
            'google_scholar_url' => ['nullable', 'url', 'starts_with:http://,https://', 'max:2048'],
            'researchgate_url' => ['nullable', 'url', 'starts_with:http://,https://', 'max:2048'],
            'linkedin_url' => ['nullable', 'url', 'starts_with:http://,https://', 'max:2048'],
            'personal_website_url' => ['nullable', 'url', 'starts_with:http://,https://', 'max:2048'],
            'profile_photo_id' => ['nullable', Rule::exists('media_files', 'id')->where('file_type', 'image')],
            'public_email' => ['nullable', 'boolean'],
            'public_phone' => ['nullable', 'boolean'],
            'public_institution' => ['nullable', 'boolean'],
            'public_country' => ['nullable', 'boolean'],
            'public_research_area' => ['nullable', 'boolean'],
            'public_research_line' => ['nullable', 'boolean'],
            'public_cv' => ['nullable', 'boolean'],
            'publications_section_enabled' => ['nullable', 'boolean'],
            'contributions_section_enabled' => ['nullable', 'boolean'],
        ];
    }
}
