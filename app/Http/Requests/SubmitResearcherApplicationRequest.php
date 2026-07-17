<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitResearcherApplicationRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    protected function prepareForValidation(): void
    {
        $application = $this->user()?->researcherApplication;
        if ($application) {
            $this->merge($application->only(['motivation', 'experience_summary', 'expected_contribution']));
        }
    }
    public function rules(): array
    {
        return ['motivation' => ['required', 'string', 'max:5000'], 'experience_summary' => ['required', 'string', 'max:5000'], 'expected_contribution' => ['required', 'string', 'max:5000']];
    }
}
