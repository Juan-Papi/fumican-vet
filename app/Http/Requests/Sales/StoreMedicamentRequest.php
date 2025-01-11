<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicamentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'], // Nombre obligatorio
            'dosage' => ['required', 'string', 'max:100'],        // Dosificación obligatoria
            'manufacturer' => ['required', 'string', 'max:255'],  // Fabricante obligatorio
            'expiration_date' => ['required', 'date', 'after:today'], // Fecha futura
            'controlled_substance' => ['required', 'in:yes,no'],  // Debe ser "yes" o "no"
            'category_id' => ['required', 'exists:categories,id'], // Debe existir en la tabla de categorías
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del medicamento es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'name.max' => 'El nombre no debe exceder los 255 caracteres.',
            'dosage.required' => 'La dosificación es obligatoria.',
            'dosage.max' => 'La dosificación no debe exceder los 100 caracteres.',
            'manufacturer.required' => 'El fabricante es obligatorio.',
            'manufacturer.max' => 'El nombre del fabricante no debe exceder los 255 caracteres.',
            'expiration_date.required' => 'La fecha de expiración es obligatoria.',
            'expiration_date.date' => 'La fecha de expiración debe ser una fecha válida.',
            'expiration_date.after' => 'La fecha de expiración debe ser una fecha futura.',
            'controlled_substance.required' => 'Debes indicar si es una sustancia controlada.',
            'controlled_substance.in' => 'El valor para sustancia controlada debe ser "sí" o "no".',
            'category_id.required' => 'La categoría es obligatoria.',
            'category_id.exists' => 'La categoría seleccionada no es válida.',
        ];
    }
}
