<?php

namespace App\Http\Requests\Services;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'ci' => ['required', 'string', 'max:15', 'unique:customers,ci'],
            'phone_number' => ['required', 'string', 'max:20'],
            'gender' => ['required', 'integer', 'in:0,1,2'],
            'birthdate' => ['required', 'date'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'El campo nombre es requerido.',
            'first_name.string' => 'El campo nombre debe ser una cadena de texto.',
            'first_name.max' => 'El campo nombre no debe exceder los 50 caracteres.',
            'last_name.required' => 'El campo apellido es requerido.',
            'last_name.string' => 'El campo apellido debe ser una cadena de texto.',
            'last_name.max' => 'El campo apellido no debe exceder los 50 caracteres.',
            'ci.required' => 'El campo CI es requerido.',
            'ci.string' => 'El campo CI debe ser una cadena de texto.',
            'ci.max' => 'El campo CI no debe exceder los 15 caracteres.',
            'phone_number.required' => 'El campo número de teléfono es requerido.',
            'phone_number.string' => 'El campo número de teléfono debe ser una cadena de texto.',
            'phone_number.max' => 'El campo número de teléfono no debe exceder los 20 caracteres.',
            'gender.required' => 'El campo género es requerido.',
            'gender.integer' => 'El campo género debe ser un número entero.',
            'gender.in' => 'El campo género debe ser uno de los siguientes valores: Masculino, Femenino, Otro.',
            'birthdate.required' => 'El campo fecha de nacimiento es requerido.',
            'birthdate.date' => 'El campo fecha de nacimiento debe ser una fecha válida.',
        ];
    }
}
