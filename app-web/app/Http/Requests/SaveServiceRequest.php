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
            'es' => 'required|array:name,description,preparation,duration,bring',
            'en' => 'required|array:name,description,preparation,duration,bring',
            'es.name' => 'required|string|max:200',
            'en.name' => 'required|string|max:200',
            'es.description' => 'required|string|max:3000',
            'en.description' => 'required|string|max:3000',
            'es.preparation' => 'nullable|required_with:en.preparation|string|max:1500',
            'en.preparation' => 'nullable|required_with:es.preparation|string|max:1500',
            'es.duration' => 'nullable|required_with:en.duration|string|max:200',
            'en.duration' => 'nullable|required_with:es.duration|string|max:200',
            'es.bring' => 'nullable|required_with:en.bring|string|max:1000',
            'en.bring' => 'nullable|required_with:es.bring|string|max:1000',
            'visible' => 'required|boolean',
            'position' => 'required|integer|min:1|max:999',
            'image_id' => 'nullable|integer|exists:media_assets,id',
            'image_position' => 'sometimes|array:x,y',
            'image_position.x' => 'required_with:image_position|integer|between:0,100',
            'image_position.y' => 'required_with:image_position|integer|between:0,100',
        ];
    }

    public function messages(): array
    {
        return [
            '*.required' => 'Completa este campo antes de guardar.',
            '*.required_with' => 'Completa este dato en ambos idiomas o déjalo vacío en los dos.',
            '*.max' => 'El contenido excede el límite permitido.',
            'position.integer' => 'El orden debe ser un número entero.',
            'image_id.exists' => 'La imagen ya no está disponible. Selecciona otra de la biblioteca.',
        ];
    }
}
