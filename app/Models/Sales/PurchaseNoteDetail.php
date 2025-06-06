<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseNoteDetail extends Model
{
    use HasFactory;

    protected $table = 'purchase_note_details';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'quantity',
        'purchase_price',
        'percentage',
        'subtotal',
        'purchase_note_id',
        'medicament_id',
    ];

    public function purchaseNote()
    {
        return $this->belongsTo(PurchaseNote::class);
    }

    public function medicament()
    {
        return $this->belongsTo(Medicament::class);
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class, 'purchase_note_detail_id');
    }
}
