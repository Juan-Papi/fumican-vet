<?php

namespace App\Models\Services;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specie extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function breeds(): HasMany
    {
        return $this->hasMany(Breed::class);
    }
}
