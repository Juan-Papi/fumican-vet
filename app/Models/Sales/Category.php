<?php

namespace App\Models\Sales;

use App\Traits\SerializeDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    use SerializeDates;

    protected $table = 'categories';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'name',
    ];

    public function medicaments()
    {
        return $this->hasMany(Medicament::class);
    }
}
