<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeclarationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation fine (update policy + statut en_stock) est vérifiée
        // dans le contrôleur ($this->authorize('update', $declaration)).
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'lignes'                     => 'required|array|min:1',
            'lignes.*.service_id'        => 'required|integer|exists:services,id',
            'lignes.*.type_contenant_id' => 'required|integer|exists:type_contenants,id',
            'lignes.*.nombre_contenants' => 'required|integer|min:1|max:999',
            'notes'                      => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'lignes.required' => __('declarations.error_min_one_line'),
            'lignes.min'      => __('declarations.error_min_one_line'),
        ];
    }
}
