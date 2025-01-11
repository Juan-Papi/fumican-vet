<?php

namespace App\Http\Requests\Services;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $canUpdateCustomers = $this->user()->can(PermissionEnum::EDITAR_CLIENTES->value);
        return $canUpdateCustomers;
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
            'ci' => ['required', 'string', 'max:15'],
            'phone_number' => ['required', 'string', 'max:20'],
            'gender' => ['required', 'integer', 'in:0,1,2'],
            'birthdate' => ['required', 'date'],
        ];
    }
}
