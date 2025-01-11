<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PurchaseNote extends Model
{
    use HasFactory;

    protected $table = 'purchase_notes';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'warehouse_id',
        'supplier_id',
        'user_id',
        'purchase_date',
        'total_amount',
    ];

     public function warehouse()
     {
         return $this->belongsTo(Warehouse::class);
     }

     public function supplier()
     {
         return $this->belongsTo(Supplier::class);
     }

     public function user()
     {
         return $this->belongsTo(User::class);
     }

     public function purchaseNoteDetails()
    {
        return $this->hasMany(PurchaseNoteDetail::class);
    }
}
