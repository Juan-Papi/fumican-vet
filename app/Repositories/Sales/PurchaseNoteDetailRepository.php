<?php

namespace App\Repositories\Sales;

use App\Models\Sales\PurchaseNoteDetail;

class PurchaseNoteDetailRepository
{

    public function getAll()
    {
        return PurchaseNoteDetail::orderBy('updated_at', 'desc')
            ->paginate();
    }


    public function findById($id)
    {
        return PurchaseNoteDetail::findOrFail($id);
    }

    public function create(array $purchaseNoteDetailData)
    {
        return PurchaseNoteDetail::create($purchaseNoteDetailData);
    }

    public function deleteByMedicamentAndPurchaseNoteId($medicamentId, $purchaseNoteId)
    {
        return PurchaseNoteDetail::where('medicament_id', $medicamentId)
            ->where('purchase_note_id', $purchaseNoteId)
            ->delete();
    }

    public function update($id, array $data)
    {
        return PurchaseNoteDetail::where('id', $id)->update($data);
    }

    public function findByPurchaseNoteId($purchaseNoteId)
    {
        return PurchaseNoteDetail::where('purchase_note_id', $purchaseNoteId)->with('medicament')->get();
    }

    public function deleteById($id)
    {
        return PurchaseNoteDetail::where('id', $id)->delete();
    }
}
