<?php

namespace App\Models\Services;

use App\Traits\SerializeDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pet extends Model
{
    use HasFactory, SerializeDates;

    protected $fillable = [
        'name',
        'weight',
        'color',
        'age',
        'photo_url',
        'breed_id',
        'customer_id',
    ];

    public function breed(): BelongsTo
    {
        return $this->belongsTo(Breed::class);
    }

    // public function specie()
    // {
    //     return $this->breed->specie;
    // }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function medicalConsultations(): HasMany
    {
        return $this->hasMany(MedicalConsultation::class);
    }
}
