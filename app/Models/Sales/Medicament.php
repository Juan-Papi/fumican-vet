<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicament extends Model
{
    use HasFactory;

    protected $table = 'medicaments';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'dosage',
        'manufacturer',
        'expiration_date',
        'controlled_substance',
        'category_id',
    ];

    public function purchaseNoteDetails()
    {
        return $this->hasMany(PurchaseNoteDetail::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function salesNoteDetails()
    {
        return $this->hasMany(SalesNoteDetail::class);
    }
}
