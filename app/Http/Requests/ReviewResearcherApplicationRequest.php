<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewResearcherApplicationRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['review_notes' => ['required', 'string', 'max:5000']]; }
}
