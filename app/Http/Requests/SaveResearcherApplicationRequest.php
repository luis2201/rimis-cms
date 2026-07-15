<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveResearcherApplicationRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return ['motivation' => ['nullable', 'string', 'max:5000'], 'experience_summary' => ['nullable', 'string', 'max:5000'], 'expected_contribution' => ['nullable', 'string', 'max:5000']];
    }
}
