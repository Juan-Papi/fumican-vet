<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesNoteDetail extends Model
{
    use HasFactory;

    protected $table = 'sales_note_details';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'quantity',
        'sale_price',
        'subtotal',
        'sales_note_id',
        'medicament_id',
    ];

    public function salesNote()
    {
        return $this->belongsTo(SalesNote::class);
    }

    public function medicament()
    {
        return $this->belongsTo(Medicament::class);
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class, 'sales_note_detail_id');
    }
}
