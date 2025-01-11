<?php

namespace App\Http\Requests\Users;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $canCreateUser = $this->user()->hasPermissionTo(PermissionEnum::CREAR_USUARIOS->value);
        return $canCreateUser;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required|exists:roles,id',
        ];
    }
}
