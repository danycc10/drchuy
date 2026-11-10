<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('service'));
    }

    public function rules(): array
    {
        return [
            'revision_id' => 'required|integer|min:1',
            'es' => 'required|array:name,description',
            'en' => 'required|array:name,description',
            'es.name' => 'required|string|max:200',
            'en.name' => 'required|string|max:200',
            'es.description' => 'required|string|max:3000',
            'en.description' => 'required|string|max:3000',
            'visible' => 'required|boolean',
            'position' => 'required|integer|min:1|max:999',
            'image_id' => 'nullable|integer|exists:media_assets,id',
        ];
    }

    public function messages(): array
    {
        return [
            '*.required' => 'Completa este campo antes de guardar.',
            '*.max' => 'El contenido excede el límite permitido.',
            'position.integer' => 'El orden debe ser un número entero.',
            'image_id.exists' => 'La imagen ya no está disponible. Selecciona otra de la biblioteca.',
        ];
    }
}
