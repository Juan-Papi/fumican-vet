<?php

namespace App\Http\Requests\Users;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $canUpdateUser = $this->user()->hasPermissionTo(PermissionEnum::EDITAR_USUARIOS->value);
        return $canUpdateUser;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Se obtiene el ID del usuario de la ruta para ignorarlo en la regla 'unique'
        $userId = $this->route('user')->id;

        return [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            // CORREGIDO: Se asegura que el email sea único, ignorando al usuario actual.
            'email' => 'required|email|unique:users,email,' . $userId,
            'role_id' => 'required|exists:roles,id',
            // AÑADIDO: Reglas para la contraseña opcional.
            'password' => ['sometimes', 'nullable', 'string', 'min:8', 'confirmed'],
        ];
    }
}
