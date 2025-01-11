<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => 'required|exists:warehouses,id',
            'customer_id' => 'required|exists:customers,id',
            'total_amount' => 'required|numeric',
            'medicaments' => 'required|array',
            'medicaments.*.id' => 'required|exists:medicaments,id',
            'medicaments.*.quantity' => 'required|integer|min:1',
            'medicaments.*.sale_price' => 'required|numeric|min:0',
            'medicaments.*.subtotal' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'warehouse_id.required' => 'The warehouse field is required.',
            'warehouse_id.exists' => 'The selected warehouse is invalid.',
            'customer_id.required' => 'The customer field is required.',
            'customer_id.exists' => 'The selected customer is invalid.',
            'total_amount.required' => 'The total amount field is required.',
            'total_amount.numeric' => 'The total amount must be a number.',
            'medicaments.required' => 'The medicaments field is required.',
            'medicaments.array' => 'The medicaments must be an array.',
            'medicaments.*.id.required' => 'The medicament id field is required.',
            'medicaments.*.id.exists' => 'The selected medicament is invalid.',
            'medicaments.*.quantity.required' => 'The quantity field is required.',
            'medicaments.*.quantity.integer' => 'The quantity must be an integer.',
            'medicaments.*.quantity.min' => 'The quantity must be at least 1.',
            'medicaments.*.sale_price.required' => 'The sale price field is required.',
            'medicaments.*.sale_price.numeric' => 'The sale price must be a number.',
            'medicaments.*.sale_price.min' => 'The sale price must be at least 0.',
            'medicaments.*.subtotal.required' => 'The subtotal field is required.',
            'medicaments.*.subtotal.numeric' => 'The subtotal must be a number.',
            'medicaments.*.subtotal.min' => 'The subtotal must be at least 0.',
        ];
    }
}
