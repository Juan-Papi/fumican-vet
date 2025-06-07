<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'inventory_id',
        'sales_note_detail_id',
        'quantity',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function salesNoteDetail()
    {
        return $this->belongsTo(SalesNoteDetail::class);
    }
}
