<?php

namespace App\Http\Requests\Services;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $canEditPets = $this->user()->can(PermissionEnum::EDITAR_MASCOTAS->value);
        return $canEditPets;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'age' => 'required|integer',
            'photo_url' => 'nullable|string|max:255',
            'breed_id' => 'required|exists:breeds,id',
            'customer_id' => 'required|exists:customers,id',
        ];
    }
}
