<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePqrsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'categoria' => ['required', Rule::in(['peticion', 'queja', 'reclamo', 'sugerencia'])],
            'descripcion' => ['required', 'string', 'min:10'],
            'adjuntos' => ['nullable', 'array', 'max:5'],
            'adjuntos.*' => ['file', 'max:5120'],
        ];
    }
}
