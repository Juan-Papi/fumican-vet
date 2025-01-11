<?php

namespace App\Repositories\Sales;

use App\Models\Sales\PurchaseNote;

class PurchaseNoteRepository
{

    public function getAll()
    {
        return PurchaseNote::orderBy('updated_at', 'desc')
            ->paginate();
    }

    public function findById($id)
    {
        return PurchaseNote::with(['warehouse', 'supplier', 'user', 'purchaseNoteDetails.medicament'])->findOrFail($id);
    }

    public function create(array $purchaseNoteData)
    {
        try {
            $purchase = PurchaseNote::create($purchaseNoteData);
            return $purchase;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update($id, array $data)
    {
        return PurchaseNote::where('id', $id)->update($data);
    }


    public function deleteById($id)
    {
        return PurchaseNote::where('id', $id)->delete();
    }
}
