<?php

namespace App\Models\Services;

use App\Traits\SerializeDates;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Sales\SalesNote;

class Customer extends Model
{
    use HasFactory, SerializeDates;

    protected $fillable = [
        'first_name',
        'last_name',
        'ci',
        'phone_number',
        'gender',
        'birthdate',
    ];

    public function getFullNameAttribute(): string {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d/m/Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d/m/Y H:i:s');
    }

    public function pets(): HasMany {
        return $this->hasMany(Pet::class);
    }

    public function saleNotes()
    {
        return $this->hasMany(SalesNote::class);
    }
}
