<?php

namespace App\Http\Requests\Services;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalConsultationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $canCreateMedicalConsultation = $this->user()->can(PermissionEnum::CREAR_CONSULTAS_MEDICAS->value);
        return $canCreateMedicalConsultation;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => 'required|string|max:255',
            'dewormed_at' => 'nullable|date',
            'previous_ilnesses' => 'nullable|string|max:255',
            'previous_interventions' => 'nullable|string|max:255',
            'general_condition' => 'required|string|max:120',
            'weight' => 'required|numeric|min:0',
            'appetite' => 'required|string|max:120',
            'hydratation' => 'required|string|max:120',
            'mucosa' => 'required|string|max:120',
            'digestive_system' => 'nullable|string|max:120',
            'genitourinary_system' => 'nullable|string|max:120',
            'respiratory_system' => 'nullable|string|max:120',
            'temperature' => 'nullable|numeric',
            'heart_rate' => 'nullable|numeric',
            'respiratory_rate' => 'nullable|numeric',
            'clinical_observation' => 'nullable|string',
            'complementary_tests' => 'nullable|string|max:150',
            'pronostic' => 'nullable|string|max:150',
            'presumptive_diagnosis' => 'nullable|string|max:120',
            'confirmatory_diagnosis' => 'nullable|string|max:120',
            'treatment' => 'nullable|string',
            'consultation_fee' => 'nullable|numeric|min:0',
            'pet_id' => 'required|exists:pets,id',
            'veterinarian_id' => 'required|exists:users,id',
        ];
    }
}
