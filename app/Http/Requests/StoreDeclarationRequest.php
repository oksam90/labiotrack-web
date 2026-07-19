<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeclarationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'accès est déjà cadré par les middlewares auth + tenant ; tout
        // utilisateur authentifié rattaché à un établissement peut déclarer.
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
            'photo'                      => 'nullable|image|max:5120',
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
