<?php

namespace App\Models\Services;

use App\Models\User;
use App\Traits\SerializeDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalConsultation extends Model
{
    /** @use HasFactory<\Database\Factories\Services\MedicalConsultationFactory> */
    use HasFactory, SerializeDates;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reason',
        'dewormed_at',
        'previous_illnesses',
        'previous_interventions',
        'general_condition',
        'weight',
        'appetite',
        'hydratation',
        'mucosa',
        'digestive_system',
        'genitourinary_system',
        'respiratory_system',
        'temperature',
        'heart_rate',
        'respiratory_rate',
        'clinical_observation',
        'complementary_tests',
        'pronostic',
        'presumptive_diagnosis',
        'confirmatory_diagnosis',
        'consultation_fee',
        'pet_id',
        'veterinarian_id',
        'treatment',
    ];

    /**
     * Get the pet that owns the medical consultation.
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * Get the user that owns the medical consultation.
     */
    public function user(): BelongsTo
    {
        // CORREGIDO: Se especifica la clave foránea correcta 'veterinarian_id'.
        // Esto le dice a Laravel que use esta columna para la relación en lugar de la predeterminada 'user_id'.
        return $this->belongsTo(User::class, 'veterinarian_id');
    }

    /**
     * Get the treatments for the medical consultation.
     */
    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class);
    }
}
