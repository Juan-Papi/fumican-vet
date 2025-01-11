<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventories';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'stock',
        'price',
        'warehouse_id',
        'medicament_id',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function medicament()
    {
        return $this->belongsTo(Medicament::class);
    }
}
