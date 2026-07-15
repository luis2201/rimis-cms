<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveResearcherApplicationRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['review_notes' => ['nullable', 'string', 'max:5000']]; }
}
