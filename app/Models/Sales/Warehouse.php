<?php

namespace App\Models\Sales;

use App\Traits\SerializeDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;
    use SerializeDates;

    protected $table = 'warehouses';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'location',
        'description',
    ];

    public function purchaseNotes()
    {
        return $this->hasMany(PurchaseNote::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function saleNotes()
    {
        return $this->hasMany(SalesNote::class);
    }
}
