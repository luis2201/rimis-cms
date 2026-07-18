<?php

namespace App\Http\Requests;

use App\Models\ResearchPublication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SubmitResearchPublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('submit', $this->route('publication'));
    }

    public function rules(): array
    {
        return [];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var ResearchPublication $publication */
            $publication = $this->route('publication')->load('authors');
            $keywords = collect($publication->keywords)->filter();
            $authorNames = $publication->authors->pluck('author_name')->map(fn ($name) => mb_strtolower(trim($name)));

            foreach (['title', 'abstract', 'publication_type', 'research_area', 'institution'] as $field) {
                if (blank($publication->{$field})) $validator->errors()->add($field, 'Este campo es obligatorio para enviar la publicación.');
            }

            if (mb_strlen((string) $publication->abstract) < 100) $validator->errors()->add('abstract', 'El resumen debe contener al menos 100 caracteres.');
            if (! array_key_exists((string) $publication->publication_type, ResearchPublication::TYPE_LABELS)) $validator->errors()->add('publication_type', 'El tipo de publicación no es válido.');
            if ($keywords->count() < 3 || $keywords->count() > 10) $validator->errors()->add('keywords', 'Registre entre 3 y 10 palabras clave.');
            if ($publication->authors->isEmpty()) $validator->errors()->add('authors', 'Registre al menos un autor.');
            if ($authorNames->duplicates()->isNotEmpty()) $validator->errors()->add('authors', 'No se permiten autores duplicados en la publicación.');
            if (! $publication->hasPdf() && ! $publication->hasExternalAccess()) $validator->errors()->add('access', 'Adjunte un PDF o registre un DOI o enlace externo.');
            if ($publication->hasPdf() && ! $publication->pdf_distribution_authorized) $validator->errors()->add('pdf_distribution_authorized', 'Debe declarar que tiene autorización para distribuir el PDF.');
        });
    }
}
